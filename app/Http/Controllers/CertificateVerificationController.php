<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CertificateVerificationController extends Controller
{
    public function show(Request $request, ?string $certificateNumber = null): View
    {
        $number = $certificateNumber ?? $request->query('number');

        $certificate = $number
            ? Certificate::where('certificate_number', $number)->first()
            : null;

        return view('certificates.verify', [
            'certificate' => $certificate,
            'certificateNumber' => $number,
        ]);
    }
}
