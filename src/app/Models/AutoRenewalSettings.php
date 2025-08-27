<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutoRenewalSettings extends Model
{
    protected $table = 'AutoRenewalSettings';
    protected $primaryKey = 'setting_id';
    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'renewal_plan_id',
        // Optional columns may exist depending on schema (e.g., is_enabled)
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'renewal_plan_id', 'plan_id');
    }
}
