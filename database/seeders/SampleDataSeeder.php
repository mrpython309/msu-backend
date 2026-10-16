<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\Branch;
use App\Models\BranchLocation;
use App\Models\Brand;
use App\Models\Designation;
use App\Models\Department;
use App\Models\Vendor;
use App\Models\AssetType;
use App\Models\Asset;
use App\AssetCondition;
use App\AssetStatus;
use App\Models\User;
use App\Models\AssetAssignment;
use App\Models\AssetWarranty;
use App\WarrantyType;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use App\Models\AssetCategory;
use Illuminate\Support\Facades\Auth;
use App\Models\Settings;


class SampleDataSeeder extends Seeder
{
    
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Auth::loginUsingId(User::first()->id);

        $companies = [
            [
                'name' => 'Empire Technologies',
                'code' => 'EMP001',
                'city' => 'Erode',
                'state' => 'Tamil Nadu',
                'country' => 'India',
                'status' => 'active',
                'branches' => [
                    [
                        'name' => 'Empire - Head Office',
                        'code' => 'EMP-HO',
                        'city' => 'Erode',
                        'locations' => ['Admin Block', 'Accounts Dept', 'Server Room']
                    ],
                    [
                        'name' => 'Empire - Coimbatore Branch',
                        'code' => 'EMP-CBE',
                        'city' => 'Coimbatore',
                        'locations' => ['Front Office', 'Sales Dept', 'Service Center']
                    ],
                ]
            ],
            [
                'name' => 'Smart Solutions Pvt Ltd',
                'code' => 'SSPL002',
                'city' => 'Coimbatore',
                'state' => 'Tamil Nadu',
                'country' => 'India',
                'status' => 'active',
                'branches' => [
                    [
                        'name' => 'Smart - Main Branch',
                        'code' => 'SSPL-HO',
                        'city' => 'Coimbatore',
                        'locations' => ['Reception', 'Conference Hall', 'HR Cabin']
                    ],
                    [
                        'name' => 'Smart - Erode Branch',
                        'code' => 'SSPL-ERD',
                        'city' => 'Erode',
                        'locations' => ['Admin Office', 'Warehouse', 'Support Team']
                    ],
                ]
            ],
        ];



         foreach ($companies as $companyData) {

             $company = Company::updateOrCreate(
                ['code' => $companyData['code']],
                [
                    'name' => $companyData['name'],
                    'code' => $companyData['code'],
                    'city' => $companyData['city'],
                    'state' => $companyData['state'],
                    'country' => $companyData['country'],
                    'status' => $companyData['status'],
                ]
            );

            foreach ($companyData['branches'] as $branchData) {

                 $branch = Branch::updateOrCreate(
                    ['code' => $branchData['code']],
                    [
                        'name' => $branchData['name'],
                        'code' => $branchData['code'],
                        'company_id' => $company->id,
                        'city' => $branchData['city'],
                        'state' => $company->state,
                        'country' => $company->country,
                        'status' => 'active',
                    ]
                );


                foreach ($branchData['locations'] as $locationName) {

                    BranchLocation::updateOrCreate(
                        [
                            'location_name' => $locationName,
                            'branch_id' => $branch->id,
                        ],
                        [
                            'status' => 'active'
                        ]
                    );
                }

            }

         }


          $brands = [
            [
                'brand_name' => 'Dell',
                'brand_description' => 'Enterprise laptops, desktops and servers',
                'status' => 'active',
            ],
            [
                'brand_name' => 'HP',
                'brand_description' => 'Business and personal computing solutions',
                'status' => 'active',
            ],
            [
                'brand_name' => 'Lenovo',
                'brand_description' => 'ThinkPad laptops, desktops and workstations',
                'status' => 'active',
            ],
            [
                'brand_name' => 'Apple',
                'brand_description' => 'Premium laptops, mobiles and accessories',
                'status' => 'active',
            ],
            [
                'brand_name' => 'Acer',
                'brand_description' => 'Affordable laptops and desktops',
                'status' => 'active',
            ],
        ];

        foreach ($brands as $brandData) {
            Brand::updateOrCreate(
                ['brand_name' => $brandData['brand_name']],
                [
                    'brand_name' => $brandData['brand_name'],
                    'brand_description' => $brandData['brand_description'],
                    'status' => $brandData['status'],
                ]
            );
        }


         $designations = [
            ['designation_name' => 'Managing Director', 'status' => 'active'],
            ['designation_name' => 'Chief Executive Officer', 'status' => 'active'],
            ['designation_name' => 'Operations Manager', 'status' => 'active'],
            ['designation_name' => 'HR Manager', 'status' => 'active'],
            ['designation_name' => 'Accounts Manager', 'status' => 'active'],
            ['designation_name' => 'Senior Software Engineer', 'status' => 'active'],
            ['designation_name' => 'Software Engineer', 'status' => 'active'],
            ['designation_name' => 'System Administrator', 'status' => 'active'],
            ['designation_name' => 'IT Support Engineer', 'status' => 'active'],
            ['designation_name' => 'Sales Executive', 'status' => 'active'],
        ];

        foreach ($designations as $designation) {
            Designation::updateOrCreate(
                ['designation_name' => $designation['designation_name']], // unique key
                $designation
            );
        }

        $vendors = [
            [
                'name' => 'Tech Supplies India Pvt Ltd',
                'phone_number' => '9876543210',
                'email' => 'sales@techsupplies.in',
                'gst_number' => '33ABCDE1234F1Z5',
                'address' => '12, Industrial Estate',
                'city' => 'Coimbatore',
                'state' => 'Tamil Nadu',
                'country' => 'India',
                'status' => 'active',
                'notes' => 'Authorized IT hardware distributor',
            ],
            [
                'name' => 'Smart IT Solutions',
                'phone_number' => '9843012345',
                'email' => 'info@smartitsolutions.in',
                'gst_number' => '33AACCS9876L1Z9',
                'address' => 'Gandhipuram Main Road',
                'city' => 'Coimbatore',
                'state' => 'Tamil Nadu',
                'country' => 'India',
                'status' => 'active',
                'notes' => 'Laptop & desktop wholesale supplier',
            ],
            [
                'name' => 'Delta Office Systems',
                'phone_number' => '9787612345',
                'email' => 'contact@deltaoffice.in',
                'gst_number' => '33BBCCD5678A1Z1',
                'address' => 'Perundurai Road',
                'city' => 'Erode',
                'state' => 'Tamil Nadu',
                'country' => 'India',
                'status' => 'active',
                'notes' => 'Office automation products',
            ],
            [
                'name' => 'NextGen Tech Mart',
                'phone_number' => '9790012345',
                'email' => 'support@nextgentech.in',
                'gst_number' => '33DDDDE4444Q1Z8',
                'address' => 'Avinashi Road',
                'city' => 'Tiruppur',
                'state' => 'Tamil Nadu',
                'country' => 'India',
                'status' => 'active',
                'notes' => 'Network & server equipment vendor',
            ],
            [
                'name' => 'Prime Solutions',
                'phone_number' => '9003012345',
                'email' => 'hello@primesolutions.in',
                'gst_number' => '33EEEFF9999P1Z2',
                'address' => 'RS Puram',
                'city' => 'Coimbatore',
                'state' => 'Tamil Nadu',
                'country' => 'India',
                'status' => 'inactive',
                'notes' => 'Seasonal supplier',
            ],
        ];

        foreach ($vendors as $vendor) {
            Vendor::updateOrCreate(
                ['phone_number' => $vendor['phone_number']], // unique key
                $vendor
            );
        }


        /**  Asset Category */


        // Parent Categories
        $tangibleId = DB::table('asset_categories')->insertGetId([
            'category_name' => 'Tangible Assets (Physical)',
            'parent_id' => null,
            'status' => 'active',
        ]);

        $intangibleId = DB::table('asset_categories')->insertGetId([
            'category_name' => 'Intangible Assets (Non-Physical)',
            'parent_id' => null,
            'status' => 'active',
        ]);

        // Tangible Sub Categories
        DB::table('asset_categories')->insert([
            ['category_name' => 'Machinery & Equipment', 'parent_id' => $tangibleId, 'status' => 'active'],
            ['category_name' => 'Vehicles', 'parent_id' => $tangibleId, 'status' => 'active'],
            ['category_name' => 'Computers / Laptops', 'parent_id' => $tangibleId, 'status' => 'active'],
            ['category_name' => 'Furniture & Fixtures', 'parent_id' => $tangibleId, 'status' => 'active'],
            ['category_name' => 'Tools & Instruments', 'parent_id' => $tangibleId, 'status' => 'active'],
            ['category_name' => 'Electrical & Mechanical Assets', 'parent_id' => $tangibleId, 'status' => 'active'],
        ]);

        // Intangible Sub Categories
        DB::table('asset_categories')->insert([
            ['category_name' => 'Software Licenses', 'parent_id' => $intangibleId, 'status' => 'active'],
            ['category_name' => 'Patents & Trademarks', 'parent_id' => $intangibleId, 'status' => 'active'],
            ['category_name' => 'Brand Value', 'parent_id' => $intangibleId, 'status' => 'active'],
            ['category_name' => 'Domain Names', 'parent_id' => $intangibleId, 'status' => 'active'],
            ['category_name' => 'Digital Certificates', 'parent_id' => $intangibleId, 'status' => 'active'],
            ['category_name' => 'Intellectual Property', 'parent_id' => $intangibleId, 'status' => 'active'],
        ]);


        $tangibleCategoryId = AssetCategory::where('category_name', 'Tangible Assets (Physical)')->value('id');

        $computerSubCatId = AssetCategory::where('category_name', 'Computers / Laptops')->value('id');
        $electricalSubCatId = AssetCategory::where('category_name', 'Electrical & Mechanical Assets')->value('id');
        $machinerySubCatId = AssetCategory::where('category_name', 'Machinery & Equipment')->value('id');

        $assetTypes = [
            [
                'asset_type_name' => 'Laptop',
                'asset_type_description' => 'Company issued laptops for employees',
                'category_id' => $tangibleCategoryId,
                'sub_category_id' => $computerSubCatId,
                'status' => 'active',
            ],
            [
                'asset_type_name' => 'Desktop',
                'asset_type_description' => 'Office desktop computers',
                'category_id' => $tangibleCategoryId,
                'sub_category_id' => $computerSubCatId,
                'status' => 'active',
            ],
            [
                'asset_type_name' => 'Printer',
                'asset_type_description' => 'Office printers and scanners',
                'category_id' => $tangibleCategoryId,
                'sub_category_id' => $electricalSubCatId,
                'status' => 'active',
            ],
            [
                'asset_type_name' => 'Router',
                'asset_type_description' => 'Network routers and gateways',
                'category_id' => $tangibleCategoryId,
                'sub_category_id' => $electricalSubCatId,
                'status' => 'active',
            ],
            [
                'asset_type_name' => 'Switch',
                'asset_type_description' => 'Network switches',
                'category_id' => $tangibleCategoryId,
                'sub_category_id' => $electricalSubCatId,
                'status' => 'active',
            ],
            [
                'asset_type_name' => 'Monitor',
                'asset_type_description' => 'External monitors and displays',
                'category_id' => $tangibleCategoryId,
                'sub_category_id' => $electricalSubCatId,
                'status' => 'active',
            ],
            [
                'asset_type_name' => 'Projector',
                'asset_type_description' => 'Projectors for meeting rooms',
                'category_id' => $tangibleCategoryId,
                'sub_category_id' => $machinerySubCatId,
                'status' => 'active',
            ],
            [
                'asset_type_name' => 'Biometric Device',
                'asset_type_description' => 'Attendance & access control systems',
                'category_id' => $tangibleCategoryId,
                'sub_category_id' => $machinerySubCatId,
                'status' => 'active',
            ],
            [
                'asset_type_name' => 'Server',
                'asset_type_description' => 'Physical and virtual servers',
                'category_id' => $tangibleCategoryId,
                'sub_category_id' => $machinerySubCatId,
                'status' => 'active',
            ],
            [
                'asset_type_name' => 'CCTV Camera',
                'asset_type_description' => 'Surveillance and security cameras',
                'category_id' => $tangibleCategoryId,
                'sub_category_id' => $machinerySubCatId,
                'status' => 'active',
            ],
        ];

        foreach ($assetTypes as $type) {
            AssetType::updateOrCreate(
                ['asset_type_name' => $type['asset_type_name']],
                $type
            );
        }

        $assetTypes = AssetType::with(['category', 'subCategory'])->get();
        $vendors    = Vendor::pluck('id')->toArray();
        $brands     = Brand::pluck('id')->toArray();
        $companies  = Company::with('branches.locations')->get();
        $users      = User::pluck('id')->toArray();

        $assets = [
            'Dell Latitude 7420',
            'HP EliteBook 850 G8',
            'Lenovo ThinkPad T14',
            'Canon ImageRunner 2525',
            'Epson L3150 Printer',
            'Cisco ISR 4331 Router',
            'HP ProLiant DL380 Server',
            'Samsung 27 Monitor',
            'BenQ Projector',
            'Hikvision CCTV Camera',
        ];

        foreach ($assets as $index => $name) {

            $company  = $companies->random();
            $branch   = $company->branches->random();
            $location = $branch->locations->random();
            $assetType = $assetTypes->random();

            Asset::updateOrCreate(
                ['asset_code' => Asset::ASSET_PREFIX . str_pad($index + 1, 6, '0', STR_PAD_LEFT)],
                [
                    'asset_name'        => $name,
                    'asset_structure'   => 'single',
                    'items_count'       => 1,

                    // 🔥 IMPORTANT: derive from asset type
                    'category_id'       => $assetType->category_id,
                    'sub_category_id'   => $assetType->sub_category_id,
                    'asset_type_id'     => $assetType->id,

                    'serial_no'         => strtoupper(Str::random(10)),

                    'status'            => AssetStatus::AVAILABLE,
                    'condition'         => AssetCondition::NEW,

                    'company_id'        => $company->id,
                    'branch_id'         => $branch->id,
                    'location_id'       => $location->id,

                    'purchase_date'     => now()->subDays(rand(30, 1000)),
                    'purchase_cost'     => rand(25000, 250000),

                    'supplier'          => $vendors[array_rand($vendors)] ?? null,
                    'brand_id'          => $brands[array_rand($brands)] ?? null,

                    'invoice_details'   => 'INV-' . strtoupper(Str::random(6)),

                    'depreciation_method' => 'straight_line',
                    'userful_life'        => rand(3, 5),
                    'salvage_value'       => rand(1000, 10000),

                    'description'       => 'Auto generated asset for ERP testing',

                    'created_by'        => $users[array_rand($users)] ?? null,
                    'updated_by'        => $users[array_rand($users)] ?? null,
                ]
            );
        }



 $assets   = Asset::all();
        $branches = Branch::with('locations')->get();
        $users    = User::all();

        if ($assets->isEmpty() || $branches->count() < 2) {
            return;
        }

        foreach ($assets as $asset) {

            // Random FROM branch
            $fromBranch = $branches->random();

            // Random TO branch (different)
            $toBranch = $branches
                ->where('id', '!=', $fromBranch->id)
                ->random();

            // Pick location from FROM branch
            $fromLocation = $fromBranch->locations->isNotEmpty()
                ? $fromBranch->locations->random()
                : null;

            // Pick location from TO branch
            $toLocation = $toBranch->locations->isNotEmpty()
                ? $toBranch->locations->random()
                : null;

            $assignedUser = $users->random();

            $statusList = [
                AssetAssignment::IN_TRANSIT,
                AssetAssignment::ACCEPTED,
                AssetAssignment::REJECTED,
                AssetAssignment::RETURN,
                AssetAssignment::RETURN_ACCEPTED,
            ];

            $status = collect($statusList)->random();

            DB::transaction(function () use (
                $asset,
                $fromBranch,
                $toBranch,
                $fromLocation,
                $toLocation,
                $assignedUser,
                $status
            ) {

                $assignment = AssetAssignment::create([
                    'asset_id'         => $asset->id,

                    'from_company_id'  => $fromBranch->company_id,
                    'from_branch'      => $fromBranch->id,
                    'from_location'    => $fromLocation?->id,

                    'to_company_id'    => $toBranch->company_id,
                    'to_branch'        => $toBranch->id,
                    'to_location'      => $toLocation?->id,

                    'assigned_to'      => 'branch',
                    'transfer_status'  => $status,

                    'assigned_by'      => 1,
                    'assigned_at'      => now()->subDays(rand(1, 10)),
                    'assigned_notes'   => 'Initial assignment',

                    'assign_date'      => now(),
                    'status'           => 'assigned',
                ]);

                // Fill logs based on status
                switch ($status) {

                    case AssetAssignment::ACCEPTED:
                        $assignment->update([
                            'accepted_by'    => $assignedUser->id,
                            'accepted_at'    => now()->subDays(rand(1, 5)),
                            'accepted_notes' => 'Accepted successfully',
                        ]);
                        break;

                    case AssetAssignment::REJECTED:
                        $assignment->update([
                            'rejected_by'    => $assignedUser->id,
                            'rejected_at'    => now()->subDays(rand(1, 5)),
                            'rejected_notes' => 'Rejected due to issue',
                        ]);
                        break;

                    case AssetAssignment::RETURN:
                        $assignment->update([
                            'returned_by'  => $assignedUser->id,
                            'returned_at'  => now()->subDays(rand(1, 5)),
                            'return_notes' => 'Asset returned',
                        ]);
                        break;

                    case AssetAssignment::RETURN_ACCEPTED:
                        $assignment->update([
                            'returned_by'           => $assignedUser->id,
                            'returned_at'           => now()->subDays(3),
                            'return_notes'          => 'Asset returned',
                            'return_accepted_by'    => 1,
                            'return_accepted_at'    => now()->subDays(1),
                            'return_accepted_notes' => 'Return accepted',
                        ]);
                        break;
                }
            });
        }

        $assets = Asset::pluck('id')->toArray();

        foreach ($assets as $assetId) {

            AssetWarranty::updateOrCreate(
                ['asset_id' => $assetId],
                [
                    'covered_parts' => 'Motherboard, Processor, RAM, SSD',
                    'warranty_type' => collect(WarrantyType::cases())->random()->value,
                    'warranty_number' => 'WAR-' . strtoupper(Str::random(8)),
                    'warranty_expire_date' => Carbon::now()->addMonths(rand(6, 36)),
                    'max_usage' => rand(1000, 10000),
                    'usage_metric' => 'hours',
                    'notes' => 'Auto seeded warranty record',
                ]
            );
            
        }



        $companies = Company::with(['branches.locations'])->get();

        if ($companies->isEmpty()) {
            return;
        }

        // Pick random company
        $company = $companies->random();

        // Pick branch from that company
        $branch = $company->branches->isNotEmpty()
            ? $company->branches->random()
            : null;

        if (!$branch) {
            return;
        }

        // Pick location from that branch
        $location = $branch->locations->isNotEmpty()
            ? $branch->locations->random()
            : null;

        DB::transaction(function () use ($company, $branch, $location) {

            Settings::updateOrCreate(
                ['key' => 'warehouse_company_id'],
                ['value' => $company->id]
            );

            Settings::updateOrCreate(
                ['key' => 'warehouse_id'],
                ['value' => $branch->id]
            );

            Settings::updateOrCreate(
                ['key' => 'warehouse_location'],
                ['value' => $location?->id]
            );

            Settings::updateOrCreate(
                ['key' => 'pagination_size'],
                ['value' => 10]
            );

        });



        $departments = [
            ['department_name' => 'Information Technology', 'status' => 'active'],
            ['department_name' => 'Human Resources', 'status' => 'active'],
            ['department_name' => 'Finance & Accounts', 'status' => 'active'],
            ['department_name' => 'Sales & Marketing', 'status' => 'active'],
            ['department_name' => 'Customer Support', 'status' => 'active'],
            ['department_name' => 'Research & Development', 'status' => 'active'],
            ['department_name' => 'Operations', 'status' => 'active'],
            ['department_name' => 'Quality Assurance', 'status' => 'active'],
            ['department_name' => 'Administration', 'status' => 'active'],
            ['department_name' => 'Procurement', 'status' => 'active'],
            ['department_name' => 'Legal', 'status' => 'active'],
            ['department_name' => 'Training & Development', 'status' => 'active'],
            ['department_name' => 'Network Operations', 'status' => 'active'],
            ['department_name' => 'Data Center', 'status' => 'active'],
            ['department_name' => 'Security', 'status' => 'active'],
        ];

        foreach ($departments as $department) {
            Department::updateOrCreate(
                ['department_name' => $department['department_name']],
                $department
            );
        }


        $companies   = Company::with('branches')->get();
        $departments = Department::all(); // GLOBAL TABLE
        $roles       = Role::all();

        if ($companies->isEmpty() || $departments->isEmpty() || $roles->isEmpty()) {
            $this->command->warn('Missing companies / departments / roles. Seeder skipped.');
            return;
        }

        foreach ($companies as $company) {

            if ($company->branches->isEmpty()) continue;

            for ($i = 1; $i <= 2; $i++) {

                $user = User::create([
                    'name'             => fake()->name(),
                    'emp_code'         => 'EMP-' . strtoupper(Str::random(6)),
                    'date_of_joining' => fake()->dateTimeBetween('-3 years', 'now'),
                    'mobile_number'   => fake()->unique()->numerify('9#########'),
                    'email'           => fake()->unique()->safeEmail(),
                    'designation_id' => rand(1, 6),
                    'password'        => Hash::make('password'),
                    'role_id'         => $roles->random()->id,
                    'company_id'      => $company->id,
                    'employee_status'=> fake()->randomElement(['active','inactive']),
                ]);

                // Multi Branch Mapping
                $user->branches()->sync(
                    $company->branches
                        ->random(rand(1, min(2, $company->branches->count())))
                        ->pluck('id')
                        ->toArray()
                );

                // Global Department Mapping
                $user->departments()->sync(
                    $departments
                        ->random(rand(1, min(2, $departments->count())))
                        ->pluck('id')
                        ->toArray()
                );

                // Global Role Mapping
                $user->assignRole('employee');
            }
        }



    }
}
