<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->user_id === (int) $id;
});

// Kênh chat riêng tư của chúng ta
Broadcast::channel('chat.{user1}.{user2}', function ($user, $user1, $user2) {
    // Ủy quyền cho người dùng nếu ID của họ khớp với một trong hai ID trên kênh
    return $user->user_id == $user1 || $user->user_id == $user2;
});