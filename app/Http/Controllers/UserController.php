<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchCepRequest;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\DeleteUserRequest;
use App\Http\Resources\CepResource;
use App\Http\Resources\ErrorResource;
use App\Dtos\User\CreateUserDto;
use App\Dtos\User\DeleteUserDto;
use App\Services\External\Cep\CepService;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse;
use App\Models\User;

class UserController extends Controller
{
    public function __construct(
        private CepService $cepService,
        private UserService $userService
    ) {}

    public function index()
    {
        $users = $this->userService->findAll();

        return view('admin.users', compact('users'));
    }

    public function store(CreateUserRequest $request): RedirectResponse
    {
        try {
            $this->userService->create(CreateUserDto::createFromRequest($request));

            return redirect()->route('admin.users')
                ->with('success', 'Funcionário cadastrado com sucesso!');
        } catch (\Exception $e) {
            return redirect()->route('admin.users')
                ->withInput()
                ->with('error', 'Erro ao cadastrar funcionário. Tente novamente.');
        }
    }

    public function destroy(DeleteUserRequest $request): RedirectResponse
    {
        try {
            $userId = $request->input('user_id');
            $user = User::findOrFail($userId);
            
            $dto = DeleteUserDto::createFromId($user->id);
            $deleted = $this->userService->delete($dto);

            if (!$deleted) {
                return redirect()->route('admin.users')
                    ->with('error', 'Erro ao excluir usuário. Usuário não encontrado.');
            }

            return redirect()->route('admin.users')
                ->with('success', 'Usuário excluído com sucesso!');

        } catch (\Exception $e) {
            return redirect()->route('admin.users')
                ->with('error', 'Erro interno. Tente novamente.');
        }
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
