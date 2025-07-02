<?php

namespace App\Models;

use App\Enums\RequisitionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory; 
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Requisition extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status',
        'approved_by_id',
        'approved_at',
        'vendor_id', // <-- Add this
        'quotation_number', // <-- Add this
        'quotation_file_path',
        'total_amount', // <-- Add this
    ];

    // Tell Laravel to treat 'status' as an Enum
    protected $casts = [
        'status' => RequisitionStatus::class,
        'approved_at' => 'datetime',
    ];

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(RequisitionItem::class);
    }

    // The critical relationship method
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

}
