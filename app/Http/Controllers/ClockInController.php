<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ClockInController extends Controller
{
    public function index()
    {
        return view('employee.index');
    }

    public function store(Request $request)
    {
        return redirect()->back()->with('success', 'Ponto registrado com sucesso!');
    }
} 