<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSubscription extends Model
{
    protected $table = 'UserSubscriptions';
    protected $primaryKey = 'subscription_id';
    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'plan_id',
        'start_date',
        'end_date',
        'payment_status',
        'auto_renewal_id',
        'renewal_count',
        'last_renewal_date',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'last_renewal_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id', 'plan_id');
    }

    public function subscriptionPlan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id', 'plan_id');
    }
}
