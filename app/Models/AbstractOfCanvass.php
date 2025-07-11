<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AbstractOfCanvass extends Model
{
    use HasFactory;

    protected $fillable = ['purchase_request_id', 'reference_no'];

    public function items()
    {
        return $this->hasMany(AbstractOfCanvassItem::class);
    }

    public function purchaseRequest()
    {
        return $this->belongsTo(PurchaseRequest::class);
    }
}
