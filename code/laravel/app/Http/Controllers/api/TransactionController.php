<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use App\Models\Vcard;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\CreateTransactionRequest;
use App\Http\Requests\TransactionHistoryRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Http\Requests\UpdateTransactionRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class TransactionController extends Controller
{
    public function getTransactionsOfVcard(Vcard $vcard, TransactionHistoryRequest $request)
    {
        try {
            $query = Transaction::where('vcard', $vcard->phone_number);

            // Apply filters
            if ($request->has('start_date')) {
                $query->whereDate('datetime', '>=', $request->input('start_date'));
            }

            if ($request->has('end_date')) {
                $query->whereDate('datetime', '<=', $request->input('end_date'));
            }

            if ($request->has('transaction_type')) {
                $query->where('type', $request->input('transaction_type'));
            }

            if ($request->has('category_id')) {
                $categoryFilter = $request->input('category_id');
                if ($categoryFilter > 0) {
                    $query->where('category_id', $request->input('category_id'));
                } else {
                    $query->whereNull('category_id');
                }
            }

            if ($request->has('pair_vcard')) {
                $query->where('pair_vcard', $request->input('pair_vcard'));
            }

            if ($request->has('payment_type')) {
                $query->where('payment_type', $request->input('payment_type'));
            }

            if ($request->has('min_value')) {
                $query->where('value', '>=', $request->input('min_value'));
            }

            if ($request->has('max_value')) {
                $query->where('value', '<=', $request->input('max_value'));
            }

            // Fetch and return the results
            $transactions = $query->get();

            return TransactionResource::collection($transactions);
        } catch (\Exception $ex) {
            // Handle any exceptions or errors
            return response()->json(['message' => 'Error fetching transactions', 'error' => $ex->getMessage()], 500);
        }
    }

    public function show(Transaction $transaction)
    {
        return new TransactionResource($transaction);
    }

    public function handleVcardTransaction(CreateTransactionRequest $request, Vcard $vcard)
    {
        $confirmation_code = $request->confirmation_code;
        if (!Hash::check($confirmation_code, $vcard->confirmation_code)) {
            return response()->json([
                'errors' => [
                    'confirmation_code' => ['The confirmation code field is incorrect!']
                ]
            ], 422);
        }

        $receiver = Vcard::find($request->pair_vcard);
        if ($receiver->blocked == 1) {
            return response()->json([
                'errors' => [
                    'pair_vcard' => ['This pair vcard is blocked!']
                ]
            ], 422);
        }
        $date = date('Y-m-d');
        $datetime = date('Y-m-d H:i:s'); // Add a debugging statement to check the value of $date
        $transaction = new Transaction();
        $transaction->vcard = $vcard->phone_number;
        $transaction->pair_vcard = $request->pair_vcard;
        $transaction->value = $request->value;
        $transaction->category_id = $request->category_id;
        $transaction->description = $request->description;
        $transaction->payment_type = $request->payment_type;
        $transaction->payment_reference = $request->pair_vcard;
        $transaction->old_balance = $vcard->balance;
        $transaction->new_balance = $vcard->balance - $request->value;
        $transaction->type = 'D';
        $transaction->date = $date;
        $transaction->datetime = $datetime;
        $transaction->save();


        $pair_transaction = new Transaction(
            [
                'vcard' => $request->pair_vcard,
                'pair_vcard' => $vcard->phone_number,
                'value' => $request->value,
                'payment_type' => $request->payment_type,
                'payment_reference' => $vcard->phone_number,
                'old_balance' => $receiver->balance,
                'new_balance' => $receiver->balance + $request->value,
                'date' => $date,
                'datetime' => $datetime,
                'pair_transaction' => $transaction->id,
                'type' => 'C'
            ]
        );

        $pair_transaction->save();
        $transaction->pair_transaction = $pair_transaction->id;
        $transaction->save();
        $receiver->balance += $request->value;
        $receiver->save();
        $vcard->balance -= $request->value;
        $vcard->save();
        return new TransactionResource($transaction);
    }

    public function handleNotVcardTransaction(CreateTransactionRequest $request, Vcard $vcard, bool $isAdmin)
    {
        // Create the transaction
        $transaction = new Transaction();
        $transaction->vcard = $vcard->phone_number;
        $transaction->type = 'C';
        $transaction->value = $request->value;
        $transaction->payment_type = $request->payment_type;
        $transaction->payment_reference = $request->payment_reference;
        $transaction->old_balance = $vcard->balance;
        $transaction->new_balance = $vcard->balance + $request->value;
        $transaction->date = date('Y-m-d');
        $transaction->datetime = date('Y-m-d H:i:s');
        $transaction->save();


        // Update the vcard balance
        $vcard->balance += $request->value;
        $vcard->save();
        return new TransactionResource($transaction);
    }

    public function store(CreateTransactionRequest $request)
    {
        try {
            $user = Auth::guard('api')->user();
            $isAdmin = $user->isAdmin();
            if ($isAdmin) {
                $vcard = Vcard::find($request->vcard);
            } else {
                $vcard = Vcard::find($user->id);

                if ($vcard->balance - $request->value < 0) {
                    return response()->json([
                        'errors' => [
                            'value' => ['Insufficient balance!']
                        ]
                    ], 422);
                }
                if ($request->value > $vcard->max_debit) {
                    return response()->json([
                        'errors' => [
                            'value' => ['Value exceeds maximum debit!']
                        ]
                    ], 422);
                }
            }

            if ($request->payment_type == 'VCARD') {
                return $this->handleVcardTransaction($request, $vcard);
            } else {
                if ($vcard->blocked == 1) {
                    return response()->json([
                        'errors' => [
                            'vcard' => ['This vcard is blocked!']
                        ]
                    ], 422);
                }
                // Handle transactions with other payment types

                // Example payload for the external payment gateway service
                $paymentGatewayPayload = [
                    'type' => $request->payment_type,
                    'reference' => $request->payment_reference, // You might want to use a unique reference here
                    'value' => $request->value,
                ];

                // Make a request to the external payment gateway service
                if ($isAdmin) {
                    $response = Http::post('https://dad-202324-payments-api.vercel.app/api/debit', $paymentGatewayPayload);
                } else {
                    $response = Http::post('https://dad-202324-payments-api.vercel.app/api/credit', $paymentGatewayPayload);
                }

                // Check the response from the external service
                if ($response->successful()) {
                    // Payment operation created successfully
                    // Handle any additional logic here
                    return $this->handleNotVcardTransaction($request, $vcard, $isAdmin);
                } else {
                    // Payment operation not created successfully
                    $errorMessage = $response->json()['message'] ?? 'Unknown error';
                    if ($response->status() == 422) {
                        return response()->json([
                            'errors' => [
                                'value' => [$errorMessage]
                            ]
                        ], 422);
                    }
                    return response()->json(['message' => 'Error creating payment: ' . $errorMessage], $response->status());
                }
            }
        } catch (\Exception $ex) {
            return response()->json(['message' => 'Error creating transaction' . $ex->getMessage()], 500);
        }
    }

    public function update(UpdateTransactionRequest $request, Transaction $transaction)
    {
        try {
            $transaction->update($request->validated());
            return new TransactionResource($transaction);
        } catch (\Exception $ex) {
            return response()->json(['message' => 'Error updating transaction'], 500);
        }
    }

    public function getAllTransactions(){
        $dates = Transaction::select('date')->get();
        return response()->json(['dates' => $dates]);
    }
}
