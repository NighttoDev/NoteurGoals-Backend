<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\SubscriptionPlan;
use App\Models\UserSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    // Phương thức này của bạn đã đúng, giữ nguyên.
    public function createVnPayPayment(Request $request)
    {
        try {
            $request->validate([
                'plan_id' => 'required|exists:SubscriptionPlans,plan_id',
                'bank_code' => 'nullable|string',
            ]);

            $plan = SubscriptionPlan::find($request->plan_id);
            if (!$plan) {
                return response()->json(['message' => 'Gói đăng ký không hợp lệ.'], 404);
            }

            $user = Auth::user();
            $payment = Payment::create([
                'user_id' => $user->user_id, 'plan_id' => $plan->plan_id,
                'amount' => $plan->price, 'status' => 'pending', 'payment_date' => now(),
            ]);

            $vnp_Url = env('VNPAY_URL');
            $vnp_Returnurl = env('VNPAY_RETURN_URL');
            $vnp_TmnCode = env('VNPAY_TMN_CODE');
            $vnp_HashSecret = env('VNPAY_HASH_SECRET');

            if (!$vnp_TmnCode || !$vnp_HashSecret) {
                Log::error('VNPay configuration is missing.');
                return response()->json(['message' => 'Cổng thanh toán chưa được cấu hình.'], 500);
            }

            $vnp_TxnRef = $payment->payment_id;
            $vnp_OrderInfo = "Thanh toan goi " . $plan->name;
            $vnp_OrderType = 'billpayment';
            $vnp_Amount = $plan->price * 100;
            $vnp_Locale = 'vn';
            $vnp_BankCode = $request->bank_code ?? '';
            $vnp_IpAddr = $request->ip();
            
            $inputData = [
                "vnp_Version" => "2.1.0", "vnp_TmnCode" => $vnp_TmnCode,
                "vnp_Amount" => $vnp_Amount, "vnp_Command" => "pay",
                "vnp_CreateDate" => date('YmdHis'), "vnp_CurrCode" => "VND",
                "vnp_IpAddr" => $vnp_IpAddr, "vnp_Locale" => $vnp_Locale,
                "vnp_OrderInfo" => $vnp_OrderInfo, "vnp_OrderType" => $vnp_OrderType,
                "vnp_ReturnUrl" => $vnp_Returnurl, "vnp_TxnRef" => $vnp_TxnRef,
            ];

            if (isset($vnp_BankCode) && $vnp_BankCode != "") {
                $inputData['vnp_BankCode'] = $vnp_BankCode;
            }

            ksort($inputData);
            $query = "";
            $hashdata = "";
            foreach ($inputData as $key => $value) {
                if ($value !== null && $value !== '') {
                    $hashdata .= urlencode($key) . "=" . urlencode($value) . '&';
                    $query .= urlencode($key) . '=' . urlencode($value) . '&';
                }
            }
            $hashdata = rtrim($hashdata, '&');
            $query = rtrim($query, '&');
            $vnp_Url = $vnp_Url . "?" . $query;
            $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
            $vnp_Url .= '&vnp_SecureHash=' . $vnpSecureHash;
            return response()->json(['payment_url' => $vnp_Url]);
        } catch (\Exception $e) {
            Log::error('VNPay Creation Error: ' . $e->getMessage() . ' on line ' . $e->getLine());
            return response()->json(['message' => 'Lỗi khi tạo link thanh toán.'], 500);
        }
    }

    // *** PHƯƠNG THỨC MỚI ĐƯỢC THÊM VÀO ĐỂ GIẢI QUYẾT VẤN ĐỀ CHỜ IPN ***
    /**
     * Xác thực dữ liệu trả về từ VNPay (do người dùng mang về)
     * và cấp quyền nếu hợp lệ. Được gọi bởi frontend.
     */
    public function verifyReturnData(Request $request)
    {
        $vnp_HashSecret = env('VNPAY_HASH_SECRET');
        $inputData = $request->all();
        
        if (empty($inputData) || !isset($inputData['vnp_SecureHash'])) {
            return response()->json(['message' => 'Dữ liệu không hợp lệ.'], 400);
        }

        $vnp_SecureHash = $inputData['vnp_SecureHash'];
        unset($inputData['vnp_SecureHash']);

        ksort($inputData);
        $hashData = "";
        foreach ($inputData as $key => $value) {
            $hashData .= urlencode($key) . '=' . urlencode($value) . '&';
        }
        $hashData = rtrim($hashData, '&');
        
        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

        if ($secureHash != $vnp_SecureHash) {
            return response()->json(['message' => 'Chữ ký không hợp lệ.'], 400);
        }

        $payment = Payment::find($inputData['vnp_TxnRef']);
        if (!$payment) {
            return response()->json(['message' => 'Không tìm thấy đơn hàng.'], 404);
        }
        
        // Chỉ xử lý nếu đơn hàng đang chờ để tránh xử lý trùng lặp
        if ($payment->status != 'pending') {
            // Nếu đã thành công rồi thì vẫn trả về 200 OK
            if ($payment->status == 'success') {
                return response()->json(['message' => 'Giao dịch đã được xác nhận thành công trước đó.']);
            }
            return response()->json(['message' => 'Đơn hàng đã được xử lý.'], 409); // 409 Conflict
        }
        
        // Kiểm tra mã giao dịch thành công
        if ($inputData['vnp_ResponseCode'] == '00') {
            $payment->status = 'success';
            $payment->save();
            $this->provisionSubscription($payment); // Cấp quyền
            return response()->json(['message' => 'Giao dịch thành công!']);
        } else {
            $payment->status = 'failed';
            $payment->save();
            return response()->json(['message' => 'Giao dịch không thành công từ VNPay.'], 400);
        }
    }
    /**
     * Xử lý callback IPN từ VNPay.
     * PHIÊN BẢN NÀY ĐÃ ĐƯỢC NÂNG CẤP ĐỂ CHẮC CHẮN HƠN.
     */
    public function handleVnPayCallback(Request $request)
    {
        $vnp_HashSecret = env('VNPAY_HASH_SECRET');
        $inputData = $request->all();
        
        if (!isset($inputData['vnp_SecureHash'])) {
            Log::warning('VNPay IPN callback missing vnp_SecureHash', $inputData);
            return response()->json(['RspCode' => '97', 'Message' => 'Invalid signature']);
        }

        $vnp_SecureHash = $inputData['vnp_SecureHash'];
        unset($inputData['vnp_SecureHash']);

        ksort($inputData);
        $hashData = "";
        foreach ($inputData as $key => $value) {
            // Tạo hash theo đúng quy tắc của VNPay
            $hashData .= urlencode($key) . '=' . urlencode($value) . '&';
        }
        $hashData = rtrim($hashData, '&');
        
        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

        if ($secureHash == $vnp_SecureHash) {
            $payment = Payment::find($inputData['vnp_TxnRef']);

            if (!$payment) {
                return response()->json(['RspCode' => '01', 'Message' => 'Order not found']);
            }
            
            // Chỉ xử lý nếu đơn hàng đang chờ
            if ($payment->status != 'pending') {
                return response()->json(['RspCode' => '02', 'Message' => 'Order already confirmed']);
            }

            // Theo tài liệu VNPay, kiểm tra cả vnp_ResponseCode và vnp_TransactionStatus
            if ($inputData['vnp_ResponseCode'] == '00' && $inputData['vnp_TransactionStatus'] == '00') {
                $payment->status = 'success';
                $this->provisionSubscription($payment); // Cấp quyền
            } else {
                $payment->status = 'failed';
            }
            $payment->save();
            
            Log::info('VNPay IPN processed for payment_id: ' . $payment->id . ' with status: ' . $payment->status);
            // Luôn trả về success để VNPay không gửi lại IPN
            return response()->json(['RspCode' => '00', 'Message' => 'Confirm Success']);

        } else {
            return response()->json(['RspCode' => '97', 'Message' => 'Invalid signature']);
        }
    }

    /**
     * Hàm helper để cấp quyền.
     * PHIÊN BẢN NÀY ĐÃ ĐƯỢC NÂNG CẤP ĐỂ CHẮC CHẮN HƠN.
     */
    private function provisionSubscription(Payment $payment)
    {
        $user = $payment->user;
        $plan = $payment->plan;

        if (!$user || !$plan) {
            Log::error("Could not provision subscription. User or Plan not found for payment ID: " . $payment->payment_id);
            return;
        }

        // Hủy các gói active cũ
        $user->subscriptions()->where('payment_status', 'active')->update(['payment_status' => 'cancelled']);

        // Tạo gói mới
        $newSub = UserSubscription::create([
            'user_id' => $user->user_id,
            'plan_id' => $plan->plan_id,
            'start_date' => Carbon::today(),
            'end_date' => Carbon::today()->addMonths($plan->duration),
            'payment_status' => 'active', // CHẮC CHẮN LÀ 'active'
        ]);

        Log::info("Provisioned new subscription ID {$newSub->subscription_id} for user ID {$user->user_id}");
    }
}