<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequisitionVoucher extends Model
{
    //
    protected $fillable = [
        'purpose',
        'requisitioner',
        'requisitioner_position',
        'recommending_approval',
        'recommending_position',
        'approved_by',
        'approved_position',
        'date',
    ];
     public function items()
    {
        return $this->hasMany(RequisitionVoucherItem::class);
    }
}
