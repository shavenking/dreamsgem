<?php

namespace App\Http\Controllers;

class DownloadController extends Controller
{
    public function androidAPK()
    {
        return redirect('https://s3-ap-northeast-1.amazonaws.com/dreasgem-resources/dgchat-1-0-8-4.apk');
    }
}
