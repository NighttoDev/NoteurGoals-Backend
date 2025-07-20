<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    /**
     * Lấy thông tin profile của người dùng đang đăng nhập.
     */
    public function getProfile(Request $request)
    {
        $user = $request->user()->load('profile');

        // Đảm bảo avatar_url luôn là URL tuyệt đối khi trả về
        if ($user->avatar_url && !filter_var($user->avatar_url, FILTER_VALIDATE_URL)) {
             $user->avatar_url = asset($user->avatar_url);
        }

        return response()->json([
            'status' => 'success',
            'data' => $user,
        ]);
    }

    /**
     * Cập nhật thông tin public profile (tên, avatar).
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'display_name' => 'sometimes|required|string|max:100',
            'avatar' => 'sometimes|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // max 5MB
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => 'Dữ liệu không hợp lệ.', 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            if ($request->filled('display_name')) {
                $user->display_name = $request->input('display_name');
            }

            if ($request->hasFile('avatar')) {
                if ($user->avatar_url) {
                    $oldPath = str_replace(asset('/storage').'/', '', $user->avatar_url);
                    if (Storage::disk('public')->exists($oldPath)) {
                        Storage::disk('public')->delete($oldPath);
                    }
                }
                
                $path = $request->file('avatar')->store('avatars', 'public');
                $user->avatar_url = asset(Storage::url($path));
            }

            $user->save();
            DB::commit();

            $user->load('profile');

            return response()->json([
                'status' => 'success',
                'message' => 'Cập nhật thông tin thành công!',
                'data' => $user,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Lỗi cập nhật profile: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Có lỗi xảy ra, không thể cập nhật profile.'], 500);
        }
    }

    /**
     * Thay đổi mật khẩu của người dùng.
     */
    public function changePassword(Request $request)
    {
        $user = $request->user();

        if ($user->registration_type !== 'email') {
            return response()->json(['status' => 'error', 'message' => 'Tài khoản đăng nhập bằng mạng xã hội không thể đổi mật khẩu.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'current_password' => ['required', function ($attribute, $value, $fail) use ($user) {
                if (!Hash::check($value, $user->password_hash)) {
                    $fail('Mật khẩu hiện tại không đúng.');
                }
            }],
            'new_password' => ['required', 'string', 'min:8', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => 'Dữ liệu không hợp lệ.', 'errors' => $validator->errors()], 422);
        }

        try {
            $user->password_hash = Hash::make($request->input('new_password'));
            $user->save();

            return response()->json(['status' => 'success', 'message' => 'Đổi mật khẩu thành công!']);

        } catch (\Exception $e) {
            Log::error("Lỗi đổi mật khẩu: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Có lỗi xảy ra, không thể đổi mật khẩu.'], 500);
        }
    }

    /**
     * Xóa tài khoản người dùng.
     */
    public function deleteAccount(Request $request)
    {
        $user = $request->user();

        try {
            if ($user->avatar_url) {
                $oldPath = str_replace(asset('/storage').'/', '', $user->avatar_url);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }
            $user->tokens()->delete();
            $user->delete();

            // ĐÃ SỬA LỖI Ở ĐÂY
            return response()->json(['status' => 'success', 'message' => 'Tài khoản của bạn đã được xóa thành công.']);

        } catch (\Exception $e) {
            Log::error("Lỗi xóa tài khoản: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Có lỗi xảy ra, không thể xóa tài khoản.'], 500);
        }
    }
}