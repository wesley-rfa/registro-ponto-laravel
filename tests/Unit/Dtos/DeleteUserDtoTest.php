<?php

namespace Tests\Unit\Dtos;

use App\Dtos\User\DeleteUserDto;
use Tests\TestCase;

class DeleteUserDtoTest extends TestCase
{
    public function test_creates_dto_with_user_id()
    {
        $userId = 567;
        
        $dto = new DeleteUserDto($userId);

        $this->assertEquals($userId, $dto->userId);
    }

    public function test_create_from_id_creates_dto()
    {
        $userId = 890;
        
        $dto = DeleteUserDto::createFromId($userId);

        $this->assertEquals($userId, $dto->userId);
    }

    public function test_to_array_returns_correct_structure()
    {
        $userId = 123;
        
        $dto = new DeleteUserDto($userId);

        $array = $dto->toArray();

        $expected = [
            'user_id' => $userId,
        ];

        $this->assertEquals($expected, $array);
    }

    public function test_property_is_readonly()
    {
        $dto = new DeleteUserDto(456);

        $this->assertTrue(property_exists($dto, 'userId'));
        
        $this->assertEquals(456, $dto->userId);
    }

    public function test_works_with_zero_user_id()
    {
        $dto = new DeleteUserDto(0);

        $this->assertEquals(0, $dto->userId);
        
        $array = $dto->toArray();
        $this->assertEquals(['user_id' => 0], $array);
    }

    public function test_works_with_negative_user_id()
    {
        $dto = new DeleteUserDto(-3);

        $this->assertEquals(-3, $dto->userId);
        
        $array = $dto->toArray();
        $this->assertEquals(['user_id' => -3], $array);
    }
} 