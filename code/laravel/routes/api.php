<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\auth\AuthController;
use App\Http\Controllers\api\CategoryControlle;
use App\Http\Controllers\api\UserController;
use App\Http\Controllers\api\VcardController;

Route::post('auth/login', [AuthController::class, 'login']);
Route::middleware('auth:api')->post(
    'logout',
    [AuthController::class, 'auth/logout']
);

Route::middleware('auth:api')->group(
    function () {
        //Route::apiResource('categories', CategoryController::class);

        Route::get('users/me', [UserController::class, 'show_me']);
        Route::put('users/{user}', [UserController::class, 'update'])->middleware('can:update,user');
        Route::patch('users/{user}/password', [UserController::class, 'update_password'])->middleware('can:updatePassword,user');

        Route::patch('vcards/{vcard}/confirmation_code', [VcardController::class, 'updatesConfirmationCode'])->middleware('can:updateConfirmationCode,vcard');
        
        Route::get('vcards/{vcard}/categories', [CategoryControlle::class, 'getCategoriesOfVcard'])->middleware('can:viewCategories,vcard');
        Route::get('categories/{category}', [CategoryControlle::class, 'view'])->middleware('can:view,category');
        Route::put('categories/{category}', [CategoryControlle::class, 'update'])->middleware('can:update,category');
        // ALL OTHER ROUTES ARE HERE, SO THAT ONLY AUTHENTICATED USERS CAN ACCESS THEM
    }
);
