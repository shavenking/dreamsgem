<?php

namespace App\Http\Controllers\API;

use App\Tree;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TreePriceController extends Controller
{
    public function index()
    {
        return response()->json((new Tree)->prices());
    }
}
