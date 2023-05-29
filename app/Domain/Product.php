<?php

declare(strict_types=1);

namespace App\Domain;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $guarded = [];

    public function invoice(): BelongsToMany
    {
        return $this->belongsToMany(Invoice::class, 'invoice_product_lines');
    }
}
