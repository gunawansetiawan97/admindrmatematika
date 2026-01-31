<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\QuestionPackage;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Collection;

class CartService
{
    public function getCartItems(User $user): Collection
    {
        return Cart::where('user_id', $user->id)
            ->with('cartable')
            ->get();
    }

    public function getCartCount(User $user): int
    {
        return Cart::where('user_id', $user->id)->count();
    }

    public function getCartTotal(User $user): float
    {
        $items = $this->getCartItems($user);
        $total = 0;

        foreach ($items as $item) {
            if ($item->cartable) {
                $total += $item->cartable->price * $item->quantity;
            }
        }

        return $total;
    }

    public function addPackage(User $user, QuestionPackage $package): Cart
    {
        // Check if already in cart
        $existingCart = Cart::where('user_id', $user->id)
            ->where('cartable_type', QuestionPackage::class)
            ->where('cartable_id', $package->id)
            ->first();

        if ($existingCart) {
            return $existingCart;
        }

        return Cart::create([
            'user_id' => $user->id,
            'cartable_type' => QuestionPackage::class,
            'cartable_id' => $package->id,
            'quantity' => 1,
        ]);
    }

    public function addSubscription(User $user, Subscription $subscription): Cart
    {
        // Check if already in cart
        $existingCart = Cart::where('user_id', $user->id)
            ->where('cartable_type', Subscription::class)
            ->where('cartable_id', $subscription->id)
            ->first();

        if ($existingCart) {
            return $existingCart;
        }

        return Cart::create([
            'user_id' => $user->id,
            'cartable_type' => Subscription::class,
            'cartable_id' => $subscription->id,
            'quantity' => 1,
        ]);
    }

    public function removeItem(User $user, int $cartId): bool
    {
        return Cart::where('user_id', $user->id)
            ->where('id', $cartId)
            ->delete() > 0;
    }

    public function clearCart(User $user): bool
    {
        return Cart::where('user_id', $user->id)->delete() > 0;
    }

    public function isPackageInCart(User $user, QuestionPackage $package): bool
    {
        return Cart::where('user_id', $user->id)
            ->where('cartable_type', QuestionPackage::class)
            ->where('cartable_id', $package->id)
            ->exists();
    }

    public function isSubscriptionInCart(User $user, Subscription $subscription): bool
    {
        return Cart::where('user_id', $user->id)
            ->where('cartable_type', Subscription::class)
            ->where('cartable_id', $subscription->id)
            ->exists();
    }
}
