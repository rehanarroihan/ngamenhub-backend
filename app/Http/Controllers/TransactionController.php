<?php

namespace App\Http\Controllers;

use Exception;
use Midtrans\Snap;
use App\Models\User;
use Midtrans\Config;
use App\Models\Event;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Notification;

class TransactionController extends Controller
{
    public function all(Request $request) {
        $id = $request->input('id');

        if ($id) {
            $transaction = Transaction::with(['event', 'customer', 'candidate'])->find($id);
            if ($transaction) {
                return ResponseFormatter::success(
                    null,
                    'Transaction detail fetched'
                ); 
            }
        } else {
            return ResponseFormatter::success(
                Transaction::where('user_id', $request->user->id)->get(),
                'Transaction list fetched'
            ); 
        }
    }

    public function create(Request $request) {
        $validator = Validator::make($request->all(), [
			'event_id'      => 'required|exists:events,id',
            'candidate_id'  => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
			return ResponseFormatter::error($validator->errors()->all(), $request->all());
        }

        $transaction = Transaction::create([
            'user_id'       => $request->user->id,
            'candidate_id'  => $request->candidate_id,
            'event_id'      => $request->event_id,
            'status'        => 'PENDING',
            'payment_url'   => '',
        ]);

        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = env('MIDTRANS_IS_PRODUCTION');
        Config::$isSanitized = env('MIDTRANS_IS_SANITIZED');
        Config::$is3ds = env('MIDTRANS_IS_3DS');

        $eventDetail = Event::where('id', $transaction->event_id)->get()->first();
        $customerDetail = User::where('id', $transaction->user_id)->get()->first();

        $midtransRequestData = [
            'transaction_details' => [
                'order_id'      => $transaction->id,
                'gross_amount'  => (int) $eventDetail->fee,
            ],
            'customer_details' => [
                'first_name'    => $customerDetail->full_name,
                'email'         => $customerDetail->email,
            ],
            'enabled_payments'  => ['gopay','bank_transfer'],
            'vtweb' => []
        ];

        try {
            $paymentUrl = Snap::createTransaction($midtransRequestData)->redirect_url;
            
            $transaction->payment_url = $paymentUrl;
            $transaction->save();

            return ResponseFormatter::success(
                Transaction::where('id', $transaction->id)
                    ->with(['customer', 'candidate', 'event'])
                    ->get()
                    ->first(),
                'Transaction created successfully'
            ); 
        } catch (Exception $e) {
            return ResponseFormatter::error(
                $e->getMessage(),
                'Transaction failed'
            ); 
        }
    }

    public function callback(Request $request) {
        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = env('MIDTRANS_IS_PRODUCTION');
        Config::$isSanitized = env('MIDTRANS_IS_SANITIZED');
        Config::$is3ds = env('MIDTRANS_IS_3DS');

        $notification = new Notification();

        $status = $notification->transaction_status;
        $type = $notification->payment_type;
        $fraud = $notification->fraud_status;
        $order_id = $notification->order_id;

        $transaction = Transaction::findOrFail($order_id);
        
        if ($status == 'capture') {
            if ($type == 'credit_card') {
                if ($fraud == 'challenge') {
                    $transaction->status = 'PENDING';
                } else {
                    $transaction->status = 'SUCCESS';
                }
            }
        } else if ($status == 'settlement') {
            $transaction->status = 'SUCCESS';
        } else if ($status == 'pending') {
            $transaction->status = 'PENDING';
        } else if ($status == 'deny') {
            $transaction->status = 'CANCELLED';
        } else if ($status == 'expire') {
            $transaction->status = 'CANCELLED';
        } else if ($status == 'cancel') {
            $transaction->status = 'CANCELLED';
        }

        $transaction->save();
    }

    public function success() {
        return 'Payment successful';
    }

    public function unfinish() {
        return 'Payment unfinish';
    }

    public function error() {
        return 'Payment error';
    }
}
