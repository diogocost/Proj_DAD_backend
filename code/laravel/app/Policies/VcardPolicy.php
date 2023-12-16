<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vcard;
use Illuminate\Auth\Access\HandlesAuthorization;

class VcardPolicy
{
    public function updateConfirmationCode(User $user, Vcard $vcard)
    {
        return $vcard->phone_number == $user->id;
    }

    public function viewCategories(User $user, Vcard $vcard)
    {
        return $vcard->phone_number == $user->id;
    }

    public function viewTransactions(User $user, Vcard $vcard)
    {
        return $vcard->phone_number == $user->id;
    }

    public function viewAny(User $user)
    {
        return $user->isAdmin();
    }

   
    public function view(User $user, Vcard $vcard)
    {
        return $vcard->phone_number == $user->id || $user->isAdmin();
    }

    public function delete(User $user, Vcard $vcard)
    {
        return $vcard->phone_number == $user->id || $user->isAdmin();
    }

    public function manageVcard(User $user, Vcard $vcard)
    {
        // Define your logic to determine if the user can manage the vcard
        return $user->isAdmin();
    }
    
    public function showAll(User $user)
    {
        return $user->isAdmin();
    }
    
}

