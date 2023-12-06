<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vcard;

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
        
        return $vcard->balance == 0 && $user->isAdmin();
    }
    

    public function block(User $user)
    {
        return $user->isAdmin();
    }

    public function unblock(User $user)
    {
        return $user->isAdmin();
    }

    public function updateMaxDebit(User $user)
    {
        return $user->isAdmin();
    }

    
}

