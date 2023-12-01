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
}
