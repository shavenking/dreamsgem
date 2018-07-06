<?php

namespace App\Http\Controllers\API;

use App\CardApplication;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class CardApplicationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json(Auth::user()->cardApplications()->paginate());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'nickname' => 'required|max:255',
            'address' => 'required|max:255',
            'phone' => 'required|max:255',
        ]);

        $cardApplication = Auth::user()->cardApplications()->create(
            [
                'nickname' => $request->nickname,
                'address' => $request->address,
                'phone' => $request->phone,
                'status' => CardApplication::STATUS_PENDING,
            ]
        );

        return response()->json($cardApplication, Response::HTTP_CREATED);
    }
}
