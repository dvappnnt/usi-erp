<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Signatory extends Model
{
    use HasFactory;

    protected $fillable = [
        'module',
        'name',
        'label',
        'position',
    ];

    /**
     * Polymorphic relationship to the module (purchase request, purchase order, etc.)
     */
}
