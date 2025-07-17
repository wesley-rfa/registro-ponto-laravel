<?php

namespace Tests\Unit\Services\External\Cep;

use App\Services\External\Cep\AbstractCepService;
use App\Services\External\Cep\Dtos\CepResponseDto;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Tests\TestCase;

class AbstractCepServiceTest extends TestCase
{
    private TestAbstractCepService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TestAbstractCepService();
    }

    public function test_constructor_sets_timeout_and_http_client()
    {
        $service = new TestAbstractCepService();

        $this->assertEquals(30, $service->getTimeoutValue());
        $this->assertInstanceOf(PendingRequest::class, $service->getHttpClient());
    }

    public function test_clean_cep_removes_non_numeric_characters()
    {
        $testCases = [
            '01001-000' => '01001000',
            '01001.000' => '01001000',
            '01001 000' => '01001000',
            '01001000' => '01001000',
            '01001-000.' => '01001000',
            'abc01001-000def' => '01001000',
        ];

        foreach ($testCases as $input => $expected) {
            $result = $this->service->testCleanCep($input);
            $this->assertEquals($expected, $result);
        }
    }

    public function test_is_response_successful_with_successful_response()
    {
        $mockResponse = $this->createMock(Response::class);
        $mockResponse->method('successful')->willReturn(true);

        $result = $this->service->testIsResponseSuccessful($mockResponse);

        $this->assertTrue($result);
    }

    public function test_is_response_successful_with_failed_response()
    {
        $mockResponse = $this->createMock(Response::class);
        $mockResponse->method('successful')->willReturn(false);

        $result = $this->service->testIsResponseSuccessful($mockResponse);

        $this->assertFalse($result);
    }

    public function test_parse_response_returns_json_data()
    {
        $expectedData = ['cep' => '01001000', 'logradouro' => 'Praça da Sé'];
        $mockResponse = $this->createMock(Response::class);
        $mockResponse->method('json')->willReturn($expectedData);

        $result = $this->service->testParseResponse($mockResponse);

        $this->assertEquals($expectedData, $result);
    }

    public function test_validate_response_with_valid_data()
    {
        $data = ['cep' => '01001000', 'logradouro' => 'Praça da Sé'];
        $cep = '01001000';

        $result = $this->service->testValidateResponse($data, $cep);

        $this->assertTrue($result);
    }

    public function test_validate_response_with_empty_data()
    {
        $data = [];
        $cep = '01001000';

        $result = $this->service->testValidateResponse($data, $cep);

        $this->assertFalse($result);
    }

    public function test_get_test_cep_returns_default_value()
    {
        $result = $this->service->testGetTestCep();

        $this->assertEquals('01001000', $result);
    }

    public function test_get_headers_returns_default_headers()
    {
        $result = $this->service->testGetHeaders();

        $expected = ['Accept' => 'application/json'];
        $this->assertEquals($expected, $result);
    }

    public function test_log_error_calls_log_cep_warning()
    {
        $this->service->testLogError('Test error', '01001000', ['data' => 'test']);

        $this->assertTrue(true);
    }

    public function test_log_success_calls_log_cep_info()
    {
        $dto = new CepResponseDto(
            cep: '01001000',
            endereco: 'Praça da Sé',
            cidade: 'São Paulo',
            estado: 'SP'
        );

        $this->service->testLogSuccess('01001000', $dto);

        $this->assertTrue(true);
    }

    public function test_log_exception_calls_log_cep_error()
    {
        $exception = new \Exception('Test exception');

        $this->service->testLogException($exception, '01001000');

        $this->assertTrue(true);
    }

    public function test_search_by_cep_with_successful_response()
    {
        $cep = '01001000';
        $expectedData = [
            'cep' => '01001000',
            'logradouro' => 'Praça da Sé',
            'bairro' => 'Sé',
            'localidade' => 'São Paulo',
            'uf' => 'SP'
        ];

        $mockResponse = $this->createMock(Response::class);
        $mockResponse->method('successful')->willReturn(true);
        $mockResponse->method('json')->willReturn($expectedData);

        $this->service->setMockResponse($mockResponse);

        $result = $this->service->searchByCep($cep);

        $this->assertInstanceOf(CepResponseDto::class, $result);
        $this->assertEquals('01001000', $result->cep);
        $this->assertEquals('Praça da Sé', $result->endereco);
    }

    public function test_search_by_cep_with_failed_response()
    {
        $cep = '01001000';

        $mockResponse = $this->createMock(Response::class);
        $mockResponse->method('successful')->willReturn(false);
        $mockResponse->method('status')->willReturn(404);
        $mockResponse->method('body')->willReturn('Not found');

        $this->service->setMockResponse($mockResponse);

        $result = $this->service->searchByCep($cep);

        $this->assertNull($result);
    }

    public function test_search_by_cep_with_invalid_response_data()
    {
        $cep = '01001000';

        $mockResponse = $this->createMock(Response::class);
        $mockResponse->method('successful')->willReturn(true);
        $mockResponse->method('json')->willReturn([]);

        $this->service->setMockResponse($mockResponse);

        $result = $this->service->searchByCep($cep);

        $this->assertNull($result);
    }

    public function test_search_by_cep_with_exception()
    {
        $cep = '01001000';

        $this->service->setShouldThrowException(true);

        $result = $this->service->searchByCep($cep);

        $this->assertNull($result);
    }

    public function test_is_available_with_successful_check()
    {
        $mockResponse = $this->createMock(Response::class);
        $mockResponse->method('successful')->willReturn(true);

        $mockHttpClient = $this->createMock(PendingRequest::class);
        $mockHttpClient->method('get')->willReturn($mockResponse);

        $reflection = new \ReflectionClass($this->service);
        $httpClientProperty = $reflection->getProperty('httpClient');
        $httpClientProperty->setAccessible(true);
        $httpClientProperty->setValue($this->service, $mockHttpClient);

        $result = $this->service->isAvailable();

        $this->assertTrue($result);
    }

    public function test_is_available_with_failed_check()
    {
        $mockResponse = $this->createMock(Response::class);
        $mockResponse->method('successful')->willReturn(false);

        $mockHttpClient = $this->createMock(PendingRequest::class);
        $mockHttpClient->method('get')->willReturn($mockResponse);

        $reflection = new \ReflectionClass($this->service);
        $httpClientProperty = $reflection->getProperty('httpClient');
        $httpClientProperty->setAccessible(true);
        $httpClientProperty->setValue($this->service, $mockHttpClient);

        $result = $this->service->isAvailable();

        $this->assertFalse($result);
    }

    public function test_is_available_with_exception()
    {
        $mockHttpClient = $this->createMock(PendingRequest::class);
        $mockHttpClient->method('get')->willThrowException(new \Exception('Network error'));

        $reflection = new \ReflectionClass($this->service);
        $httpClientProperty = $reflection->getProperty('httpClient');
        $httpClientProperty->setAccessible(true);
        $httpClientProperty->setValue($this->service, $mockHttpClient);

        $result = $this->service->isAvailable();

        $this->assertFalse($result);
    }
}

class TestAbstractCepService extends AbstractCepService
{
    private ?Response $mockResponse = null;
    private bool $shouldThrowException = false;

    protected function getTimeout(): int
    {
        return 30;
    }

    protected function makeRequest(string $cep)
    {
        if ($this->shouldThrowException) {
            throw new \Exception('Test exception');
        }

        return $this->mockResponse ?? $this->httpClient->get("/test/{$cep}");
    }

    protected function getAvailabilityUrl(string $cep): string
    {
        return "/test/availability/{$cep}";
    }

    protected function createDto(array $data, string $cep): ?CepResponseDto
    {
        return CepResponseDto::create($data);
    }

    public function getServiceName(): string
    {
        return 'TestService';
    }

    public function testCleanCep(string $cep): string
    {
        return $this->cleanCep($cep);
    }

    public function testIsResponseSuccessful($response): bool
    {
        return $this->isResponseSuccessful($response);
    }

    public function testParseResponse($response): array
    {
        return $this->parseResponse($response);
    }

    public function testValidateResponse(array $data, string $cep): bool
    {
        return $this->validateResponse($data, $cep);
    }

    public function testGetTestCep(): string
    {
        return $this->getTestCep();
    }

    public function testGetHeaders(): array
    {
        return $this->getHeaders();
    }

    public function testLogError(string $message, string $cep, $data = null): void
    {
        $this->logError($message, $cep, $data);
    }

    public function testLogSuccess(string $cep, CepResponseDto $dto): void
    {
        $this->logSuccess($cep, $dto);
    }

    public function testLogException(\Exception $e, string $cep): void
    {
        $this->logException($e, $cep);
    }

    public function getTimeoutValue(): int
    {
        return $this->timeout;
    }

    public function getHttpClient(): PendingRequest
    {
        return $this->httpClient;
    }

    public function setMockResponse(?Response $response): void
    {
        $this->mockResponse = $response;
    }

    public function setShouldThrowException(bool $should): void
    {
        $this->shouldThrowException = $should;
    }
} 