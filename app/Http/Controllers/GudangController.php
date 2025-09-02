<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;

class GudangController extends Controller
{
    public function index()
    {
        $breadcrumbs = [
            ['title' => 'Gudang', 'href' => route('gudang.index')],
        ];

        return Inertia::render('gudang/Index', [
            'breadcrumbs' => $breadcrumbs
        ]);
    }
}
