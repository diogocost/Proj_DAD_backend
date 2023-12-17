<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Transaction;

class TransactionPolicy
{

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Transaction $transaction): bool
    {
        
        return $user->id == $transaction->vcard;
    }

    public function viewAny(User $user): bool
    {
        
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->user_type == "V" || $user->user_type == "A";
    }

    public function update(User $user, Transaction $transaction): bool
    {
        return $user->id == $transaction->vcard;
    }
}
