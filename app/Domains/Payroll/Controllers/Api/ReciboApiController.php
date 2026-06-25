<?php

namespace App\Domains\Payroll\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Recibo;
use Illuminate\Http\Request;

class ReciboApiController extends Controller
{
    public function index(Request $request) {}
    public function show(Recibo $recibo) {}
    public function store(Request $request) {}
    public function update(Request $request, Recibo $recibo) {}
    public function destroy(Recibo $recibo) {}
}
