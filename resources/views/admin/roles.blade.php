<!DOCTYPE html>
<html>
@include('template.header', ['title' => 'Roles'])
<link href="https://unpkg.com/mobius1-selectr@latest/dist/selectr.min.css" rel="stylesheet" type="text/css">

<body>
    @include('template.navbar', ['title' => 'Roles'])
    <div class="container-fluid d-flex flex-row">
        <div class="row flex-nowrap">
            @include('template.sidebar')
            <div id="mainContent" class="d-flex flex-column">
                <div class="container-fluid mt-4">
                    <div class="row mb-2">
                        <div class="col">
                            <div class="card">
                                <div class="card-body">
                                    <div class="input-group">
                                        @if (in_array("CREATE_ROLE", $privs))
                                        <button data-bs-toggle="modal" href="#rolesModal" class="btn btn-primary">
                                            <i class="bi bi-plus"></i>
                                            Add Role
                                        </button>
                                        @endif
                                        <input id="filterRolesField" placeholder="Filter results..." type="text" class="form-control">
                                        @if ($data["hasFilter"])
                                        <button id="clearFilterRolesBtn" class="btn btn-secondary">
                                            <i class="bi bi-eraser"></i>
                                        </button>
                                        @endif
                                        <button id="filterRolesBtn" class="btn btn-primary">
                                            <i class="bi bi-funnel"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover caption-top" style="min-width: 1100px;">
                                    <caption>List of roles</caption>
                                    <thead class="table-primary">
                                        <tr>
                                            <th scope="col" style="width: 50px;">#</th>
                                            <th scope="col" style="width: 250px;">Name</th>
                                            <th scope="col" style="width: 380px;">Description</th>
                                            <th scope="col" style="width: 150px; text-align: right;">Menus (count)</th>
                                            <th scope="col" style="width: 150px; text-align: right;">Privileges (count)</th>
                                            <th scope="col" style="width: 100px; text-align: center;">Is Active?</th>
                                            <th scope="col" style="width: 170px; text-align: center;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if (count($data['rows']) > 0)
                                        @php
                                        $i = 1;
                                        @endphp
                                        @foreach ($data['rows'] as $row)
                                        <tr>
                                            <td scope="row">{{ $i }}</td>
                                            <td>{{ $row->name }}</td>
                                            <td>{{ $row->description }}</td>
                                            <td style="text-align: right;">{{ $row->menu_count }}</td>
                                            <td style="text-align: right;">{{ $row->privilege_count }}</td>
                                            <td style="text-align: center;"><i class="bi {{ $row->deleted_at ? 'bi-x-lg text-danger' : 'bi-check-lg text-success' }}"></i></td>
                                            <td style="text-align: center;">
                                                @if (in_array("UPDATE_ROLE", $privs))
                                                <button data-bs-toggle="modal" href="#rolesModal" data-row="{{ $row }}" class="btn btn-sm btn-secondary">Edit</button>
                                                @endif
                                                @if (in_array("DELETE_ROLE", $privs))
                                                <button data-bs-toggle="modal" href="#chgStateRoleModal" data-row="{{ $row }}" class="btn btn-sm {{ $row->deleted_at ? 'btn-success' : 'btn-danger' }}">
                                                    {{ $row->deleted_at ? 'Activate' : 'Deactivate' }}
                                                </button>
                                                @endif
                                            </td>
                                        </tr>
                                        @php
                                        $i++;
                                        @endphp
                                        @endforeach
                                        @else
                                        <tr>
                                            <td colspan="7">No Data.</td>
                                        </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                            {!! $data['rows']->appends(request()->input())->links() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('toasts.live_toast')
    @include('modals.roles_modal')
    <div id="chgStateRoleModal" class="modal fade" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="chgStateRoleModalHead"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="chgStateRoleModalBody">
                    <p>Do you want to <span id="chgStateRoleModalDetail"></span>?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">No</button>
                    <button type="button" id="chgStateRoleHandler" class="btn btn-primary">Yes</button>
                </div>
            </div>
        </div>
    </div>
</body>

</html>

@include('template.footer')
@include('admin.roles_js')