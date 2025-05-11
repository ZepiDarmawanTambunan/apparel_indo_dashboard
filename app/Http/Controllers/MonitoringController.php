<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;

class MonitoringController extends Controller
{
    public function index()
    {
        return Inertia::render('monitoring/Index');
    }

    public function deadline()
    {
        return Inertia::render('monitoring/Deadline');
    }

    public function tracking()
    {
        return Inertia::render('monitoring/Tracking');
    }
}
