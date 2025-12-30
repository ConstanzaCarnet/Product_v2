<?php
namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->role === 'admin';
    }
    public function update(User $user, Product $product): bool
    {
        return $user->role === 'admin';
    }
}



