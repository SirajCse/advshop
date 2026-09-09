<?php

namespace Botble\Ecommerce\Tests\Unit;

use Botble\Base\Supports\EmailAbstract;
use Botble\Ecommerce\Enums\InvoiceStatusEnum;
use Botble\Ecommerce\Enums\OrderStatusEnum;
use Botble\Ecommerce\Enums\ShippingMethodEnum;
use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Models\Invoice;
use Botble\Ecommerce\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InvoiceEmailAttachmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    protected function createCustomer(): Customer
    {
        return Customer::query()->create([
            'name' => 'Test Customer',
            'email' => 'customer@example.com',
            'password' => bcrypt('password'),
        ]);
    }

    protected function createOrder(Customer $customer): Order
    {
        return Order::query()->create([
            'user_id' => $customer->id,
            'amount' => 100,
            'sub_total' => 100,
            'status' => OrderStatusEnum::COMPLETED,
            'shipping_method' => ShippingMethodEnum::DEFAULT,
            'is_finished' => true,
        ]);
    }

    protected function createInvoice(Order $order): Invoice
    {
        return Invoice::query()->create([
            'code' => 'INV-' . uniqid(),
            'reference_id' => $order->id,
            'reference_type' => Order::class,
            'customer_name' => 'Test Customer',
            'customer_email' => 'customer@example.com',
            'customer_phone' => '1234567890',
            'customer_address' => '123 Test Street',
            'status' => InvoiceStatusEnum::COMPLETED,
            'amount' => 100,
            'sub_total' => 100,
        ]);
    }

    public function test_email_abstract_handles_raw_data_attachment(): void
    {
        $pdfContent = '%PDF-1.4 test content';
        $attachments = [
            [
                'data' => $pdfContent,
                'name' => 'invoice-123.pdf',
                'mime' => 'application/pdf',
            ],
        ];

        $email = new EmailAbstract(
            'Test email content',
            'Test Subject',
            ['attachments' => $attachments]
        );

        $builtEmail = $email->build();

        $this->assertNotNull($builtEmail);
        $this->assertCount(1, $builtEmail->rawAttachments);
        $this->assertEquals($pdfContent, $builtEmail->rawAttachments[0]['data']);
        $this->assertEquals('invoice-123.pdf', $builtEmail->rawAttachments[0]['name']);
        $this->assertEquals('application/pdf', $builtEmail->rawAttachments[0]['options']['mime']);
    }

    public function test_email_abstract_handles_file_path_with_metadata(): void
    {
        $filePath = Storage::disk('local')->path('test-invoice.pdf');
        File::put($filePath, '%PDF-1.4 test content');

        $attachments = [
            [
                'file' => $filePath,
                'name' => 'custom-name.pdf',
                'mime' => 'application/pdf',
            ],
        ];

        $email = new EmailAbstract(
            'Test email content',
            'Test Subject',
            ['attachments' => $attachments]
        );

        $builtEmail = $email->build();

        $this->assertNotNull($builtEmail);
        $this->assertCount(1, $builtEmail->attachments);
        $this->assertEquals($filePath, $builtEmail->attachments[0]['file']);
        $this->assertEquals('custom-name.pdf', $builtEmail->attachments[0]['options']['as']);

        File::delete($filePath);
    }

    public function test_email_abstract_handles_plain_file_path(): void
    {
        $filePath = Storage::disk('local')->path('test-invoice.pdf');
        File::put($filePath, '%PDF-1.4 test content');

        $attachments = [$filePath];

        $email = new EmailAbstract(
            'Test email content',
            'Test Subject',
            ['attachments' => $attachments]
        );

        $builtEmail = $email->build();

        $this->assertNotNull($builtEmail);
        $this->assertCount(1, $builtEmail->attachments);
        $this->assertEquals($filePath, $builtEmail->attachments[0]['file']);

        File::delete($filePath);
    }

    public function test_email_abstract_handles_mixed_attachment_formats(): void
    {
        $filePath1 = Storage::disk('local')->path('test-invoice-1.pdf');
        $filePath2 = Storage::disk('local')->path('test-invoice-2.pdf');
        File::put($filePath1, '%PDF-1.4 test content 1');
        File::put($filePath2, '%PDF-1.4 test content 2');

        $attachments = [
            [
                'data' => 'raw pdf content',
                'name' => 'raw-attachment.pdf',
                'mime' => 'application/pdf',
            ],
            [
                'file' => $filePath1,
                'name' => 'file-attachment.pdf',
            ],
            $filePath2,
        ];

        $email = new EmailAbstract(
            'Test email content',
            'Test Subject',
            ['attachments' => $attachments]
        );

        $builtEmail = $email->build();

        $this->assertNotNull($builtEmail);
        $this->assertCount(1, $builtEmail->rawAttachments);
        $this->assertCount(2, $builtEmail->attachments);

        File::delete($filePath1);
        File::delete($filePath2);
    }

    public function test_email_abstract_handles_empty_attachments(): void
    {
        $email = new EmailAbstract(
            'Test email content',
            'Test Subject',
            ['attachments' => []]
        );

        $builtEmail = $email->build();

        $this->assertNotNull($builtEmail);
        $this->assertEmpty($builtEmail->attachments);
        $this->assertEmpty($builtEmail->rawAttachments);
    }

    public function test_email_abstract_handles_no_attachments_key(): void
    {
        $email = new EmailAbstract(
            'Test email content',
            'Test Subject',
            []
        );

        $builtEmail = $email->build();

        $this->assertNotNull($builtEmail);
        $this->assertEmpty($builtEmail->attachments);
        $this->assertEmpty($builtEmail->rawAttachments);
    }

    public function test_raw_data_attachment_defaults_to_generic_mime_type(): void
    {
        $attachments = [
            [
                'data' => 'some binary content',
                'name' => 'file.bin',
            ],
        ];

        $email = new EmailAbstract(
            'Test email content',
            'Test Subject',
            ['attachments' => $attachments]
        );

        $builtEmail = $email->build();

        $this->assertEquals('application/octet-stream', $builtEmail->rawAttachments[0]['options']['mime']);
    }

    public function test_raw_data_attachment_defaults_to_generic_filename(): void
    {
        $attachments = [
            [
                'data' => 'some binary content',
            ],
        ];

        $email = new EmailAbstract(
            'Test email content',
            'Test Subject',
            ['attachments' => $attachments]
        );

        $builtEmail = $email->build();

        $this->assertEquals('attachment', $builtEmail->rawAttachments[0]['name']);
    }

    public function test_invoice_helper_generates_pdf_file(): void
    {
        $customer = $this->createCustomer();
        $order = $this->createOrder($customer);
        $invoice = $this->createInvoice($order);

        $this->markTestSkipped('PDF generation requires full application setup');
    }

    public function test_attachment_data_is_serializable_for_queue(): void
    {
        $pdfContent = '%PDF-1.4 test content with special chars: äöü';
        $attachments = [
            [
                'data' => $pdfContent,
                'name' => 'invoice-123.pdf',
                'mime' => 'application/pdf',
            ],
        ];

        $serialized = serialize(['attachments' => $attachments]);
        $unserialized = unserialize($serialized);

        $this->assertEquals($attachments, $unserialized['attachments']);
        $this->assertEquals($pdfContent, $unserialized['attachments'][0]['data']);
    }

    public function test_large_attachment_data_is_serializable(): void
    {
        $largePdfContent = str_repeat('%PDF-1.4 ', 10000);
        $attachments = [
            [
                'data' => $largePdfContent,
                'name' => 'large-invoice.pdf',
                'mime' => 'application/pdf',
            ],
        ];

        $serialized = serialize(['attachments' => $attachments]);
        $unserialized = unserialize($serialized);

        $this->assertEquals(strlen($largePdfContent), strlen($unserialized['attachments'][0]['data']));
    }
}
