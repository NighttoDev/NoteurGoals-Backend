<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class AuthController extends Controller
{
    /**
     * Đăng ký người dùng mới với xác thực email
     */
     public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'display_name' => 'required|string|max:100',
            'email' => 'required|string|email|max:100|unique:Users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Trả về JSON lỗi tường minh nếu validation thất bại
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Dữ liệu không hợp lệ.',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Xóa các tài khoản chưa xác thực có cùng email để người dùng có thể thử lại
            User::where('email', $request->email)
                ->where('status', 'unverified')
                ->delete();

            // Tạo mã OTP
            $otp = strval(random_int(100000, 999999));

            // Tạo User mới với status 'unverified'
            $user = User::create([
                'display_name' => $request->display_name,
                'email' => $request->email,
                'password_hash' => Hash::make($request->password),
                'registration_type' => 'email', // Luôn là 'email' cho luồng này
                'status' => 'unverified',
                'verification_token' => $otp,
            ]);

            // Gửi email chứa mã OTP
            $this->sendOtpEmail($user, $otp);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Mã xác thực đã được gửi đến email của bạn. Vui lòng kiểm tra hộp thư.',
                'data' => ['email' => $user->email]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi đăng ký (OTP Flow): ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Đã có lỗi xảy ra phía máy chủ, không thể hoàn tất đăng ký.'
            ], 500);
        }
    }

    /**
     * [HOÀN CHỈNH] Xác thực email bằng OTP
     */
    public function verifyEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:Users,email',
            'otp' => 'required|string|min:6|max:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => 'Dữ liệu không hợp lệ.', 'errors' => $validator->errors()], 422);
        }
        
        $user = User::where('email', $request->email)
                    ->where('status', 'unverified')
                    ->first();

        // Kiểm tra OTP có đúng không và có còn hiệu lực không (10 phút)
        if (!$user || $user->verification_token !== $request->otp || Carbon::parse($user->created_at)->addMinutes(10)->isPast()) {
            return response()->json(['status' => 'error', 'message' => 'Mã OTP không hợp lệ hoặc đã hết hạn.'], 400);
        }

        // Kích hoạt tài khoản thành công
        $user->status = 'active';
        $user->verification_token = null;
        // $user->email_verified_at = now();
        $user->save();

        // Tạo token đăng nhập tự động
        $token = $user->createToken('auth_token')->plainTextToken;
        $user->load('profile'); // Load thêm thông tin profile nếu cần

        return response()->json([
            'status' => 'success',
            'message' => 'Tài khoản đã được xác thực thành công!',
            'data' => ['token' => $token, 'user' => $user]
        ]);
    }

    /**
     * [HOÀN CHỈNH] Gửi lại email xác thực
     */
    public function resendVerificationEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [ 'email' => 'required|email|exists:Users,email' ]);
        if ($validator->fails()) { return response()->json(['status' => 'error', 'message' => 'Dữ liệu không hợp lệ.', 'errors' => $validator->errors()], 422); }
        
        $user = User::where('email', $request->email)->where('status', 'unverified')->first();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Tài khoản này không tồn tại hoặc đã được xác thực.'], 400);
        }
        
        try {
            $otp = strval(random_int(100000, 999999));
            $user->verification_token = $otp;
            $user->created_at = now(); // Reset lại thời gian để tính giờ hết hạn OTP mới
            $user->save();
            
            $this->sendOtpEmail($user, $otp);

            return response()->json(['status' => 'success', 'message' => 'Một mã xác thực mới đã được gửi đến email của bạn.']);
        } catch (\Exception $e) {
            Log::error('Lỗi gửi lại email OTP: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Có lỗi xảy ra khi gửi lại email.'], 500);
        }
    }

    /**
     * Hàm trợ giúp gửi email OTP
     */
    private function sendOtpEmail(User $user, string $otp)
    {
        $subject = "{$otp} là mã xác thực tài khoản NoteurGoals của bạn";
        $message = "Xin chào {$user->display_name},\n\n"
                 . "Cảm ơn bạn đã đăng ký. Mã xác thực của bạn là: {$otp}\n\n"
                 . "Mã này sẽ hết hạn trong vòng 10 phút. Vui lòng không chia sẻ mã này với bất kỳ ai.\n\n"
                 . "Trân trọng,\n"
                 . "Đội ngũ NoteurGoals";
        
        Mail::raw($message, function ($mail) use ($user, $subject) {
            $mail->to($user->email)->subject($subject);
        });
    }
    /**
     * Đăng nhập người dùng
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Tìm người dùng theo email
            $user = User::where('email', $request->email)->first();

            // Kiểm tra người dùng tồn tại và mật khẩu đúng
            if (!$user || !Hash::check($request->password, $user->password_hash)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Email hoặc mật khẩu không đúng'
                ], 401);
            }

            // Kiểm tra trạng thái xác thực
            if ($user->status === 'unverified') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tài khoản chưa được xác thực. Vui lòng kiểm tra email để xác thực tài khoản.',
                    'verification_required' => true,
                    'user_email' => $user->email
                ], 403);
            }

            // Kiểm tra trạng thái tài khoản
            if ($user->status === 'banned') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ hỗ trợ.'
                ], 403);
            }

            // Cập nhật thời gian đăng nhập
            $user->last_login_at = now();
            $user->save();

            // Tạo token xác thực
            $token = $user->createToken('auth_token')->plainTextToken;

            // Lấy profile của user
            $user->load('profile');

            return response()->json([
                'status' => 'success',
                'message' => 'Đăng nhập thành công',
                'data' => [
                    'user' => $user,
                    'token' => $token
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi đăng nhập: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Có lỗi xảy ra khi đăng nhập',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Lấy thông tin người dùng hiện tại
     */
    public function me(Request $request)
    {
       $user = $request->user()->load('profile'); // Load relationship nếu cần
        
        return response()->json([
            'status' => 'success',
            'data' => [
                'user' => $user
            ]
        ]);
    }

    /**
     * Đăng xuất người dùng
     */
    public function logout(Request $request)
    {
        try {
            // Xóa token hiện tại
            $request->user()->currentAccessToken()->delete();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Đăng xuất thành công'
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi đăng xuất: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Có lỗi xảy ra khi đăng xuất',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Yêu cầu đặt lại mật khẩu
     */
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:Users,email'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email không hợp lệ hoặc không tồn tại',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Tạo mã reset token
            $token = Str::random(60);
            
            $user = User::where('email', $request->email)->first();
            $user->reset_token = $token;
            $user->save();

            // TODO: Gửi email đặt lại mật khẩu
            // Ở đây cần tích hợp với hệ thống email

            return response()->json([
                'status' => 'success',
                'message' => 'Đã gửi email hướng dẫn đặt lại mật khẩu'
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi quên mật khẩu: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Có lỗi xảy ra khi xử lý yêu cầu đặt lại mật khẩu',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Đặt lại mật khẩu
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::where('reset_token', $request->token)->first();
            
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Mã xác thực không hợp lệ hoặc đã hết hạn'
                ], 400);
            }

            $user->password_hash = Hash::make($request->password);
            $user->reset_token = null;
            $user->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Đặt lại mật khẩu thành công'
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi đặt lại mật khẩu: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Có lỗi xảy ra khi đặt lại mật khẩu',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Tạo URL xác thực Google và trả về cho frontend
     */

    // public function getGoogleAuthUrl()
    // {
    //     return response()->json([
    //         'status' => 'error',
    //         'message' => 'Google OAuth not implemented yet',
    //         'url' => null
    //     ], 501);
    // }

    // /**
    //  * Get Facebook OAuth URL (commented out implementation)
    //  */
    // public function getFacebookAuthUrl()
    // {
    //     return response()->json([
    //         'status' => 'error',
    //         'message' => 'Facebook OAuth not implemented yet',
    //         'url' => null
    //     ], 501);
    // }

 /**
     * Xử lý callback từ Google trực tiếp không qua Socialite
     */
    public function handleGoogleCallbackDirect(Request $request)
    {
        try {
            if ($request->has('error')) {
                throw new \Exception('Google auth error: ' . $request->error_description);
            }
            if (!$request->has('code')) {
                throw new \Exception('No authorization code received from Google');
            }

            // 1. Trao đổi code lấy access token
            $tokenData = $this->exchangeCodeForToken('google', $request->code);
            
            // 2. Sử dụng access token để lấy thông tin người dùng
            $userInfoResponse = Http::withToken($tokenData['access_token'])->get('https://www.googleapis.com/oauth2/v3/userinfo');
            if (!$userInfoResponse->successful()) {
                throw new \Exception('Failed to get user info from Google');
            }
            $userData = $userInfoResponse->json();

            // 3. Chuẩn hóa dữ liệu người dùng
            $socialUser = new \stdClass();
            $socialUser->id = $userData['sub'] ?? null;
            $socialUser->email = $userData['email'] ?? null;
            $socialUser->name = $userData['name'] ?? 'Google User';
            $socialUser->avatar = $userData['picture'] ?? null;

            // 4. Xử lý đăng nhập hoặc đăng ký và redirect
            return $this->processSocialLogin($socialUser, 'google');

        } catch (\Exception $e) {
            Log::error('Google callback direct error: ' . $e->getMessage());
            return $this->redirectToFrontendWithError($e->getMessage());
        }
    }

    /**
     * Xử lý callback từ Facebook trực tiếp không qua Socialite
     */
    public function handleFacebookCallbackDirect(Request $request)
    {
        try {
            if ($request->has('error')) {
                throw new \Exception('Facebook auth error: ' . $request->error_description);
            }
            if (!$request->has('code')) {
                throw new \Exception('No authorization code received from Facebook');
            }

            // 1. Trao đổi code lấy access token
            $tokenData = $this->exchangeCodeForToken('facebook', $request->code);
            
            // 2. Sử dụng access token để lấy thông tin người dùng
            $userInfoResponse = Http::get('https://graph.facebook.com/v18.0/me', [
                'fields' => 'id,name,email,picture.width(200)',
                'access_token' => $tokenData['access_token']
            ]);
            if (!$userInfoResponse->successful()) {
                throw new \Exception('Failed to get user info from Facebook');
            }
            $userData = $userInfoResponse->json();

            // 3. Chuẩn hóa dữ liệu người dùng
            $socialUser = new \stdClass();
            $socialUser->id = $userData['id'] ?? null;
            $socialUser->email = $userData['email'] ?? null;
            $socialUser->name = $userData['name'] ?? 'Facebook User';
            $socialUser->avatar = $userData['picture']['data']['url'] ?? null;
            
            // 4. Xử lý đăng nhập hoặc đăng ký và redirect
            return $this->processSocialLogin($socialUser, 'facebook');

        } catch (\Exception $e) {
            Log::error('Facebook callback direct error: ' . $e->getMessage());
            return $this->redirectToFrontendWithError($e->getMessage());
        }
    }
    
    // TỐI ƯU: Tách hàm trao đổi code lấy token để tái sử dụng
    private function exchangeCodeForToken(string $provider, string $code)
    {
        $config = config("services.{$provider}");
        $url = '';
        $params = [
            'client_id' => $config['client_id'],
            'client_secret' => $config['client_secret'],
            'code' => $code,
            'redirect_uri' => $config['redirect'],
        ];

        if ($provider === 'google') {
            $url = 'https://oauth2.googleapis.com/token';
            $params['grant_type'] = 'authorization_code';
            $response = Http::asForm()->post($url, $params);
        } elseif ($provider === 'facebook') {
            $url = 'https://graph.facebook.com/v18.0/oauth/access_token';
            $response = Http::get($url, $params);
        } else {
            throw new \Exception("Provider {$provider} is not supported.");
        }

        if (!$response->successful()) {
            Log::error("Failed to exchange code for token with {$provider}", ['response' => $response->body()]);
            throw new \Exception("Failed to get access token from {$provider}.");
        }

        return $response->json();
    }

    // TỐI ƯU: Tách logic xử lý social login chung
    private function processSocialLogin(\stdClass $socialUser, string $provider)
    {
        if (empty($socialUser->email)) {
            throw new \Exception("Email is not provided by {$provider}.");
        }

        // Tìm hoặc tạo user
        $user = $this->findOrCreateUser($socialUser, $provider);

        // Tạo token, cập nhật login time
        $token = $user->createToken('auth_token')->plainTextToken;
        $user->last_login_at = now();
        $user->save();
        $user->load('profile'); // Load profile để gửi về frontend

        // TỐI ƯU QUAN TRỌNG: Gửi cả token và user_info về frontend
        $frontendUrl = rtrim(env('FRONTEND_URL', 'http://localhost:5173'), '/');
        $callbackPath = '/auth/social-callback';

        // Mã hóa dữ liệu để an toàn trên URL
        $encodedToken = base64_encode($token);
        $encodedUser = base64_encode(json_encode($user));

        $redirectUrl = "{$frontendUrl}{$callbackPath}?token={$encodedToken}&user={$encodedUser}";
        
        return redirect()->away($redirectUrl);
    }
    
    // TỐI ƯU: Hàm findOrCreateUser được làm gọn hơn
    private function findOrCreateUser(\stdClass $socialUser, string $provider)
    {
        return DB::transaction(function () use ($socialUser, $provider) {
            $user = User::where('email', $socialUser->email)->first();
            
            if ($user) {
                // Nếu user đã tồn tại, cập nhật avatar và loại đăng nhập nếu cần
                if (!$user->avatar_url && $socialUser->avatar) {
                    $user->avatar_url = $socialUser->avatar;
                }
                // Nếu user đăng ký bằng email trước đó, có thể cập nhật `registration_type`
                // $user->registration_type = $user->registration_type . ',' . $provider;
                $user->save();
                return $user;
            }

            // Tạo user mới nếu chưa tồn tại
            return User::create([
                'display_name' => $socialUser->name,
                'email' => $socialUser->email,
                'password_hash' => Hash::make(Str::random(24)), // Mật khẩu ngẫu nhiên
                'avatar_url' => $socialUser->avatar,
                'registration_type' => $provider,
                'status' => 'active', // Kích hoạt ngay lập tức
                // 'email_verified_at' => now(), // Đánh dấu đã xác thực email
            ]);
        });
    }

    /**
     * Helper method để redirect về frontend với lỗi
     */
    private function redirectToFrontendWithError($errorMessage)
    {
        $frontendUrl = rtrim(env('FRONTEND_URL', 'http://localhost:5173'), '/');
        // TỐI ƯU: Gửi lỗi về trang login để hiển thị
        $loginPath = '/login'; 
        $message = urlencode($errorMessage);
        $redirectUrl = "{$frontendUrl}{$loginPath}?error_social=1&message={$message}";
        return redirect()->away($redirectUrl);
    }
}