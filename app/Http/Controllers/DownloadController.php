<?php

namespace App\Http\Controllers;

class DownloadController extends Controller
{
    public function androidAPK()
    {
        return redirect('https://download.jjlinpai.com/dreasgem-resources/dgchat-1-0-8-4.apk');
    }
}
