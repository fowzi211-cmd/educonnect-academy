<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Services\Assessments\CertificateService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CertificateController extends Controller
{
    public function show(Request $request, Certificate $certificate, CertificateService $certificates): View
    {
        $user = $request->user();

        abort_unless($certificate->user_id === $user->id || $user->can('manage certificates'), 403);

        return view('certificates.show', [
            'certificate' => $certificate,
            'qrCodeSvg' => $certificates->qrCodeSvg($certificate),
            'verificationUrl' => $certificates->verificationUrl($certificate),
        ]);
    }
}
