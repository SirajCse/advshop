<?php

namespace Botble\Contact\Database\Seeders;

use Botble\Base\Supports\BaseSeeder;
use Botble\Contact\Enums\ContactStatusEnum;
use Botble\Contact\Models\Contact;

class ContactSeeder extends BaseSeeder
{
    public function run(): void
    {
        Contact::query()->truncate();

        $contacts = [
            [
                'name' => 'John Smith',
                'email' => 'john.smith@example.com',
                'phone' => '+1 (555) 123-4567',
                'address' => '123 Main Street, New York, NY 10001',
                'subject' => 'General Inquiry',
                'content' => 'Hello, I am interested in learning more about your services. Could you please provide me with additional information about pricing and availability? I would appreciate a prompt response. Thank you for your time.',
                'status' => ContactStatusEnum::READ,
            ],
            [
                'name' => 'Emily Johnson',
                'email' => 'emily.johnson@example.com',
                'phone' => '+1 (555) 234-5678',
                'address' => '456 Oak Avenue, Los Angeles, CA 90001',
                'subject' => 'Partnership Opportunity',
                'content' => 'I represent a company that would like to explore potential partnership opportunities with your organization. We believe there could be mutual benefits from collaboration. Please let me know if you would be interested in scheduling a call to discuss further.',
                'status' => ContactStatusEnum::UNREAD,
            ],
            [
                'name' => 'Michael Brown',
                'email' => 'michael.brown@example.com',
                'phone' => '+1 (555) 345-6789',
                'address' => '789 Pine Road, Chicago, IL 60601',
                'subject' => 'Technical Support',
                'content' => 'I am experiencing some technical difficulties with your platform. The login feature seems to be not working properly on mobile devices. Could you please assist me in resolving this issue? I have attached screenshots for reference.',
                'status' => ContactStatusEnum::READ,
            ],
            [
                'name' => 'Sarah Davis',
                'email' => 'sarah.davis@example.com',
                'phone' => '+1 (555) 456-7890',
                'address' => '321 Elm Street, Houston, TX 77001',
                'subject' => 'Feedback and Suggestions',
                'content' => 'I wanted to share some feedback about your website. Overall, I find it user-friendly, but I think the navigation could be improved. Additionally, it would be great if you could add more filtering options to the search feature.',
                'status' => ContactStatusEnum::UNREAD,
            ],
            [
                'name' => 'James Wilson',
                'email' => 'james.wilson@example.com',
                'phone' => '+1 (555) 567-8901',
                'address' => '654 Maple Drive, Phoenix, AZ 85001',
                'subject' => 'Service Request',
                'content' => 'I would like to request a custom service package for my business. We have specific requirements that may not be covered by your standard offerings. Could we schedule a meeting to discuss our needs in detail?',
                'status' => ContactStatusEnum::READ,
            ],
            [
                'name' => 'Jennifer Taylor',
                'email' => 'jennifer.taylor@example.com',
                'phone' => '+1 (555) 678-9012',
                'address' => '987 Cedar Lane, Philadelphia, PA 19101',
                'subject' => 'Account Assistance',
                'content' => 'I am having trouble accessing my account. I have tried resetting my password multiple times, but I am still unable to log in. Could you please help me regain access to my account? My username is jennifer_t.',
                'status' => ContactStatusEnum::UNREAD,
            ],
            [
                'name' => 'David Anderson',
                'email' => 'david.anderson@example.com',
                'phone' => '+1 (555) 789-0123',
                'address' => '135 Birch Boulevard, San Antonio, TX 78201',
                'subject' => 'Product Information',
                'content' => 'I am interested in your premium product line and would like to receive a detailed brochure or catalog. Could you also provide information about bulk order discounts? Thank you for your assistance.',
                'status' => ContactStatusEnum::READ,
            ],
            [
                'name' => 'Lisa Martinez',
                'email' => 'lisa.martinez@example.com',
                'phone' => '+1 (555) 890-1234',
                'address' => '246 Walnut Street, San Diego, CA 92101',
                'subject' => 'Event Sponsorship',
                'content' => 'Our organization is hosting a charity event next month and we are looking for sponsors. Would your company be interested in participating? This would be a great opportunity for brand visibility and community engagement.',
                'status' => ContactStatusEnum::UNREAD,
            ],
            [
                'name' => 'Robert Garcia',
                'email' => 'robert.garcia@example.com',
                'phone' => '+1 (555) 901-2345',
                'address' => '357 Spruce Court, Dallas, TX 75201',
                'subject' => 'Career Opportunities',
                'content' => 'I am interested in exploring career opportunities at your company. I have over 5 years of experience in the industry and believe I could be a valuable addition to your team. Please find my resume attached for your review.',
                'status' => ContactStatusEnum::READ,
            ],
            [
                'name' => 'Jessica Rodriguez',
                'email' => 'jessica.rodriguez@example.com',
                'phone' => '+1 (555) 012-3456',
                'address' => '468 Ash Avenue, San Jose, CA 95101',
                'subject' => 'Media Inquiry',
                'content' => 'I am a journalist working on a story about innovative companies in the tech industry. I would love to feature your company in my upcoming article. Could we arrange an interview with a company representative at your earliest convenience?',
                'status' => ContactStatusEnum::UNREAD,
            ],
        ];

        foreach ($contacts as $contact) {
            Contact::query()->create($contact);
        }
    }
}
