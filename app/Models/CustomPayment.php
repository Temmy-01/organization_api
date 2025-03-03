<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomPayment extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $fillable = ['custom_invoice_id', 'amount_paid', 'date_of_payment'];

    protected static function boot()
    {
        parent::boot();

        static::created(function ($payment) {
            // Ensure the related CustomInvoice exists
            $custom_invoice = $payment->custom_invoice;

            if ($custom_invoice) {
                // Update the total amount paid
                $custom_invoice->amount_paid += $payment->amount_paid;

                // Recalculate balance
                // $custom_invoice->balance = $custom_invoice->amount - $custom_invoice->amount_paid;
                $custom_invoice->balance -= $payment->amount_paid;


                // Save the updated invoice
                $custom_invoice->save();
            }
        });
    }

    public function custom_invoice()
    {
        return $this->belongsTo(CustomInvoice::class);
    }
}
