<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;

class GlobalFilterController extends Controller
{
    public function setCompany(Request $request)
    {
        $companyId = $request->input('global_company_id');

        if ($companyId) {
            $allowed = Company::query()
                ->visibleToUser($request->user())
                ->whereKey($companyId)
                ->exists();

            if (! $allowed) {
                session()->forget('global_company_id');

                return back()->with('error', 'You do not have access to that company.');
            }

            session(['global_company_id' => $companyId]);
        } else {
            session()->forget('global_company_id');
        }

        return back();
    }
}
