<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequisitionItem extends Model
{
    //
    protected $fillable = ['requisition_id', 'product_id', 'quantity', 'price'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function requisition(): BelongsTo
    {
        return $this->belongsTo(Requisition::class);
    }
}