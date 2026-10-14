<?php

namespace App\Services;

use App\Helpers\Files;
use App\Models\Company;
use Illuminate\Database\Eloquent\Model;

class CompanyLogoService
{
    public const LOGO_PATH = 'company-logos';

    public function resolveCompany(?array $contextData = null): ?Company
    {
        if (session()->has('global_company_id')) {
            $company = Company::find(session('global_company_id'));
            if ($company) {
                return $company;
            }
        }

        if ($contextData) {
            $company = $this->extractCompanyFromData($contextData);
            if ($company) {
                return $company;
            }
        }

        $user = auth()->user();
        if ($user) {
            $userCompany = $user->companies()->first();
            if ($userCompany) {
                return $userCompany;
            }
        }

        return Company::active()->first();
    }

    public function pdfDataUri(?Company $company = null, ?array $contextData = null): ?string
    {
        $company = $company ?? $this->resolveCompany($contextData);

        if (! $company || ! $company->logo) {
            return null;
        }

        $relativePath = self::LOGO_PATH.'/'.$company->logo;
        $localPath = public_path(Files::UPLOAD_FOLDER.'/'.$relativePath);

        if (! is_file($localPath)) {
            return null;
        }

        $mime = mime_content_type($localPath) ?: 'image/png';

        return 'data:'.$mime.';base64,'.base64_encode((string) file_get_contents($localPath));
    }

    private function extractCompanyFromData(array $data): ?Company
    {
        if (isset($data['company']) && $data['company'] instanceof Company) {
            return $data['company'];
        }

        $models = [
            $data['grn'] ?? null,
            $data['msuGrn'] ?? null,
            $data['msuPurchaseOrder'] ?? null,
            $data['materialRequest'] ?? null,
            $data['materialIssue'] ?? null,
            $data['purchaseOrder'] ?? null,
            $data['indent'] ?? null,
            $data['rfq'] ?? null,
        ];

        foreach ($models as $model) {
            $company = $this->companyFromModel($model);
            if ($company) {
                return $company;
            }
        }

        return null;
    }

    private function companyFromModel(mixed $model): ?Company
    {
        if (! $model instanceof Model) {
            return null;
        }

        if ($model instanceof Company) {
            return $model;
        }

        if ($model->relationLoaded('company') && $model->getRelation('company') instanceof Company) {
            return $model->getRelation('company');
        }

        if ($model->relationLoaded('branch')) {
            $branch = $model->getRelation('branch');

            if ($branch?->relationLoaded('company') && $branch->getRelation('company') instanceof Company) {
                return $branch->getRelation('company');
            }

            if ($branch?->company_id) {
                return Company::find($branch->company_id);
            }
        }

        if ($model->relationLoaded('purchaseOrder') && $model->getRelation('purchaseOrder')) {
            return $this->companyFromModel($model->getRelation('purchaseOrder'));
        }

        if ($model->relationLoaded('materialRequest') && $model->getRelation('materialRequest')) {
            return $this->companyFromModel($model->getRelation('materialRequest'));
        }

        return null;
    }
}
