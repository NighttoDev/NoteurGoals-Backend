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

        // Đảm bảo avatar_url luôn là URL tuyệt đối khi trả về (lưu trong DB dạng tương đối)
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
            'display_name' => ['sometimes','required','string','max:100','regex:/^(?!.*\\d).+$/u'],
            'avatar' => 'sometimes|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // max 5MB
        ], [
            'display_name.regex' => 'Display names that do not contain numbers.'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => 'Invalid data.', 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            if ($request->filled('display_name')) {
                $user->display_name = $request->input('display_name');
            }

            if ($request->hasFile('avatar')) {
                // Delete old avatar safely (supports absolute or relative URLs)
                if ($user->avatar_url) {
                    $pathUrl = parse_url($user->avatar_url, PHP_URL_PATH) ?: '';
                    // Convert '/storage/foo/bar.png' to 'foo/bar.png'
                    $oldRelativePath = ltrim(str_replace('/storage/', '', $pathUrl), '/');
                    if (!empty($oldRelativePath) && Storage::disk('public')->exists($oldRelativePath)) {
                        Storage::disk('public')->delete($oldRelativePath);
                    }
                }
                
                // Store new avatar and persist relative path (portable across envs)
                $path = $request->file('avatar')->store('avatars', 'public');
                $user->avatar_url = Storage::url($path); // e.g. '/storage/avatars/xxx.png'
            }

            $user->save();
            DB::commit();

            $user->load('profile');

            return response()->json([
                'status' => 'success',
                'message' => 'Information updated successfully!',
                'data' => $user,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error Updating Profile: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'An Error Occurred, Unable to update your profile.'], 500);
        }
    }

    /**
     * Thay đổi mật khẩu của người dùng.
     */
    public function changePassword(Request $request)
    {
        $user = $request->user();

        if ($user->registration_type !== 'email') {
            return response()->json(['status' => 'error', 'message' => 'Social media accounts cannot change passwords.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'current_password' => ['required', function ($attribute, $value, $fail) use ($user) {
                if (!Hash::check($value, $user->password_hash)) {
                    $fail('Current password is incorrect.');
                }
            }],
            'new_password' => ['required', 'string', 'min:8', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => 'Invalid data.', 'errors' => $validator->errors()], 422);
        }

        try {
            $user->password_hash = Hash::make($request->input('new_password'));
            $user->save();

            return response()->json(['status' => 'success', 'message' => 'Password changed successfully!']);

        } catch (\Exception $e) {
            Log::error("Error changing password: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'An error occurred, unable to change password.'], 500);
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
            return response()->json(['status' => 'success', 'message' => 'Your account has been deleted successfully.']);

        } catch (\Exception $e) {
            Log::error("Error deleting account: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'An error occurred, unable to delete account.'], 500);
        }
    }
}