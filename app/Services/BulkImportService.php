<?php

namespace App\Services;

use App\Models\BulkImportLog;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\StaffSchedule;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class BulkImportService
{
    /**
     * Process bulk import based on type.
     */
    public function import(string $type, UploadedFile $file, ?int $userId = null, bool $updateExisting = true): array
    {
        $handle = fopen($file->getRealPath(), 'r');
        if (!$handle) {
            throw new \RuntimeException("Unable to open the uploaded file for reading.");
        }

        // Read and sanitize header row
        $rawHeaders = fgetcsv($handle);
        if (!$rawHeaders) {
            fclose($handle);
            throw new \InvalidArgumentException("The CSV file is empty or formatted incorrectly.");
        }

        $headers = array_map(function ($h) {
            return strtolower(trim(preg_replace('/[\x{FEFF}]/u', '', $h)));
        }, $rawHeaders);

        $rows = [];
        $lineNumber = 1;
        while (($data = fgetcsv($handle)) !== false) {
            $lineNumber++;
            if (empty(array_filter($data, fn($v) => trim($v) !== ''))) {
                continue; // Skip empty lines
            }
            // Pad or trim data to match header count
            $rowAssoc = [];
            foreach ($headers as $idx => $header) {
                $rowAssoc[$header] = isset($data[$idx]) ? trim($data[$idx]) : '';
            }
            $rows[] = ['line' => $lineNumber, 'data' => $rowAssoc];
        }
        fclose($handle);

        $totalRows = count($rows);
        $successCount = 0;
        $failedCount = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            switch ($type) {
                case 'customers':
                    $result = $this->importCustomers($rows, $updateExisting);
                    break;
                case 'services':
                    $result = $this->importServices($rows, $updateExisting);
                    break;
                case 'products':
                    $result = $this->importProducts($rows, $userId, $updateExisting);
                    break;
                case 'staff':
                    $result = $this->importStaff($rows, $updateExisting);
                    break;
                default:
                    throw new \InvalidArgumentException("Invalid import type: {$type}");
            }

            $successCount = $result['success'];
            $failedCount = $result['failed'];
            $errors = $result['errors'];

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $errors[] = [
                'line' => 'Global Transaction Error',
                'error' => $e->getMessage(),
            ];
            $failedCount = $totalRows;
            $successCount = 0;
        }

        $status = 'completed';
        if ($failedCount > 0 && $successCount > 0) {
            $status = 'partial';
        } elseif ($failedCount > 0 && $successCount === 0) {
            $status = 'failed';
        }

        // Log the import execution
        BulkImportLog::create([
            'user_id' => $userId,
            'import_type' => $type,
            'file_name' => $file->getClientOriginalName(),
            'total_rows' => $totalRows,
            'successful_rows' => $successCount,
            'failed_rows' => $failedCount,
            'errors' => !empty($errors) ? $errors : null,
            'status' => $status,
        ]);

        return [
            'total' => $totalRows,
            'success' => $successCount,
            'failed' => $failedCount,
            'errors' => $errors,
            'status' => $status,
        ];
    }

    /**
     * Import Customers.
     */
    protected function importCustomers(array $rows, bool $updateExisting): array
    {
        $success = 0;
        $failed = 0;
        $errors = [];

        foreach ($rows as $item) {
            $line = $item['line'];
            $d = $item['data'];

            if (empty($d['name']) || empty($d['phone'])) {
                $failed++;
                $errors[] = ['line' => $line, 'error' => 'Customer name and phone are required.'];
                continue;
            }

            try {
                $customer = Customer::where('phone', $d['phone'])->first();

                if ($customer && !$updateExisting) {
                    $failed++;
                    $errors[] = ['line' => $line, 'error' => "Customer with phone {$d['phone']} already exists."];
                    continue;
                }

                $customerCode = $customer ? $customer->customer_code : ($d['customer_code'] ?? 'CUST-' . strtoupper(Str::random(6)));

                $payload = [
                    'customer_code' => $customerCode,
                    'name' => $d['name'],
                    'email' => !empty($d['email']) ? $d['email'] : null,
                    'phone' => $d['phone'],
                    'gender' => !empty($d['gender']) ? strtolower($d['gender']) : null,
                    'date_of_birth' => !empty($d['date_of_birth']) ? date('Y-m-d', strtotime($d['date_of_birth'])) : null,
                    'address' => $d['address'] ?? null,
                    'skin_type' => $d['skin_type'] ?? null,
                    'hair_type' => $d['hair_type'] ?? null,
                    'allergies' => $d['allergies'] ?? null,
                    'loyalty_points' => isset($d['loyalty_points']) ? (int) $d['loyalty_points'] : 0,
                    'notes' => $d['notes'] ?? null,
                    'status' => !empty($d['status']) ? strtolower($d['status']) : 'active',
                ];

                if ($customer) {
                    $customer->update($payload);
                } else {
                    Customer::create($payload);
                }

                $success++;
            } catch (\Throwable $e) {
                $failed++;
                $errors[] = ['line' => $line, 'error' => $e->getMessage()];
            }
        }

        return compact('success', 'failed', 'errors');
    }

    /**
     * Import Services.
     */
    protected function importServices(array $rows, bool $updateExisting): array
    {
        $success = 0;
        $failed = 0;
        $errors = [];

        foreach ($rows as $item) {
            $line = $item['line'];
            $d = $item['data'];

            if (empty($d['code']) || empty($d['name']) || !isset($d['price'])) {
                $failed++;
                $errors[] = ['line' => $line, 'error' => 'Service code, name, and price are required.'];
                continue;
            }

            try {
                // Find or create category
                $categoryName = !empty($d['category']) ? trim($d['category']) : 'General Services';
                $category = ServiceCategory::firstOrCreate(
                    ['name' => $categoryName],
                    ['slug' => Str::slug($categoryName), 'description' => $categoryName . ' category', 'icon' => 'bi-stars']
                );

                $service = Service::where('code', $d['code'])->first();

                if ($service && !$updateExisting) {
                    $failed++;
                    $errors[] = ['line' => $line, 'error' => "Service with code {$d['code']} already exists."];
                    continue;
                }

                $payload = [
                    'category_id' => $category->id,
                    'code' => strtoupper($d['code']),
                    'name' => $d['name'],
                    'slug' => Str::slug($d['name']),
                    'description' => $d['description'] ?? null,
                    'duration_minutes' => !empty($d['duration_minutes']) ? (int) $d['duration_minutes'] : 60,
                    'buffer_time_minutes' => isset($d['buffer_time_minutes']) ? (int) $d['buffer_time_minutes'] : 10,
                    'price' => (float) $d['price'],
                    'cost_price' => !empty($d['cost_price']) ? (float) $d['cost_price'] : 0.00,
                    'is_active' => isset($d['is_active']) ? filter_var($d['is_active'], FILTER_VALIDATE_BOOLEAN) : true,
                    'requires_room' => isset($d['requires_room']) ? filter_var($d['requires_room'], FILTER_VALIDATE_BOOLEAN) : false,
                ];

                if ($service) {
                    $service->update($payload);
                } else {
                    Service::create($payload);
                }

                $success++;
            } catch (\Throwable $e) {
                $failed++;
                $errors[] = ['line' => $line, 'error' => $e->getMessage()];
            }
        }

        return compact('success', 'failed', 'errors');
    }

    /**
     * Import Products & Inventory.
     */
    protected function importProducts(array $rows, ?int $userId, bool $updateExisting): array
    {
        $success = 0;
        $failed = 0;
        $errors = [];

        foreach ($rows as $item) {
            $line = $item['line'];
            $d = $item['data'];

            if (empty($d['sku']) || empty($d['name']) || !isset($d['retail_price'])) {
                $failed++;
                $errors[] = ['line' => $line, 'error' => 'Product SKU, name, and retail_price are required.'];
                continue;
            }

            try {
                $categoryName = !empty($d['category']) ? trim($d['category']) : 'General Supplies';
                $category = ProductCategory::firstOrCreate(
                    ['name' => $categoryName],
                    ['slug' => Str::slug($categoryName), 'description' => $categoryName]
                );

                $product = Product::where('sku', $d['sku'])->first();

                if ($product && !$updateExisting) {
                    $failed++;
                    $errors[] = ['line' => $line, 'error' => "Product with SKU {$d['sku']} already exists."];
                    continue;
                }

                $initialStock = isset($d['stock_quantity']) ? (int) $d['stock_quantity'] : 0;

                $payload = [
                    'category_id' => $category->id,
                    'sku' => strtoupper($d['sku']),
                    'barcode' => $d['barcode'] ?? null,
                    'name' => $d['name'],
                    'brand' => $d['brand'] ?? null,
                    'type' => !empty($d['type']) ? strtolower($d['type']) : 'both',
                    'description' => $d['description'] ?? null,
                    'cost_price' => !empty($d['cost_price']) ? (float) $d['cost_price'] : 0.00,
                    'retail_price' => (float) $d['retail_price'],
                    'stock_quantity' => $initialStock,
                    'low_stock_threshold' => !empty($d['low_stock_threshold']) ? (int) $d['low_stock_threshold'] : 5,
                    'unit' => !empty($d['unit']) ? $d['unit'] : 'piece',
                    'is_active' => isset($d['is_active']) ? filter_var($d['is_active'], FILTER_VALIDATE_BOOLEAN) : true,
                ];

                if ($product) {
                    $product->update($payload);
                } else {
                    $product = Product::create($payload);

                    if ($initialStock > 0) {
                        StockMovement::create([
                            'product_id' => $product->id,
                            'user_id' => $userId,
                            'type' => 'restock',
                            'quantity' => $initialStock,
                            'unit_cost' => $payload['cost_price'],
                            'reference_number' => 'INITIAL-IMPORT',
                            'notes' => 'Initial bulk import stock balance',
                        ]);
                    }
                }

                $success++;
            } catch (\Throwable $e) {
                $failed++;
                $errors[] = ['line' => $line, 'error' => $e->getMessage()];
            }
        }

        return compact('success', 'failed', 'errors');
    }

    /**
     * Import Staff / Stylists.
     */
    protected function importStaff(array $rows, bool $updateExisting): array
    {
        $success = 0;
        $failed = 0;
        $errors = [];

        foreach ($rows as $item) {
            $line = $item['line'];
            $d = $item['data'];

            if (empty($d['name']) || empty($d['email'])) {
                $failed++;
                $errors[] = ['line' => $line, 'error' => 'Staff name and email are required.'];
                continue;
            }

            try {
                $user = User::where('email', $d['email'])->first();

                if ($user && !$updateExisting) {
                    $failed++;
                    $errors[] = ['line' => $line, 'error' => "User with email {$d['email']} already exists."];
                    continue;
                }

                $role = !empty($d['role']) ? strtolower($d['role']) : 'therapist';
                if (!in_array($role, ['admin', 'manager', 'receptionist', 'therapist'])) {
                    $role = 'therapist';
                }

                $payload = [
                    'name' => $d['name'],
                    'email' => $d['email'],
                    'role' => $role,
                    'phone' => $d['phone'] ?? null,
                    'specialization' => $d['specialization'] ?? null,
                    'commission_rate' => isset($d['commission_rate']) ? (float) $d['commission_rate'] : 0.00,
                    'status' => !empty($d['status']) ? strtolower($d['status']) : 'active',
                    'bio' => $d['bio'] ?? null,
                ];

                if (!$user) {
                    $payload['password'] = Hash::make(!empty($d['password']) ? $d['password'] : 'password');
                    $user = User::create($payload);

                    // Add default Monday to Saturday work schedule for therapist
                    if ($role === 'therapist') {
                        for ($day = 1; $day <= 6; $day++) {
                            StaffSchedule::firstOrCreate(
                                ['user_id' => $user->id, 'day_of_week' => $day],
                                ['start_time' => '09:00:00', 'end_time' => '18:00:00', 'is_working' => true]
                            );
                        }
                    }
                } else {
                    if (!empty($d['password'])) {
                        $payload['password'] = Hash::make($d['password']);
                    }
                    $user->update($payload);
                }

                $success++;
            } catch (\Throwable $e) {
                $failed++;
                $errors[] = ['line' => $line, 'error' => $e->getMessage()];
            }
        }

        return compact('success', 'failed', 'errors');
    }

    /**
     * Generate sample CSV template content for download.
     */
    public function getSampleCsv(string $type): string
    {
        return match ($type) {
            'customers' => "name,phone,email,gender,date_of_birth,address,skin_type,hair_type,allergies,loyalty_points,notes,status\n"
                . "\"Camilla Thorne\",\"+1 (555) 789-0123\",\"camilla.thorne@example.com\",\"female\",\"1992-05-14\",\"402 Oak Ridge Ave, Los Angeles, CA\",\"Sensitive\",\"Wavy\",\"Aloe Vera sensitivity\",150,\"Prefers unscented lotion\",\"active\"\n"
                . "\"David Sterling\",\"+1 (555) 890-1234\",\"david.s@example.com\",\"male\",\"1985-11-20\",\"105 Ocean Way, Santa Monica, CA\",\"Normal\",\"Straight\",\"None\",80,\"Frequent business traveler\",\"active\"\n",

            'services' => "code,name,category,duration_minutes,buffer_time_minutes,price,cost_price,requires_room,description\n"
                . "\"SRV-MASS-05\",\"Hot Herbal Compress Massage\",\"Massage & Body Rituals\",75,15,145.00,20.00,true,\"Warm botanical poultices steamed with lemongrass and ginger.\"\n"
                . "\"SRV-HAIR-09\",\"Olaplex Deep Molecular Repair\",\"Hair Styling & Color\",45,15,75.00,14.00,false,\"Intense bond rebuilding treatment for chemically damaged hair.\"\n",

            'products' => "sku,barcode,name,category,brand,type,cost_price,retail_price,stock_quantity,low_stock_threshold,unit,description\n"
                . "\"PRD-SK-009\",\"8934521009\",\"Botanical Peptide Eye Cream 15ml\",\"Skincare\",\"Lumière Derma\",\"both\",16.50,55.00,25,5,\"tube\",\"Targets fine lines and puffiness.\"\n"
                . "\"PRD-HR-012\",\"8934521012\",\"Detoxifying Sea Salt Scalp Scrub 250ml\",\"Hair Care\",\"Botanical Reserve\",\"both\",12.00,38.00,18,4,\"jar\",\"Deep cleansing scalp scrub with tea tree.\"\n",

            'staff' => "name,email,role,phone,specialization,commission_rate,status,password,bio\n"
                . "\"Natasha Romanov\",\"natasha.r@salonspa.local\",\"therapist\",\"+1 (555) 912-3456\",\"Holistic Facialist & Gua Sha Specialist\",18.00,\"active\",\"password\",\"10 years of clinical experience in European skin therapy.\"\n"
                . "\"Julian Thorne\",\"julian.t@salonspa.local\",\"therapist\",\"+1 (555) 923-4567\",\"Master Precision Barber & Stylist\",20.00,\"active\",\"password\",\"Specializes in modern men's fades and beard spa rituals.\"\n",

            default => throw new \InvalidArgumentException("No template available for type: {$type}")
        };
    }
}
