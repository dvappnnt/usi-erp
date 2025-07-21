<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RFQ extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'rfqs';

    protected $fillable = [
        'pr_id',
        'rfq_no',
        'supplier_id',
        'date_sent',
        'deadline',
        'status',
        'notes',
    ];

    public function pr()
    {
        return $this->belongsTo(PurchaseRequest::class, 'pr_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items()
{
    return $this->hasMany(RFQItem::class, 'rfq_id');
}

}

