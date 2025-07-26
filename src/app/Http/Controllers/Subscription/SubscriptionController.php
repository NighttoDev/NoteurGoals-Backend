<?php

namespace App\Http\Controllers\Subscription;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Models\UserSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;

class SubscriptionController extends Controller
{
    public function plans()
    {
        return response()->json(SubscriptionPlan::all());
    }

    public function mySubscriptions()
    {
        $subs = Auth::user()->subscriptions()->with('plan')->get();
        return response()->json($subs);
    }

    public function subscribe(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'plan_id' => 'required|exists:SubscriptionPlans,plan_id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $plan = SubscriptionPlan::find($request->plan_id);
        $start = Carbon::today();
        $end = Carbon::today()->addMonths($plan->duration);

        $subscription = UserSubscription::create([
            'user_id' => Auth::user()->user_id,
            'plan_id' => $plan->plan_id,
            'start_date' => $start,
            'end_date' => $end,
            'payment_status' => 'active',
        ]);

        return response()->json(['message' => 'Subscribed successfully', 'subscription' => $subscription], 201);
    }

    public function cancel(UserSubscription $subscription)
    {
        if ($subscription->user_id !== Auth::user()->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $subscription->payment_status = 'cancelled';
        $subscription->save();

        return response()->json(['message' => 'Subscription cancelled']);
    }
}
