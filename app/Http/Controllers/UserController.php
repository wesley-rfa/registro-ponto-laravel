<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchCepRequest;
use App\Http\Resources\CepResource;
use App\Http\Resources\ErrorResource;
use App\Services\External\Cep\CepService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class UserController extends Controller
{
    public function __construct(
        private CepService $cepService
    ) {}

    public function index()
    {
        return view('admin.users');
    }

    public function searchCep(SearchCepRequest $request): JsonResponse
    {
        $cep = $request->input('cep');
        $resultado = $this->cepService->searchByCep($cep);

        if (!$resultado) {
            $errorResource = new ErrorResource('CEP não encontrado', Response::HTTP_NOT_FOUND);
            return response()->json($errorResource, $errorResource->getStatusCode());
        }

        return response()->json(new CepResource($resultado));
    }
}
