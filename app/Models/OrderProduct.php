<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Observers\OrderProductObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy([OrderProductObserver::class])]
class OrderProduct extends Model
{

    use HasFactory;

    protected $fillable = ['order_id', 'product_id', 'quantity', 'unit_price'];

    public function products(): BelongsTo
    {
        return $this->BelongsTo(Product::class);
    }

    public function orders(): BelongsTo
    {
        return $this->BelongsTo(Order::class);
    }
}
