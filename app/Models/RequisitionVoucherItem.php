<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequisitionVoucherItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'requisition_voucher_id',
        'mat_code',
        'particular',
        'quantity',
    ];

    /**
     * Get the requisition voucher that owns the item.
     */
    public function requisitionVoucher()
    {
        return $this->belongsTo(RequisitionVoucher::class);
    }
}
