<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

/**
 * Order ownership rules. Admin access to every order is enforced by the
 * "admin" route middleware instead of being repeated here.
 */
class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Order $order): bool
    {
        return $this->owns($user, $order);
    }

    public function cancel(User $user, Order $order): bool
    {
        return $this->owns($user, $order) && $order->isCancellable();
    }

    private function owns(User $user, Order $order): bool
    {
        return $order->user_id === $user->id;
    }
}
