<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use App\Http\Requests\Companies\CompanyRequest;
use App\Helpers\Files;
use App\Services\CompanyLogoService;
use App\Services\ReportPdfExportService;
use App\Support\MasterListing;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;

class CompanyController extends Controller
{
    public function index(Request $request)
    {
        $perPage = MasterListing::perPage($request);
        $companies = $this->filteredCompanies($request)->latest()->paginate($perPage)->withQueryString();

        return view('companies.index', compact('companies', 'perPage'));
    }

    public function export(Request $request)
    {
        $rows = $this->filteredCompanies($request)->latest()->get();

        $export = new class($rows) implements FromCollection, WithHeadings
        {
            public function __construct(private readonly Collection $rows) {}

            public function collection()
            {
                return $this->rows->map(fn (Company $company) => [
                    $company->name,
                    $company->code,
                    $company->address_line_1,
                    $company->address_line_2,
                    $company->city,
                    $company->state,
                    $company->country,
                    $company->pincode,
                    $company->contact_person,
                    $company->contact_number,
                    $company->email,
                    ucfirst($company->status ?? ''),
                ]);
            }

            public function headings(): array
            {
                return [
                    'Name',
                    'Code',
                    'Address Line 1',
                    'Address Line 2',
                    'City',
                    'State',
                    'Country',
                    'Pincode',
                    'Contact Person',
                    'Contact Number',
                    'Email',
                    'Status',
                ];
            }
        };

        return Excel::download($export, 'companies-'.now()->format('Ymd_His').'.xlsx');
    }

    public function exportPdf(Request $request, ReportPdfExportService $pdfExport)
    {
        $companies = $this->filteredCompanies($request)->latest()->get();
        $filters = $request->only(['name', 'city', 'status']);

        return $pdfExport->download(
            'companies.export-list',
            compact('companies', 'filters'),
            'companies-'.now()->format('Ymd_His').'.pdf',
            'landscape'
        );
    }

    public function create()
    {
        return view('companies.create');
    }

    public function store(CompanyRequest $request)
    {
        $validated = $request->validated();

        if ($request->hasFile('logo')) {
            $validated['logo'] = Files::uploadLocalOrS3($request->file('logo'), CompanyLogoService::LOGO_PATH);
        }

        $company=Company::create($validated);

        // $user=auth()->user();

       //  $user->companies()->syncWithoutDetaching([$company->id]);

        return redirect()->route('companies.index')
            ->with('success', 'Company created successfully.');
    }

    public function show(Company $company)
    {
        return view('companies.show', compact('company'));
    }

    public function edit(Company $company)
    {
        return view('companies.edit', compact('company'));
    }

    public function update(CompanyRequest $request, Company $company)
    {
        $validated = $request->validated();

        if ($request->hasFile('logo')) {
            if ($company->logo) {
                Files::deleteFile($company->logo, CompanyLogoService::LOGO_PATH);
            }

            $validated['logo'] = Files::uploadLocalOrS3($request->file('logo'), CompanyLogoService::LOGO_PATH);
        }

        $company->update($validated);

        return redirect()->route('companies.index')
            ->with('success', 'Company updated successfully.');
    }

    public function destroy(Company $company)
    {
         if ($company->hasDependencies()) {
            return back()->with('error', 'cannot delete company because it has mapped with other records');
        }

        if ($company->logo) {
            Files::deleteFile($company->logo, CompanyLogoService::LOGO_PATH);
        }

        $company->delete();

        return redirect()->route('companies.index')
            ->with('success', 'Company deleted successfully.');
    }

    private function filteredCompanies(Request $request): Builder
    {
        $query = Company::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('code', 'like', '%'.$search.'%')
                    ->orWhere('city', 'like', '%'.$search.'%')
                    ->orWhere('contact_person', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%');
            });
        }

        if ($request->filled('name')) {
            $query->where('name', 'like', '%'.$request->name.'%');
        }

        if ($request->filled('city')) {
            $query->where('city', 'like', '%'.$request->city.'%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return $query;
    }
}
