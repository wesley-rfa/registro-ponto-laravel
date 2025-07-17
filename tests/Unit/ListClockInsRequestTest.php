<?php

namespace Tests\Unit;

use App\Http\Requests\ListClockInsRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ListClockInsRequestTest extends TestCase
{
    use RefreshDatabase;

    private ListClockInsRequest $request;

    protected function setUp(): void
    {
        parent::setUp();
        $this->request = new ListClockInsRequest();
    }

    public function test_authorize_returns_true()
    {
        $this->assertTrue($this->request->authorize());
    }

    public function test_rules_returns_correct_validation_rules()
    {
        $rules = $this->request->rules();

        $this->assertIsArray($rules);
        $this->assertArrayHasKey('start_date', $rules);
        $this->assertArrayHasKey('end_date', $rules);

        $this->assertEquals('nullable|date', $rules['start_date']);
        $this->assertEquals('nullable|date', $rules['end_date']);
    }

    public function test_messages_returns_correct_custom_messages()
    {
        $messages = $this->request->messages();

        $this->assertIsArray($messages);
        $this->assertArrayHasKey('end_date.after_or_equal', $messages);
        $this->assertEquals('A data final deve ser igual ou posterior à data inicial.', $messages['end_date.after_or_equal']);
    }

    public function test_with_validator_allows_valid_date_range()
    {
        $this->request->merge([
            'start_date' => '2024-01-01',
            'end_date' => '2024-01-31'
        ]);

        $validator = Validator::make([], []);
        
        $this->request->withValidator($validator);

        $this->assertFalse($validator->errors()->has('end_date'));
    }

    public function test_with_validator_allows_same_start_and_end_date()
    {
        $this->request->merge([
            'start_date' => '2024-01-01',
            'end_date' => '2024-01-01'
        ]);

        $validator = Validator::make([], []);
        
        $this->request->withValidator($validator);

        $this->assertFalse($validator->errors()->has('end_date'));
    }

    public function test_with_validator_prevents_invalid_date_range()
    {
        $this->request->merge([
            'start_date' => '2024-01-31',
            'end_date' => '2024-01-01'
        ]);

        $validator = Validator::make([], []);
        
        $this->request->withValidator($validator);

        $this->assertTrue($validator->errors()->has('end_date'));
        $this->assertEquals('A data final deve ser igual ou posterior à data inicial.', $validator->errors()->first('end_date'));
    }

    public function test_with_validator_handles_only_start_date()
    {
        $this->request->merge([
            'start_date' => '2024-01-01'
        ]);

        $validator = Validator::make([], []);
        
        $this->request->withValidator($validator);

        $this->assertFalse($validator->errors()->has('end_date'));
    }

    public function test_with_validator_handles_only_end_date()
    {
        $this->request->merge([
            'end_date' => '2024-01-31'
        ]);

        $validator = Validator::make([], []);
        
        $this->request->withValidator($validator);

        $this->assertFalse($validator->errors()->has('end_date'));
    }

    public function test_with_validator_handles_no_dates()
    {
        $this->request->merge([]);

        $validator = Validator::make([], []);
        
        $this->request->withValidator($validator);

        $this->assertFalse($validator->errors()->has('end_date'));
    }

    public function test_validation_passes_with_valid_start_date()
    {
        $validator = Validator::make(
            ['start_date' => '2024-01-01'],
            $this->request->rules()
        );

        $this->assertTrue($validator->passes());
    }

    public function test_validation_passes_with_valid_end_date()
    {
        $validator = Validator::make(
            ['end_date' => '2024-01-31'],
            $this->request->rules()
        );

        $this->assertTrue($validator->passes());
    }

    public function test_validation_passes_with_both_valid_dates()
    {
        $validator = Validator::make(
            [
                'start_date' => '2024-01-01',
                'end_date' => '2024-01-31'
            ],
            $this->request->rules()
        );

        $this->assertTrue($validator->passes());
    }

    public function test_validation_passes_without_dates()
    {
        $validator = Validator::make(
            [],
            $this->request->rules()
        );

        $this->assertTrue($validator->passes());
    }

    public function test_validation_fails_with_invalid_start_date_format()
    {
        $validator = Validator::make(
            ['start_date' => 'invalid-date'],
            $this->request->rules()
        );

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('start_date'));
    }

    public function test_validation_fails_with_invalid_end_date_format()
    {
        $validator = Validator::make(
            ['end_date' => 'invalid-date'],
            $this->request->rules()
        );

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('end_date'));
    }

    public function test_validation_passes_with_different_date_formats()
    {
        $validator = Validator::make(
            [
                'start_date' => '2024-01-01',
                'end_date' => '2024-01-31 23:59:59'
            ],
            $this->request->rules()
        );

        $this->assertTrue($validator->passes());
    }
} 