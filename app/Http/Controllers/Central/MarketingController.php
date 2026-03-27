<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class MarketingController extends Controller
{
    public function pricing(): Response
    {
        return Inertia::render('Central/Pricing');
    }

    public function features(): Response
    {
        return Inertia::render('Central/Features');
    }

    public function contact(): Response
    {
        return Inertia::render('Central/Contact');
    }
}
