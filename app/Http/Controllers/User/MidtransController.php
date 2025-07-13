<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User\Order;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon; // <-- Tambahkan ini

class MidtransController extends Controller
{
    public function notificationHandler(Request $request)
    {
        $notificationPayload = $request->all();

        $transactionId = $notificationPayload['transaction_id'] ?? null;
        $orderId = $notificationPayload['order_id'] ?? null;
        $statusCode = $notificationPayload['status_code'] ?? null;
        $grossAmount = $notificationPayload['gross_amount'] ?? null;
        $signatureKey = $notificationPayload['signature_key'] ?? null;
        $transactionStatus = $notificationPayload['transaction_status'] ?? null;
        
        // --- Ambil data baru dari payload ---
        $paymentType = $notificationPayload['payment_type'] ?? null;
        $fraudStatus = $notificationPayload['fraud_status'] ?? null;
        $settlementTime = $notificationPayload['settlement_time'] ?? null;

        if (!$orderId) {
            return response()->json(['message' => 'Invalid notification'], 400);
        }

        $serverKey = config('services.midtrans.server_key');
        $hashed = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        if ($signatureKey !== $hashed) {
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $order = Order::where('order_number', $orderId)->first();
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        if ($order->payment_status !== 'pending') {
            return response()->json(['message' => 'Order already processed'], 200);
        }

        // Simpan detail dasar yang selalu ada
        $order->midtrans_transaction_id = $transactionId;
        $order->payment_type = $paymentType;
        $order->fraud_status = $fraudStatus;

        switch ($transactionStatus) {
            case 'settlement':
            case 'capture':
                $order->payment_status = 'paid';
                $order->order_status = 'processing';
                // Simpan waktu pembayaran lunas
                $order->paid_at = Carbon::parse($settlementTime);
                break;
            
            case 'deny':
                $order->payment_status = 'failed';
                $order->order_status = 'cancelled';
                break;

            case 'expire':
                $order->payment_status = 'expired';
                $order->order_status = 'cancelled';
                break;

            case 'cancel':
                $order->payment_status = 'cancelled';
                $order->order_status = 'cancelled';
                break;
        }

        $order->save();

        return response()->json(['message' => 'Notification processed successfully'], 200);
    }
}