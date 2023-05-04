<?php

namespace App\Http\Controllers\Api;

use App\Events\PrintReceipt;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PrintController extends Controller
{
    public function dispatchPrint(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id'      => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status'    => false,
                    'message'   => $validator->errors()
                ], 422);
            }

            $trx = Transaction::where('id', $request->input('id'))->first()->toArray();

            PrintReceipt::dispatch($trx);

            return response()->json([
                'status'    => true,
                'message'   => 'Print event dispatched.'
            ], 200);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status'    => false,
                'message'   => env('APP_ENV') === 'production' ? 'Unexpected error. Please check log.' : $e->getMessage()
            ], 500);
        }
    }
}
