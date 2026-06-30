@extends('layouts.header')

@section('css')
<link rel="stylesheet" href="{{ asset('design/vendors/datatables.net-bs4/dataTables.bootstrap4.css') }}">
<style>
    .areas-page { display: grid; gap: 16px; }
    .areas-head { display: flex; align-items: flex-end; justify-content: space-between; gap: 14px; }
    .areas-title { margin: 0; color: #101828; font-size: 24px; font-weight: 900; }
    .areas-copy { margin: 4px 0 0; color: #667085; font-size: 13px; }
    .areas-panel { overflow: hidden; background: #fff; border: 1px solid #e6e9ef; border-radius: 8px; box-shadow: 0 10px 26px rgba(15, 23, 42, .06); }
    .areas-panel-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 14px; border-bottom: 1px solid #edf0f5; background: #fcfcfd; }
    .areas-table { margin: 0; }
    .areas-table th { padding: 12px 14px; color: #667085; font-size: 11px; font-weight: 800; letter-spacing: .04em; text-transform: uppercase; background: #f8fafc; border-bottom: 1px solid #edf0f5; white-space: nowrap; }
    .areas-table td { padding: 14px; border-color: #f1f3f6; vertical-align: middle; }
    .area-name { color: #101828; font-weight: 900; }
    .area-badge { display: inline-flex; align-items: center; gap: 5px; padding: 5px 9px; border-radius: 999px; background: #e0f2fe; color: #075985; font-size: 11px; font-weight: 800; white-space: nowrap; }
    .area-actions { display: flex; justify-content: flex-end; gap: 6px; }
    .area-icon-btn { width: 34px; height: 34px; display: inline-flex; align-items: center; justify-content: center; padding: 0; }
    .areas-panel .dataTables_wrapper { padding: 14px; }
    .areas-panel .dataTables_filter input,
    .areas-panel .dataTables_length select { border: 1px solid #d0d5dd; border-radius: 6px; }
    .areas-panel .dataTables_filter input { min-height: 34px; padding: 5px 9px; }
    .areas-panel .dataTables_length select { min-height: 32px; padding: 4px 26px 4px 8px; }
    .areas-panel .dataTables_info { color: #667085; font-size: 13px; }
    .areas-panel .pagination { margin: 0; }
    @media (max-width: 768px) {
        .areas-head, .areas-panel-head { align-items: stretch; flex-direction: column; }
        .areas-panel { overflow-x: auto; }
        .areas-table { min-width: 720px; }
    }
</style>
@endsection

@section('content')
<div class="areas-page">
    <div class="areas-head">
        <div>
            <h4 class="areas-title">Areas</h4>
            <p class="areas-copy">Manage master area names used by distributors, dealers, orders, vouchers, and reports.</p>
        </div>
        <button class="btn btn-danger btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#areaCreateModal">
            <i class="bi bi-plus-lg"></i> Add Area
        </button>
    </div>

    @if($errors->any())
        <div class="alert alert-danger mb-0">
            <strong>Please check the form.</strong>
            <div>{{ $errors->first() }}</div>
        </div>
    @endif
    @if(session('success'))
        <div class="alert alert-success mb-0">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger mb-0">{{ session('error') }}</div>
    @endif

    <div class="areas-panel">
        <div class="areas-panel-head">
            <div>
                <div class="fw-bold text-dark">Area List</div>
                <div class="text-muted small">{{ number_format($areas->count()) }} area(s) found</div>
            </div>
        </div>

        <table id="areasTable" class="table areas-table align-middle" style="width:100%">
            <thead>
                <tr>
                    <th>Area Name</th>
                    <th>Assigned Distributors</th>
                    <th>Created</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($areas as $area)
                    <tr>
                        <td>
                            <div class="area-name">{{ $area->name }}</div>
                        </td>
                        <td>
                            <span class="area-badge">
                                <i class="bi bi-geo-alt"></i>
                                {{ number_format($area->assigned_distributors_count ?? 0) }} assigned
                            </span>
                        </td>
                        <td class="text-muted small">
                            {{ $area->created_at ? $area->created_at->format('M d, Y') : 'N/A' }}
                        </td>
                        <td>
                            <div class="area-actions">
                                <button type="button" class="btn btn-sm btn-outline-primary area-icon-btn" data-bs-toggle="modal" data-bs-target="#areaEditModal{{ $area->id }}" title="Edit area">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form action="{{ route('areas.destroy', $area->id) }}" method="POST" onsubmit="return confirm('Delete this area?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger area-icon-btn" type="submit" title="Delete area">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="areaCreateModal" tabindex="-1" aria-labelledby="areaCreateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('areas.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="areaCreateModalLabel">Add Area</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label" for="areaCreateName">Area Name <span class="text-danger">*</span></label>
                    <input type="text" id="areaCreateName" name="name" class="form-control" value="{{ old('name') }}" maxlength="255" placeholder="Enter area name" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Save Area</button>
                </div>
            </form>
        </div>
    </div>
</div>

@foreach($areas as $area)
    <div class="modal fade" id="areaEditModal{{ $area->id }}" tabindex="-1" aria-labelledby="areaEditModal{{ $area->id }}Label" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('areas.update', $area->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title" id="areaEditModal{{ $area->id }}Label">Edit Area</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <label class="form-label" for="areaEditName{{ $area->id }}">Area Name <span class="text-danger">*</span></label>
                        <input type="text" id="areaEditName{{ $area->id }}" name="name" class="form-control" value="{{ old('name', $area->name) }}" maxlength="255" required>
                        <small class="text-muted d-block mt-2">Renaming an area also updates matching distributor and dealer assignments.</small>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Area</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach
@endsection

@section('javascript')
<script>
    $(document).ready(async function () {
        function loadScript(src) {
            return new Promise(function (resolve, reject) {
                var script = document.createElement('script');
                script.src = src;
                script.async = false;
                script.onload = resolve;
                script.onerror = reject;
                document.head.appendChild(script);
            });
        }

        async function ensureDataTables() {
            if ($.fn && $.fn.DataTable) {
                return true;
            }

            var dataTableSources = [
                "{{ asset('design/assets/libs/datatables.net/js/jquery.dataTables.min.js') }}",
                "{{ asset('design/vendors/datatables.net/jquery.dataTables.js') }}",
                "https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"
            ];

            for (var i = 0; i < dataTableSources.length; i++) {
                try {
                    await loadScript(dataTableSources[i]);

                    if ($.fn && $.fn.DataTable) {
                        break;
                    }
                } catch (error) {
                    console.warn('Unable to load DataTables from:', dataTableSources[i]);
                }
            }

            if (!$.fn || !$.fn.DataTable) {
                return false;
            }

            try {
                await loadScript("{{ asset('design/vendors/datatables.net-bs4/dataTables.bootstrap4.js') }}");
            } catch (error) {
                console.warn('DataTables Bootstrap styling script did not load. Continuing with core DataTables.');
            }

            return true;
        }

        if (!await ensureDataTables()) {
            console.error('DataTables failed to load from local assets and CDN fallback.');
            return;
        }

        if ($.fn.DataTable.isDataTable('#areasTable')) {
            $('#areasTable').DataTable().destroy();
        }

        $('#areasTable').DataTable({
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
            order: [[0, 'asc']],
            columnDefs: [
                { targets: 3, orderable: false, searchable: false }
            ],
            language: {
                emptyTable: 'No areas found. Add an area to get started.',
                search: 'Search:',
                lengthMenu: 'Show _MENU_ areas',
                info: 'Showing _START_ to _END_ of _TOTAL_ areas',
                infoEmpty: 'Showing 0 areas',
                zeroRecords: 'No matching areas found'
            }
        });
    });
</script>
@endsection
