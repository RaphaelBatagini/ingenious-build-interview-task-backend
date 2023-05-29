<?php

declare(strict_types=1);

namespace App\Domain;

use App\Domain\Company;
use App\Domain\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Invoice extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $guarded = [];

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
