<?php

namespace App\Domains\Payroll\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Remision;
use Illuminate\Http\Request;

class RemisionApiController extends Controller
{
    public function index(Request $request) {}
    public function show(Remision $remision) {}
    public function store(Request $request) {}
    public function update(Request $request, Remision $remision) {}
    public function destroy(Remision $remision) {}
}
