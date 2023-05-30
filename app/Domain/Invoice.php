<?php

declare(strict_types=1);

namespace App\Domain;

use App\Domain\Company;
use App\Domain\Product;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Invoice extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $guarded = [];

    protected function total(): Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes) => $this->products()->get()->sum(
                fn ($product) => $product['price'] * $product['pivot']['quantity']
            ),
        );
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'invoice_product_lines')
            ->withPivot('quantity');
    }
}
