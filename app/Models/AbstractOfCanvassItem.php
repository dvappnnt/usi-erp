<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AbstractOfCanvassItem extends Model
{
    use HasFactory;

    protected $fillable = ['abstract_of_canvass_id', 'product_id', 'supplier_id', 'unit_price', 'total_price','units','qty',];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
