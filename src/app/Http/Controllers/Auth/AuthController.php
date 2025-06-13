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

class AuthController extends Controller
{
    /**
     * Đăng ký người dùng mới
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'display_name' => 'required|string|max:100',
            'email' => 'required|string|email|max:100|unique:Users',
            'password' => 'required|string|min:8|confirmed',
            'registration_type' => 'required|in:email,google,facebook'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Bắt đầu transaction để đảm bảo dữ liệu nhất quán
            DB::beginTransaction();
            
            // Tạo người dùng mới
            $user = User::create([
                'display_name' => $request->display_name,
                'email' => $request->email,
                'password_hash' => Hash::make($request->password),
                'registration_type' => $request->registration_type,
                'status' => 'active',
                'avatar_url' => null
            ]);

            // Trigger after_user_insert sẽ tự động tạo profile
            // Chúng ta không cần tạo thủ công profile nữa

            // Tạo token xác thực
            $token = $user->createToken('auth_token')->plainTextToken;
            
            // Commit transaction nếu mọi thứ OK
            DB::commit();

            // Lấy người dùng với profile để trả về
            $user = User::with('profile')->find($user->user_id);

            return response()->json([
                'status' => 'success',
                'message' => 'Đăng ký tài khoản thành công',
                'data' => [
                    'user' => $user,
                    'token' => $token
                ]
            ], 201);
        } catch (\Exception $e) {
            // Rollback transaction nếu có lỗi
            DB::rollBack();
            
            // Ghi log lỗi
            Log::error('Lỗi đăng ký: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Có lỗi xảy ra khi đăng ký tài khoản',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
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
     * Tạo URL xác thực Google và trả về cho frontend
     */
public function getGoogleAuthUrl()
{
    try {
        // Kiểm tra xem có thể tạo instance Socialite không
        if (!class_exists('Laravel\Socialite\Facades\Socialite')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Laravel Socialite chưa được cài đặt'
            ], 500);
        }

        // Debug thông tin cấu hình
        $clientId = config('services.google.client_id');
        $clientSecret = config('services.google.client_secret');
        $redirectUri = config('services.google.redirect');
        
        if (!$clientId || !$clientSecret || !$redirectUri) {
            return response()->json([
                'status' => 'error',
                'message' => 'Thiếu cấu hình OAuth',
                'debug_info' => [
                    'client_id_exists' => !empty($clientId),
                    'client_secret_exists' => !empty($clientSecret),
                    'redirect_uri_exists' => !empty($redirectUri)
                ]
            ], 500);
        }

        // Thử tạo URL trực tiếp thay vì dùng facade
        $url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'email profile',
            'access_type' => 'online'
        ]);

        return response()->json([
            'status' => 'success',
            'url' => $url
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Không thể tạo URL xác thực Google',
            'error' => $e->getMessage()
        ], 500);
    }
}

public function getFacebookAuthUrl()
{
    try {
        $clientId = config('services.facebook.client_id');
        $redirectUri = config('services.facebook.redirect');
        
        // Thay đổi từ v13.0 thành v18.0 (phiên bản mới nhất hiện tại)
        $url = 'https://www.facebook.com/v18.0/dialog/oauth?' . http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'email,public_profile'  // Thêm public_profile
        ]);

        return response()->json([
            'status' => 'success',
            'url' => $url
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Không thể tạo URL xác thực Facebook',
            'error' => $e->getMessage()
        ], 500);
    }
}

    /**
     * Xử lý callback từ Google
     */
    public function handleGoogleCallback(Request $request)
    {
        try {
            // Kiểm tra lỗi từ Google
            if ($request->has('error')) {
                Log::error('Google auth error: ' . $request->error_description);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Google auth error: ' . $request->error_description
                ], 400);
            }

            // Ghi log thông tin request để debug
            Log::info('Google callback received', ['code' => $request->has('code'), 'state' => $request->has('state')]);

            // Thử lấy thông tin người dùng từ Google với debug chi tiết
            try {
                $googleUser = Socialite::driver('google')->user();
                Log::info('Google user retrieved', [
                    'id' => $googleUser->getId(),
                    'email' => $googleUser->getEmail(),
                    'name' => $googleUser->getName()
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to get Google user: ' . $e->getMessage());
                throw $e;
            }
            
            // Tìm hoặc tạo user
            $user = $this->findOrCreateUser($googleUser, 'google');
            
            // Tạo token authentication
            $token = $user->createToken('auth_token')->plainTextToken;
            
            // Cập nhật login time
            $user->last_login_at = now();
            $user->save();
            
            // Load user profile
            $user->load('profile');
            
            // Redirect to frontend
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
            $redirectUrl = "{$frontendUrl}/auth/social-callback?token={$token}&user_id={$user->user_id}";
            Log::info('Redirecting to: ' . $redirectUrl);
            
            return redirect()->to($redirectUrl);
        } catch (\Exception $e) {
            Log::error('Google callback error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            // Chuyển hướng về frontend với chi tiết lỗi để debug
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
            $message = urlencode('Lỗi: ' . $e->getMessage());
            $redirectUrl = "{$frontendUrl}/auth/social-callback?error=1&message={$message}";
            
            return redirect()->to($redirectUrl);
        }
    }

    /**
     * Xử lý callback từ Facebook
     */
    public function handleFacebookCallback(Request $request)
    {
        try {
            // Nếu có lỗi từ Facebook
            if ($request->has('error')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Đăng nhập Facebook thất bại: ' . $request->error_description
                ], 400);
            }

            // Lấy thông tin người dùng từ Facebook
            $facebookUser = Socialite::driver('facebook')
                ->user();
            
            // Tìm người dùng theo email hoặc tạo mới
            $user = $this->findOrCreateUser($facebookUser, 'facebook');
            
            // Tạo token và trả về
            $token = $user->createToken('auth_token')->plainTextToken;

            // Cập nhật thông tin đăng nhập
            $user->last_login_at = now();
            $user->save();

            // Lấy thông tin user kèm profile
            $user->load('profile');

            // Redirect URL cho frontend với token và user id
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
            $redirectUrl = "{$frontendUrl}/auth/social-callback?token={$token}&user_id={$user->user_id}";

            return redirect()->to($redirectUrl);
        } catch (\Exception $e) {
            Log::error('Lỗi xử lý callback Facebook: ' . $e->getMessage());
            
            // Redirect về frontend với thông báo lỗi
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
            $redirectUrl = "{$frontendUrl}/auth/social-callback?error=1&message=" . urlencode('Đăng nhập không thành công');
            
            return redirect()->to($redirectUrl);
        }
    }

    /**
     * Xử lý đăng nhập xã hội trực tiếp từ token (cho mobile)
     */
    /**
     * Xử lý đăng nhập xã hội trực tiếp từ token (cho mobile)
     */
public function socialLogin(Request $request)
{
    $validator = Validator::make($request->all(), [
        'provider' => 'required|string|in:google,facebook',
        'token' => 'required|string',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'message' => 'Dữ liệu không hợp lệ',
            'errors' => $validator->errors()
        ], 422);
    }

    try {
        $provider = $request->provider;
        $token = $request->token;
        $socialUser = null;
        
        // Xử lý theo từng provider
        if ($provider === 'google') {
            // Gọi trực tiếp API Google thay vì dùng Socialite
            $response = Http::get('https://www.googleapis.com/oauth2/v3/userinfo', [
                'access_token' => $token
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                
                // Tạo đối tượng user tự định nghĩa
                $socialUser = (object) [
                    'id' => $data['sub'] ?? null,
                    'email' => $data['email'] ?? null,
                    'name' => $data['name'] ?? null,
                    'avatar' => $data['picture'] ?? null,
                ];
            } else {
                // Thử với cách xác thực ID token
                try {
                    // Cần cài gói này: composer require google/apiclient
                    $client = new \Google_Client(['client_id' => config('services.google.client_id')]);
                    $payload = $client->verifyIdToken($token);
                    
                    if ($payload) {
                        $socialUser = (object) [
                            'id' => $payload['sub'] ?? null,
                            'email' => $payload['email'] ?? null,
                            'name' => $payload['name'] ?? null,
                            'avatar' => $payload['picture'] ?? null,
                        ];
                    }
                } catch (\Exception $e) {
                    Log::error('Google ID token verification failed: ' . $e->getMessage());
                }
            }
        } elseif ($provider === 'facebook') {
            // Gọi trực tiếp API Facebook
            $response = Http::get('https://graph.facebook.com/v13.0/me', [
                'fields' => 'id,name,email,picture.width(200)',
                'access_token' => $token
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                
                // Tạo đối tượng user tự định nghĩa
                $socialUser = (object) [
                    'id' => $data['id'] ?? null,
                    'email' => $data['email'] ?? null,
                    'name' => $data['name'] ?? null,
                    'avatar' => isset($data['picture']['data']['url']) ? $data['picture']['data']['url'] : null,
                ];
            }
        }

        // Kiểm tra xem có lấy được thông tin người dùng không
        if (!$socialUser || empty($socialUser->email)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không thể lấy thông tin từ token hoặc thiếu email',
                'data' => $socialUser // Trả về để debug
            ], 401);
        }

        // Thêm các phương thức getter để tương thích với code hiện tại
        $socialUser->getEmail = function() use ($socialUser) {
            return $socialUser->email;
        };
        $socialUser->getName = function() use ($socialUser) {
            return $socialUser->name;
        };
        $socialUser->getAvatar = function() use ($socialUser) {
            return $socialUser->avatar;
        };

        // Tìm hoặc tạo người dùng
        $user = $this->findOrCreateUser($socialUser, $provider);
        
        // Tạo token xác thực
        $authToken = $user->createToken('auth_token')->plainTextToken;

        // Cập nhật thông tin đăng nhập
        $user->last_login_at = now();
        $user->save();

        // Lấy thông tin user kèm profile
        $user->load('profile');

        return response()->json([
            'status' => 'success',
            'message' => 'Đăng nhập thành công',
            'data' => [
                'user' => $user,
                'token' => $authToken
            ]
        ]);
    } catch (\Exception $e) {
        Log::error('Lỗi đăng nhập xã hội: ' . $e->getMessage());
        
        return response()->json([
            'status' => 'error',
            'message' => 'Có lỗi xảy ra khi đăng nhập',
            'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
        ], 500);
    }
}

    /**
     * Xử lý đăng nhập với ID token từ Google (đặc biệt hữu ích cho mobile)
     */
    public function googleIdTokenLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error', 
                'message' => 'ID token không hợp lệ'
            ], 422);
        }

        try {
            $client = new \Google_Client(['client_id' => config('services.google.client_id')]);
            $payload = $client->verifyIdToken($request->id_token);
            
            if (!$payload) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'ID token không hợp lệ hoặc đã hết hạn'
                ], 401);
            }
            
            // Tạo đối tượng user từ payload
            $socialUser = new \stdClass();
            $socialUser->id = $payload['sub'];
            $socialUser->name = $payload['name'] ?? '';
            $socialUser->email = $payload['email'] ?? '';
            $socialUser->avatar = $payload['picture'] ?? null;
            
            // Thêm các phương thức getter để tương thích với interface của Socialite
            $socialUser->getEmail = function() use ($socialUser) {
                return $socialUser->email;
            };
            $socialUser->getName = function() use ($socialUser) {
                return $socialUser->name;
            };
            $socialUser->getAvatar = function() use ($socialUser) {
                return $socialUser->avatar;
            };
            
            // Tìm hoặc tạo user
            $user = $this->findOrCreateUser($socialUser, 'google');
            
            // Tạo token xác thực
            $authToken = $user->createToken('auth_token')->plainTextToken;
            
            // Cập nhật thông tin đăng nhập
            $user->last_login_at = now();
            $user->save();
            
            // Lấy thông tin user kèm profile
            $user->load('profile');
            
            return response()->json([
                'status' => 'success',
                'message' => 'Đăng nhập thành công',
                'data' => [
                    'user' => $user,
                    'token' => $authToken
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi đăng nhập với Google ID token: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Có lỗi xảy ra khi đăng nhập',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Tìm người dùng theo email hoặc tạo mới
     */
    private function findOrCreateUser($socialUser, $provider)
    {
        // Bắt đầu transaction
        DB::beginTransaction();
        
        try {
            // Đảm bảo có email
            $email = $socialUser->getEmail() ?? ($socialUser->email ?? null);
            
            if (!$email) {
                throw new \Exception('Không thể lấy email từ tài khoản xã hội');
            }
            
            // Tìm user theo email
            $user = User::where('email', $email)->first();
            
            if (!$user) {
                // Lấy tên hiển thị
                $displayName = $socialUser->getName() ?? ($socialUser->name ?? 'User');
                
                // Lấy avatar URL
                $avatarUrl = null;
                if (method_exists($socialUser, 'getAvatar')) {
                    $avatarUrl = $socialUser->getAvatar();
                } elseif (isset($socialUser->avatar)) {
                    $avatarUrl = $socialUser->avatar;
                }
                
                // Tạo user mới nếu chưa tồn tại
                $user = User::create([
                    'display_name' => $displayName,
                    'email' => $email,
                    'password_hash' => Hash::make(Str::random(16)), // Mật khẩu ngẫu nhiên
                    'avatar_url' => $avatarUrl,
                    'registration_type' => $provider,
                    'status' => 'active'
                ]);
            } else {
                // Cập nhật thông tin nếu đăng nhập với provider khác
                if ($user->registration_type !== $provider) {
                    // Ghi log về việc user đăng nhập với provider khác
                    Log::info("User {$user->email} đăng nhập với provider {$provider} khác với provider đăng ký ban đầu {$user->registration_type}");
                }
                
                // Cập nhật avatar nếu chưa có
                if (!$user->avatar_url && (method_exists($socialUser, 'getAvatar') || isset($socialUser->avatar))) {
                    $user->avatar_url = method_exists($socialUser, 'getAvatar') ? $socialUser->getAvatar() : $socialUser->avatar;
                    $user->save();
                }
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

    public function simpleSocialLogin(Request $request)
{
    $validator = Validator::make($request->all(), [
        'email' => 'required|email',
        'name' => 'required|string',
        'provider' => 'required|string|in:google,facebook',
        'avatar_url' => 'nullable|string',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'message' => 'Dữ liệu không hợp lệ',
            'errors' => $validator->errors()
        ], 422);
    }

    try {
        // Tìm user theo email
        $user = User::where('email', $request->email)->first();
        
        // Nếu không tìm thấy, tạo mới
        if (!$user) {
            $user = User::create([
                'display_name' => $request->name,
                'email' => $request->email,
                'password_hash' => Hash::make(Str::random(16)),
                'avatar_url' => $request->avatar_url,
                'registration_type' => $request->provider,
                'status' => 'active'
            ]);
        }
        
        // Tạo token xác thực
        $token = $user->createToken('auth_token')->plainTextToken;
        
        // Cập nhật thời gian đăng nhập
        $user->last_login_at = now();
        $user->save();
        
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
        Log::error('Lỗi đăng nhập xã hội đơn giản: ' . $e->getMessage());
        
        return response()->json([
            'status' => 'error',
            'message' => 'Có lỗi xảy ra khi đăng nhập',
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