<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Asset;
use App\Models\Company;
use App\AssetCondition;
use App\AssetStatus;
use App\Models\Branch;
use App\AssetOperationalStatus;
use App\SupportTicketPriority;
use App\SupportTicketStatus;
use App\Models\Brand;
use App\Models\BranchLocation;
use App\Models\Vendor;
use App\Models\AssetCategory;
use App\Models\AssetType;
use App\Models\SupportTicket;
use App\Models\Department;
use App\Models\SupportTicketAssignment;
use App\Models\User;
use App\Services\AssetReportExportService;
use App\Services\ReportPdfExportService;
use App\Services\SupportTicketReportExportService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportsController extends Controller
{
    private const REPORT_PER_PAGE = 50;

    public function companywiseassets(Request $request){

        $companies=Company::visibleToUser(auth()->user())->active()->select('id','name')->get();

         $statuses=AssetStatus::cases();

        $conditions=AssetCondition::cases();

        $operations_status=AssetOperationalStatus::cases();

        $assetqry=Asset::query();

        $assetqry->VisibleToUser(auth()->user());

        $this->eagerLoadAssetReportRelations($assetqry);

        if($request->company_id){

            $assetqry->where('company_id', $request->company_id);

        }

        if($request->status){

            $assetqry->where('status', $request->status);

        }

        if($request->condition){

            $assetqry->where('condition', $request->condition);

        }

        if($request->operational_status){

            $assetqry->where('operational_status', $request->operational_status);

        }

        if($request->from_date){
            $assetqry->whereDate('purchase_date', '>=', $request->from_date);
        }

        if($request->to_date){
            $assetqry->whereDate('purchase_date', '<=', $request->to_date);
        }

        $tableColumns=array('serial_no','asset_code','status','sub_category_id','company_id','branch_id','condition','operational_status');

        $assets = $this->assetReportResults($assetqry, $request);

        if ($export = $this->maybeExportAssetReport($request, $assets, $tableColumns, 'Companywise Assets')) {
            return $export;
        }

        return view('reports.companywiseasset',compact('companies','assets','statuses','conditions','operations_status','tableColumns'));


    }


    public function branchwiseassets(Request $request){

        $companies=Company::visibleToUser(auth()->user())->active()->select('id','name')->get();

        $branches=collect();

        $locations=collect();

        $vendors=Vendor::active()->orderBy('name')->get();

        $statuses=AssetStatus::cases();

        $conditions=AssetCondition::cases();

        $operations_status=AssetOperationalStatus::cases();

        $assetqry=Asset::query();

        $assetqry->VisibleToUser(auth()->user());

        $this->eagerLoadAssetReportRelations($assetqry);

        if($request->company_id){

            $assetqry->where('company_id', $request->company_id);

            $branches=Branch::visibleToUser(auth()->user())->where('status','active')->where('company_id', $request->company_id)->select('id','name')->get();

        }

        if ($request->branch_id) {
            $assetqry->filteredByBranch((int) $request->branch_id);

            $locations = BranchLocation::where('status', 'active')->where('branch_id', $request->branch_id)->select('id', 'location_name')->orderBy('location_name')->get();
        }

        if($request->vendor_id){

            $assetqry->where('supplier', $request->vendor_id);

        }

        if($request->location_id){

            $assetqry->where('location_id', $request->location_id);

        }

        if($request->status){

            $assetqry->where('status', $request->status);

        }

        if($request->condition){    

            $assetqry->where('condition', $request->condition);

        }

        if($request->operational_status){    

            $assetqry->where('operational_status', $request->operational_status);    

        }

        if($request->from_date){
            $assetqry->whereDate('purchase_date', '>=', $request->from_date);
        }

        if($request->to_date){
            $assetqry->whereDate('purchase_date', '<=', $request->to_date);
        }

        $tableColumns=array('serial_no','asset_code','status','sub_category_id','company_id','branch_id','location_id','vendor_id','condition','operational_status');

        $assets = $this->assetReportResults($assetqry, $request);

        if ($export = $this->maybeExportAssetReport($request, $assets, $tableColumns, 'Branchwise Assets')) {
            return $export;
        }

        return view('reports.branchwiseasset',compact('companies','branches','locations','vendors','assets','statuses','conditions','operations_status','tableColumns'));

    }


    public function brandwiseassets(Request $request){

        $companies = Company::visibleToUser(auth()->user())->active()->select('id', 'name')->get();

        $branches = collect();

        $brands = Brand::active()->orderBy('brand_name')->get(['id', 'brand_name']);

        $statuses=AssetStatus::cases();

        $conditions=AssetCondition::cases();

        $operations_status=AssetOperationalStatus::cases();

        $assetqry=Asset::query(); 

        $assetqry->VisibleToUser(auth()->user());

        $this->eagerLoadAssetReportRelations($assetqry);

        if ($request->company_id) {
            $assetqry->where('company_id', $request->company_id);
            $branches = Branch::visibleToUser(auth()->user())->where('status', 'active')->where('company_id', $request->company_id)->select('id', 'name')->orderBy('name')->get();
        }

        if ($request->branch_id) {
            $assetqry->filteredByBranch((int) $request->branch_id);
        }

        $needle = trim((string) $request->input('brand_search', ''));
        if ($needle !== '') {
            $assetqry->whereHas('brand', static function ($q) use ($needle) {
                $q->where('brand_name', 'like', '%'.addcslashes($needle, '%_\\').'%');
            });
        }

        if($request->status){

            $assetqry->where('status', $request->status);

        }

        if($request->condition){    

            $assetqry->where('condition', $request->condition);

        }

        if($request->operational_status){    

            $assetqry->where('operational_status', $request->operational_status);    

        }

        if($request->from_date){
            $assetqry->whereDate('purchase_date', '>=', $request->from_date);
        }

        if($request->to_date){
            $assetqry->whereDate('purchase_date', '<=', $request->to_date);
        }

        $tableColumns=array('serial_no','asset_code','status','sub_category_id','company_id','branch_id','brand_id','condition','operational_status');

        $assets = $this->assetReportResults($assetqry, $request);

        if ($export = $this->maybeExportAssetReport($request, $assets, $tableColumns, 'Brandwise Assets')) {
            return $export;
        }

        return view('reports.brandwiseassets', compact('assets', 'brands', 'companies', 'branches', 'statuses', 'conditions', 'operations_status', 'tableColumns'));


    }

    public function vendorwiseassets(Request $request){

        $companies=Company::visibleToUser(auth()->user())->active()->select('id','name')->get();

        $branches=collect();

        $vendors=Vendor::active()->get();

        $statuses=AssetStatus::cases();

        $conditions=AssetCondition::cases();

        $operations_status=AssetOperationalStatus::cases();

        $assetqry=Asset::query(); 

        $assetqry->VisibleToUser(auth()->user());

        $this->eagerLoadAssetReportRelations($assetqry);

        if ($request->company_id) {

            $assetqry->where('company_id', $request->company_id);

            $branches=Branch::visibleToUser(auth()->user())->where('status','active')->where('company_id', $request->company_id)->select('id','name')->orderBy('name')->get();

        }

        if ($request->branch_id) {
            $assetqry->filteredByBranch((int) $request->branch_id);
        }

        if($request->vendor_id){

            $assetqry->where('supplier', $request->vendor_id);

        } 

        if($request->status){

            $assetqry->where('status', $request->status);

        }    

        if($request->condition){    

            $assetqry->where('condition', $request->condition);

        }    

        if($request->operational_status){    

            $assetqry->where('operational_status', $request->operational_status);    

        }

        if($request->from_date){
            $assetqry->whereDate('purchase_date', '>=', $request->from_date);
        }

        if($request->to_date){
            $assetqry->whereDate('purchase_date', '<=', $request->to_date);
        }

        $tableColumns=array('serial_no','asset_code','status','sub_category_id','company_id','branch_id','vendor_id','condition','operational_status');

        $assets = $this->assetReportResults($assetqry, $request);

        if ($export = $this->maybeExportAssetReport($request, $assets, $tableColumns, 'Vendorwise Assets')) {
            return $export;
        }

        return view('reports.vendorwiseassets',compact('companies','branches','assets','vendors','statuses','conditions','operations_status','tableColumns'));


    }


    public function locationwiseassets(Request $request){

        $companies=Company::visibleToUser(auth()->user())->active()->select('id','name')->get();

        $branches=collect();

        $locations=collect();

        $statuses=AssetStatus::cases();

        $conditions=AssetCondition::cases();

        $operations_status=AssetOperationalStatus::cases();

        $assetqry=Asset::query();

        $assetqry->VisibleToUser(auth()->user());

        $this->eagerLoadAssetReportRelations($assetqry);

        if($request->company_id){

            $assetqry->where('company_id', $request->company_id);

            $branches=Branch::visibleToUser(auth()->user())->where('status','active')->where('company_id', $request->company_id)->select('id','name')->get();

        }

        if ($request->branch_id) {
            $assetqry->filteredByBranch((int) $request->branch_id);

            $locations = BranchLocation::where('status', 'active')->where('branch_id', $request->branch_id)->select('id', 'location_name')->get();
        }

        if($request->location_id){

            $assetqry->where('location_id', $request->location_id);

        }

        if($request->status){

            $assetqry->where('status', $request->status);

        }   

        if($request->condition){    

            $assetqry->where('condition', $request->condition);

        }

        if($request->operational_status){

            $assetqry->where('operational_status', $request->operational_status);

        }

        if($request->from_date){
            $assetqry->whereDate('purchase_date', '>=', $request->from_date);
        }

        if($request->to_date){
            $assetqry->whereDate('purchase_date', '<=', $request->to_date);
        }

        $tableColumns=array('serial_no','asset_code','status','sub_category_id','company_id','branch_id','location_id','condition','operational_status');

        $assets = $this->assetReportResults($assetqry, $request);

        if ($export = $this->maybeExportAssetReport($request, $assets, $tableColumns, 'Locationwise Assets')) {
            return $export;
        }

        return view('reports.locationwiseassets',compact('assets','companies','branches','locations','statuses','conditions','operations_status','tableColumns'));


    }

    public function categorywiseassets(Request $request){

        $categories=AssetCategory::active()->WhereNULL('parent_id')->get();

        $subcategories=collect();

        $statuses=AssetStatus::cases();

        $conditions=AssetCondition::cases();

        $operations_status=AssetOperationalStatus::cases();

        $assetqry=Asset::query();

         $assetqry->VisibleToUser(auth()->user());

        $this->eagerLoadAssetReportRelations($assetqry);

        if($request->category_id){

            $assetqry->where('category_id', $request->category_id);

            $subcategories=AssetCategory::where('status','active')->where('parent_id', $request->category_id)->select('id','category_name')->get();

        }

        if($request->sub_category_id){

            $assetqry->where('sub_category_id', $request->sub_category_id);

        }

        if($request->status){

            $assetqry->where('status', $request->status);

        }

        if($request->condition){    

            $assetqry->where('condition', $request->condition);

        }

        if($request->operational_status){

            $assetqry->where('operational_status', $request->operational_status);

        }

        if($request->from_date){
            $assetqry->whereDate('purchase_date', '>=', $request->from_date);
        }

        if($request->to_date){
            $assetqry->whereDate('purchase_date', '<=', $request->to_date);
        }

        $tableColumns=array('serial_no','asset_code','status','sub_category_id','condition','operational_status');

        $assets = $this->assetReportResults($assetqry, $request);

        if ($export = $this->maybeExportAssetReport($request, $assets, $tableColumns, 'Categorywise Assets')) {
            return $export;
        }

        return view('reports.categorywiseassets',compact('subcategories','assets','categories','statuses','conditions','operations_status','tableColumns'));


    }


    public function typewiseassets(Request $request){

        $categories=AssetCategory::active()->WhereNULL('parent_id')->get();

        $subcategories=collect();

        $assetTypes=collect();

        $statuses=AssetStatus::cases();

        $conditions=AssetCondition::cases();

        $operations_status=AssetOperationalStatus::cases();

        $assetqry=Asset::query();

         $assetqry->VisibleToUser(auth()->user());

        $this->eagerLoadAssetReportRelations($assetqry);

        if($request->category_id){

            $assetqry->where('category_id', $request->category_id);

            $subcategories=AssetCategory::where('status','active')->where('parent_id', $request->category_id)->select('id','category_name')->get();

        }

        if($request->sub_category_id){

            $assetqry->where('sub_category_id', $request->sub_category_id);

            $assetTypes=AssetType::where('status','active')->where('sub_category_id', $request->sub_category_id)->select('id','asset_type_name')->get();

        }

        if($request->asset_type_id){

            $assetqry->where('asset_type_id', $request->asset_type_id);
        }

        if($request->status){

            $assetqry->where('status', $request->status);

        }

        if($request->condition){

            $assetqry->where('condition', $request->condition);
        }

        if($request->operational_status){

            $assetqry->where('operational_status', $request->operational_status);
        }

        if($request->from_date){
            $assetqry->whereDate('purchase_date', '>=', $request->from_date);
        }

        if($request->to_date){
            $assetqry->whereDate('purchase_date', '<=', $request->to_date);
        }

        $tableColumns=array('serial_no','asset_code','status','sub_category_id','asset_type_id','condition','operational_status');

        $assets = $this->assetReportResults($assetqry, $request);

        if ($export = $this->maybeExportAssetReport($request, $assets, $tableColumns, 'Typewise Assets')) {
            return $export;
        }

        return view('reports.typewiseassets',compact('subcategories','assetTypes','assets','categories','statuses','conditions','operations_status','tableColumns'));

        
    }


    public function assetwisesupportticket(Request $request){

        $priorities=SupportTicketPriority::cases();

        $statuses=SupportTicketStatus::cases();

        $companies=Company::visibleToUser(auth()->user())->active()->select('id','name')->get(); 

        $branches=collect();

        $assets=collect();

        $assetqry=SupportTicket::query();

        $assetqry->VisibleToUser(auth()->user());

        if($request->company_id){

            $assetqry->where('company_id', $request->company_id);

            $branches=Branch::visibleToUser(auth()->user())->where('status','active')->where('company_id', $request->company_id)->select('id','name')->orderBy('name')->get();

        }   

        if($request->branch_id){

            $assetqry->where('branch_id', $request->branch_id);

            $assets=Asset::where('status','active')->where('branch_id', $request->branch_id)->select('id','serial_no','asset_name','asset_code')->orderBy('asset_name')->get();

        }

        $filterUsers = $this->usersForSupportTicketFilter($request);

        if($request->asset_id){

            $assetqry->where('asset_id', $request->asset_id);

        }

        if ($request->filled('user_id')) {

            $assetqry->where('created_by', $request->user_id);

        }


        if($request->priority){

            $assetqry->where('priority', $request->priority);

        }


        if($request->status){

            $assetqry->where('status', $request->status);

        }

        if($request->from_date){
            $assetqry->whereDate('due_date', '>=', $request->from_date);
        }

        if($request->to_date){
            $assetqry->whereDate('due_date', '<=', $request->to_date);
        }

        $this->applyCollaboratorFilter($assetqry, $request);

        $tableColumns=array('subject','priority','status','branch_id','company_id','created_by','due_date','asset_id');

        $tickets = $this->supportTicketReportResults($assetqry, $request);
        $collaboratorUsers = $filterUsers;

        if ($export = $this->maybeExportSupportTicketReport($request, $tickets, $tableColumns, 'Asset Wise Support Tickets')) {
            return $export;
        }

        return view('reports.supporttickets.assetwisereport',compact('companies','branches','assets','filterUsers','collaboratorUsers','priorities','statuses','tickets','tableColumns'));


    }


    public function branchwisesupportticket(Request $request){

           $priorities=SupportTicketPriority::cases();

           $statuses=SupportTicketStatus::cases();

            $companies=Company::visibleToUser(auth()->user())->active()->select('id','name')->get();

        $branches=collect();

        $assetqry=SupportTicket::query();

        $assetqry->VisibleToUser(auth()->user());

        if($request->company_id){

            $assetqry->where('company_id', $request->company_id);

            $branches=Branch::visibleToUser(auth()->user())->where('status','active')->where('company_id', $request->company_id)->select('id','name')->get();

        }   

        if($request->branch_id){

            $assetqry->where('branch_id', $request->branch_id);

        }


        if($request->priority){

            $assetqry->where('priority', $request->priority);

        }


        if($request->status){

            $assetqry->where('status', $request->status);

        }

        if($request->from_date){
            $assetqry->whereDate('due_date', '>=', $request->from_date);
        }

        if($request->to_date){
            $assetqry->whereDate('due_date', '<=', $request->to_date);
        }

        $this->applyCollaboratorFilter($assetqry, $request);

        $tableColumns=array('subject','priority','status','branch_id','company_id','created_by','due_date','department_id');

        $tickets = $this->supportTicketReportResults($assetqry, $request);
        $collaboratorUsers = $this->usersForSupportTicketFilter($request);

        if ($export = $this->maybeExportSupportTicketReport($request, $tickets, $tableColumns, 'Branch Wise Support Tickets')) {
            return $export;
        }

        return view('reports.supporttickets.branchwisereport',compact('branches','tickets','companies','collaboratorUsers','priorities','statuses','tableColumns'));


    }


    public function departmentwisesupportticket(Request $request){

         $priorities=SupportTicketPriority::cases();

         $statuses=SupportTicketStatus::cases();

         $companies=Company::visibleToUser(auth()->user())->active()->select('id','name')->get();

         $departments=Department::active()->select('id','department_name')->get();

        $branches=collect();

        $assetqry=SupportTicket::query();

        $assetqry->VisibleToUser(auth()->user());

        if($request->company_id){

            $assetqry->where('company_id', $request->company_id);

            $branches=Branch::visibleToUser(auth()->user())->where('status','active')->where('company_id', $request->company_id)->select('id','name')->get();

        }   

        if($request->branch_id){

            $assetqry->where('branch_id', $request->branch_id);

        }

        if($request->department_id){

            $assetqry->where('department_id', $request->department_id);

        }


        if($request->priority){

            $assetqry->where('priority', $request->priority);

        }


        if($request->status){

            $assetqry->where('status', $request->status);

        }

        if($request->from_date){
            $assetqry->whereDate('due_date', '>=', $request->from_date);
        }

        if($request->to_date){
            $assetqry->whereDate('due_date', '<=', $request->to_date);
        }

        $this->applyCollaboratorFilter($assetqry, $request);

        $tableColumns=array('subject','priority','status','branch_id','company_id','created_by','due_date','department_id');

        $tickets = $this->supportTicketReportResults($assetqry, $request);
        $collaboratorUsers = $this->usersForSupportTicketFilter($request);

        if ($export = $this->maybeExportSupportTicketReport($request, $tickets, $tableColumns, 'Department Wise Support Tickets')) {
            return $export;
        }

        return view('reports.supporttickets.departmentwisesupportticket',compact('branches','tickets','companies','departments','collaboratorUsers','priorities','statuses','tableColumns'));

    }


    public function userwisesupportTicket(Request $request){

      $companies=Company::visibleToUser(auth()->user())->active()->select('id','name')->get();

      $branches=collect();

      $departments=Department::active()->select('id','department_name')->get();

      $filterUsers = $this->usersForSupportTicketFilter($request);
      $collaboratorUsers = $filterUsers;

      if($request->company_id){

        $branches=Branch::visibleToUser(auth()->user())->where('status','active')->where('company_id', $request->company_id)->select('id','name')->get();
      }

      $statuses = [
    'accepted',
    'work_in_progress',
    'hold',
    'completed',
    'closed'
    ];








      $users = User::query()->visibleToUser(auth()->user())
->with([
        'designation:id,designation_name',
        'departments:id,department_name',
        'companies:id,name',
        'branches:id,name'
    ])
    ->when($request->company_id, fn($q) =>
        $q->whereHas('companies', fn($c) =>
            $c->where('companies.id', $request->company_id)
        )
    )

    ->when($request->branch_id, fn($q) =>
        $q->whereHas('branches', fn($b) =>
            $b->where('branches.id', $request->branch_id)
        )
    )

    ->when($request->department_id, fn($q) =>
        $q->whereHas('departments', fn($d) =>
            $d->where('departments.id', $request->department_id)
        )
    )

     ->when($request->user_id, fn($q) =>
        $q->where('id', $request->user_id)
    )

        ->whereHas('reportedSupportTickets', fn ($t) => $this->applyUserWiseReportTicketScope($t, $request));

      foreach ($statuses as $status) {
          $users->addSelect([
              "{$status}_count" => $this->userWiseTicketCountSubquery($request, $status),
          ]);
      }

      $users->addSelect([
          'total_count' => $this->userWiseTicketCountSubquery($request, null),
      ]);

      $users = $users->orderByDesc('total_count')->get();

      $totalTicketsQuery = SupportTicket::query();
      $this->applyUserWiseReportTicketScope($totalTicketsQuery, $request);
      if ($request->filled('user_id')) {
          $totalTicketsQuery->where('created_by', $request->user_id);
      }
      $totalTickets = $totalTicketsQuery->count();

      if ($export = $this->maybeExportUserWiseSupportTicketReport($request, $users, $totalTickets, 'User Wise Support Tickets')) {
          return $export;
      }

    return view('reports.supporttickets.userwisetickets', compact(
        'companies',
        'branches',
        'departments',
        'users',
        'filterUsers',
        'collaboratorUsers',
        'totalTickets'
    ));


    }

    /** Scope support tickets for user-wise report counts and inclusion. */
    protected function applyUserWiseReportTicketScope($query, Request $request): void
    {
        $query->visibleToUser(auth()->user());

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->from_date) {
            $query->whereDate('due_date', '>=', $request->from_date);
        }

        if ($request->to_date) {
            $query->whereDate('due_date', '<=', $request->to_date);
        }

        $this->applyCollaboratorFilter($query, $request);
    }

    protected function applyCollaboratorFilter($query, Request $request): void
    {
        if ($request->filled('collaborator_id')) {
            $query->whereHas('assignments', function ($assignment) use ($request) {
                $assignment->where('user_id', $request->collaborator_id)->where('is_active', true);
            });
        }
    }

    /** Count tickets reported (created) by each user — matches All Support Tickets user filter. */
    protected function userWiseTicketCountSubquery(Request $request, ?string $status)
    {
        return SupportTicket::query()
            ->selectRaw('count(distinct support_tickets.id)')
            ->whereColumn('support_tickets.created_by', 'users.id')
            ->when($status, fn ($q) => $q->where('support_tickets.status', $status))
            ->tap(fn ($q) => $this->applyUserWiseReportTicketScope($q, $request));
    }

    /** Users list for support ticket report filters (scoped by branch / company when set). */
    protected function usersForSupportTicketFilter(Request $request)
    {
        $query = User::query()
            ->where('employee_status', 'active')
            ->visibleToUser(auth()->user())
            ->orderBy('name')
            ->select('id', 'name');

        if ($request->filled('department_id')) {
            $query->whereHas('departments', function ($q) use ($request) {
                $q->where('departments.id', $request->department_id);
            });
        }

        if ($request->filled('branch_id')) {
            $query->whereHas('branches', function ($q) use ($request) {
                $q->where('branches.id', $request->branch_id);
            });
        } elseif ($request->filled('company_id')) {
            $query->whereHas('branches', function ($q) use ($request) {
                $q->where('company_id', $request->company_id);
            });
        }

        return $query->get();
    }

    /** Eager relations used by asset report grids (avoid N+1 on category / lookups). */
    protected function eagerLoadAssetReportRelations(\Illuminate\Database\Eloquent\Builder $query): void
    {
        $query->with([
            'subCategory',
            'assetType',
            'company',
            'branch',
            'brand',
            'vendor',
            'location',
        ]);
    }

    /** Paginated list (50/page) for screen; full collection when exporting. */
    protected function assetReportResults(Builder $assetqry, Request $request)
    {
        $ordered = $assetqry->orderBy('id', 'DESC');

        if (in_array($request->input('export_format'), ['csv', 'pdf'], true)) {
            return $ordered->get();
        }

        return $ordered->paginate(self::REPORT_PER_PAGE)->withQueryString();
    }

    protected function maybeExportAssetReport(
        Request $request,
        \Illuminate\Support\Collection|\Illuminate\Pagination\LengthAwarePaginator $assets,
        array $tableColumns,
        string $reportTitle
    ): StreamedResponse|Response|null {
        $format = $request->input('export_format');

        if (! in_array($format, ['csv', 'pdf'], true)) {
            return null;
        }

        $rows = $assets instanceof \Illuminate\Support\Collection ? $assets : collect($assets->items());
        $exportService = app(AssetReportExportService::class);
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $reportTitle) ?: 'asset-report');

        if ($format === 'csv') {
            return $exportService->downloadCsv($rows, $tableColumns, $slug.'-'.now()->format('Y-m-d').'.csv');
        }

        return app(ReportPdfExportService::class)->download(
            'reports.exports.assets-print',
            [
                'assets' => $rows,
                'tableColumns' => $tableColumns,
                'reportTitle' => $reportTitle,
            ],
            $slug.'-'.now()->format('Y-m-d').'.pdf'
        );
    }

    protected function eagerLoadSupportTicketReportRelations(Builder $query): void
    {
        $query->with([
            'company',
            'branch',
            'department',
            'creator',
            'asset',
            'activeAssignments.user',
        ]);
    }

    protected function supportTicketReportResults(Builder $query, Request $request)
    {
        $this->eagerLoadSupportTicketReportRelations($query);
        $ordered = $query->orderByDesc('id');

        if (in_array($request->input('export_format'), ['csv', 'pdf'], true)) {
            return $ordered->get();
        }

        return $ordered->paginate(self::REPORT_PER_PAGE)->withQueryString();
    }

    protected function maybeExportSupportTicketReport(
        Request $request,
        \Illuminate\Support\Collection|\Illuminate\Pagination\LengthAwarePaginator $tickets,
        array $tableColumns,
        string $reportTitle
    ): StreamedResponse|Response|null {
        $format = $request->input('export_format');

        if (! in_array($format, ['csv', 'pdf'], true)) {
            return null;
        }

        $rows = $tickets instanceof \Illuminate\Support\Collection ? $tickets : collect($tickets->items());
        $exportService = app(SupportTicketReportExportService::class);
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $reportTitle) ?: 'support-ticket-report');

        if ($format === 'csv') {
            return $exportService->downloadTicketCsv($rows, $tableColumns, $slug.'-'.now()->format('Y-m-d').'.csv');
        }

        return app(ReportPdfExportService::class)->download(
            'reports.exports.tickets-print',
            [
                'tickets' => $rows,
                'tableColumns' => $tableColumns,
                'reportTitle' => $reportTitle,
            ],
            $slug.'-'.now()->format('Y-m-d').'.pdf'
        );
    }

    protected function maybeExportUserWiseSupportTicketReport(
        Request $request,
        \Illuminate\Support\Collection $users,
        int $totalTickets,
        string $reportTitle
    ): StreamedResponse|Response|null {
        $format = $request->input('export_format');

        if (! in_array($format, ['csv', 'pdf'], true)) {
            return null;
        }

        $exportService = app(SupportTicketReportExportService::class);
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $reportTitle) ?: 'user-wise-support-tickets');

        if ($format === 'csv') {
            return $exportService->downloadUserWiseCsv($users, $slug.'-'.now()->format('Y-m-d').'.csv');
        }

        return app(ReportPdfExportService::class)->download(
            'reports.exports.userwise-tickets-print',
            [
                'users' => $users,
                'totalTickets' => $totalTickets,
                'reportTitle' => $reportTitle,
            ],
            $slug.'-'.now()->format('Y-m-d').'.pdf'
        );
    }


}
