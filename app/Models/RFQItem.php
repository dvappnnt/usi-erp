<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RFQItem extends Model
{
    use HasFactory;
    protected $table = 'rfq_items';

    protected $fillable = [
        'rfq_id',
        'description',
        'quantity',
        'unit',
        'brand',
        'remarks',
    ];

    public function rfq()
    {
        return $this->belongsTo(RFQ::class);
    }
}
