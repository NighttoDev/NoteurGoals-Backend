<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    protected $table = 'SubscriptionPlans';
    protected $primaryKey = 'plan_id';
    public $timestamps = true;

    protected $fillable = [
        'name',
        'description',
        'duration',
        'price',
    ];

    protected $casts = [
        'price' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
