<?php

namespace App\Http\Controllers\Api\App;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\App\WalletTopUpRequest;
use App\Http\Resources\WalletResource;
use App\Http\Resources\WalletTransactionResource;
use App\Http\Traits\ResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    use ResponseTrait;

    public function show(Request $request): JsonResponse
    {
        $wallet = $request->user()->wallet()->firstOrCreate([]);

        return $this->successReturn([
            'wallet' => new WalletResource($wallet),
        ]);
    }

    public function transactions(Request $request): JsonResponse
    {
        $transactions = $request->user()->wallet?->transactions()->latest()->paginate(15) ?? collect();

        return $this->successReturn([
            'transactions' => WalletTransactionResource::collection($transactions),
        ]);
    }

    public function topUp(WalletTopUpRequest $request): JsonResponse
    {
        $user = $request->user();

        DB::transaction(function () use ($user, $request): void {
            $wallet = $user->wallet()->firstOrCreate([]);
            $amount = $request->float('amount');
            $balance = (float) $wallet->balance + $amount;

            $wallet->balance = $balance;
            $wallet->save();

            $wallet->transactions()->create([
                'user_id' => $user->id,
                'type' => 'credit',
                'amount' => $amount,
                'balance_after' => $balance,
                'description' => trans('messages.wallet_top_up'),
            ]);
        });

        return $this->successReturn([
            'wallet' => new WalletResource($user->wallet()->first()),
        ]);
    }
}
