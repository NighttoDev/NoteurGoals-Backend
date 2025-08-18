<?php

namespace App\Http\Controllers\Subscription;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Models\UserSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class SubscriptionController extends Controller
{
    /**
     * Lấy tất cả các gói đăng ký có sẵn.
     * GET /api/subscriptions/plans
     */
    public function plans()
    {
        return response()->json(SubscriptionPlan::all());
    }

    // *** MỚI: Thêm phương thức show() bị thiếu ***
    /**
     * Lấy thông tin chi tiết của một gói đăng ký.
     * GET /api/subscriptions/plans/{planId}
     */
    public function show($planId)
    {
        try {
            // Tìm plan theo ID, nếu không thấy sẽ tự động báo lỗi
            $plan = SubscriptionPlan::findOrFail($planId); 
            
            return response()->json($plan);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Trả về lỗi 404 chuẩn nếu không tìm thấy plan
            return response()->json(['message' => 'Subscription plan not found.'], 404);
        } catch (\Exception $e) {
            // Ghi lại các lỗi bất ngờ khác
            Log::error('Error in SubscriptionController@show for plan ID ' . $planId . ': ' . $e->getMessage());
            return response()->json(['message' => 'An internal server error occurred.'], 500);
        }
    }


    /**
     * Lấy gói đăng ký đang hoạt động hiện tại của người dùng.
     * GET /api/subscriptions/my-current
     */
    public function myCurrentSubscription()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            $subscription = UserSubscription::where('user_id', $user->user_id)
                ->where('payment_status', 'active')
                ->with('plan')
                ->latest('created_at')
                ->first();

            return response()->json($subscription);

        } catch (\Exception $e) {
            Log::error('Error in myCurrentSubscription: ' . $e->getMessage());
            return response()->json(['message' => 'An internal server error occurred.'], 500);
        }
    }


    /**
     * Hủy một gói đăng ký.
     * POST /api/subscriptions/cancel/{subscription}
     */
    public function cancel(UserSubscription $subscription)
    {
        $user = Auth::user();
        if ($subscription->user_id !== $user->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Nếu bản ghi được truyền vào không ở trạng thái active thì trả lỗi rõ ràng
        if ($subscription->payment_status !== 'active') {
            return response()->json(['message' => 'This subscription is not active or already cancelled.'], 422);
        }

        // Hủy tất cả các subscription đang active của user để tránh dữ liệu "mồ côi"
        UserSubscription::where('user_id', $user->user_id)
            ->where('payment_status', 'active')
            ->update(['payment_status' => 'cancelled']);

        // Tải lại bản ghi vừa hủy để trả về cho client
        $subscription->refresh()->load('plan');

        return response()->json([
            'message' => 'Subscription cancelled successfully.',
            'subscription' => $subscription,
        ]);
    }

}