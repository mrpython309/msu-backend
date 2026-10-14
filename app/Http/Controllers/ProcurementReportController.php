<?php

namespace App\Http\Controllers;

use App\Enums\Procurement\PurchaseOrderPaymentStatus;
use App\Models\Asset;
use App\Models\Grn;
use App\Models\Indent;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequisition;
use App\Models\Rfq;
use App\Models\Vendor;
use App\Services\ReportPdfExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProcurementReportController extends Controller
{
    public function __construct(private readonly ReportPdfExportService $pdf)
    {
    }

    public function index()
    {
        return view('procurement.reports');
    }

    public function show(Request $request, string $report)
    {
        if ($report === 'payments') {
            return $this->showPaymentReport($request);
        }

        [$records, $headings, $rows] = $this->buildReport($request, $report);

        if ($request->get('format') === 'pdf') {
            return $this->pdf->download(
                'procurement.report-print',
                compact('records', 'report'),
                "{$report}.pdf"
            );
        }

        if ($request->get('format') === 'excel') {
            return $this->downloadExcel($report, $headings, $rows);
        }

        return view('procurement.report-show', compact('records', 'report'));
    }

    private function showPaymentReport(Request $request)
    {
        $filters = [
            'vendor_id' => $request->integer('vendor_id') ?: null,
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
            'status' => $request->input('status'),
        ];

        [$records, $headings, $rows, $summary] = $this->paymentReport($filters);
        $vendors = Vendor::query()->active()->orderBy('name')->get(['id', 'name']);
        $statuses = PurchaseOrderPaymentStatus::cases();
        $report = 'payments';

        if ($request->get('format') === 'pdf') {
            return $this->pdf->download(
                'procurement.payment-report-print',
                compact('records', 'summary', 'filters', 'report'),
                'payment-report.pdf'
            );
        }

        if ($request->get('format') === 'excel') {
            $exportRows = $rows->values();
            $exportRows->push([
                'TOTAL',
                $summary['vendor_label'],
                '',
                number_format((float) $summary['total_po_amount'], 2, '.', ''),
                number_format((float) $summary['total_paid_amount'], 2, '.', ''),
                number_format((float) $summary['total_remaining'], 2, '.', ''),
                '',
            ]);

            return $this->downloadExcel('payment-report', $headings, $exportRows);
        }

        return view('procurement.payment-report-show', compact(
            'records',
            'summary',
            'filters',
            'vendors',
            'statuses',
            'report'
        ));
    }

    /**
     * PO-centric payment report so Pending / Partially Paid / Paid all appear.
     *
     * @param  array{vendor_id:?int,date_from:?string,date_to:?string,status:?string}  $filters
     * @return array{0: Collection, 1: list<string>, 2: Collection, 3: array<string, mixed>}
     */
    private function paymentReport(array $filters): array
    {
        $query = PurchaseOrder::query()
            ->with(['vendor', 'payments'])
            ->withSum('payments as payments_sum', 'payment_amount')
            ->when($filters['vendor_id'] ?? null, fn ($q, $vendorId) => $q->where('vendor_id', $vendorId))
            ->when($filters['date_from'] ?? null, fn ($q, $from) => $q->whereDate('purchase_date', '>=', $from))
            ->when($filters['date_to'] ?? null, fn ($q, $to) => $q->whereDate('purchase_date', '<=', $to))
            ->latest('purchase_date')
            ->latest('id');

        $records = $query->get();

        $statusFilter = $filters['status'] ?? null;
        if (filled($statusFilter)) {
            $records = $records->filter(function (PurchaseOrder $po) use ($statusFilter) {
                return $po->paymentStatusEnum()->value === $statusFilter;
            })->values();
        }

        $headings = [
            'PO No',
            'Vendor',
            'PO Date',
            'PO Total',
            'Total Paid',
            'Remaining',
            'Payment Status',
        ];

        $rows = $records->map(function (PurchaseOrder $po) {
            $poTotal = (float) $po->orderTotalAmount();
            $paid = (float) $po->paidAmount();
            $remaining = (float) $po->remainingAmount();

            return [
                $po->po_no,
                $po->vendor?->name ?? '--',
                optional($po->purchase_date)->format('Y-m-d') ?? '--',
                number_format($poTotal, 2, '.', ''),
                number_format($paid, 2, '.', ''),
                number_format($remaining, 2, '.', ''),
                $po->paymentStatusEnum()->value,
            ];
        });

        $totalPoAmount = round((float) $records->sum(fn (PurchaseOrder $po) => (float) $po->orderTotalAmount()), 2);
        $totalPaidAmount = round((float) $records->sum(fn (PurchaseOrder $po) => (float) $po->paidAmount()), 2);
        $totalRemaining = round((float) $records->sum(fn (PurchaseOrder $po) => (float) $po->remainingAmount()), 2);

        $vendorLabel = 'All Vendors';
        if (! empty($filters['vendor_id'])) {
            $vendorLabel = Vendor::query()->whereKey($filters['vendor_id'])->value('name') ?? '--';
        } elseif ($records->pluck('vendor_id')->filter()->unique()->count() === 1) {
            $vendorLabel = $records->first()?->vendor?->name ?? 'All Vendors';
        }

        $summary = [
            'payments_count' => (int) $records->sum(fn (PurchaseOrder $po) => $po->payments->count()),
            'po_count' => $records->count(),
            'total_paid_amount' => $totalPaidAmount,
            'total_po_amount' => $totalPoAmount,
            'total_remaining' => $totalRemaining,
            'vendor_label' => $vendorLabel,
        ];

        return [$records, $headings, $rows, $summary];
    }

    /**
     * @return array{0: Collection, 1: list<string>, 2: Collection}
     */
    private function buildReport(Request $request, string $report): array
    {
        return match ($report) {
            'indents' => $this->indentReport($request),
            'pending-approvals' => $this->pendingApprovalsReport($request),
            'purchase-requisitions' => $this->prReport(),
            'rfqs' => $this->rfqReport(),
            'purchase-orders' => $this->poReport(),
            'vendor-performance' => $this->vendorPerformanceReport(),
            'grns' => $this->grnReport(),
            'asset-creation' => $this->assetCreationReport(),
            default => abort(404),
        };
    }

    private function indentReport(Request $request): array
    {
        $records = Indent::visibleToUser($request->user())
            ->with(['department', 'requestedBy'])
            ->latest()
            ->get();

        $rows = $records->map(fn (Indent $indent) => [
            $indent->indent_no,
            optional($indent->request_date)->format('Y-m-d'),
            $indent->department->department_name ?? '',
            $indent->requestedBy->name ?? '',
            $indent->priority,
            $indent->status->value ?? $indent->status,
        ]);

        return [$records, ['Indent No', 'Request Date', 'Department', 'Requested By', 'Priority', 'Status'], $rows];
    }

    private function pendingApprovalsReport(Request $request): array
    {
        $records = Indent::visibleToUser($request->user())
            ->pendingApproval()
            ->with(['department', 'requestedBy'])
            ->latest()
            ->get();

        $rows = $records->map(fn (Indent $indent) => [
            $indent->indent_no,
            $indent->department->department_name ?? '',
            $indent->status->value ?? $indent->status,
            $indent->priority,
            $indent->requestedBy->name ?? '',
        ]);

        return [$records, ['Indent No', 'Department', 'Status', 'Priority', 'Requested By'], $rows];
    }

    private function prReport(): array
    {
        $records = PurchaseRequisition::with(['indent.department', 'vendor', 'purchaseOfficer'])->latest()->get();
        $rows = $records->map(fn (PurchaseRequisition $pr) => [
            $pr->pr_no,
            $pr->indent->indent_no ?? '',
            $pr->vendor->name ?? '',
            optional($pr->required_date)->format('Y-m-d'),
            $pr->purchaseOfficer->name ?? '',
            $pr->status->value ?? $pr->status,
        ]);

        return [$records, ['PR No', 'Indent No', 'Vendor', 'Required Date', 'Purchase Officer', 'Status'], $rows];
    }

    private function rfqReport(): array
    {
        $records = Rfq::with(['purchaseRequisition', 'quotations.vendor'])->latest()->get();
        $rows = $records->map(fn (Rfq $rfq) => [
            $rfq->rfq_no,
            $rfq->purchaseRequisition->pr_no ?? '',
            $rfq->status->value ?? $rfq->status,
            $rfq->quotations->count(),
            $rfq->quotations->where('is_selected', true)->first()?->vendor?->name ?? '',
        ]);

        return [$records, ['RFQ No', 'PR No', 'Status', 'Quotations', 'Selected Vendor'], $rows];
    }

    private function poReport(): array
    {
        $records = PurchaseOrder::with('vendor')->latest()->get();
        $rows = $records->map(fn (PurchaseOrder $po) => [
            $po->po_no,
            $po->vendor->name ?? '',
            optional($po->purchase_date)->format('Y-m-d'),
            optional($po->delivery_date)->format('Y-m-d'),
            number_format((float) $po->grand_total, 2, '.', ''),
            $po->status->value ?? $po->status,
        ]);

        return [$records, ['PO No', 'Vendor', 'Purchase Date', 'Delivery Date', 'Grand Total', 'Status'], $rows];
    }

    private function vendorPerformanceReport(): array
    {
        $records = Vendor::query()
            ->withCount([
                'quotations as quotations_count',
                'purchaseOrders as purchase_orders_count',
                'grns as grns_count',
            ])
            ->withAvg('quotations as avg_quote_total', 'total_cost')
            ->withAvg('quotations as avg_delivery_days', 'delivery_days')
            ->orderBy('name')
            ->get();

        $rows = $records->map(fn (Vendor $vendor) => [
            $vendor->name,
            (int) $vendor->quotations_count,
            (int) $vendor->purchase_orders_count,
            (int) $vendor->grns_count,
            number_format((float) ($vendor->avg_quote_total ?? 0), 2, '.', ''),
            number_format((float) ($vendor->avg_delivery_days ?? 0), 1, '.', ''),
        ]);

        return [$records, ['Vendor', 'Quotations', 'POs', 'GRNs', 'Avg Quote Total', 'Avg Delivery Days'], $rows];
    }

    private function grnReport(): array
    {
        $records = Grn::with(['vendor', 'purchaseOrder', 'receivedBy'])->latest()->get();
        $rows = $records->map(fn (Grn $grn) => [
            $grn->grn_no,
            $grn->purchaseOrder->po_no ?? '',
            $grn->vendor->name ?? '',
            optional($grn->received_date)->format('Y-m-d'),
            $grn->receivedBy->name ?? '',
            $grn->status->value ?? $grn->status,
        ]);

        return [$records, ['GRN No', 'PO No', 'Vendor', 'Received Date', 'Received By', 'Status'], $rows];
    }

    private function assetCreationReport(): array
    {
        $records = Asset::query()
            ->whereNotNull('grn_item_id')
            ->with(['grnItem.grn', 'purchaseOrder', 'vendor'])
            ->latest()
            ->get();

        $rows = $records->map(fn (Asset $asset) => [
            $asset->asset_code,
            $asset->asset_name,
            $asset->grnItem->grn->grn_no ?? '',
            $asset->purchaseOrder->po_no ?? '',
            $asset->vendor->name ?? '',
            optional($asset->purchase_date)->format('Y-m-d'),
            number_format((float) $asset->purchase_cost, 2, '.', ''),
            $asset->status->value ?? $asset->status,
        ]);

        return [$records, ['Asset Code', 'Asset Name', 'GRN No', 'PO No', 'Vendor', 'Purchase Date', 'Cost', 'Status'], $rows];
    }

    private function downloadExcel(string $report, array $headings, Collection $rows): BinaryFileResponse
    {
        $export = new class($headings, $rows) implements FromCollection, WithHeadings
        {
            public function __construct(private array $headings, private Collection $rows)
            {
            }

            public function collection(): Collection
            {
                return $this->rows;
            }

            public function headings(): array
            {
                return $this->headings;
            }
        };

        return Excel::download($export, "{$report}-".now()->format('Ymd_His').'.xlsx');
    }
}
