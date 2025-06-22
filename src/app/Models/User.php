<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'Users';
    protected $primaryKey = 'user_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'display_name',
        'email',
        'password_hash',
        'avatar_url',
        'registration_type',
        'status',
        'reset_token',
        'verification_token',
        'last_login_at'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password_hash',
        'reset_token',
        'verification_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'last_login_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    /**
     * Get the password for the user.
     * Cần định nghĩa phương thức này để Laravel Auth có thể làm việc với trường password_hash
     */
    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    // Relationships
    public function profile()
    {
        return $this->hasOne(UserProfile::class, 'user_id', 'user_id');
    }

    public function goals()
    {
        return $this->hasMany(Goal::class, 'user_id', 'user_id');
    }

    public function notes()
    {
        return $this->hasMany(Note::class, 'user_id', 'user_id');
    }

    public function events()
    {
        return $this->hasMany(Event::class, 'user_id', 'user_id');
    }

    public function files()
    {
        return $this->hasMany(File::class, 'user_id', 'user_id');
    }

    public function friendships()
    {
        return $this->hasMany(Friendship::class, 'user_id_1', 'user_id');
    }

    public function friendshipRequests()
    {
        return $this->hasMany(Friendship::class, 'user_id_2', 'user_id');
    }

    public function allFriendships()
    {
        return $this->hasMany(Friendship::class, 'user_id_1', 'user_id')
            ->union($this->hasMany(Friendship::class, 'user_id_2', 'user_id'));
    }

    public function subscriptions()
    {
        return $this->hasMany(UserSubscription::class, 'user_id', 'user_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id', 'user_id');
    }

    public function aiSuggestions()
    {
        return $this->hasMany(AISuggestion::class, 'user_id', 'user_id');
    }

    public function admin()
    {
        return $this->hasOne(Admin::class, 'user_id', 'user_id');
    }

    // Helper methods
    public function isPremium()
    {
        return $this->profile && $this->profile->is_premium;
    }

    public function hasActiveSubscription()
    {
        return $this->subscriptions()
            ->where('end_date', '>', now())
            ->where('payment_status', 'active')
            ->exists();
    }

    // Friendship helper methods
    public function isFriendWith($userId)
    {
        return $this->friendships()
            ->where('user_id_2', $userId)
            ->where('status', 'accepted')
            ->exists()
        || $this->friendshipRequests()
            ->where('user_id_1', $userId)
            ->where('status', 'accepted')
            ->exists();
    }

    public function hasPendingFriendRequestWith($userId)
    {
        return $this->friendships()
            ->where('user_id_2', $userId)
            ->where('status', 'pending')
            ->exists()
        || $this->friendshipRequests()
            ->where('user_id_1', $userId)
            ->where('status', 'pending')
            ->exists();
    }

    public function getFriends()
    {
        $friendships = $this->friendships()->where('status', 'accepted')->get();
        $friendRequests = $this->friendshipRequests()->where('status', 'accepted')->get();
        
        $friendIds = collect();
        
        foreach ($friendships as $friendship) {
            $friendIds->push($friendship->user_id_2);
        }
        
        foreach ($friendRequests as $request) {
            $friendIds->push($request->user_id_1);
        }
        
        return User::whereIn('user_id', $friendIds->unique())->get();
    }

    // Status helper methods
    public function isActive()
    {
        return $this->status === 'active';
    }

    public function isBanned()
    {
        return $this->status === 'banned';
    }

    public function isVerified()
    {
        return $this->status !== 'unverified';
    }

    public function ban()
    {
        $this->status = 'banned';
        $this->save();
    }

    public function activate()
    {
        $this->status = 'active';
        $this->save();
    }
}
