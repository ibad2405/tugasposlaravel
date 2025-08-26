<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{

    use HasFactory;

    protected $fillable = ['name','stock', 'category_id', 'price', 'is_active', 'image', ];

    public function category(): BelongsTo
    {
        return $this->BelongsTo(Category::class);
    }
}
