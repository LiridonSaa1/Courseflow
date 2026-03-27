<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Course;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CertificateController extends Controller
{
    public function index(Request $request): InertiaResponse
    {
        $certs = Certificate::query()->where('user_id', $request->user()->id)->with('course')->get();
        $courses = Course::query()->get();

        return Inertia::render('Tenant/Certificates/Index', [
            'certificates' => $certs,
            'courses' => $courses,
        ]);
    }

    public function generate(Request $request): BinaryFileResponse|RedirectResponse
    {
        $data = $request->validate([
            'course_id' => ['required', 'exists:courses,id'],
            'score' => ['nullable', 'integer', 'min:0', 'max:100'],
        ]);
        $token = (string) Str::uuid();

        $cert = Certificate::query()->create([
            'verify_token' => $token,
            'user_id' => $request->user()->id,
            'course_id' => $data['course_id'],
            'score' => $data['score'] ?? 0,
        ]);

        $course = Course::query()->findOrFail($data['course_id']);
        $verifyUrl = url('/certificates/verify/'.$token);
        $qrSvg = QrCode::format('svg')->size(120)->generate($verifyUrl);

        $pdf = Pdf::loadView('pdf.certificate', [
            'name' => $request->user()->name,
            'course' => $course->title,
            'score' => $cert->score,
            'token' => $token,
            'qrSvg' => $qrSvg,
        ]);

        $path = 'certificates/'.$cert->id.'.pdf';
        Storage::disk('public')->put($path, $pdf->output());
        $cert->update(['pdf_path' => $path]);

        return $pdf->download('certificate-'.Str::slug($course->title).'.pdf');
    }

    public function verify(string $token): InertiaResponse
    {
        $cert = Certificate::query()->where('verify_token', $token)->with(['user', 'course'])->firstOrFail();

        return Inertia::render('Tenant/Certificates/Verify', ['certificate' => $cert]);
    }
}
