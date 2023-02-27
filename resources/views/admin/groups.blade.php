<!DOCTYPE html>
<html>
@include('template.header', ['title' => 'Groups'])

<body>
    @include('template.navbar', ['title' => 'Groups'])
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
                                        @if (in_array("CREATE_GROUP", $privs))
                                        <button data-bs-toggle="modal" href="#groupsModal" class="btn btn-primary">
                                            <i class="bi bi-plus"></i>
                                            Add Group
                                        </button>
                                        @endif
                                        <input id="filterGroupsField" placeholder="Filter results..." type="text" class="form-control">
                                        @if ($data["hasFilter"])
                                        <button id="clearFilterGroupsBtn" class="btn btn-secondary">
                                            <i class="bi bi-eraser"></i>
                                        </button>
                                        @endif
                                        <button id="filterGroupsBtn" class="btn btn-primary">
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
                                    <caption>List of groups</caption>
                                    <thead class="table-primary">
                                        <tr>
                                            <th scope="col" style="width: 50px;">#</th>
                                            <th scope="col" style="width: 250px;">Name</th>
                                            <th scope="col" style="width: 400px;">Description</th>
                                            <th scope="col" style="width: 150px;">Roles</th>
                                            <th scope="col" style="width: 100px; text-align: center;">Is Active?</th>
                                            <th scope="col" style="width: 150px; text-align: center;">Action</th>
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
                                            <td>
                                                @foreach ($row->roles as $role)
                                                <span class="badge rounded-pill bg-secondary">{{ $role }}</span>
                                                @endforeach
                                            </td>
                                            <td style="text-align: center;"><i class="bi {{ $row->deleted_at ? 'bi-x-lg text-danger' : 'bi-check-lg text-success' }}"></i></td>
                                            <td style="text-align: center;">
                                                @if (in_array("UPDATE_GROUP", $privs))
                                                <button data-bs-toggle="modal" href="#groupsModal" data-row="{{ $row }}" class="btn btn-sm btn-secondary">Edit</button>
                                                @endif
                                                @if (in_array("DELETE_GROUP", $privs))
                                                <button data-bs-toggle="modal" href="#chgStateGroupModal" data-row="{{ $row }}" class="btn btn-sm {{ $row->deleted_at ? 'btn-success' : 'btn-danger' }}">
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
                                            <td colspan="6">No Data.</td>
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
    @include('modals.groups_modal')
    <div id="chgStateGroupModal" class="modal fade" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="chgStateGroupModalHead"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="chgStateGroupModalBody">
                    <p>Do you want to <span id="chgStateGroupModalDetail"></span>?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">No</button>
                    <button type="button" id="chgStateGroupHandler" class="btn btn-primary">Yes</button>
                </div>
            </div>
        </div>
    </div>
</body>

</html>

@include('template.footer')
@include('admin.js.groups_js')