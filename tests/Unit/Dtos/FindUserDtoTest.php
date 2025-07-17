<?php

namespace Tests\Unit\Dtos;

use App\Dtos\User\FindUserDto;
use Tests\TestCase;

class FindUserDtoTest extends TestCase
{
    public function test_creates_dto_with_user_id()
    {
        $userId = 345;
        
        $dto = new FindUserDto($userId);

        $this->assertEquals($userId, $dto->userId);
    }

    public function test_create_from_id_creates_dto()
    {
        $userId = 678;
        
        $dto = FindUserDto::createFromId($userId);

        $this->assertEquals($userId, $dto->userId);
    }

    public function test_to_array_returns_correct_structure()
    {
        $userId = 901;
        
        $dto = new FindUserDto($userId);

        $array = $dto->toArray();

        $expected = [
            'user_id' => $userId,
        ];

        $this->assertEquals($expected, $array);
    }

    public function test_property_is_readonly()
    {
        $dto = new FindUserDto(234);

        $this->assertTrue(property_exists($dto, 'userId'));
        
        $this->assertEquals(234, $dto->userId);
    }

    public function test_works_with_zero_user_id()
    {
        $dto = new FindUserDto(0);

        $this->assertEquals(0, $dto->userId);
        
        $array = $dto->toArray();
        $this->assertEquals(['user_id' => 0], $array);
    }

    public function test_works_with_negative_user_id()
    {
        $dto = new FindUserDto(-5);

        $this->assertEquals(-5, $dto->userId);
        
        $array = $dto->toArray();
        $this->assertEquals(['user_id' => -5], $array);
    }
} 