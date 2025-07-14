<?php

namespace App\Policies;

use App\Models\User;
use App\Models\User\Order;
use Illuminate\Auth\Access\Response;

class OrderPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Order $order): bool
    {
        // Izinkan pengguna melihat pesanan JIKA user_id di pesanan
        // sama dengan id pengguna yang sedang login.
        return $user->id === $order->user_id;
    }

    public function confirmDelivery(User $user, Order $order): bool
{
    // Ini mengembalikan TRUE hanya jika ID user yang login SAMA DENGAN user_id di pesanan
    return $user->id === $order->user_id;
}

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Order $order): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Order $order): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Order $order): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Order $order): bool
    {
        return false;
    }
}
