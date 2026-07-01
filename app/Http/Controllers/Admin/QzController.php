<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class QzController extends Controller
{
    public function certificate()
    {
        return response(file_get_contents(resource_path('js/certificate.pem')))
            ->header('Content-Type', 'text/plain');
    }

    public function sign(Request $request)
    {
        $privateKey = openssl_pkey_get_private(
            file_get_contents(resource_path('js/private-key.pem'))
        );

        openssl_sign($request->data, $signature, $privateKey, 'SHA512');

        return response(base64_encode($signature))
            ->header('Content-Type', 'text/plain');
    }
}
