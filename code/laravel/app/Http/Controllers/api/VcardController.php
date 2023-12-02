<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DeleteVcardRequest;
use Illuminate\Http\Request;
use App\Http\Resources\VcardResource;
use App\Http\Requests\UpdateUserConfirmationCodeRequest;
use App\Models\Vcard;

class VcardController extends Controller
{
    public function updatesConfirmationCode(UpdateUserConfirmationCodeRequest $request, Vcard $vcard)
    {
        $password = bcrypt($request->current_password);

        // Check if the password is correct
        if ($password != $vcard->password) {
            return response()->json(['messages' => 'The password field is incorrect!'], 403);
        }
        
        try {
            $vcard->confirmation_code = bcrypt($request->validated()['confirmation_code']);
            $vcard->save();
            return new VcardResource($vcard);
        } catch (\Exception $ex) {
            return response()->json(['message' => 'Error updating confirmation code'], 500);
        }
    }

    public function show(Vcard $vcard)
    {
        return new VcardResource($vcard);
    }

    public function destroy(DeleteVcardRequest $request, Vcard $vcard)
    {
        $password = bcrypt($request->password);
        $confirmation_code = bcrypt($request->confirmation_code);
        $messages = [];

        // Check if the password is correct
        if ($password != $vcard->password) {
            $messages['password'] = 'The password field is incorrect!';
        }

        // Check if the confirmation code is provided and valid
        if ($confirmation_code != $vcard->confirmation_code) {
            $messages['confirmation_code'] = 'The confirmation code field is incorrect!';
        }

        if (!empty($messages)) {
            return response()->json(['messages' => $messages], 403);
        }

        try {
            if($vcard->transactions()->count() > 0){
                $vcard->transactions()->delete();
                $vcard->categories()->delete();
                $vcard->delete();
            }else{
                $vcard->categories()->forceDelete();
                $vcard->forceDelete();
            }
            return response()->json(['message' => 'Vcard deleted successfully'], 200);
        } catch (\Exception $ex) {
            return response()->json(['message' => 'Error deleting vcard'], 500);
        }
    }
}
