<?php

declare(strict_types=1);

namespace App\Domain;

use App\Domain\Company;
use App\Domain\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Config;

class Invoice extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $guarded = [];

    public function getTotalAttribute(): int
    {
        return $this->products()->get()->sum(
            fn ($product) => $product['price'] * $product['pivot']['quantity']
        );
    }

    public function getCompanyAttribute(): Company
    {
        $companyData = array_merge(['id' => '0'], Config::get('company'));

        return new Company(
            $companyData
        );
    }

    public function billedCompany(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'invoice_product_lines')
            ->withPivot('quantity');
    }
}
