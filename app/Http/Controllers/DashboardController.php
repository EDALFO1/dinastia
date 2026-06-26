<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(): View
    {
        return view('dashboard');
    }

    public function afiliados(): View
    {
        return view('afiliados.index');
    }

    public function recibos(): View
    {
        return view('recibos.index');
    }

    public function invoices(): View
    {
        return view('invoices.index');
    }

    public function journalEntries(): View
    {
        return view('journal-entries.index');
    }
}
