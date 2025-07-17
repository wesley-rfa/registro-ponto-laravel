<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchCepRequest;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\CepResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\ErrorResource;
use App\Dtos\User\CreateUserDto;
use App\Dtos\User\UpdateUserDto;
use App\Dtos\User\DeleteUserDto;
use App\Dtos\User\FindUserDto;
use App\Services\External\Cep\CepService;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse;
use App\Models\User;
use App\Enums\UserRoleEnum;
use Illuminate\Support\Facades\Auth;

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

    public function show(User $user): JsonResponse
    {
        try {
            $dto = FindUserDto::createFromId($user->id);
            $user = $this->userService->findById($dto);

            if (!$user) {
                $errorResource = new ErrorResource('Usuário não encontrado', Response::HTTP_NOT_FOUND);
                return response()->json($errorResource, $errorResource->getStatusCode());
            }

            return response()->json(new UserResource($user));
        } catch (\Exception $e) {
            $errorResource = new ErrorResource('Erro interno do servidor', Response::HTTP_INTERNAL_SERVER_ERROR);
            return response()->json($errorResource, $errorResource->getStatusCode());
        }
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        try {
            $this->userService->update($user, UpdateUserDto::createFromRequest($request));

            return redirect()->route('admin.users')
                ->with('success', 'Usuário atualizado com sucesso!');
        } catch (\Exception $e) {
            return redirect()->route('admin.users')
                ->withInput()
                ->with('error', 'Erro ao atualizar usuário. Tente novamente.');
        }
    }

    public function destroy(User $user): RedirectResponse
    {
        try {
            if ($user->role === UserRoleEnum::ADMIN && $user->id !== Auth::id()) {
                return redirect()->route('admin.users')
                    ->with('error', 'Não é possível excluir outro administrador.');
            }

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
