<?php

namespace App\Http\Controllers\API;

use App\Dragon;
use App\Http\Controllers\Controller;

class DragonPriceController extends Controller
{
    public function index()
    {
        return response()->json((new Dragon)->prices());
    }
}
