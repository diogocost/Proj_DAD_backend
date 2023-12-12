<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DeleteVcardRequest;
use Illuminate\Http\Request;
use App\Http\Resources\VcardResource;
use App\Http\Requests\UpdateUserConfirmationCodeRequest;
use App\Models\Vcard;
use App\Models\User;
use Illuminate\Support\Facades\Auth;



class VcardController extends Controller
{

    public function index(Request $request)
    {
        
        try {
            $query = Vcard::query();

            // Apply filters
            if ($request->has('blocked')) {
                $query->where('blocked', $request->input('blocked'));
            }

            if ($request->has('min_balance')) {
                $query->where('balance', '>=', $request->input('min_balance'));
            }

            if ($request->has('max_balance')) {
                $query->where('balance', '<=',$request->input('max_balance'));
            }

            if ($request->has('created_at_start')) {
                $query->whereDate('created_at', '>=', $request->input('created_at_start'));
            }

            if ($request->has('created_at_end')) {
                $query->whereDate('created_at', '<=', $request->input('created_at_end'));
            }

            // Fetch and return the results
            $vcards = $query->get();

            return VcardResource::collection($vcards);
        } catch (\Exception $ex) {
            // Handle any exceptions or errors
            return response()->json(['message' => 'Error fetching vCards', 'error' => $ex->getMessage()], 500);
        }


    }



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

        $user=Auth::guard('api')->user();
        if (!$user->user_type == 'A') {
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


    public function filter(Request $request)
    {
        // Apply filters based on request parameters
        $query = Vcard::query();

        if ($request->has('blocked')) {
            $query->where('blocked', $request->blocked);
        }

        // Add more filters as needed

        $vcards = $query->get();

        return VcardResource::collection($vcards);
    }
    

    public function search(Request $request)
    {
        // Apply search based on request parameters
        $query = Vcard::query();

        if ($request->has('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('phone_number', 'like', "%$searchTerm%")
                    ->orWhere('name', 'like', "%$searchTerm%")
                    ->orWhere('email', 'like', "%$searchTerm%");
            });
        }

        // Add more search criteria as needed

        $vcards = $query->get();

        return VcardResource::collection($vcards);
    }


    public function block(Vcard $vcard,Request $request)
    {
        $vcard->blocked = $request->input('blocked', 1);
        $vcard->save();


        return new VcardResource($vcard);
    }


    public function unblock(Vcard $vcard,Request $request)
    {
        $vcard->blocked = $request->input('blocked', 0);
        $vcard->save();


        return new VcardResource($vcard);
    }

    public function updateMaxDebit(Request $request, Vcard $vcard)
    {

        $vcard->max_debit = $request->validated('max_debit'); 
        $vcard->save();

        return new VcardResource($vcard);
    }

}
