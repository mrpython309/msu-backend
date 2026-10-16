<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
  /**
   * Seed the application's database.
   */
  public function run(): void
  {
    // User::factory(10)->create();

    // Super Admin
    $superAdmin = User::firstOrCreate(
      ['email' => 'admin@gmail.com'],
      [
        'name' => 'Super Admin',
        'password' => '123456',
      ]
    );

    $this->call([                
        RolesPermissionSeeder::class,
        AssetPermissionSeeder::class,
        ApprovalWorkflowSeeder::class,
        IndentApproverSeeder::class,
        ApprovalTemplateSeeder::class,
        PurchaseOfficerSeeder::class,
        AccountsDepartmentSeeder::class,
        PaymentModulePermissionSeeder::class,
        QualityInspectionPermissionSeeder::class,
        ProcurementPermissionSeeder::class,
        AdminProcurementPermissionSeeder::class,
        RfqWorkflowRoleSeeder::class,
        SampleDataSeeder::class
    ]);

  }
}
