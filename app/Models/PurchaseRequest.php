<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'pr_no',
        'purpose',
        'date',
        'prepared_by',
        'approved_by',
        'received_by',
        'supplier_id',
    ];

    public function items()
    {
        return $this->hasMany(PurchaseRequestItem::class);
    }
    public function supplier()
{
    return $this->belongsTo(Supplier::class);
}
public function purchaseOrders()
{
    return $this->belongsToMany(PurchaseOrder::class)
                ->using(PurchaseOrderPurchaseRequest::class) // Use pivot model
                ->withTimestamps();
}
public function signatories()
{
    return $this->hasMany(Signatory::class, 'module_id')->where('module', 'purchase_requests');
}


}
