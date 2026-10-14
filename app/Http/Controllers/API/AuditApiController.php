<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AuditHistory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AuditApiController extends Controller
{
    /**
     * Mobile Dashboard Audit Statistics (Screen 2).
     */
    public function getStats(): JsonResponse
    {
        $pending = Asset::where(function ($q) {
            $q->whereNull('audit_status')
              ->orWhere('audit_status', 'Pending Audit')
              ->orWhere('audit_status', '');
        })->count();

        $audited = Asset::where('audit_status', 'audited')
            ->orWhere('audit_status', 'Audited')
            ->count();

        $notFound = Asset::where('audit_status', 'not_found')
            ->orWhere('audit_status', 'Not Found')
            ->count();

        $total = Asset::count();

        return response()->json([
            'status' => true,
            'message' => 'Stats retrieved successfully',
            'data' => [
                'pending_audit' => $pending,
                'audited' => $audited,
                'not_found' => $notFound,
                'total' => $total,
            ],
        ], 200);
    }

    /**
     * Asset Lookup by QR code or manual asset code (Screens 3 & 4).
     */
    public function lookup(Request $request): JsonResponse
    {
        $rawCode = trim((string) $request->query('code', ''));

        if (empty($rawCode)) {
            return response()->json([
                'status' => false,
                'message' => 'Please provide an asset QR code or code parameter.',
            ], 400);
        }

        $code = $this->extractAssetCode($rawCode);

        // 1. Direct match by asset_code
        $asset = Asset::with(['company', 'branch', 'location', 'department', 'custodian', 'productCategory', 'productSubCategory'])
            ->where('asset_code', $code)
            ->orWhereRaw('UPPER(asset_code) = ?', [strtoupper($code)])
            ->first();

        // 2. Try zero-padded/unpadded variants (e.g. MSU00010 vs MSU000010)
        if (!$asset && preg_match('/^([a-zA-Z]+)0*(\d+)$/', $code, $matches)) {
            $prefix = strtoupper($matches[1]);
            $num = $matches[2];
            $candidates = [
                $prefix . $num,
                $prefix . str_pad($num, 4, '0', STR_PAD_LEFT),
                $prefix . str_pad($num, 5, '0', STR_PAD_LEFT),
                $prefix . str_pad($num, 6, '0', STR_PAD_LEFT),
                $prefix . str_pad($num, 7, '0', STR_PAD_LEFT),
                $prefix . str_pad($num, 8, '0', STR_PAD_LEFT),
            ];
            $asset = Asset::with(['company', 'branch', 'location', 'department', 'custodian', 'productCategory', 'productSubCategory'])
                ->whereIn('asset_code', $candidates)
                ->first();
        }

        // 3. Fallback to numeric ID
        if (!$asset && is_numeric($code)) {
            $asset = Asset::with(['company', 'branch', 'location', 'department', 'custodian', 'productCategory', 'productSubCategory'])
                ->find((int) $code);
        }

        if (!$asset) {
            return response()->json([
                'status' => false,
                'message' => "Invalid QR code. No asset found matching \"{$rawCode}\".",
            ], 404);
        }

        // Duplicate scan check
        $isDuplicate = $asset->hasAuditRecord();
        $duplicateMessage = null;

        if ($isDuplicate) {
            $lastAudit = $asset->auditHistories()->latest('audited_at')->first();
            $auditor = $lastAudit?->auditor_name ?? $asset->auditByLabel();
            $date = $asset->auditOnLabel();
            $duplicateMessage = "This asset has already been audited on {$date} by {$auditor}. Submitting again will append a new audit history record.";
        }

        return response()->json([
            'status' => true,
            'message' => 'Asset retrieved successfully',
            'is_duplicate_scan' => $isDuplicate,
            'duplicate_message' => $duplicateMessage,
            'data' => $asset->toMobileArray(),
        ], 200);
    }

    /**
     * Submit an audit record (Screens 5, 6, 7, 8, 9).
     * Synchronizes audited_by, audited_at, audit_status in the assets table,
     * so it immediately reflects on the web portal.
     */
    public function submit(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'asset_id' => 'required|integer|exists:assets,id',
            'audit_status' => 'required|string',
            'location_status' => 'nullable|string|max:255',
            'remarks' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();
        if (!$user) {
            $user = User::first();
        }
        $auditorId = $user ? $user->id : 1;
        $auditorName = $user ? $user->name : 'Super Admin';

        $result = DB::transaction(function () use ($request, $auditorId, $auditorName) {
            $asset = Asset::lockForUpdate()->findOrFail($request->asset_id);
            $previousStatus = $asset->auditStatusLabel();
            $rawStatus = $request->audit_status;
            $normalized = Asset::normalizeAuditStatus($rawStatus) ?? 'audited';

            // 1. Maintain complete audit history trail
            $history = AuditHistory::create([
                'asset_id' => $asset->id,
                'auditor_id' => $auditorId,
                'auditor_name' => $auditorName,
                'audit_status' => $normalized === 'audited' ? 'Audited' : 'Not Found',
                'location_status' => $request->location_status ?: ($normalized === 'audited' ? 'Location Matches' : 'Location Does Not Match'),
                'remarks' => $request->remarks ?: ($normalized === 'audited' ? 'Physical verification completed.' : 'Asset marked as Not Found.'),
                'audited_at' => now(),
            ]);

            // 2. Synchronize main asset record (updates audited_by, audited_at, audit_status)
            $asset->audited_by = $auditorId;
            $asset->audited_at = now();
            $asset->audit_status = $normalized;
            $asset->save();

            return [
                'asset' => $asset,
                'history' => $history,
                'previous_status' => $previousStatus,
                'new_status' => $normalized === 'audited' ? 'Audited' : 'Not Found',
            ];
        });

        $nowFormatted = now()->format('d M Y, h:i A');

        return response()->json([
            'status' => true,
            'message' => 'Asset audit recorded successfully and synchronized with web application.',
            'data' => [
                'asset_id' => $result['asset']->id,
                'asset_code' => $result['asset']->asset_code,
                'asset_name' => $result['asset']->asset_name,
                'previous_audit_status' => $result['previous_status'],
                'previous_status' => $result['previous_status'],
                'new_audit_status' => $result['new_status'],
                'new_status' => $result['new_status'],
                'auditor' => $auditorName,
                'date_time' => $nowFormatted,
                'audited_at' => $nowFormatted,
                'remarks' => $request->remarks ?: '-',
                'location_status' => $request->location_status ?: ($result['new_status'] === 'Audited' ? 'Matches' : 'Different'),
            ],
        ], 200);
    }

    /**
     * Audit History log for Screen 10.
     */
    public function getHistory(string|int $id): JsonResponse
    {
        $asset = Asset::with(['auditHistories' => function ($q) {
            $q->orderBy('audited_at', 'desc')->orderBy('id', 'desc');
        }])->where('id', $id)->orWhere('asset_code', $id)->first();

        if (!$asset) {
            return response()->json([
                'status' => false,
                'message' => 'Asset not found',
            ], 404);
        }

        $formattedHistory = $asset->auditHistories->map(function ($h) {
            return $h->toMobileArray();
        })->values();

        return response()->json([
            'status' => true,
            'message' => 'Audit history retrieved successfully',
            'data' => [
                'asset_id' => $asset->id,
                'asset_code' => $asset->asset_code,
                'asset_name' => $asset->asset_name,
                'current_audit_status' => $asset->auditStatusLabel(),
                'total_audits' => $formattedHistory->count(),
                'history' => $formattedHistory,
            ],
        ], 200);
    }

    /**
     * Extract clean code from scanned QR string.
     */
    private function extractAssetCode(string $raw): string
    {
        $trimmed = trim($raw);

        // JSON QR format: {"asset_code":"MSU000010"}
        if (str_starts_with($trimmed, '{') && str_ends_with($trimmed, '}')) {
            $decoded = json_decode($trimmed, true);
            if (is_array($decoded)) {
                if (!empty($decoded['asset_code'])) return trim($decoded['asset_code']);
                if (!empty($decoded['code'])) return trim($decoded['code']);
                if (!empty($decoded['id'])) return (string) $decoded['id'];
            }
        }

        // URL format: http://.../assets/MSU000010 or /asset/10
        if (preg_match('/\/asset(?:s)?\/([a-zA-Z0-9_\-]+)\/?$/i', $trimmed, $matches)) {
            return $matches[1];
        }

        return $trimmed;
    }
}
