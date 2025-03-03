<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceCustomServicePay extends Model
{
    use HasFactory;


    protected $guarded = [];


    public function invoice_custom_service()
    {
        return $this->belongsTo(InvoiceCustomService::class, 'inv_cust_service_id'); 
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function ($payment) {
            // Ensure the related CustomInvoice exists
            $invoice_custom_service = $payment->invoice_custom_service;

            if ($invoice_custom_service) {
                // Update the total amount paid
                $invoice_custom_service->amount_paid += $payment->amount_paid;

                // Recalculate balance
                $invoice_custom_service->balance = $invoice_custom_service->total_amount - $invoice_custom_service->amount_paid;
                // $invoice_custom_service->balance -= $payment->amount_paid;

                // Save the updated invoice
                $invoice_custom_service->save();
            }
        });
    }
}
