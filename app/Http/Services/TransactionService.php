<?php

namespace App\Http\Services;

use App\Models\User;
use App\Models\BankAccount;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TransactionService extends Service
{
    /**
     * Validate transfer request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  User $user
     * @return \Illuminate\Support\Facades\Validator
     */
    public static function validateTransfer(Request $request, User $user)
    {
        if ($user->type === 'Admin') {
            return Validator::make($request->json()->all(), [
                'value' => ['required', 'int'],
                'payer' => ['required', 'different:payee', 'int'],
                'payee' => ['required', 'int'],
            ]);
        } else {
            return Validator::make($request->json()->all(), [
                'value' => ['required', 'int'],
                'payer' => ['different:payee', 'int'],
                'payee' => ['required', 'int'],
            ]);
        }

        return null;
    }

    /**
     * Confirm transfer payer request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return User
     */
    public static function confirmTransferPayer(Request $request, User $user)
    {
        $data = $request->json()->all();
        $data['payer'] = $data['payer'] ?? $user->id;
        if ($user->type !== 'Admin' && $data['payer'] !== $user->id) return null;

        return User::where('id', $data['payer'])
            ->where('type', 'User')
            ->where('status', 'Active')
            ->first();
    }

    /**
     * Confirm transfer payee request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return User
     */
    public static function confirmTransferPayee(Request $request)
    {
        $data = $request->json()->all();

        return User::where('id', $data['payee'])
            ->where('status', 'Active')
            ->first();
    }

    /**
     * Confirm transfer value request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return Transaction
     */
    public static function confirmTransferValue(Request $request, User $user)
    {
        $data = $request->json()->all();
        $data['payer'] = $data['payer'] ?? $user->id;
        if ($user->type !== 'Admin' && $data['payer'] !== $user->id) return null;

        $payerBankAccount = BankAccount::where('user_id', $data['payer'])
            ->where('wallet', '>=', $data['value'])
            ->first();
        if (!$payerBankAccount) return null;

        $payeeBankAccount = BankAccount::where('user_id', $data['payee'])->first();
        if (!$payeeBankAccount) {
            $payeeBankAccount = new BankAccount;
            $payeeBankAccount->user_id = $data['payee'];
            $payeeBankAccount->wallet = 0;
            $payeeBankAccount->save();
        }

        if (@self::consultExternalAuth()['message'] !== 'Autorizado') throw new \Exception('Not authorized', 401);

        $transaction = new Transaction;
        $transaction->user_id = $user->id;
        $transaction->payer_user_id = $payerBankAccount->user_id;
        $transaction->payee_user_id = $payeeBankAccount->user_id;
        $transaction->value = $data['value'];
        $transaction->type = 'Transfer';
        $transaction->status = 'Done';
        $transaction->save();

        $payerBankAccount->wallet = $payerBankAccount->wallet - $data['value'];
        $payerBankAccount->save();
        $payeeBankAccount->wallet = $payeeBankAccount->wallet + $data['value'];
        $payeeBankAccount->save();

        return $transaction;
    }

    /**
     * Validate deposit request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Support\Facades\Validator
     */
    public static function validateDeposit(Request $request, User $user)
    {
        if ($user->type === 'Admin') {
            return Validator::make($request->json()->all(), [
                'value' => ['required', 'int'],
                'user' => ['required', 'int'],
            ]);
        } else {
            return Validator::make($request->json()->all(), [
                'value' => ['required', 'int'],
                'user' => ['int'],
            ]);
        }
    }

    /**
     * Confirm deposit user request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return BankAccount
     */
    public static function confirmDepositUser(Request $request, User $user)
    {
        $data = $request->json()->all();
        $data['user'] = $data['user'] ?? $user->id;
        if ($user->type !== 'Admin' && $data['user'] !== $user->id) return null;

        $bankAccount = BankAccount::where('user_id', $data['user'])->first();

        if ($bankAccount) {
            $bankAccount->wallet = $bankAccount->wallet + $data['value'];
            $bankAccount->save();
        } else {
            $bankAccount = new BankAccount;
            $bankAccount->user_id = $data['user'] ?: $user->id;
            $bankAccount->wallet = $data['value'];
            $bankAccount->save();
        }

        return $bankAccount;
    }
}
