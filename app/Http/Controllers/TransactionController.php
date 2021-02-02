<?php

namespace App\Http\Controllers;

use Exception;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Notification;
use App\Models\User;
use App\Models\Event;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use Illuminate\Support\Facades\Validator;

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

    public function detail($transaction_id) {
        $transaction = Transaction::where('id', $transaction_id)->get()->first();

        if (!$transaction) {
            return ResponseFormatter::error(
                null,
                'Transaction not found',
                404
            );
        }

        return ResponseFormatter::success(
            Transaction::where('id', $transaction_id)
                ->with(['customer', 'candidate', 'event'])
                ->get()
                ->first(),
            'Transaction detail fetched'
        ); 
    }

    public function create(Request $request) {
        $validator = Validator::make($request->all(), [
			'event_id'      => 'required|exists:events,id',
            'candidate_id'  => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
			return ResponseFormatter::error(
                $validator->errors()->all(), 
                $request->all()
            );
        }

        $invoiceCode = date("ymdhms").rand(pow(10, 3-1), pow(10, 3)-1);
        $transaction = Transaction::create([
            'user_id'       => $request->user->id,
            'invoice_code'  => $invoiceCode,
            'candidate_id'  => $request->candidate_id,
            'group_id'      => $request->group_id,
            'event_id'      => $request->event_id,
            'status'        => 'DATA_CREATED',
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
                'order_id'      => $invoiceCode,
                'gross_amount'  => (int) $eventDetail->fee,
            ],
            'customer_details' => [
                'first_name'    => $customerDetail->full_name,
                'email'         => $customerDetail->email,
            ],
            'enabled_payments'  => ['credit_card','gopay','bank_transfer'],
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

        $transaction = Transaction::where('invoice_code', $order_id)->get()->first();
        
        if ($status == 'capture') {
            if ($type == 'credit_card') {
                if ($fraud == 'challenge') {
                    $transaction->status = 'PENDING';
                } else {
                    $transaction->status = 'SUCCESS';
                }
            }
            if ($type == 'bank_transfer') {
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
