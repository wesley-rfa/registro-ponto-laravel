<?php

namespace Tests\Unit;

use App\Http\Requests\DeleteUserRequest;
use App\Models\User;
use App\Enums\UserRoleEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class DeleteUserRequestTest extends TestCase
{
    use RefreshDatabase;

    private DeleteUserRequest $request;

    protected function setUp(): void
    {
        parent::setUp();
        $this->request = new DeleteUserRequest();
    }

    public function test_authorize_returns_true()
    {
        $this->assertTrue($this->request->authorize());
    }

    public function test_rules_returns_correct_validation_rules()
    {
        $rules = $this->request->rules();

        $this->assertIsArray($rules);
        $this->assertArrayHasKey('user_id', $rules);
        $this->assertEquals([
            'required',
            'integer',
            'exists:users,id',
        ], $rules['user_id']);
    }

    public function test_messages_returns_correct_custom_messages()
    {
        $messages = $this->request->messages();

        $this->assertIsArray($messages);
        $this->assertArrayHasKey('user_id.required', $messages);
        $this->assertArrayHasKey('user_id.integer', $messages);
        $this->assertArrayHasKey('user_id.exists', $messages);

        $this->assertEquals('ID do usuário é obrigatório.', $messages['user_id.required']);
        $this->assertEquals('ID do usuário deve ser um número inteiro.', $messages['user_id.integer']);
        $this->assertEquals('Usuário não encontrado.', $messages['user_id.exists']);
    }

    public function test_with_validator_prevents_admin_deletion()
    {
        $admin = User::factory()->create(['role' => UserRoleEnum::ADMIN]);

        $this->request->merge(['user_id' => $admin->id]);

        $validator = Validator::make([], []);
        
        $this->request->withValidator($validator);

        $this->assertTrue($validator->errors()->has('user_id'));
        $this->assertEquals('Não é possível excluir um administrador.', $validator->errors()->first('user_id'));
    }

    public function test_with_validator_allows_employee_deletion()
    {
        $employee = User::factory()->create(['role' => UserRoleEnum::EMPLOYEE]);

        $this->request->merge(['user_id' => $employee->id]);

        $validator = Validator::make([], []);
        
        $this->request->withValidator($validator);

        $this->assertFalse($validator->errors()->has('user_id'));
    }

    public function test_with_validator_handles_null_user_id()
    {
        $this->request->merge([]);

        $validator = Validator::make([], []);
        
        $this->request->withValidator($validator);

        $this->assertFalse($validator->errors()->has('user_id'));
    }

    public function test_with_validator_handles_non_existent_user()
    {
        $this->request->merge(['user_id' => 99999]);

        $validator = Validator::make([], []);
        
        $this->request->withValidator($validator);

        $this->assertFalse($validator->errors()->has('user_id'));
    }

    public function test_validation_passes_with_valid_employee_user_id()
    {
        $employee = User::factory()->create(['role' => UserRoleEnum::EMPLOYEE]);

        $validator = Validator::make(
            ['user_id' => $employee->id],
            $this->request->rules()
        );

        $this->assertTrue($validator->passes());
    }

    public function test_validation_fails_without_user_id()
    {
        $validator = Validator::make(
            [],
            $this->request->rules()
        );

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('user_id'));
    }

    public function test_validation_fails_with_invalid_user_id_type()
    {
        $validator = Validator::make(
            ['user_id' => 'not-an-integer'],
            $this->request->rules()
        );

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('user_id'));
    }

    public function test_validation_fails_with_non_existent_user_id()
    {
        $validator = Validator::make(
            ['user_id' => 99999],
            $this->request->rules()
        );

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('user_id'));
    }
} 