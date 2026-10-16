<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
class AssetAssignment extends Model
{
    protected $fillable = [
        'asset_id',
        'from_company_id',
        'from_branch',
        'from_location',
        'to_company_id',
        'to_branch',
        'to_location',
        'assigned_to',
        'transfer_status',
        'assigned_by',
        'assigned_at',
        'assigned_notes',
        'accepted_by',
        'accepted_at',
        'accepted_notes',
        'assiged_emoloyee',
        'department_id',
        'rejected_at',
        'rejected_by',
        'rejected_notes',
        'returned_by',
        'returned_at',
        'return_notes',
        'return_accepted_by', 
        'return_accepted_at',
        'return_accepted_notes',
        'notes',
        'assign_date',
        'status'
    ];


    public const IN_TRANSIT='in_transit';
    public const ACCEPTED='accepted';
    public const REJECTED='rejected';
    public const RETURN='return';
    public const RETURN_ACCEPTED='return_accepted';

    protected static function booted()
    {
        /**
         * New rows only fire `created` (not `updating`). Keep assets.status in sync
         * with the first in-transit assignment the same way as when transfer_status changes.
         */
        static::created(function (AssetAssignment $assignment) {
            if (! in_array($assignment->transfer_status, [AssetAssignment::IN_TRANSIT, AssetAssignment::ACCEPTED], true)) {
                return;
            }

            $assignment->syncAssetLocationFromTransfer();
        });

        static::updating(function (AssetAssignment $assignment) {
            if ($assignment->isDirty('transfer_status')) {
                $assignment->syncAssetLocationFromTransfer();
            }
        });

        // Accept form sets to_location in the same save as transfer_status=accepted.
        // If location is filled after status was already accepted, still push it to the asset.
        static::updated(function (AssetAssignment $assignment) {
            if (
                $assignment->transfer_status === AssetAssignment::ACCEPTED
                && $assignment->wasChanged('to_location')
                && $assignment->to_location
            ) {
                $assignment->syncAssetLocationFromTransfer();
            }
        });

        static::deleting(function ($assignment) {
            if ($assignment->transfer_status === 'accepted') {
                $asset = $assignment->asset;

                if ($asset) {
                    // Find the previous accepted assignment for this asset
                    $previousAssignment = AssetAssignment::where('asset_id', $assignment->asset_id)
                        ->where('id', '<', $assignment->id)
                        ->where('transfer_status', 'accepted')
                        ->orderBy('id', 'desc')
                        ->first();

                    if ($previousAssignment) {
                        // Revert to previous assignment's location
                        $asset->update([
                            'company_id'  => $previousAssignment->to_company_id,
                            'branch_id'   => $previousAssignment->to_branch,
                            'location_id' => $previousAssignment->to_location,
                            'status'      => 'assigned',
                        ]);
                    } else {
                        // No previous assignment, revert to from_branch
                        $asset->update([
                            'company_id'  => $assignment->from_company_id,
                            'branch_id'   => $assignment->from_branch,
                            'location_id' => $assignment->from_location,
                            'status'      => 'available',
                        ]);
                    }
                }
            }
        });

        
    }

    protected $casts = [
        'created_at'=>'datetime',
        'assign_date'=>'date',
        'assigned_at'=>'datetime',
        'accepted_at'=>'datetime',
        'rejected_at'=>'datetime',
        'returned_at' => 'datetime',
        'return_accepted_at'=>'datetime',
    ];

    protected $appends = [
        'status_label',
        'transfer_status_label',
        'can_accept_assignment',
        'can_reject_assignment',
        'can_return_assignment', 
        'can_return_accept_assignment',
    ];

    public function getCanAcceptAssignmentAttribute()
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        if (Gate::allows('updateStatus', [$this, 'accept'])) {
            return true;
        }


        return false;
        
    }

    public function getCanRejectAssignmentAttribute()
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        if (Gate::allows('updateStatus', [$this, 'reject'])) {
            return true;
        }

        return false;
        
    }

    public function getCanReturnAssignmentAttribute()
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        if (Gate::allows('updateStatus', [$this, 'return'])) {
            return true;
        }

        return false;
        
    }

    public function getCanReturnAcceptAssignmentAttribute()
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        if (Gate::allows('updateStatus', [$this, 'accept_return'])) {
            return true;

        }

        return false;
        
    }

    

    public function getStatusLabelAttribute()
    {
       $status = strtolower(trim($this->status));

        switch ($status) {
            case 'assigned':
                return '<span class="badge bg-label-info me-1">Assigned</span>';
                break;
            case 'returned':
                return '<span class="badge bg-label-danger me-1">Returned</span>';
                break;
            case 'transferred':
                return '<span class="badge bg-label-warning me-1">Transferred</span>';
                break;
            default:
                return '<span class="badge bg-label-info me-1">Assigned</span>';
                break;
        }
    }

    public function getTransferStatusLabelAttribute()
    {
        $status = strtolower(trim($this->transfer_status));

        switch ($status) {
            case 'in_transit':
                return '<span class="badge bg-label-info me-1">In Transit</span>';
                break;
            case 'accepted':
                return '<span class="badge bg-label-success me-1">Accepted</span>';
                break;
            case 'rejected':
                return '<span class="badge bg-label-danger me-1">Rejected</span>';
                break;
            case 'return':
                return '<span class="badge bg-label-danger me-1">Returned</span>';
                break;
            case 'return_accepted':
                return '<span class="badge bg-label-success me-1">Return Accepted</span>';
                break;
            default:
                return '<span class="badge bg-label-info me-1">In Transit</span>';
                break;
        }
    }   


    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function assignedBy(){

         return $this->belongsTo(User::class,'assigned_by');
    }

    public function acceptedBy()
    {
        return $this->belongsTo(User::class, 'accepted_by');
    }

    public function rejectedBy()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function returnedBy()
    {
        return $this->belongsTo(User::class, 'returned_by');
    }

    public function returnAcceptedBy()
    {
        return $this->belongsTo(User::class, 'return_accepted_by');
    }

    public function employee(){

        return $this->belongsTo(AssetCustodian::class,'assiged_emoloyee');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function fromCompany()
    {
        return $this->belongsTo(Company::class, 'from_company_id');
    }

    public function fromBranch()
    {
        return $this->belongsTo(Branch::class, 'from_branch');
    }

    public function toCompany()
    {
        return $this->belongsTo(Company::class, 'to_company_id');
    }

    public function toBranch()
    {
        return $this->belongsTo(Branch::class, 'to_branch');
    }

    public function fromBranchLocation()
    {
        return $this->belongsTo(BranchLocation::class, 'from_location');
    }

    public function toBranchLocation()
    {
        return $this->belongsTo(BranchLocation::class, 'to_location');
    }

    public function scopeVisibleToUser($query, $user)
    {
        // Privileged roles see every assignment
        if ($user->hasAnyRole(['superadmin', 'Super Admin', 'Admin', 'admin'])) {
            return $query;
        }

        $branchIds = $user->branches()->pluck('branches.id');

        if ($branchIds->isEmpty()) {
            return $query->whereRaw('1 = 0');
        }

        $table = $query->getModel()->getTable();

        return $query->where(function ($q) use ($branchIds, $table) {
            // Destination branch (current holder).
            $q->whereIn("{$table}.to_branch", $branchIds)
                // Source branch only while transfer is still open.
                ->orWhere(function ($fromScope) use ($branchIds, $table) {
                    $fromScope
                        ->whereIn("{$table}.from_branch", $branchIds)
                        ->whereIn("{$table}.transfer_status", [
                            self::IN_TRANSIT,
                            self::RETURN,
                        ]);
                });
        });
    }


    /**
     * Keep the parent asset's company / branch / location in sync with transfer status.
     * GRN-created assets often have null from_* — fall back to destination so UI can render.
     */
    public function syncAssetLocationFromTransfer(): void
    {
        $asset = $this->asset;
        if (! $asset) {
            return;
        }

        $payload = match ($this->transfer_status) {
            // Show destination immediately after assign so branch/location render in Assets.
            self::IN_TRANSIT => [
                'company_id' => $this->to_company_id ?: $this->from_company_id,
                'branch_id' => $this->to_branch ?: $this->from_branch,
                'location_id' => $this->to_location ?: $this->from_location,
                'custodian_id' => $this->assiged_emoloyee ?: null,
                'department_id' => $this->department_id ?: null,
                'status' => 'transferred',
            ],
            self::ACCEPTED => [
                'company_id' => $this->to_company_id ?: $this->from_company_id,
                'branch_id' => $this->to_branch ?: $this->from_branch,
                'location_id' => $this->to_location ?: $this->from_location,
                'custodian_id' => $this->assiged_emoloyee ?: null,
                'department_id' => $this->department_id ?: null,
                'status' => 'assigned',
            ],
            self::REJECTED => [
                'company_id' => $this->from_company_id,
                'branch_id' => $this->from_branch,
                'location_id' => $this->from_location,
                'status' => 'available',
            ],
            self::RETURN => [
                'company_id' => $this->to_company_id ?: $this->from_company_id,
                'branch_id' => $this->to_branch ?: $this->from_branch,
                'location_id' => $this->to_location ?: $this->from_location,
                'status' => 'transferred',
            ],
            self::RETURN_ACCEPTED => [
                'company_id' => $this->from_company_id,
                'branch_id' => $this->from_branch,
                'location_id' => $this->from_location,
                'status' => 'available',
            ],
            default => null,
        };

        if ($payload === null) {
            return;
        }

        if (! array_key_exists('department_id', $this->getAttributes())) {
            unset($payload['department_id']);
        }

        // Drop stale FKs so deleted company/branch/location rows cannot break the update.
        if (! empty($payload['company_id']) && ! Company::query()->whereKey($payload['company_id'])->exists()) {
            $payload['company_id'] = null;
        }
        if (! empty($payload['branch_id']) && ! Branch::query()->whereKey($payload['branch_id'])->exists()) {
            $payload['branch_id'] = null;
            $payload['location_id'] = null;
        }
        if (! empty($payload['location_id']) && ! BranchLocation::query()->whereKey($payload['location_id'])->exists()) {
            $payload['location_id'] = null;
        }
        if (! empty($payload['location_id']) && ! empty($payload['branch_id'])) {
            $belongs = BranchLocation::query()
                ->whereKey($payload['location_id'])
                ->where('branch_id', $payload['branch_id'])
                ->exists();
            if (! $belongs) {
                $payload['location_id'] = null;
            }
        }

        $asset->update($payload);
    }

    public function isToBranch(): bool
    {
        return $this->assigned_to === 'branch' && !is_null($this->to_branch);
    }

    public function isToEmployee(): bool
    {
        return $this->assigned_to === 'employee' && !is_null($this->assiged_emoloyee);
    }


    public function canAnyStatus(): bool
    {
        return Gate::any([
            ['updateStatus', [$this, 'accept']],
            ['updateStatus', [$this, 'reject']],
            ['updateStatus', [$this, 'return']],
            ['updateStatus', [$this, 'accept_return']],
        ]);
    }


    /**
     * Assigned and transferred rows stay locked except for the assigned date.
     * In-transit rows keep the full edit via canModify().
     */
    public function canEditAssignDate(?User $user = null): bool
    {
        $status = strtolower(trim((string) $this->status));

        if (! in_array($status, ['assigned', 'transferred'], true)) {
            return false;
        }

        if ($this->canModify($user)) {
            return false;
        }

        $user = $user ?? auth()->user();

        return (bool) ($user && $user->can('asset.assignment:edit'));
    }

    public function canModify(User $user = null): bool
    {

        if ($this->transfer_status != 'in_transit') {
            
            return false;
        }

        $user = $user ?? auth()->user();

        if (!$user) {
            return false;
        }

        // 1️⃣ Super Admin / Admin
        if ($user->hasAnyRole(['superadmin', 'Super Admin', 'Admin', 'admin'])) {
            return true;
        }

        // 2️⃣ Created user
        if ($user->hasBranch($this->from_branch)) {
            return true;
        }

        $wareHouseCompanyId=setting('warehouse_company_id',1);

        $wareHouseId=setting('warehouse_id',1);

        $hasCompany = $user->companies()
        ->where('companies.id', $wareHouseCompanyId)
        ->exists();

        $hasBranch = $user->branches()
            ->where('branches.id', $wareHouseId)
            ->exists();

        if ($hasCompany && $hasBranch) {
            return true;
        }

        return false;

    }

   


}
