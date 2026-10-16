<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('indent_approval_levels', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('level')->unique();
            $table->string('code', 10);
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('approver_role_names')->nullable();
            $table->timestamps();
        });

        DB::table('indent_approval_levels')->insert([
            [
                'level' => 1,
                'code' => 'L1',
                'name' => 'Level 1 Approval',
                'is_active' => true,
                'sort_order' => 1,
                'approver_role_names' => json_encode(['department_head']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'level' => 2,
                'code' => 'L2',
                'name' => 'Level 2 Approval',
                'is_active' => true,
                'sort_order' => 2,
                'approver_role_names' => json_encode(['purchase_manager']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'level' => 3,
                'code' => 'L3',
                'name' => 'Level 3 Approval',
                'is_active' => true,
                'sort_order' => 3,
                'approver_role_names' => json_encode(['superadmin']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        Schema::table('indents', function (Blueprint $table) {
            if (! Schema::hasColumn('indents', 'approval_levels_required')) {
                $table->unsignedTinyInteger('approval_levels_required')->default(3)->after('status');
            }
            if (! Schema::hasColumn('indents', 'current_level')) {
                $table->unsignedTinyInteger('current_level')->nullable()->after('approval_levels_required');
            }
            if (! Schema::hasColumn('indents', 'submitted_at')) {
                $table->timestamp('submitted_at')->nullable()->after('remarks');
            }
            if (! Schema::hasColumn('indents', 'finalized_at')) {
                $table->timestamp('finalized_at')->nullable()->after('submitted_at');
            }
        });

        Schema::table('indent_approvals', function (Blueprint $table) {
            if (! Schema::hasColumn('indent_approvals', 'from_status')) {
                $table->string('from_status')->nullable()->after('action');
            }
            if (! Schema::hasColumn('indent_approvals', 'to_status')) {
                $table->string('to_status')->nullable()->after('from_status');
            }
            if (! Schema::hasColumn('indent_approvals', 'meta')) {
                $table->json('meta')->nullable()->after('comments');
            }
            if (! Schema::hasColumn('indent_approvals', 'acted_at')) {
                $table->timestamp('acted_at')->nullable()->after('meta');
            }
        });

        Schema::table('purchase_requisitions', function (Blueprint $table) {
            if (! Schema::hasColumn('purchase_requisitions', 'created_from_approval_at')) {
                $table->timestamp('created_from_approval_at')->nullable()->after('status');
            }
            if (! Schema::hasColumn('purchase_requisitions', 'remarks')) {
                $table->text('remarks')->nullable()->after('created_from_approval_at');
            }
        });

        if (! $this->indexExists('purchase_requisitions', 'purchase_requisitions_indent_id_unique')
            && ! $this->hasDuplicateValues('purchase_requisitions', 'indent_id')) {
            Schema::table('purchase_requisitions', function (Blueprint $table) {
                $table->unique('indent_id', 'purchase_requisitions_indent_id_unique');
            });
        }

        Schema::table('rfqs', function (Blueprint $table) {
            if (! Schema::hasColumn('rfqs', 'rfq_no')) {
                $table->string('rfq_no')->nullable()->after('id');
            }
            if (! Schema::hasColumn('rfqs', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('notes');
            }
            if (! Schema::hasColumn('rfqs', 'closes_at')) {
                $table->timestamp('closes_at')->nullable()->after('created_by');
            }
        });

        if (! $this->indexExists('rfqs', 'rfqs_rfq_no_unique')) {
            Schema::table('rfqs', function (Blueprint $table) {
                $table->unique('rfq_no', 'rfqs_rfq_no_unique');
            });
        }

        if (! $this->foreignKeyExists('rfqs', 'rfqs_created_by_foreign')) {
            Schema::table('rfqs', function (Blueprint $table) {
                $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            });
        }

        Schema::create('rfq_vendors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rfq_id');
            $table->unsignedBigInteger('vendor_id');
            $table->string('status')->default('Invited');
            $table->timestamp('invited_at')->nullable();
            $table->timestamps();

            $table->unique(['rfq_id', 'vendor_id'], 'rfq_vendors_rfq_vendor_unique');
            $table->foreign('rfq_id')->references('id')->on('rfqs')->cascadeOnDelete();
            $table->foreign('vendor_id')->references('id')->on('vendors')->cascadeOnDelete();
        });

        Schema::table('vendor_quotations', function (Blueprint $table) {
            if (! Schema::hasColumn('vendor_quotations', 'contact_person')) {
                $table->string('contact_person')->nullable()->after('vendor_id');
            }
            if (! Schema::hasColumn('vendor_quotations', 'contact_phone')) {
                $table->string('contact_phone', 50)->nullable()->after('contact_person');
            }
            if (! Schema::hasColumn('vendor_quotations', 'contact_email')) {
                $table->string('contact_email')->nullable()->after('contact_phone');
            }
            if (! Schema::hasColumn('vendor_quotations', 'rating_snapshot')) {
                $table->decimal('rating_snapshot', 3, 2)->nullable()->after('contact_email');
            }
            if (! Schema::hasColumn('vendor_quotations', 'quoted_at')) {
                $table->timestamp('quoted_at')->nullable()->after('is_selected');
            }
            if (! Schema::hasColumn('vendor_quotations', 'selected_at')) {
                $table->timestamp('selected_at')->nullable()->after('quoted_at');
            }
            if (! Schema::hasColumn('vendor_quotations', 'selected_by')) {
                $table->unsignedBigInteger('selected_by')->nullable()->after('selected_at');
            }
        });

        if (! $this->indexExists('vendor_quotations', 'vendor_quotations_rfq_vendor_unique')
            && ! $this->hasDuplicatePairs('vendor_quotations', 'rfq_id', 'vendor_id')) {
            Schema::table('vendor_quotations', function (Blueprint $table) {
                $table->unique(['rfq_id', 'vendor_id'], 'vendor_quotations_rfq_vendor_unique');
            });
        }

        if (! $this->foreignKeyExists('vendor_quotations', 'vendor_quotations_selected_by_foreign')) {
            Schema::table('vendor_quotations', function (Blueprint $table) {
                $table->foreign('selected_by')->references('id')->on('users')->nullOnDelete();
            });
        }

        Schema::create('vendor_quotation_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('quotation_id');
            $table->unsignedBigInteger('indent_item_id')->nullable();
            $table->string('item_name')->nullable();
            $table->integer('quantity')->default(1);
            $table->string('unit')->default('Nos');
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['quotation_id', 'indent_item_id'], 'vq_items_quotation_indent_item_unique');
            $table->foreign('quotation_id')->references('id')->on('vendor_quotations')->cascadeOnDelete();
            $table->foreign('indent_item_id')->references('id')->on('indent_items')->nullOnDelete();
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('purchase_orders', 'pr_id')) {
                $table->unsignedBigInteger('pr_id')->nullable()->after('quotation_id');
            }
            if (! Schema::hasColumn('purchase_orders', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('status');
            }
            if (! Schema::hasColumn('purchase_orders', 'remarks')) {
                $table->text('remarks')->nullable()->after('created_by');
            }
        });

        if (! $this->indexExists('purchase_orders', 'purchase_orders_quotation_id_unique')
            && ! $this->hasDuplicateValues('purchase_orders', 'quotation_id')) {
            Schema::table('purchase_orders', function (Blueprint $table) {
                $table->unique('quotation_id', 'purchase_orders_quotation_id_unique');
            });
        }

        if (! $this->foreignKeyExists('purchase_orders', 'purchase_orders_pr_id_foreign')) {
            Schema::table('purchase_orders', function (Blueprint $table) {
                $table->foreign('pr_id')->references('id')->on('purchase_requisitions')->nullOnDelete();
                $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            });
        }

        Schema::table('po_items', function (Blueprint $table) {
            if (! Schema::hasColumn('po_items', 'item_name')) {
                $table->string('item_name')->nullable()->after('indent_item_id');
            }
            if (! Schema::hasColumn('po_items', 'description')) {
                $table->text('description')->nullable()->after('item_name');
            }
            if (! Schema::hasColumn('po_items', 'unit')) {
                $table->string('unit')->default('Nos')->after('quantity');
            }
            if (! Schema::hasColumn('po_items', 'quotation_item_id')) {
                $table->unsignedBigInteger('quotation_item_id')->nullable()->after('indent_item_id');
            }
        });

        if (! $this->foreignKeyExists('po_items', 'po_items_quotation_item_id_foreign')) {
            Schema::table('po_items', function (Blueprint $table) {
                $table->foreign('quotation_item_id')->references('id')->on('vendor_quotation_items')->nullOnDelete();
            });
        }

        Schema::table('grns', function (Blueprint $table) {
            if (! Schema::hasColumn('grns', 'remarks')) {
                $table->text('remarks')->nullable()->after('delivery_challan_path');
            }
            if (! Schema::hasColumn('grns', 'inspected_by')) {
                $table->unsignedBigInteger('inspected_by')->nullable()->after('remarks');
            }
            if (! Schema::hasColumn('grns', 'inspected_at')) {
                $table->timestamp('inspected_at')->nullable()->after('inspected_by');
            }
            if (! Schema::hasColumn('grns', 'accepted_at')) {
                $table->timestamp('accepted_at')->nullable()->after('inspected_at');
            }
        });

        if (! $this->foreignKeyExists('grns', 'grns_inspected_by_foreign')) {
            Schema::table('grns', function (Blueprint $table) {
                $table->foreign('inspected_by')->references('id')->on('users')->nullOnDelete();
            });
        }

        Schema::table('grn_items', function (Blueprint $table) {
            if (! Schema::hasColumn('grn_items', 'brand_name')) {
                $table->string('brand_name')->nullable()->after('remarks');
            }
            if (! Schema::hasColumn('grn_items', 'model_name')) {
                $table->string('model_name')->nullable()->after('brand_name');
            }
            if (! Schema::hasColumn('grn_items', 'serial_numbers')) {
                $table->json('serial_numbers')->nullable()->after('model_name');
            }
            if (! Schema::hasColumn('grn_items', 'condition')) {
                $table->string('condition')->nullable()->after('serial_numbers');
            }
            if (! Schema::hasColumn('grn_items', 'accessories')) {
                $table->text('accessories')->nullable()->after('condition');
            }
            if (! Schema::hasColumn('grn_items', 'inspected_by')) {
                $table->unsignedBigInteger('inspected_by')->nullable()->after('accessories');
            }
            if (! Schema::hasColumn('grn_items', 'inspected_at')) {
                $table->timestamp('inspected_at')->nullable()->after('inspected_by');
            }
            if (! Schema::hasColumn('grn_items', 'assets_generated_at')) {
                $table->timestamp('assets_generated_at')->nullable()->after('inspected_at');
            }
        });

        if (! $this->foreignKeyExists('grn_items', 'grn_items_inspected_by_foreign')) {
            Schema::table('grn_items', function (Blueprint $table) {
                $table->foreign('inspected_by')->references('id')->on('users')->nullOnDelete();
            });
        }

        Schema::create('grn_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('grn_id');
            $table->string('document_type', 50);
            $table->string('path');
            $table->string('original_name')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['grn_id', 'document_type']);
            $table->foreign('grn_id')->references('id')->on('grns')->cascadeOnDelete();
            $table->foreign('uploaded_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('grn_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('grn_id');
            $table->string('action');
            $table->string('from_status')->nullable();
            $table->string('to_status')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->text('comments')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['grn_id', 'created_at']);
            $table->foreign('grn_id')->references('id')->on('grns')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::table('assets', function (Blueprint $table) {
            if (! Schema::hasColumn('assets', 'grn_item_id')) {
                $table->unsignedBigInteger('grn_item_id')->nullable()->after('supplier');
            }
            if (! Schema::hasColumn('assets', 'source_unit_index')) {
                $table->unsignedInteger('source_unit_index')->nullable()->after('grn_item_id');
            }
            if (! Schema::hasColumn('assets', 'purchase_order_id')) {
                $table->unsignedBigInteger('purchase_order_id')->nullable()->after('source_unit_index');
            }
        });

        if (! $this->indexExists('assets', 'assets_grn_item_unit_unique')) {
            Schema::table('assets', function (Blueprint $table) {
                $table->unique(['grn_item_id', 'source_unit_index'], 'assets_grn_item_unit_unique');
            });
        }

        if (! $this->foreignKeyExists('assets', 'assets_grn_item_id_foreign')) {
            Schema::table('assets', function (Blueprint $table) {
                $table->foreign('grn_item_id')->references('id')->on('grn_items')->nullOnDelete();
                $table->foreign('purchase_order_id')->references('id')->on('purchase_orders')->nullOnDelete();
            });
        }

        Schema::create('procurement_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->string('subject_type');
            $table->unsignedBigInteger('subject_id');
            $table->string('action');
            $table->string('from_status')->nullable();
            $table->string('to_status')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->text('comments')->nullable();
            $table->json('properties')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['subject_type', 'subject_id'], 'procurement_activity_subject_idx');
            $table->index(['action', 'created_at']);
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procurement_activity_logs');

        if ($this->foreignKeyExists('assets', 'assets_grn_item_id_foreign')) {
            Schema::table('assets', function (Blueprint $table) {
                $table->dropForeign('assets_grn_item_id_foreign');
            });
        }
        if ($this->foreignKeyExists('assets', 'assets_purchase_order_id_foreign')) {
            Schema::table('assets', function (Blueprint $table) {
                $table->dropForeign('assets_purchase_order_id_foreign');
            });
        }
        if ($this->indexExists('assets', 'assets_grn_item_unit_unique')) {
            Schema::table('assets', function (Blueprint $table) {
                $table->dropUnique('assets_grn_item_unit_unique');
            });
        }
        Schema::table('assets', function (Blueprint $table) {
            foreach (['grn_item_id', 'source_unit_index', 'purchase_order_id'] as $column) {
                if (Schema::hasColumn('assets', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::dropIfExists('grn_histories');
        Schema::dropIfExists('grn_documents');

        if ($this->foreignKeyExists('grn_items', 'grn_items_inspected_by_foreign')) {
            Schema::table('grn_items', function (Blueprint $table) {
                $table->dropForeign('grn_items_inspected_by_foreign');
            });
        }
        Schema::table('grn_items', function (Blueprint $table) {
            foreach ([
                'brand_name', 'model_name', 'serial_numbers', 'condition',
                'accessories', 'inspected_by', 'inspected_at', 'assets_generated_at',
            ] as $column) {
                if (Schema::hasColumn('grn_items', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        if ($this->foreignKeyExists('grns', 'grns_inspected_by_foreign')) {
            Schema::table('grns', function (Blueprint $table) {
                $table->dropForeign('grns_inspected_by_foreign');
            });
        }
        Schema::table('grns', function (Blueprint $table) {
            foreach (['remarks', 'inspected_by', 'inspected_at', 'accepted_at'] as $column) {
                if (Schema::hasColumn('grns', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        if ($this->foreignKeyExists('po_items', 'po_items_quotation_item_id_foreign')) {
            Schema::table('po_items', function (Blueprint $table) {
                $table->dropForeign('po_items_quotation_item_id_foreign');
            });
        }
        Schema::table('po_items', function (Blueprint $table) {
            foreach (['item_name', 'description', 'unit', 'quotation_item_id'] as $column) {
                if (Schema::hasColumn('po_items', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        if ($this->indexExists('purchase_orders', 'purchase_orders_quotation_id_unique')) {
            Schema::table('purchase_orders', function (Blueprint $table) {
                $table->dropUnique('purchase_orders_quotation_id_unique');
            });
        }
        if ($this->foreignKeyExists('purchase_orders', 'purchase_orders_pr_id_foreign')) {
            Schema::table('purchase_orders', function (Blueprint $table) {
                $table->dropForeign('purchase_orders_pr_id_foreign');
            });
        }
        if ($this->foreignKeyExists('purchase_orders', 'purchase_orders_created_by_foreign')) {
            Schema::table('purchase_orders', function (Blueprint $table) {
                $table->dropForeign('purchase_orders_created_by_foreign');
            });
        }
        Schema::table('purchase_orders', function (Blueprint $table) {
            foreach (['pr_id', 'created_by', 'remarks'] as $column) {
                if (Schema::hasColumn('purchase_orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::dropIfExists('vendor_quotation_items');

        if ($this->indexExists('vendor_quotations', 'vendor_quotations_rfq_vendor_unique')) {
            Schema::table('vendor_quotations', function (Blueprint $table) {
                $table->dropUnique('vendor_quotations_rfq_vendor_unique');
            });
        }
        if ($this->foreignKeyExists('vendor_quotations', 'vendor_quotations_selected_by_foreign')) {
            Schema::table('vendor_quotations', function (Blueprint $table) {
                $table->dropForeign('vendor_quotations_selected_by_foreign');
            });
        }
        Schema::table('vendor_quotations', function (Blueprint $table) {
            foreach ([
                'contact_person', 'contact_phone', 'contact_email', 'rating_snapshot',
                'quoted_at', 'selected_at', 'selected_by',
            ] as $column) {
                if (Schema::hasColumn('vendor_quotations', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::dropIfExists('rfq_vendors');

        if ($this->indexExists('rfqs', 'rfqs_rfq_no_unique')) {
            Schema::table('rfqs', function (Blueprint $table) {
                $table->dropUnique('rfqs_rfq_no_unique');
            });
        }
        if ($this->foreignKeyExists('rfqs', 'rfqs_created_by_foreign')) {
            Schema::table('rfqs', function (Blueprint $table) {
                $table->dropForeign('rfqs_created_by_foreign');
            });
        }
        Schema::table('rfqs', function (Blueprint $table) {
            foreach (['rfq_no', 'created_by', 'closes_at'] as $column) {
                if (Schema::hasColumn('rfqs', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        if ($this->indexExists('purchase_requisitions', 'purchase_requisitions_indent_id_unique')) {
            Schema::table('purchase_requisitions', function (Blueprint $table) {
                $table->dropUnique('purchase_requisitions_indent_id_unique');
            });
        }
        Schema::table('purchase_requisitions', function (Blueprint $table) {
            foreach (['created_from_approval_at', 'remarks'] as $column) {
                if (Schema::hasColumn('purchase_requisitions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('indent_approvals', function (Blueprint $table) {
            foreach (['from_status', 'to_status', 'meta', 'acted_at'] as $column) {
                if (Schema::hasColumn('indent_approvals', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('indents', function (Blueprint $table) {
            foreach (['approval_levels_required', 'current_level', 'submitted_at', 'finalized_at'] as $column) {
                if (Schema::hasColumn('indents', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::dropIfExists('indent_approval_levels');
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $database = Schema::getConnection()->getDatabaseName();

        return DB::table('information_schema.statistics')
            ->where('table_schema', $database)
            ->where('table_name', $table)
            ->where('index_name', $indexName)
            ->exists();
    }

    private function foreignKeyExists(string $table, string $constraintName): bool
    {
        $database = Schema::getConnection()->getDatabaseName();

        return DB::table('information_schema.table_constraints')
            ->where('table_schema', $database)
            ->where('table_name', $table)
            ->where('constraint_name', $constraintName)
            ->where('constraint_type', 'FOREIGN KEY')
            ->exists();
    }

    private function hasDuplicateValues(string $table, string $column): bool
    {
        return DB::table($table)
            ->whereNotNull($column)
            ->select($column)
            ->groupBy($column)
            ->havingRaw('COUNT(*) > 1')
            ->exists();
    }

    private function hasDuplicatePairs(string $table, string $first, string $second): bool
    {
        return DB::table($table)
            ->select($first, $second)
            ->groupBy($first, $second)
            ->havingRaw('COUNT(*) > 1')
            ->exists();
    }
};
