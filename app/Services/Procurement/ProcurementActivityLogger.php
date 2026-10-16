<?php

namespace App\Services\Procurement;

use App\Models\ProcurementActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ProcurementActivityLogger
{
    public function log(
        Model $subject,
        string $action,
        null|string|\BackedEnum $fromStatus = null,
        null|string|\BackedEnum $toStatus = null,
        ?string $comments = null,
        array $properties = [],
        ?int $userId = null,
    ): ProcurementActivityLog {
        return ProcurementActivityLog::create([
            'subject_type' => $subject->getMorphClass(),
            'subject_id' => $subject->getKey(),
            'action' => $action,
            'from_status' => $fromStatus instanceof \BackedEnum ? (string) $fromStatus->value : $fromStatus,
            'to_status' => $toStatus instanceof \BackedEnum ? (string) $toStatus->value : $toStatus,
            'user_id' => $userId ?? Auth::id(),
            'comments' => $comments,
            'properties' => $properties ?: null,
            'ip_address' => Request::ip(),
        ]);
    }
}
