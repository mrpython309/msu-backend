 @php
    $optionalDataCols = ['serial_no', 'asset_code', 'status', 'asset_type_id', 'company_id', 'branch_id', 'brand_id', 'location_id', 'vendor_id', 'purchase_date', 'purchase_cost', 'condition', 'operational_status'];
    $reportColCount =
        2
        + (in_array('sub_category_id', $tableColumns, true) ? 1 : 0)
        + count(array_intersect($optionalDataCols, $tableColumns));
@endphp
<div class="table-responsive">
            <table class="table card-table " id="dealerLocationsTable">
                <thead>
                    <tr>
                        <th class="text-nowrap">#</th>
                        <th>Name</th>
                        @if(in_array('sub_category_id', $tableColumns))
                        <th>Category</th>
                        @endif
                        @if(in_array('serial_no', $tableColumns))
                        <th>Serial No.</th>
                        @endif
                        @if(in_array('asset_code', $tableColumns))
                        <th>Asset Code</th>
                        @endif
                        @if(in_array('status', $tableColumns))
                        <th>Status</th>
                        @endif

                         @if(in_array('asset_type_id', $tableColumns))
                        <th>Asset Type</th>
                        @endif


                        @if(in_array('company_id', $tableColumns))
                        <th>Company</th>
                        @endif

                        @if(in_array('branch_id', $tableColumns))
                        <th>Branch</th>
                        @endif

                        @if(in_array('brand_id', $tableColumns))
                        <th>Brand</th>
                        @endif

                        @if(in_array('location_id', $tableColumns))
                        <th>Physical Location</th>
                        @endif

                        @if(in_array('vendor_id', $tableColumns))
                        <th>Vendor</th>
                        @endif

                        @if(in_array('purchase_date', $tableColumns))
                        <th>Purchase Date</th>
                        @endif
                        @if(in_array('purchase_cost', $tableColumns))
                        <th>Purchase Cost</th>
                        @endif
                        @if(in_array('condition', $tableColumns))
                        <th>Condition</th>
                        @endif
                        @if(in_array('operational_status', $tableColumns))
                        <th>Operalion Status</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($assets as $asset)
                    <tr>
                        <td class="text-nowrap text-muted">
                            @if (method_exists($assets, 'currentPage'))
                                {{ ($assets->currentPage() - 1) * $assets->perPage() + $loop->iteration }}
                            @else
                                {{ $loop->iteration }}
                            @endif
                        </td>
                        <td>
                                <div class="d-flex justify-content-start align-items-center customer-name">
                                @if($asset->icon == 'images')   
                                <div class="avatar-wrapper">
                                <div class="avatar me-4 rounded-2 bg-label-secondary">
                                <img src="{{ $asset->doc_url }}" alt="{{ $asset->asset_name }}" class="rounded">
                                    </div>
                                </div>
                                @endif
                                <div class="d-flex flex-column">
                                <span class="fw-medium text-nowrap text-heading">{{ $asset->asset_name }}</span>
                                <small>{{ $asset->assetType?->asset_type_name }}</small>
                                </div>
                            </div>                            
                        </td>

                         @if(in_array('sub_category_id', $tableColumns))
                        <td> {{$asset->subCategory?->category_name}} </td>
                         @endif

                        @if(in_array('serial_no', $tableColumns)) <td>{{ $asset->serial_no }}</td> @endif
                        @if(in_array('asset_code', $tableColumns))
                        <td>{{ $asset->asset_code }}</td>
                        @endif
                        @if(in_array('status', $tableColumns))
                        <td>
                            @if($asset->status)
                            <span class="badge bg-label-{{ $asset->status->color() }}">
                                {{ $asset->status->label() }}
                            </span>
                            @endif
                        </td>
                        @endif

                          @if(in_array('asset_type_id', $tableColumns))
                        <td>{{$asset->assetType?->asset_type_name}}</td>
                        @endif

                        @if(in_array('company_id', $tableColumns))
                        <td> {{$asset->company?->name}} </td>
                         @endif

                         @if(in_array('branch_id', $tableColumns))
                        <td> {{$asset->branch?->name}} </td>
                         @endif

                         @if(in_array('location_id', $tableColumns))
                        <td> {{$asset->location?->location_name}} </td>
                         @endif

                         @if(in_array('brand_id', $tableColumns))
                        <td>{{ $asset->brand?->brand_name }}</td>
                        @endif

                         @if(in_array('vendor_id', $tableColumns))
                        <td>{{ $asset->vendor?->name }}</td>
                        @endif


                        
                        
                        @if(in_array('purchase_date', $tableColumns))
                        <td>
                            {{ optional($asset->purchase_date)->format('d M Y') }}
                        </td>
                        @endif
                        @if(in_array('purchase_cost', $tableColumns))
                        <td>
                            ₹ {{ number_format($asset->purchase_cost, 2) }}
                        </td>
                        @endif
                        @if(in_array('condition', $tableColumns))
                        <td>
                            {!! $asset->condition_label !!}
                        </td>
                        @endif
                        @if(in_array('operational_status', $tableColumns))
                        <td>
                            {!! $asset->operational_status_label !!}
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ $reportColCount }}" class="text-center py-5 text-muted">
                            No assets found
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($assets instanceof \Illuminate\Pagination\LengthAwarePaginator && $assets->hasPages())
        <div class="card-footer d-flex justify-content-between align-items-center">
            <div class="text-muted">Showing {{ $assets->firstItem() ?? 0 }} to {{ $assets->lastItem() ?? 0 }} of {{ $assets->total() }} assets</div>
            <div>{{ $assets->links() }}</div>
        </div>
        @endif
