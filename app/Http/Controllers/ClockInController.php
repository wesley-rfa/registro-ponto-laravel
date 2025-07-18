<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ClockInService;
use App\Dtos\ClockIn\ListFilterDto;
use App\Dtos\ClockIn\CreateClockInDto;
use App\Exceptions\DuplicateClockInException;
use App\Http\Requests\ListClockInsRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ClockInController extends Controller
{
    public function __construct(private ClockInService $clockInService) {}

    public function index()
    {
        return view('employee.index');
    }

    public function registers(ListClockInsRequest $request)
    {
        $clockIns = $this->clockInService->findAll(ListFilterDto::create($request->validated()));
        return view('admin.registers', compact('clockIns'));
    }

    public function store()
    {
        try {
            $dto = CreateClockInDto::create();
            $this->clockInService->create($dto);

            return redirect()->back()->with('success', 'Ponto registrado com sucesso!');
        } catch (DuplicateClockInException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            Log::error('Erro ao registrar ponto', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return redirect()->back()->with('error', 'Erro ao registrar ponto: ' . $e->getMessage());
        }
    }
}
