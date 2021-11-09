<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

use App\Http\Services\TransactionService;

class TransactionController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Transaction Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles transfer between users.
    |
    */

    /**
     * Handles the transfer of money between users.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function transfer(Request $request)
    {
        $user = Auth::user();

        $validator = TransactionService::validateTransfer($request, $user);
        if (!$validator->passes()) return new JsonResponse(['errors' => $validator->errors()->all()], 400);

        $payer = TransactionService::confirmTransferPayer($request, $user);
        if (!$payer) return new JsonResponse(['errors' => ['Invalid transfer payer']], 400);

        $payee = TransactionService::confirmTransferPayee($request);
        if (!$payee) return new JsonResponse(['errors' => ['Invalid transfer payee']], 400);

        try {
            $bankAccount = TransactionService::confirmTransfervalue($request, $user);
            if (!$bankAccount) return new JsonResponse(['errors' => ['Invalid transfer value']], 400);
        } catch (\Exception $e) {
            return new JsonResponse(['errors' => [$e->getMessage()]], $e->getCode());
        }

        return new JsonResponse([
            'payer' => $payer,
            'payee' => $payee,
            'value' => $request->json()->all()['value']
        ], 200);
    }

    /**
     * Handles the deposit of money by the user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deposit(Request $request)
    {
        $user = Auth::user();

        $validator = TransactionService::validateDeposit($request, $user);
        if (!$validator->passes()) return new JsonResponse(['errors' => $validator->errors()->all()], 400);

        $bankAccount = TransactionService::confirmDepositUser($request, $user);
        if (!$bankAccount) return new JsonResponse(['errors' => ['Invalid deposit user']], 400);

        return new JsonResponse([
            'user' => User::where('id', $bankAccount->user_id)->first(),
            'bankAccount' => $bankAccount,
        ], 200);
    }
}
