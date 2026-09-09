<?php

namespace Database\Seeders\Themes\Main;

use Botble\Contact\Enums\ContactStatusEnum;
use Botble\Contact\Models\Contact;
use Botble\Theme\Database\Seeders\ThemeSeeder;
use Illuminate\Support\Arr;

class ContactSeeder extends ThemeSeeder
{
    public function run(): void
    {
        Contact::query()->truncate();

        $names = $this->getNames();
        $emails = $this->getEmails();
        $phones = $this->getPhones();
        $addresses = $this->getAddresses();
        $subjects = $this->getSubjects();
        $contents = $this->getContents();
        $statuses = [ContactStatusEnum::READ, ContactStatusEnum::UNREAD];

        for ($i = 0; $i < 10; $i++) {
            Contact::query()->create([
                'name' => Arr::random($names),
                'email' => Arr::random($emails),
                'phone' => Arr::random($phones),
                'address' => Arr::random($addresses),
                'subject' => Arr::random($subjects),
                'content' => Arr::random($contents),
                'status' => Arr::random($statuses),
            ]);
        }
    }

    protected function getNames(): array
    {
        return [
            'John Smith',
            'Emma Wilson',
            'Michael Brown',
            'Sarah Davis',
            'James Johnson',
            'Emily Taylor',
            'Robert Anderson',
            'Jennifer Martinez',
            'David Thompson',
            'Lisa Garcia',
        ];
    }

    protected function getEmails(): array
    {
        return [
            'john.smith@example.com',
            'emma.wilson@example.com',
            'michael.brown@example.com',
            'sarah.davis@example.com',
            'james.johnson@example.com',
            'emily.taylor@example.com',
            'robert.anderson@example.com',
            'jennifer.martinez@example.com',
            'david.thompson@example.com',
            'lisa.garcia@example.com',
        ];
    }

    protected function getPhones(): array
    {
        return [
            '+1-555-0101',
            '+1-555-0102',
            '+1-555-0103',
            '+1-555-0104',
            '+1-555-0105',
            '+1-555-0106',
            '+1-555-0107',
            '+1-555-0108',
            '+1-555-0109',
            '+1-555-0110',
        ];
    }

    protected function getAddresses(): array
    {
        return [
            '123 Main Street, Los Angeles, CA 90001',
            '456 Oak Avenue, New York, NY 10001',
            '789 Pine Road, Houston, TX 77001',
            '321 Maple Drive, Miami, FL 33101',
            '654 Cedar Lane, Chicago, IL 60601',
            '987 Birch Boulevard, Phoenix, AZ 85001',
            '147 Elm Court, San Diego, CA 92101',
            '258 Walnut Way, Dallas, TX 75201',
            '369 Cherry Circle, Austin, TX 78701',
            '741 Spruce Street, Denver, CO 80201',
        ];
    }

    protected function getSubjects(): array
    {
        return [
            'Question about product availability',
            'Shipping inquiry',
            'Return request assistance',
            'Product recommendation needed',
            'Order status inquiry',
            'Payment issue',
            'Bulk order inquiry',
            'Partnership opportunity',
            'Feedback and suggestions',
            'Technical support needed',
        ];
    }

    protected function getContents(): array
    {
        return [
            'Hello, I am interested in learning more about your products. Could you please provide additional information about availability and pricing? I would appreciate a prompt response. Thank you for your assistance.',
            'I recently placed an order and would like to know the estimated delivery time. My order number is included in this message. Please let me know when I can expect my package to arrive.',
            'I received my order but one of the items was damaged during shipping. I would like to request a replacement or refund. Please advise on how to proceed with this matter.',
            'I am looking for a specific product but cannot find it on your website. Could you help me locate it or suggest similar alternatives? I would really appreciate your assistance.',
            'Thank you for the excellent service! I wanted to share my positive experience with your team. The products arrived on time and exceeded my expectations. Keep up the great work!',
            'I have a question about your return policy. How many days do I have to return an item? Are there any conditions or restrictions I should be aware of before making a purchase?',
            'I am interested in placing a bulk order for my business. Could you provide information about wholesale pricing and minimum order quantities? Looking forward to hearing from you.',
            'I noticed an error with my account information. Could you please help me update my details? I have tried to do it myself but encountered some issues with the system.',
            'Your website is very user-friendly and I found exactly what I was looking for. However, I have a suggestion for improvement that might help other customers as well.',
            'I would like to inquire about gift wrapping options for my order. Is this service available? If so, what are the charges and how can I add it to my order?',
        ];
    }
}
