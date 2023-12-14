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

    public function viewTransactions(User $user, Vcard $vcard)
    {
        return $vcard->phone_number == $user->id;
    }

    public function view(User $user, Vcard $vcard)
    {
        return $vcard->phone_number == $user->id || $user->user_type == "A";
    }

    public function destroy(User $user, Vcard $vcard)
    {
        return ($vcard->phone_number == $user->id || $user->isAdmin()) && $vcard->balance == 0;
    }

    public function manageVcard(User $user, Vcard $vcard)
    {

        return $user->isAdmin() ;
    }

    public function viewAny(User $user)
    {

        return $user->isAdmin();
    }
}
