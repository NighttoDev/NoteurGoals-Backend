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
            'display_name' => ['required','string','max:100','regex:/^(?!.*\\d).+$/u'],
            'email' => 'required|string|email|max:100|unique:Users,email',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'display_name.regex' => 'Full name does not contain numbers.',
        ]);


        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => 'Dữ liệu không hợp lệ.', 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            User::where('email', $request->email)->where('status', 'unverified')->delete();

            $otp = strval(random_int(100000, 999999));
            $user = User::create([
                'display_name' => $request->display_name,
                'email' => $request->email,
                'password_hash' => Hash::make($request->password),
                'registration_type' => 'email',
                'status' => 'unverified',
                'verification_token' => $otp,
            ]);

            // Tái sử dụng hàm gửi email chung
            $subject = "{$otp} là mã xác thực tài khoản NoteurGoals của bạn";
            $line1 = "Cảm ơn bạn đã đăng ký. Mã xác thực của bạn là: {$otp}";
            $this->sendOtpEmail($user, $subject, $line1);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Mã xác thực đã được gửi đến email của bạn. Vui lòng kiểm tra hộp thư.',
                'data' => ['email' => $user->email]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi đăng ký (OTP Flow): ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Đã có lỗi xảy ra phía máy chủ, không thể hoàn tất đăng ký.'], 500);
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
        
        $user = User::where('email', $request->email)->where('status', 'unverified')->first();

        if (!$user || $user->verification_token !== $request->otp || Carbon::parse($user->created_at)->addMinutes(10)->isPast()) {
            return response()->json(['status' => 'error', 'message' => 'Mã OTP không hợp lệ hoặc đã hết hạn.'], 400);
        }

        $user->status = 'active';
        $user->verification_token = null;
        $user->save();

        $token = $user->createToken('auth_token')->plainTextToken;
        $user->load('profile');

        return response()->json(['status' => 'success', 'message' => 'Tài khoản đã được xác thực thành công!', 'data' => ['token' => $token, 'user' => $user]]);
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
            $user->created_at = now();
            $user->save();
            
            // Tái sử dụng hàm gửi email chung
            $subject = "{$otp} là mã xác thực tài khoản NoteurGoals của bạn";
            $line1 = "Đây là mã xác thực mới của bạn: {$otp}";
            $this->sendOtpEmail($user, $subject, $line1);

            return response()->json(['status' => 'success', 'message' => 'Một mã xác thực mới đã được gửi đến email của bạn.']);
        } catch (\Exception $e) {
            Log::error('Lỗi gửi lại email OTP: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Có lỗi xảy ra khi gửi lại email.'], 500);
        }
    }


    /**
     * Hàm trợ giúp gửi email OTP
     */
    private function sendOtpEmail(User $user, string $subject, string $line1, string $line2 = null)
    {
        $messageLines = ["Xin chào {$user->display_name},", "", $line1, ""];
        if ($line2) {
            $messageLines[] = $line2;
            $messageLines[] = "";
        }
        $messageLines = array_merge($messageLines, ["Mã này sẽ hết hạn trong vòng 10 phút. Vui lòng không chia sẻ mã này với bất kỳ ai.", "", "Trân trọng,", "Đội ngũ NoteurGoals"]);
        $message = implode("\n", $messageLines);
        
        try {
            Mail::raw($message, function ($mail) use ($user, $subject) {
                $mail->to($user->email)->subject($subject);
            });
        } catch (\Exception $e) {
            Log::error("Lỗi gửi email OTP đến {$user->email}: " . $e->getMessage());
            // Ném lại lỗi để transaction có thể rollback
            throw $e;
        }
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
            'email' => 'required|email|exists:Users,email',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => 'Email không tồn tại trong hệ thống.', 'errors' => $validator->errors()], 422);
        }

        try {
            $user = User::where('email', $request->email)->first();
            
            $otp = strval(random_int(100000, 999999));
            $resetToken = Str::random(60) . time();

            $user->verification_token = $otp;
            $user->reset_token = $resetToken;
            $user->created_at = now();
            $user->save();

            // Tái sử dụng hàm gửi email chung với nội dung khác
            $subject = "Mã OTP đặt lại mật khẩu NoteurGoals của bạn là {$otp}";
            $line1 = "Bạn đã yêu cầu đặt lại mật khẩu. Mã xác thực của bạn là: {$otp}";
            $line2 = "Nếu bạn không yêu cầu, vui lòng bỏ qua email này.";
            $this->sendOtpEmail($user, $subject, $line1, $line2);

            return response()->json([
                'status' => 'success',
                'message' => 'Mã OTP để đặt lại mật khẩu đã được gửi đến email của bạn.',
                'data' => [
                    'reset_token' => $resetToken,
                    'email' => $user->email,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi quên mật khẩu: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Có lỗi xảy ra, không thể gửi email đặt lại mật khẩu.'], 500);
        }
    }

    /**
     * Đặt lại mật khẩu
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:Users,email',
            'token' => 'required|string',
            'otp' => 'required|string|min:6|max:6',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => 'Dữ liệu không hợp lệ.', 'errors' => $validator->errors()], 422);
        }

        try {
            $user = User::where('email', $request->email)
                        ->where('reset_token', $request->token)
                        ->first();
            
            if (!$user || $user->verification_token !== $request->otp || Carbon::parse($user->created_at)->addMinutes(10)->isPast()) {
                return response()->json(['status' => 'error', 'message' => 'Mã OTP hoặc token không hợp lệ, hoặc đã hết hạn.'], 400);
            }

            $user->password_hash = Hash::make($request->password);
            $user->reset_token = null;
            $user->verification_token = null;
            $user->save();

            return response()->json(['status' => 'success', 'message' => 'Đặt lại mật khẩu thành công. Bạn có thể đăng nhập ngay bây giờ.']);
        } catch (\Exception $e) {
            Log::error('Lỗi đặt lại mật khẩu: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Có lỗi xảy ra khi đặt lại mật khẩu.'], 500);
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
            // Ưu tiên OIDC userinfo endpoint để ổn định hơn
            $userInfoResponse = Http::withToken($tokenData['access_token'])->get('https://openidconnect.googleapis.com/v1/userinfo');
            if (!$userInfoResponse->successful()) {
                // Fallback sang endpoint cũ nếu cần
                $userInfoResponse = Http::withToken($tokenData['access_token'])->get('https://www.googleapis.com/oauth2/v3/userinfo');
            }
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
            // 2. Lấy thông tin cơ bản của người dùng
            $userInfoResponse = Http::get('https://graph.facebook.com/v18.0/me', [
                'fields' => 'id,name,email',
                'access_token' => $tokenData['access_token']
            ]);
            if (!$userInfoResponse->successful()) {
                throw new \Exception('Failed to get user info from Facebook');
            }
            $userData = $userInfoResponse->json();

            // 2b. Lấy avatar ổn định qua endpoint picture với redirect=0 để nhận URL JSON
            $pictureResp = Http::get('https://graph.facebook.com/v18.0/me/picture', [
                'width' => 200,
                'redirect' => 0,
                'access_token' => $tokenData['access_token']
            ]);
            $pictureJson = $pictureResp->json();
            $avatarUrl = $pictureJson['data']['url'] ?? null;

            // 3. Chuẩn hóa dữ liệu người dùng
            $socialUser = new \stdClass();
            $socialUser->id = $userData['id'] ?? null;
            $socialUser->email = $userData['email'] ?? null;
            $socialUser->name = $userData['name'] ?? 'Facebook User';
            $socialUser->avatar = $avatarUrl;
            
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
        
        // Đảm bảo lấy bản ghi mới nhất từ DB (tránh cache thuộc tính cũ)
        $user->refresh();
        $user->load('profile'); // Load profile để gửi về frontend

        // TỐI ƯU QUAN TRỌNG: Gửi cả token và user_info về frontend
        $frontendUrl = rtrim(env('FRONTEND_URL', 'http://localhost:5173'), '/');
        $callbackPath = '/auth/social-callback';

        // Mã hóa dữ liệu để an toàn trên URL
        $encodedToken = base64_encode($token);
        $encodedUser = base64_encode(json_encode($user));

        // URL-encode để an toàn khi đính vào query string
        $redirectUrl = sprintf(
            '%s%s?token=%s&user=%s',
            $frontendUrl,
            $callbackPath,
            urlencode($encodedToken),
            urlencode($encodedUser)
        );
        
        return redirect()->away($redirectUrl);
    }
    
    // TỐI ƯU: Hàm findOrCreateUser được làm gọn hơn
    private function findOrCreateUser(\stdClass $socialUser, string $provider)
    {
        return DB::transaction(function () use ($socialUser, $provider) {
            $user = User::where('email', $socialUser->email)->first();
            
            if ($user) {
                // Cập nhật avatar nếu có URL mới từ social provider
                if ($socialUser->avatar && $user->avatar_url !== $socialUser->avatar) {
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