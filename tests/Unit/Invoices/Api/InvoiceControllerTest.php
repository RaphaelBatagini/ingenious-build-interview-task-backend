<?php

declare(strict_types=1);

namespace Tests\Unit\Invoices\Api;

use App\Modules\Invoices\Api\InvoiceController;
use App\Modules\Invoices\Application\Exceptions\InvoiceNotFoundException;
use App\Modules\Invoices\Application\InvoiceService;
use App\Modules\Invoices\Application\Dto\CompanyDto;
use App\Modules\Invoices\Application\Dto\InvoiceDto;
use App\Modules\Invoices\Application\Dto\ProductDto;
use Illuminate\Http\JsonResponse;
use Mockery;
use Tests\TestCase;
use Ramsey\Uuid\Uuid;

class InvoiceControllerTest extends TestCase
{
    protected $invoiceService;
    protected $invoiceController;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a mock instance of the InvoiceService
        $this->invoiceService = Mockery::mock(InvoiceService::class);

        // Create an instance of the InvoiceController with the mock InvoiceService
        $this->invoiceController = new InvoiceController($this->invoiceService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testShow_ReturnsJsonResponseWithInvoiceData()
{
    // Arrange
    $invoiceId = Uuid::uuid4()->toString();
    $invoiceData = [
        'number' => 'INV-001',
        'status' => 'paid',
        'date' => '2022-01-01',
        'due_date' => '2022-01-31',
        'company' => [
            'name' => 'ABC Company',
            'street' => '123 Main St',
            'city' => 'City',
            'zip_code' => '12345',
            'phone' => '123-456-7890',
            'email' => 'someone@someemail.com',
        ],
        'products' => [
            [
                'name' => 'Product A',
                'quantity' => 2,
                'price' => 10.99,
                'total' => 21.98,
            ],
            [
                'name' => 'Product B',
                'quantity' => 1,
                'price' => 5.99,
                'total' => 5.99,
            ],
        ],
        'total_price' => 27.97,
    ];

    // Create an instance of the InvoiceDto
    $invoiceDto = new InvoiceDto(
        $invoiceData['number'],
        $invoiceData['status'],
        $invoiceData['date'],
        $invoiceData['due_date'],
        new CompanyDto(
            $invoiceData['company']['name'],
            $invoiceData['company']['street'],
            $invoiceData['company']['city'],
            $invoiceData['company']['zip_code'],
            $invoiceData['company']['phone'],
            $invoiceData['company']['email'],
        ),
        [
            new ProductDto(
                $invoiceData['products'][0]['name'],
                $invoiceData['products'][0]['quantity'],
                $invoiceData['products'][0]['price'],
                $invoiceData['products'][0]['total']
            ),
            new ProductDto(
                $invoiceData['products'][1]['name'],
                $invoiceData['products'][1]['quantity'],
                $invoiceData['products'][1]['price'],
                $invoiceData['products'][1]['total']
            ),
        ],
        $invoiceData['total_price']
    );

    // Set up the mock InvoiceService to return the InvoiceDto
    $this->invoiceService
        ->shouldReceive('getInvoiceById')
        ->once()
        ->with($invoiceId)
        ->andReturn($invoiceDto);

    // Act
    $response = $this->invoiceController->show($invoiceId);

    // Assert
    $this->assertInstanceOf(JsonResponse::class, $response);
    $this->assertEquals($invoiceData, $response->getData(true));
    $this->assertEquals(200, $response->getStatusCode());
}

    public function testShow_ReturnsJsonResponseWithErrorWhenInvoiceNotFound()
    {
        // Arrange
        $invoiceId = Uuid::uuid4()->toString();

        // Set up the InvoiceService to throw an InvoiceNotFoundException
        $this->invoiceService
            ->shouldReceive('getInvoiceById')
            ->once()
            ->with($invoiceId)
            ->andThrow(new InvoiceNotFoundException('Invoice not found.'));

        // Act
        $response = $this->invoiceController->show($invoiceId);

        // Assert
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(['error' => 'Invoice not found.'], $response->getData(true));
        $this->assertEquals(404, $response->getStatusCode());
    }
}
