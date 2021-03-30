<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QRController extends Controller
{
    //
    public function createQR()
    {
        QrCode::size(200)->format('svg')->generate('Andri Nur H', public_path('img/qr/' . time() . '.svg'));
    }
}
