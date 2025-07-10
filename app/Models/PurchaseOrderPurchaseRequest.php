<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class PurchaseOrderPurchaseRequest extends Pivot
{
    use HasFactory;

    protected $table = 'purchase_order_purchase_request';

    protected $fillable = [
        'purchase_order_id',
        'purchase_request_id',
    ];
}
