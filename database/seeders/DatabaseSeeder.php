<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\AppointmentItem;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\SpaRoom;
use App\Models\StaffSchedule;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Core Users & Staff
        $admin = User::firstOrCreate(
            ['email' => 'admin@salonspa.local'],
            [
                'name' => 'Victoria Sterling',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'phone' => '+1 (555) 019-2831',
                'specialization' => 'Executive Spa Director',
                'commission_rate' => 0.00,
                'status' => 'active',
                'bio' => 'Over 15 years in luxury wellness and salon operations management.',
            ]
        );

        $manager = User::firstOrCreate(
            ['email' => 'manager@salonspa.local'],
            [
                'name' => 'Alexander Hayes',
                'password' => Hash::make('password'),
                'role' => 'manager',
                'phone' => '+1 (555) 019-4820',
                'specialization' => 'Salon Operations & Guest Relations',
                'commission_rate' => 5.00,
                'status' => 'active',
                'bio' => 'Oversees day-to-day salon logistics, scheduling, and customer experience.',
            ]
        );

        $receptionist = User::firstOrCreate(
            ['email' => 'reception@salonspa.local'],
            [
                'name' => 'Grace Montgomery',
                'password' => Hash::make('password'),
                'role' => 'receptionist',
                'phone' => '+1 (555) 019-8812',
                'specialization' => 'Lead Concierge & POS Specialist',
                'commission_rate' => 0.00,
                'status' => 'active',
                'bio' => 'Front desk reception, booking coordinator, and VIP guest relations.',
            ]
        );

        $therapist1 = User::firstOrCreate(
            ['email' => 'elena.hair@salonspa.local'],
            [
                'name' => 'Elena Vance',
                'password' => Hash::make('password'),
                'role' => 'therapist',
                'phone' => '+1 (555) 019-3341',
                'specialization' => 'Master Hair Stylist & Balayage Expert',
                'commission_rate' => 20.00,
                'status' => 'active',
                'bio' => 'Certified French Balayage colorist and master precision hair stylist.',
            ]
        );

        $therapist2 = User::firstOrCreate(
            ['email' => 'chloe.facial@salonspa.local'],
            [
                'name' => 'Chloe Bennett',
                'password' => Hash::make('password'),
                'role' => 'therapist',
                'phone' => '+1 (555) 019-5562',
                'specialization' => 'Clinical Aesthetician & Holistic Facialist',
                'commission_rate' => 18.00,
                'status' => 'active',
                'bio' => 'Specializes in cellular rejuvenation facials, chemical peels, and lymphatic drainage.',
            ]
        );

        $therapist3 = User::firstOrCreate(
            ['email' => 'marcus.body@salonspa.local'],
            [
                'name' => 'Marcus Reed',
                'password' => Hash::make('password'),
                'role' => 'therapist',
                'phone' => '+1 (555) 019-7719',
                'specialization' => 'Deep Tissue & Hot Stone Massage Therapist',
                'commission_rate' => 20.00,
                'status' => 'active',
                'bio' => 'Licensed massage therapist focusing on sports recovery, trigger point, and hot stone rituals.',
            ]
        );

        $therapist4 = User::firstOrCreate(
            ['email' => 'sophia.nails@salonspa.local'],
            [
                'name' => 'Sophia Tanaka',
                'password' => Hash::make('password'),
                'role' => 'therapist',
                'phone' => '+1 (555) 019-9943',
                'specialization' => 'Luxury Nail Artist & Lash Designer',
                'commission_rate' => 15.00,
                'status' => 'active',
                'bio' => 'Expert in Japanese gel manicures, organic keratin pedicures, and Russian volume lashes.',
            ]
        );

        // 2. Staff Schedules
        $therapists = [$therapist1, $therapist2, $therapist3, $therapist4];
        foreach ($therapists as $t) {
            for ($day = 1; $day <= 6; $day++) {
                StaffSchedule::firstOrCreate(
                    ['user_id' => $t->id, 'day_of_week' => $day],
                    [
                        'start_time' => '09:00:00',
                        'end_time' => '18:00:00',
                        'is_working' => true,
                        'notes' => 'Regular shift',
                    ]
                );
            }
            // Sunday off
            StaffSchedule::firstOrCreate(
                ['user_id' => $t->id, 'day_of_week' => 0],
                [
                    'start_time' => '10:00:00',
                    'end_time' => '16:00:00',
                    'is_working' => false,
                    'notes' => 'Weekly Day Off',
                ]
            );
        }

        // 3. Spa Rooms & Treatment Stations
        $rooms = [
            ['room_code' => 'RM-101', 'name' => 'Royal Lavender Massage Suite', 'type' => 'massage_room', 'capacity' => 2],
            ['room_code' => 'RM-102', 'name' => 'Zen Botanical Facial Sanctuary', 'type' => 'facial_suite', 'capacity' => 1],
            ['room_code' => 'ST-01', 'name' => 'Signature Hair Station Alpha', 'type' => 'hair_station', 'capacity' => 1],
            ['room_code' => 'ST-02', 'name' => 'Signature Hair Station Beta', 'type' => 'hair_station', 'capacity' => 1],
            ['room_code' => 'NB-01', 'name' => 'Luxe Manicure & Pedicure Lounge', 'type' => 'nail_bar', 'capacity' => 4],
            ['room_code' => 'SU-01', 'name' => 'Nordic Cedar Sauna & Steam Suite', 'type' => 'sauna_steam', 'capacity' => 6],
        ];
        $roomModels = [];
        foreach ($rooms as $r) {
            $roomModels[$r['room_code']] = SpaRoom::firstOrCreate(['room_code' => $r['room_code']], $r);
        }

        // 4. Service Categories & Services
        $catHair = ServiceCategory::firstOrCreate(
            ['slug' => 'hair-styling-color'],
            ['name' => 'Hair Styling & Color', 'description' => 'Precision cuts, French balayage, botanical glosses, and couture blowouts.', 'icon' => 'bi-scissors', 'sort_order' => 1]
        );

        $catFacial = ServiceCategory::firstOrCreate(
            ['slug' => 'skincare-facials'],
            ['name' => 'Skincare & Facials', 'description' => 'Targeted dermatological treatments, diamond microdermabrasion, and organic hydration.', 'icon' => 'bi-stars', 'sort_order' => 2]
        );

        $catMassage = ServiceCategory::firstOrCreate(
            ['slug' => 'massage-body-rituals'],
            ['name' => 'Massage & Body Rituals', 'description' => 'Therapeutic deep tissue, volcanic hot stone, and Himalayan salt scrubs.', 'icon' => 'bi-heart-pulse', 'sort_order' => 3]
        );

        $catNails = ServiceCategory::firstOrCreate(
            ['slug' => 'nails-beauty'],
            ['name' => 'Nails & Lash Artistry', 'description' => 'Spa manicures, organic volcanic pedicures, and bespoke lash enhancements.', 'icon' => 'bi-gem', 'sort_order' => 4]
        );

        $catPackages = ServiceCategory::firstOrCreate(
            ['slug' => 'signature-spa-packages'],
            ['name' => 'Signature Spa Packages', 'description' => 'Curated multi-hour holistic indulgence journeys combining multiple treatments.', 'icon' => 'bi-flower1', 'sort_order' => 5]
        );

        $servicesData = [
            // Hair
            ['category_id' => $catHair->id, 'code' => 'HAIR-01', 'name' => 'Couture Haircut & Blowdry', 'duration_minutes' => 60, 'buffer_time_minutes' => 15, 'price' => 85.00, 'cost_price' => 12.00, 'requires_room' => false],
            ['category_id' => $catHair->id, 'code' => 'HAIR-02', 'name' => 'French Balayage & Glaze', 'duration_minutes' => 120, 'buffer_time_minutes' => 15, 'price' => 220.00, 'cost_price' => 35.00, 'requires_room' => false],
            ['category_id' => $catHair->id, 'code' => 'HAIR-03', 'name' => 'Deep Botanical Keratin Infusion', 'duration_minutes' => 90, 'buffer_time_minutes' => 15, 'price' => 160.00, 'cost_price' => 28.00, 'requires_room' => false],
            // Facials
            ['category_id' => $catFacial->id, 'code' => 'FAC-01', 'name' => 'Cellular Hydration Glow Facial', 'duration_minutes' => 60, 'buffer_time_minutes' => 15, 'price' => 135.00, 'cost_price' => 20.00, 'requires_room' => true],
            ['category_id' => $catFacial->id, 'code' => 'FAC-02', 'name' => 'Diamond Microdermabrasion Ritual', 'duration_minutes' => 75, 'buffer_time_minutes' => 15, 'price' => 175.00, 'cost_price' => 25.00, 'requires_room' => true],
            ['category_id' => $catFacial->id, 'code' => 'FAC-03', 'name' => '24K Gold Collagen Lift Treatment', 'duration_minutes' => 90, 'buffer_time_minutes' => 15, 'price' => 240.00, 'cost_price' => 45.00, 'requires_room' => true],
            // Massage
            ['category_id' => $catMassage->id, 'code' => 'MSG-01', 'name' => 'Aromatherapy Swedish Massage (60m)', 'duration_minutes' => 60, 'buffer_time_minutes' => 15, 'price' => 110.00, 'cost_price' => 15.00, 'requires_room' => true],
            ['category_id' => $catMassage->id, 'code' => 'MSG-02', 'name' => 'Deep Tissue Muscle Recovery (90m)', 'duration_minutes' => 90, 'buffer_time_minutes' => 15, 'price' => 165.00, 'cost_price' => 18.00, 'requires_room' => true],
            ['category_id' => $catMassage->id, 'code' => 'MSG-03', 'name' => 'Volcanic Basalt Hot Stone Ritual (90m)', 'duration_minutes' => 90, 'buffer_time_minutes' => 15, 'price' => 180.00, 'cost_price' => 22.00, 'requires_room' => true],
            // Nails
            ['category_id' => $catNails->id, 'code' => 'NAIL-01', 'name' => 'Deluxe Rosewater Gel Manicure', 'duration_minutes' => 45, 'buffer_time_minutes' => 10, 'price' => 60.00, 'cost_price' => 8.00, 'requires_room' => false],
            ['category_id' => $catNails->id, 'code' => 'NAIL-02', 'name' => 'Volcanic Stone Spa Pedicure', 'duration_minutes' => 60, 'buffer_time_minutes' => 15, 'price' => 85.00, 'cost_price' => 12.00, 'requires_room' => false],
            // Packages
            ['category_id' => $catPackages->id, 'code' => 'PKG-01', 'name' => 'Royal Sanctuary Signature Journey (3h)', 'duration_minutes' => 180, 'buffer_time_minutes' => 30, 'price' => 380.00, 'cost_price' => 55.00, 'requires_room' => true],
        ];

        $serviceModels = [];
        foreach ($servicesData as $s) {
            $serviceModels[$s['code']] = Service::firstOrCreate(['code' => $s['code']], $s);
        }

        // 5. Product Categories & Products (Retail & Backbar)
        $prodCatHair = ProductCategory::firstOrCreate(['slug' => 'hair-care-retail'], ['name' => 'Luxury Hair Care', 'description' => 'Shampoos, conditioners, and styling elixirs.']);
        $prodCatSkin = ProductCategory::firstOrCreate(['slug' => 'skincare-retail'], ['name' => 'Dermatological Skincare', 'description' => 'Botanical serums, anti-aging creams, and sun shields.']);
        $prodCatBody = ProductCategory::firstOrCreate(['slug' => 'body-wellness'], ['name' => 'Essential Oils & Body Butters', 'description' => 'Cold-pressed massage oils, bath salts, and aromatherapy essences.']);

        $productsData = [
            ['category_id' => $prodCatHair->id, 'sku' => 'PRD-HR-001', 'name' => 'Silk Keratin Conditioning Shampoo (250ml)', 'brand' => 'Botanical Reserve', 'type' => 'both', 'cost_price' => 14.00, 'retail_price' => 36.00, 'stock_quantity' => 45, 'low_stock_threshold' => 10, 'unit' => 'bottle'],
            ['category_id' => $prodCatHair->id, 'sku' => 'PRD-HR-002', 'name' => 'Moroccan Argan Thermal Serum (100ml)', 'brand' => 'Botanical Reserve', 'type' => 'retail', 'cost_price' => 18.00, 'retail_price' => 48.00, 'stock_quantity' => 28, 'low_stock_threshold' => 8, 'unit' => 'bottle'],
            ['category_id' => $prodCatHair->id, 'sku' => 'PRD-HR-003', 'name' => 'Caviar Anti-Aging Deep Hair Mask (500g)', 'brand' => 'Botanical Reserve', 'type' => 'both', 'cost_price' => 25.00, 'retail_price' => 65.00, 'stock_quantity' => 4, 'low_stock_threshold' => 5, 'unit' => 'jar'], // Low stock trigger
            ['category_id' => $prodCatSkin->id, 'sku' => 'PRD-SK-001', 'name' => 'Hyaluronic Glow Elixir 2% (50ml)', 'brand' => 'Lumière Derma', 'type' => 'both', 'cost_price' => 22.00, 'retail_price' => 75.00, 'stock_quantity' => 32, 'low_stock_threshold' => 10, 'unit' => 'bottle'],
            ['category_id' => $prodCatSkin->id, 'sku' => 'PRD-SK-002', 'name' => '24K Nano-Gold Peptide Firming Cream (50ml)', 'brand' => 'Lumière Derma', 'type' => 'retail', 'cost_price' => 38.00, 'retail_price' => 110.00, 'stock_quantity' => 18, 'low_stock_threshold' => 5, 'unit' => 'jar'],
            ['category_id' => $prodCatBody->id, 'sku' => 'PRD-BD-001', 'name' => 'Organic French Lavender Body Oil (500ml)', 'brand' => 'Pure Spa Reserve', 'type' => 'both', 'cost_price' => 16.00, 'retail_price' => 42.00, 'stock_quantity' => 3, 'low_stock_threshold' => 6, 'unit' => 'bottle'], // Low stock
        ];

        $productModels = [];
        foreach ($productsData as $p) {
            $productModels[$p['sku']] = Product::firstOrCreate(['sku' => $p['sku']], $p);
        }

        // 6. Customers
        $customersData = [
            [
                'customer_code' => 'CUST-1001',
                'name' => 'Evelyn DeWitt',
                'email' => 'evelyn.dewitt@example.com',
                'phone' => '+1 (555) 234-5678',
                'gender' => 'female',
                'date_of_birth' => '1988-04-12',
                'address' => '742 Evergreen Terrace, Beverly Hills, CA',
                'skin_type' => 'Combination',
                'hair_type' => 'Wavy',
                'allergies' => 'None',
                'preferred_therapist_id' => $therapist2->id,
                'loyalty_points' => 350,
                'status' => 'vip',
                'notes' => 'Prefers chamomile tea and extra firm pressure on shoulder massages.',
            ],
            [
                'customer_code' => 'CUST-1002',
                'name' => 'Jonathan Miller',
                'email' => 'j.miller@example.com',
                'phone' => '+1 (555) 345-6789',
                'gender' => 'male',
                'date_of_birth' => '1982-11-23',
                'address' => '120 Ocean View Blvd, Santa Monica, CA',
                'skin_type' => 'Sensitive',
                'hair_type' => 'Straight',
                'allergies' => 'Mild eucalyptus sensitivity',
                'preferred_therapist_id' => $therapist3->id,
                'loyalty_points' => 180,
                'status' => 'active',
                'notes' => 'Regular deep tissue client every 2 weeks after marathon training.',
            ],
            [
                'customer_code' => 'CUST-1003',
                'name' => 'Seraphina Rossi',
                'email' => 'seraphina.rossi@example.com',
                'phone' => '+1 (555) 456-7890',
                'gender' => 'female',
                'date_of_birth' => '1995-09-08',
                'address' => '88 Bel Air Crest Road, Los Angeles, CA',
                'skin_type' => 'Dry',
                'hair_type' => 'Curly',
                'allergies' => 'Nut allergy (no almond oil)',
                'preferred_therapist_id' => $therapist1->id,
                'loyalty_points' => 520,
                'status' => 'vip',
                'notes' => 'Loves custom balayage toning and silk keratin treatments.',
            ],
            [
                'customer_code' => 'CUST-1004',
                'name' => 'Liam O\'Connor',
                'email' => 'liam.oconnor@example.com',
                'phone' => '+1 (555) 567-8901',
                'gender' => 'male',
                'date_of_birth' => '1991-02-17',
                'address' => '314 Canyon Drive, Hollywood Hills, CA',
                'skin_type' => 'Oily',
                'hair_type' => 'Straight',
                'allergies' => 'None',
                'preferred_therapist_id' => $therapist1->id,
                'loyalty_points' => 90,
                'status' => 'active',
                'notes' => 'Prefers early morning appointments.',
            ],
            [
                'customer_code' => 'CUST-1005',
                'name' => 'Isabella Martinez',
                'email' => 'isabella.martinez@example.com',
                'phone' => '+1 (555) 678-9012',
                'gender' => 'female',
                'date_of_birth' => '1990-07-30',
                'address' => '505 Wilshire Blvd, Los Angeles, CA',
                'skin_type' => 'Normal',
                'hair_type' => 'Coily',
                'allergies' => 'None',
                'preferred_therapist_id' => $therapist4->id,
                'loyalty_points' => 240,
                'status' => 'active',
                'notes' => 'Monthly gel manicure and Russian volume lash refill.',
            ],
        ];

        $customerModels = [];
        foreach ($customersData as $c) {
            $customerModels[$c['customer_code']] = Customer::firstOrCreate(['customer_code' => $c['customer_code']], $c);
        }

        // 7. Seed Realistic Appointments & Invoices
        $today = now()->format('Y-m-d');
        $yesterday = now()->subDay()->format('Y-m-d');
        $tomorrow = now()->addDay()->format('Y-m-d');

        // Past Completed Appointment & Invoice (Yesterday)
        $apt1 = Appointment::firstOrCreate(
            ['appointment_number' => 'APT-'.str_replace('-', '', $yesterday).'-0001'],
            [
                'customer_id' => $customerModels['CUST-1001']->id,
                'therapist_id' => $therapist2->id,
                'room_id' => $roomModels['RM-102']->id,
                'appointment_date' => $yesterday,
                'start_time' => '10:00:00',
                'end_time' => '11:15:00',
                'status' => 'completed',
                'booking_type' => 'phone',
                'subtotal' => 175.00,
                'discount_amount' => 15.00,
                'tax_amount' => 16.00,
                'total_amount' => 176.00,
                'notes' => 'First microdermabrasion session. Very pleased with result.',
            ]
        );
        AppointmentItem::firstOrCreate(
            ['appointment_id' => $apt1->id, 'service_id' => $serviceModels['FAC-02']->id],
            [
                'therapist_id' => $therapist2->id,
                'price' => 175.00,
                'duration_minutes' => 75,
                'notes' => 'Applied organic rose soothing mask post-treatment.',
            ]
        );

        // Invoice for Apt 1
        $inv1 = Invoice::firstOrCreate(
            ['invoice_number' => 'INV-'.str_replace('-', '', $yesterday).'-0001'],
            [
                'appointment_id' => $apt1->id,
                'customer_id' => $customerModels['CUST-1001']->id,
                'cashier_id' => $receptionist->id,
                'subtotal' => 250.00, // 175 service + 75 retail serum
                'discount_amount' => 15.00,
                'tax_rate' => 10.00,
                'tax_amount' => 23.50,
                'tip_amount' => 30.00,
                'total_amount' => 288.50,
                'paid_amount' => 300.00,
                'change_amount' => 11.50,
                'payment_method' => 'credit_card',
                'payment_status' => 'paid',
                'notes' => 'Paid via Visa ending in 4242. Customer purchased Hyaluronic Glow Elixir.',
            ]
        );
        InvoiceItem::firstOrCreate(
            ['invoice_id' => $inv1->id, 'item_type' => 'service', 'service_id' => $serviceModels['FAC-02']->id],
            [
                'item_name' => $serviceModels['FAC-02']->name,
                'therapist_id' => $therapist2->id,
                'quantity' => 1,
                'unit_price' => 175.00,
                'subtotal' => 175.00,
                'commission_rate' => 18.00,
                'commission_amount' => 31.50,
            ]
        );
        InvoiceItem::firstOrCreate(
            ['invoice_id' => $inv1->id, 'item_type' => 'product', 'product_id' => $productModels['PRD-SK-001']->id],
            [
                'item_name' => $productModels['PRD-SK-001']->name,
                'therapist_id' => $therapist2->id,
                'quantity' => 1,
                'unit_price' => 75.00,
                'subtotal' => 75.00,
                'commission_rate' => 10.00,
                'commission_amount' => 7.50,
            ]
        );

        // Today In-Service Appointment
        $apt2 = Appointment::firstOrCreate(
            ['appointment_number' => 'APT-'.str_replace('-', '', $today).'-0001'],
            [
                'customer_id' => $customerModels['CUST-1003']->id,
                'therapist_id' => $therapist1->id,
                'room_id' => $roomModels['ST-01']->id,
                'appointment_date' => $today,
                'start_time' => '11:00:00',
                'end_time' => '13:00:00',
                'status' => 'in_service',
                'booking_type' => 'online',
                'subtotal' => 220.00,
                'discount_amount' => 0.00,
                'tax_amount' => 22.00,
                'total_amount' => 242.00,
                'notes' => 'Balayage touchup on crown and face-framing highlight.',
            ]
        );
        AppointmentItem::firstOrCreate(
            ['appointment_id' => $apt2->id, 'service_id' => $serviceModels['HAIR-02']->id],
            [
                'therapist_id' => $therapist1->id,
                'price' => 220.00,
                'duration_minutes' => 120,
            ]
        );

        // Today Confirmed Upcoming Appointment
        $apt3 = Appointment::firstOrCreate(
            ['appointment_number' => 'APT-'.str_replace('-', '', $today).'-0002'],
            [
                'customer_id' => $customerModels['CUST-1002']->id,
                'therapist_id' => $therapist3->id,
                'room_id' => $roomModels['RM-101']->id,
                'appointment_date' => $today,
                'start_time' => '14:30:00',
                'end_time' => '16:00:00',
                'status' => 'confirmed',
                'booking_type' => 'walk_in',
                'subtotal' => 165.00,
                'discount_amount' => 0.00,
                'tax_amount' => 16.50,
                'total_amount' => 181.50,
                'notes' => 'Deep tissue focused on lower back and hamstrings.',
            ]
        );
        AppointmentItem::firstOrCreate(
            ['appointment_id' => $apt3->id, 'service_id' => $serviceModels['MSG-02']->id],
            [
                'therapist_id' => $therapist3->id,
                'price' => 165.00,
                'duration_minutes' => 90,
            ]
        );

        // Tomorrow Upcoming
        Appointment::firstOrCreate(
            ['appointment_number' => 'APT-'.str_replace('-', '', $tomorrow).'-0001'],
            [
                'customer_id' => $customerModels['CUST-1005']->id,
                'therapist_id' => $therapist4->id,
                'room_id' => $roomModels['NB-01']->id,
                'appointment_date' => $tomorrow,
                'start_time' => '13:00:00',
                'end_time' => '14:00:00',
                'status' => 'confirmed',
                'booking_type' => 'online',
                'subtotal' => 85.00,
                'discount_amount' => 0.00,
                'tax_amount' => 8.50,
                'total_amount' => 93.50,
                'notes' => 'Volcanic Stone Spa Pedicure.',
            ]
        );
    }
}
