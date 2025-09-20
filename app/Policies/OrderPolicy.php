<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Order;

class OrderPolicy
{
    public function changeStatus(User $user, Order $order): bool
    {
        return $user->role === 'admin';
    }

    public function assign(User $user, Order $order): bool
    {
        return $user->role === 'admin';
    }
}

