<?php

namespace Database\Seeders;

use App\Models\ApprovalTemplate;
use App\Models\ApprovalTemplateStep;
use App\Models\AssetCategory;
use App\Models\User;
use Illuminate\Database\Seeder;

class ApprovalTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $l1 = User::where('email', 'l1@ks.com')->first();
        $l2 = User::where('email', 'l2@ks.com')->first();
        $l3 = User::where('email', 'l3@ks.com')->first();
        $l4 = User::where('email', 'l4@ks.com')->first();
        $adminUser = User::role(['Admin', 'admin', 'Super Admin', 'superadmin'])->orderBy('id')->first()
            ?? User::where('email', 'admin@gmail.com')->first();

        if (! $l1 || ! $l2 || ! $l3) {
            $this->command?->warn('L1/L2/L3 users missing. Run IndentApproverSeeder first.');

            return;
        }

        // Default can be any user — prefer L1 for sample data, Admin is also supported.
        $defaultApproverId = $l1->id ?? $adminUser?->id;
        if (! $defaultApproverId) {
            $this->command?->warn('No default approver user found.');

            return;
        }

        $this->seedTemplate('Default Approval', null, [
            ['user', $defaultApproverId, null, null, true],
        ]);

        $examples = [
            'Laptop' => [
                ['user', $l1->id, null, null, true],
            ],
            'Mobile Phone' => [
                ['user', $l1->id, null, null, true],
                ['user', $l2->id, null, null, true],
            ],
            'Server' => [
                ['user', $l1->id, null, null, true],
                ['user', $l2->id, null, null, true],
                ['user', $l3->id, null, null, true],
            ],
            'CCTV' => [
                ['user', $l1->id, null, null, true],
            ],
            'Networking Equipment' => array_values(array_filter([
                ['user', $l1->id, null, null, true],
                ['user', $l2->id, null, null, true],
                ['user', $l3->id, null, null, true],
                $l4 ? ['user', $l4->id, null, null, true] : null,
            ])),
        ];

        foreach ($examples as $categoryName => $steps) {
            $category = AssetCategory::query()->firstOrCreate(
                ['category_name' => $categoryName],
                ['status' => 'active']
            );

            $this->seedTemplate($categoryName.' Approval', $category->id, $steps, true);
        }
    }

    /**
     * @param  list<array{0:string,1:?int,2:?string,3:?string,4:bool}>  $steps
     */
    private function seedTemplate(string $name, ?int $categoryId, array $steps, bool $replaceSteps = false): void
    {
        $query = ApprovalTemplate::query();
        if ($categoryId === null) {
            $query->whereNull('category_id');
        } else {
            $query->where('category_id', $categoryId);
        }

        $template = $query->first();

        if (! $template) {
            $template = ApprovalTemplate::create([
                'name' => $name,
                'category_id' => $categoryId,
                'is_active' => true,
            ]);
        } else {
            $template->update([
                'name' => $name,
                'is_active' => true,
            ]);
        }

        if ($replaceSteps || ! $template->steps()->exists()) {
            $template->steps()->delete();

            foreach ($steps as $i => [$type, $userId, $role, $permission, $mandatory]) {
                ApprovalTemplateStep::create([
                    'approval_template_id' => $template->id,
                    'sequence' => $i + 1,
                    'assignee_type' => $type,
                    'approver_user_id' => $userId,
                    'assignee_role' => $role,
                    'assignee_permission' => $permission,
                    'is_mandatory' => $mandatory,
                ]);
            }
        }
    }
}
