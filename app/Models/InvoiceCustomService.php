<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceCustomService extends Model
{
    use HasFactory;

    protected $guarded = [];

    // Relationship with Product
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Relationship with Custom Product
    public function customProduct()
    {
        return $this->belongsTo(CustomProduct::class);
    }

    // Relationship with Account
    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    // Relationship with Category
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Relationship with SubCategory
    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class);
    }



    protected static function boot()
    {
        parent::boot();

        // Update the balance whenever the amount, quantity, or discount is modified
        static::updating(function ($invoice_custom_service) {
            // Check if amount, discount, or quantity is modified
            if ($invoice_custom_service->isDirty('amount') || $invoice_custom_service->isDirty('discount') || $invoice_custom_service->isDirty('quantity')) {
                // Calculate discounted amount if discount is provided
                $discountedAmount = $invoice_custom_service->amount;

                if ($invoice_custom_service->discount) {
                    $discountedAmount -= ($invoice_custom_service->amount * ($invoice_custom_service->discount / 100));
                }

                // Calculate the total amount based on quantity
                $totalAmount = $discountedAmount * $invoice_custom_service->quantity;

                // Update the balance
                $invoice_custom_service->balance = $totalAmount - $invoice_custom_service->amount_paid;
            }
        });
    }

}
