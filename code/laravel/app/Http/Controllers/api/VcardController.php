<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\VcardResource;
use App\Http\Requests\UpdateUserConfirmationCodeRequest;
use App\Models\Vcard;

class VcardController extends Controller
{
    public function updatesConfirmationCode(UpdateUserConfirmationCodeRequest $request, Vcard $vcard)
    {
        $vcard->confirmation_code = bcrypt($request->validated()['confirmation_code']);
        $vcard->save();
        return new VcardResource($vcard);
    }
}
