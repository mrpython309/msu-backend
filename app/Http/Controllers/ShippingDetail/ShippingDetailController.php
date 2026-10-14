<?php

namespace App\Http\Controllers\ShippingDetail;

use App\Http\Controllers\Controller;
use App\Http\Requests\ShippingDetail\ShippingDetailRequest;
use App\Models\ShippingDetail;
use App\Services\MasterListExportService;
use App\Support\MasterListing;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ShippingDetailController extends Controller
{
    public function index(Request $request)
    {
        $perPage = MasterListing::perPage($request);
        $shippingDetails = $this->filteredShippingDetails($request)->orderByDesc('id')->paginate($perPage)->withQueryString();

        return view('shipping-details.index', compact('shippingDetails', 'perPage'));
    }

    public function export(Request $request, MasterListExportService $exporter)
    {
        return $exporter->excel(
            $this->shippingDetailRows($request),
            ['S.No', 'Shipping Name', 'GST Number', 'Contact Person', 'Contact No', 'Email', 'Status'],
            'shipping-details'
        );
    }

    public function exportPdf(Request $request, MasterListExportService $exporter)
    {
        return $exporter->pdf(
            'Shipping Details',
            ['S.No', 'Shipping Name', 'GST Number', 'Contact Person', 'Contact No', 'Email', 'Status'],
            $this->shippingDetailRows($request),
            'shipping-details'
        );
    }

    private function filteredShippingDetails(Request $request): Builder
    {
        $query = ShippingDetail::query();
        MasterListing::applySearch($query, $request, ['shipping_name', 'gst_number', 'contact_person', 'contact_no', 'email', 'address']);
        MasterListing::applyStatus($query, $request);

        return $query;
    }

    private function shippingDetailRows(Request $request)
    {
        return $this->filteredShippingDetails($request)->orderByDesc('id')->get()->values()->map(
            fn (ShippingDetail $detail, int $index) => [
                $index + 1,
                $detail->shipping_name,
                $detail->gst_number,
                $detail->contact_person,
                $detail->contact_no,
                $detail->email,
                ucfirst((string) $detail->status),
            ]
        );
    }

    public function create()
    {
        return view('shipping-details.create');
    }

    public function store(ShippingDetailRequest $request)
    {
        ShippingDetail::create($request->validated());

        return redirect()
            ->route('shipping-details.index')
            ->with('success', 'Shipping detail created successfully.');
    }

    public function edit(ShippingDetail $shippingDetail)
    {
        return view('shipping-details.edit', compact('shippingDetail'));
    }

    public function update(ShippingDetailRequest $request, ShippingDetail $shippingDetail)
    {
        $shippingDetail->update($request->validated());

        return redirect()
            ->route('shipping-details.index')
            ->with('success', 'Shipping detail updated successfully.');
    }

    public function destroy(ShippingDetail $shippingDetail)
    {
        $shippingDetail->delete();

        return redirect()
            ->route('shipping-details.index')
            ->with('success', 'Shipping detail deleted successfully.');
    }

    public function updateStatus(Request $request)
    {
        $shippingDetail = ShippingDetail::findOrFail($request->id);
        $shippingDetail->status = $request->status;
        $shippingDetail->save();

        return response()->json(['success' => true]);
    }
}
