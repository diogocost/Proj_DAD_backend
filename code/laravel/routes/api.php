<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\auth\AuthController;
use App\Http\Controllers\api\CategoryController;
use App\Http\Controllers\api\UserController;
use App\Http\Controllers\api\VcardController;
use App\Http\Controllers\api\TransactionController;
use App\Http\Controllers\api\AdminController;
use App\Http\Controllers\api\DefaultCategoryController;


Route::post('auth/login', [AuthController::class, 'login']);
Route::post('auth/register', [UserController::class, 'store'])->middleware('can:create,App\Models\User');
Route::middleware('auth:api', 'blocked')->group(
    function () {
        Route::post('auth/logout', [AuthController::class, 'logout']);

        //User routes
        Route::get('users/me', [UserController::class, 'show_me']);
        Route::put('users/{user}', [UserController::class, 'update'])->middleware('can:update,user');
        Route::patch('users/{user}/password', [UserController::class, 'update_password'])->middleware('can:updatePassword,user');

        //Vcard routes
        Route::patch('vcards/{vcard}/confirmation_code', [VcardController::class, 'updatesConfirmationCode'])->middleware('can:updateConfirmationCode,vcard');
        Route::get('vcards/{vcard}/categories', [CategoryController::class, 'getCetegoriesOfVcard'])->middleware('can:viewCategories,vcard');
        Route::get('vcards/{vcard}/transactions', [TransactionController::class, 'getTransactionsOfVcard'])->middleware('can:viewTransactions,vcard');
        Route::get('vcards', [VcardController::class, 'index'])->middleware('can:showAll,App\Models\Vcard');
        Route::get('totalbalance', [VcardController::class, 'vcardsBalanceSum'])->middleware('can:viewAny, App\Models\Admin');

        Route::get('vcards/{vcard}', [VcardController::class, 'show'])->middleware('can:view,vcard');
        Route::delete('vcards/{vcard}', [VcardController::class, 'destroy'])->middleware('can:delete,vcard');   // NOT working
        Route::patch('vcards/{vcard}', [VcardController::class, 'manageVcard'])->middleware('can:manageVcard,vcard');

        //Category routes
        Route::get('categories/{category}', [CategoryController::class, 'show'])->middleware('can:view,category');
        Route::post('categories', [CategoryController::class, 'store'])->middleware('can:create,App\Models\Category');
        Route::put('categories/{category}', [CategoryController::class, 'update'])->middleware('can:update,category');
        Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->middleware('can:delete,category');

        //Transaction routes
        Route::get('transactions/{transaction}', [TransactionController::class, 'show'])->middleware('can:view,transaction');
        Route::post('transactions', [TransactionController::class, 'store'])->middleware('can:create,App\Models\Transaction');
        Route::get('transactions', [TransactionController::class, 'getAllTransactions'])->middleware('can:viewAny,App\Models\Admin');        
        Route::patch('transactions/{transaction}', [TransactionController::class, 'update'])->middleware('can:update,transaction');

        //Admin routes
        Route::get('admins/{admin}', [AdminController::class, 'show'])->middleware('can:view,admin');
        Route::get('admins', [AdminController::class, 'index'])->middleware('can:viewAny,App\Models\Admin');   //working
        Route::delete('admins/{admin}', [AdminController::class, 'destroy'])->middleware('can:delete,admin');
        Route::post('admins', [UserController::class, 'store'])->middleware('can:create,App\Models\User');

        //DefaultCategory routes
        Route::get('default_categories', [DefaultCategoryController::class, 'index'])->middleware('can:viewAny,App\Models\DefaultCategory');
        Route::get('default_categories/{defaultCategory}', [DefaultCategoryController::class, 'show'])->middleware('can:view,defaultCategory');
        Route::post('default_categories', [DefaultCategoryController::class, 'store'])->middleware('can:create,App\Models\DefaultCategory');
        Route::put('default_categories/{defaultCategory}', [DefaultCategoryController::class, 'update'])->middleware('can:update,defaultCategory');
        Route::delete('default_categories/{defaultCategory}', [DefaultCategoryController::class, 'destroy'])->middleware('can:delete,defaultCategory');
    }
);
