<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $fillable = ['invoice_id', 'amount_paid', 'date_of_payment'];


    protected static function boot()
    {
        parent::boot();

        static::created(function ($payment) {
            $invoice = $payment->invoice;

            // Update the total amount paid
            $invoice->amount_paid += $payment->amount_paid;

            // Recalculate balance
            $invoice->balance -= $payment->amount_paid;

            // Save the updated invoice
            $invoice->save();
        });
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

}
