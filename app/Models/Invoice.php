<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;
    protected $guarded = [];

    // Relationship with Product
    public function product()
    {
        return $this->belongsTo(Product::class);
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

        // Update the balance whenever the amount or amount_paid is modified
        static::updating(function ($invoice) {
            // Check if either amount or discount is changed
            if ($invoice->isDirty('amount') || $invoice->isDirty('discount')) {
                // Recalculate discounted amount
                $discountedAmount = $invoice->amount - ($invoice->amount * ($invoice->discount / 100));
                // Update the balance
                $invoice->balance = $discountedAmount - $invoice->amount_paid;
            }
        });
    }
}
