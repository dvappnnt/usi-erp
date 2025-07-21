<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AbstractOfCanvass;
use Illuminate\Http\Request;

class AbstractOfCanvassApiController extends Controller
{
    public function index(Request $request)
    {
        $aocs = AbstractOfCanvass::with([
            'purchaseRequest',
            'items.product',
            'items.supplier',
        ])->latest()->paginate(10);

        return response()->json([
            'data' => $aocs
        ]);
    }
}
