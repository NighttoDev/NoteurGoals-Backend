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
        try {
            $user = $request->user();
            $user->load('profile');
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'user' => $user
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi lấy thông tin người dùng: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Có lỗi xảy ra khi lấy thông tin người dùng',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
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
 * Xử lý callback từ Google trực tiếp không qua Socialite
 */
public function handleGoogleCallbackDirect(Request $request)
{
    try {
        // Kiểm tra lỗi từ Google
        if ($request->has('error')) {
            Log::error('Google auth error: ' . $request->error_description);
            return $this->redirectToFrontendWithError('Google auth error: ' . $request->error_description);
        }

        // Kiểm tra xem có code không
        if (!$request->has('code')) {
            return $this->redirectToFrontendWithError('No authorization code received');
        }

        // Lấy thông tin cấu hình
        $clientId = config('services.google.client_id');
        $clientSecret = config('services.google.client_secret');
        $redirectUri = config('services.google.redirect');

        // 1. Trao đổi code lấy access token
        $tokenResponse = Http::post('https://oauth2.googleapis.com/token', [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'code' => $request->code,
            'redirect_uri' => $redirectUri,
            'grant_type' => 'authorization_code'
        ]);

        if (!$tokenResponse->successful()) {
            Log::error('Failed to exchange code for token', ['response' => $tokenResponse->body()]);
            return $this->redirectToFrontendWithError('Failed to exchange code for token');
        }

        $tokenData = $tokenResponse->json();
        $accessToken = $tokenData['access_token'];

        // 2. Sử dụng access token để lấy thông tin người dùng
        $userInfoResponse = Http::withToken($accessToken)
            ->get('https://www.googleapis.com/oauth2/v3/userinfo');

        if (!$userInfoResponse->successful()) {
            Log::error('Failed to get user info', ['response' => $userInfoResponse->body()]);
            return $this->redirectToFrontendWithError('Failed to get user info');
        }

        $userData = $userInfoResponse->json();

        // 3. Tạo đối tượng user từ dữ liệu
        $socialUser = new \stdClass();
        $socialUser->id = $userData['sub'] ?? '';
        $socialUser->email = $userData['email'] ?? '';
        $socialUser->name = $userData['name'] ?? '';
        $socialUser->avatar = $userData['picture'] ?? null;

        // Kiểm tra email
        if (empty($socialUser->email)) {
            return $this->redirectToFrontendWithError('Email not provided in the Google account');
        }

        // QUAN TRỌNG: Gán các thuộc tính trực tiếp thay vì dùng closures
        $socialUser->getEmail = $socialUser->email;
        $socialUser->getName = $socialUser->name;
        $socialUser->getAvatar = $socialUser->avatar;

        // 4. Tìm hoặc tạo user - sử dụng phương thức fixed mới
        $user = $this->findOrCreateUserFixed($socialUser, 'google');

        // 5. Tạo token và thực hiện các bước đăng nhập
        $token = $user->createToken('auth_token')->plainTextToken;
        $user->last_login_at = now();
        $user->save();
        $user->load('profile');

        // 6. Redirect về frontend với token
        $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
        $redirectUrl = "{$frontendUrl}/auth/social-callback?token={$token}&user_id={$user->user_id}";
        
        return redirect()->to($redirectUrl);
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
        // Kiểm tra lỗi từ Facebook
        if ($request->has('error')) {
            Log::error('Facebook auth error: ' . $request->error_description);
            return $this->redirectToFrontendWithError('Facebook auth error: ' . $request->error_description);
        }

        // Kiểm tra xem có code không
        if (!$request->has('code')) {
            return $this->redirectToFrontendWithError('No authorization code received');
        }

        // Lấy thông tin cấu hình
        $clientId = config('services.facebook.client_id');
        $clientSecret = config('services.facebook.client_secret');
        $redirectUri = config('services.facebook.redirect');

        // 1. Trao đổi code lấy access token
        $tokenResponse = Http::get('https://graph.facebook.com/v18.0/oauth/access_token', [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'code' => $request->code,
            'redirect_uri' => $redirectUri
        ]);

        if (!$tokenResponse->successful()) {
            Log::error('Failed to exchange code for token', ['response' => $tokenResponse->body()]);
            return $this->redirectToFrontendWithError('Failed to exchange code for token');
        }

        $tokenData = $tokenResponse->json();
        $accessToken = $tokenData['access_token'];

        // 2. Sử dụng access token để lấy thông tin người dùng
        $userInfoResponse = Http::get('https://graph.facebook.com/v18.0/me', [
            'fields' => 'id,name,email,picture.width(200)',
            'access_token' => $accessToken
        ]);

        if (!$userInfoResponse->successful()) {
            Log::error('Failed to get user info', ['response' => $userInfoResponse->body()]);
            return $this->redirectToFrontendWithError('Failed to get user info');
        }

        $userData = $userInfoResponse->json();

        // 3. Tạo đối tượng user từ dữ liệu
        $socialUser = new \stdClass();
        $socialUser->id = $userData['id'] ?? '';
        $socialUser->email = $userData['email'] ?? '';
        $socialUser->name = $userData['name'] ?? '';
        $socialUser->avatar = isset($userData['picture']['data']['url']) ? $userData['picture']['data']['url'] : null;

        // Kiểm tra email
        if (empty($socialUser->email)) {
            return $this->redirectToFrontendWithError('Facebook account does not have an email. Facebook may not provide email without email permission approval.');
        }

        // QUAN TRỌNG: Gán các thuộc tính trực tiếp thay vì dùng closures
        $socialUser->getEmail = $socialUser->email;
        $socialUser->getName = $socialUser->name;
        $socialUser->getAvatar = $socialUser->avatar;

        // 4. Tìm hoặc tạo user - sửa lại phương thức findOrCreateUser để xử lý đúng
        $user = $this->findOrCreateUserFixed($socialUser, 'facebook');

        // 5. Tạo token và thực hiện các bước đăng nhập
        $token = $user->createToken('auth_token')->plainTextToken;
        $user->last_login_at = now();
        $user->save();
        $user->load('profile');

        // 6. Redirect về frontend với token
        $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
        $redirectUrl = "{$frontendUrl}/auth/social-callback?token={$token}&user_id={$user->user_id}";
        
        return redirect()->to($redirectUrl);
    } catch (\Exception $e) {
        Log::error('Facebook callback direct error: ' . $e->getMessage());
        return $this->redirectToFrontendWithError($e->getMessage());
    }
}

/**
 * Helper method để redirect về frontend với lỗi
 */
private function redirectToFrontendWithError($errorMessage)
{
    $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
    $message = urlencode($errorMessage);
    $redirectUrl = "{$frontendUrl}/auth/social-callback?error=1&message={$message}";
    return redirect()->to($redirectUrl);
}

/**
 * Phiên bản sửa đổi của findOrCreateUser để xử lý cả thuộc tính và phương thức
 */
private function findOrCreateUserFixed($socialUser, $provider)
{
    // Bắt đầu transaction
    DB::beginTransaction();
    
    try {
        // Lấy email từ thuộc tính hoặc phương thức
        $email = null;
        if (method_exists($socialUser, 'getEmail')) {
            $email = $socialUser->getEmail();
        } elseif (is_callable([$socialUser, 'getEmail'])) {
            $email = $socialUser->getEmail();
        } elseif (property_exists($socialUser, 'email')) {
            $email = $socialUser->email;
        } elseif (isset($socialUser->email)) {
            $email = $socialUser->email;
        }
        
        if (!$email) {
            throw new \Exception('Không thể lấy email từ tài khoản xã hội');
        }
        
        // Tìm user theo email
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            // Lấy tên hiển thị
            $displayName = null;
            if (method_exists($socialUser, 'getName')) {
                $displayName = $socialUser->getName();
            } elseif (is_callable([$socialUser, 'getName'])) {
                $displayName = $socialUser->getName();
            } elseif (property_exists($socialUser, 'name')) {
                $displayName = $socialUser->name;
            } elseif (isset($socialUser->name)) {
                $displayName = $socialUser->name;
            } else {
                $displayName = 'User';
            }
            
            // Lấy avatar URL
            $avatarUrl = null;
            if (method_exists($socialUser, 'getAvatar')) {
                $avatarUrl = $socialUser->getAvatar();
            } elseif (is_callable([$socialUser, 'getAvatar'])) {
                $avatarUrl = $socialUser->getAvatar();
            } elseif (property_exists($socialUser, 'avatar')) {
                $avatarUrl = $socialUser->avatar;
            } elseif (isset($socialUser->avatar)) {
                $avatarUrl = $socialUser->avatar;
            }
            
            // Tạo user mới
            $user = User::create([
                'display_name' => $displayName,
                'email' => $email,
                'password_hash' => Hash::make(Str::random(16)),
                'avatar_url' => $avatarUrl,
                'registration_type' => $provider,
                'status' => 'active'
            ]);
        }
        
        // Commit transaction
        DB::commit();
        
        return $user;
    } catch (\Exception $e) {
        // Rollback nếu có lỗi
        DB::rollBack();
        throw $e;
    }
}
}