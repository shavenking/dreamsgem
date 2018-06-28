<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\QRCode;
use Faker\Generator;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

class QRCodeController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:api', 'email.verified'])->except(['store']);
    }

    public function store(Generator $faker)
    {
        $qrCode = QRCode::create(
            [
                'password' => Hash::make($password = $faker->password),
            ]
        );

        return response()->json([
            'id' => $qrCode->id,
            'password' => $password,
            'qrcode_url' => rtrim(env('APP_QRCODE_URL'), '/') . '/' . $qrCode->id,
        ], Response::HTTP_CREATED);
    }

    public function update($qrCode, Request $request)
    {
        $qrCode = QRCode::findOrFail($qrCode);

        abort_if($qrCode->user_id, Response::HTTP_BAD_REQUEST, 'QR Code has been used');

        $qrCode->update([
            'user_id' => $request->user()->id,
        ]);

        return response()->json($qrCode);
    }
}
