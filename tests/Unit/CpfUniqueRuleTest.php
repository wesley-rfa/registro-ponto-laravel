<?php

namespace Tests\Unit;

use App\Rules\CpfUniqueRule;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;
use Mockery;

class CpfUniqueRuleTest extends TestCase
{
    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_cpf_is_unique_passes_when_cpf_not_exists()
    {
        $mockRepo = Mockery::mock(UserRepositoryInterface::class);
        $mockRepo->shouldReceive('existsByCpf')->with('12345678900', null)->andReturn(false);
        App::instance(UserRepositoryInterface::class, $mockRepo);

        $rule = new CpfUniqueRule();
        $validator = Validator::make([
            'cpf' => '123.456.789-00',
        ], [
            'cpf' => [$rule],
        ]);

        $this->assertTrue($validator->passes());
    }

    public function test_cpf_is_unique_fails_when_cpf_exists()
    {
        $mockRepo = Mockery::mock(UserRepositoryInterface::class);
        $mockRepo->shouldReceive('existsByCpf')->with('12345678900', null)->andReturn(true);
        App::instance(UserRepositoryInterface::class, $mockRepo);

        $rule = new CpfUniqueRule();
        $validator = Validator::make([
            'cpf' => '123.456.789-00',
        ], [
            'cpf' => [$rule],
        ]);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('cpf', $validator->errors()->toArray());
    }

    public function test_cpf_is_unique_ignores_user_id()
    {
        $mockRepo = Mockery::mock(UserRepositoryInterface::class);
        $mockRepo->shouldReceive('existsByCpf')->with('12345678900', 5)->andReturn(false);
        App::instance(UserRepositoryInterface::class, $mockRepo);

        $rule = new CpfUniqueRule(5);
        $validator = Validator::make([
            'cpf' => '123.456.789-00',
        ], [
            'cpf' => [$rule],
        ]);

        $this->assertTrue($validator->passes());
    }
} 