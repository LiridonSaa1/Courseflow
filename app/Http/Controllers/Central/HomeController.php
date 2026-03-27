<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(Request $request): Response|RedirectResponse
    {
        $host = $request->getHost();

        if (! in_array($host, config('tenancy.central_domains'), true)) {
            return redirect('/login');
        }

        return Inertia::render('Central/Home');
    }
}
