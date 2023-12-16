<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use App\Http\Requests\CreateUserRequest;
use App\Http\Resources\AdminResource;
use App\Http\Resources\VcardResource;
use App\Models\Admin;
use App\Models\User;
use App\Models\Vcard;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\UpdateUserPasswordRequest;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Support\Facades\Auth;
use App\Services\Base64Services;
use GuzzleHttp\Psr7\Message;

class UserController extends Controller
{
    private function storeBase64AsFile(Vcard $vcard, String $base64String)
    {
        $targetDir = storage_path('app/public/fotos');
        $newfilename = $vcard->phone_number . "_" . rand(1000,9999);
        $base64Service = new Base64Services();
        return $base64Service->saveFile($base64String, $targetDir, $newfilename);
    }
    public function show_me(Request $request)
    {
        return new UserResource($request->user());
    }

    public function store(CreateUserRequest $request)
    {
        try {
            $user = Auth::guard('api')->user();
            if($user){
                if($request->has('base64ImagePhoto') || $request->has('confirmation_code')){
                    return response()->json(['message' => 'Admins cannot create vCards'], 403);
                }
                $admin = new Admin();
                $admin->name = $request->name;
                $admin->email = $request->email;
                $admin->password = Hash::make($request->password);
                $admin->save();
                return new AdminResource($admin);
            }else{
                $vcard = new Vcard();
                $vcard->phone_number = $request->phone_number;
                $vcard->name = $request->name;
                $vcard->email = $request->email;
                $vcard->password = Hash::make($request->password);
                $vcard->confirmation_code = Hash::make($request->confirmation_code);
                if($request->has('base64ImagePhoto')){
                    if($request->base64ImagePhoto == null){
                        $vcard->photo_url = null;
                    }else{
                        $vcard->photo_url = $this->storeBase64AsFile($vcard, $request->base64ImagePhoto);
                    }
                }
                $vcard->blocked = 0;
                $vcard->balance = 0;
                $vcard->max_debit = 5000;
                $vcard->save();
                $vcard = Vcard::find($request->phone_number);
                $vcard->initializeDefaultCategories();
                return new VcardResource($vcard);
            }
        }catch(\Exception $ex){
            if ($ex->getCode() == 23000)
                return response()->json(['message' => 'User already exists'], 500);
            else
            return response()->json(['message' => 'Error creating user'], 500);
        }
    }

    public function update_password(UpdateUserPasswordRequest $request, User $user)
    {
        // Check if the password is correct
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
            'errors' => [
                'current_password' => ['The current password field is incorrect!']
            ]
        ], 422);
        }
        
        try{
            if($user->isAdmin()){
                $admin = Admin::find($user->id);
                $admin->password = Hash::make($request->validated()['password']);
                $admin->save();
                return new AdminResource($admin);
            }else{
                $vcard = Vcard::find($user->id);
                $vcard->password = Hash::make($request->validated()['password']);
                $vcard->save();
                return new VcardResource($vcard);
            }
        }catch(\Exception $ex){
            return response()->json(['message' => 'Error updating password'], 500);
        }
    }

    public function update(UpdateUserRequest $request, User $user){
        try{
            if($user->isAdmin()){
                $admin = Admin::find($user->id);
                $admin->update($request->validated());
                return new AdminResource($admin);
            }else{
                $vcard = Vcard::find($user->id);
                if($request->has('base64ImagePhoto')){
                    if($request->base64ImagePhoto == null){
                        $vcard->photo_url = null;
                    }else{
                        $vcard->photo_url = $this->storeBase64AsFile($vcard, $request->base64ImagePhoto);
                    }
                }
                $vcard->name = $request->name;
                $vcard->email = $request->email;
                $vcard->save();
                return new VcardResource($vcard);
            }
        }catch(\Exception $ex){
            return response()->json(['message' => $ex->getMessage()], 500);
        }
    }
}
