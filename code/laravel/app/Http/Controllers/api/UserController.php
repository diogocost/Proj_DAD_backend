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

class UserController extends Controller
{
    public function show_me(Request $request)
    {
        return new UserResource($request->user());
    }

    public function register(CreateUserRequest $request)
    {
        $request->validated();
        if($request->user_type == 'A'){
            $admin = new Admin();
            $admin->name = $request->name;
            $admin->email = $request->email;
            $admin->password = Hash::make($request->password);
            if($request->has('photo_url'))
                $admin->photo_url = $request->photo_url;
            $admin->save();
            return new AdminResource($admin);
        }else{
            $vcard = new Vcard();
            $vcard->phone_number = $request->phone_number;
            $vcard->name = $request->name;
            $vcard->email = $request->email;
            $vcard->password = Hash::make($request->password);
            $vcard->confirmation_code = Hash::make($request->confirmation_code);
            if($request->has('photo_url'))
                $vcard->photo_url = $request->photo_url;
            $vcard->blocked = 0;
            $vcard->balance = 0;
            $vcard->max_debit = 5000;
            $vcard->initializeDefaultCategories();
            $vcard->save();
            return new VcardResource($vcard);
        }
    }

    public function update_password(UpdateUserPasswordRequest $request, User $user)
    {
        if($user->isAdmin()){
            $admin = Admin::find($user->id);
            $admin->password = bcrypt($request->validated()['password']);
            $admin->save();
            return new AdminResource($admin);
        }else{
            $vcard = Vcard::find($user->id);
            $vcard->password = bcrypt($request->validated()['password']);
            $vcard->save();
            return new VcardResource($vcard);
        }
    }

    public function update(UpdateUserRequest $request, User $user){
        if($user->isAdmin()){
            $admin = Admin::find($user->id);
            $admin->update($request->validated());
            return new AdminResource($admin);
        }else{
            $vcard = Vcard::find($user->id);
            $vcard->update($request->validated());
            return new VcardResource($vcard);
        }
    }
}
