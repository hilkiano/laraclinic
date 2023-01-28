<!DOCTYPE html>
<html>
@include('template.header', ['title' => 'Users'])

<body>
    @include('template.navbar', ['title' => 'Users'])
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
                                        @if (in_array("CREATE_USER", $privs))
                                        <button data-bs-toggle="modal" href="#usersModal" class="btn btn-primary">
                                            <i class="bi bi-plus"></i>
                                            Add User
                                        </button>
                                        @endif
                                        <input id="filterUsersField" placeholder="Filter results..." type="text" class="form-control">
                                        @if ($data["hasFilter"])
                                        <button id="clearFilterUsersBtn" class="btn btn-secondary">
                                            <i class="bi bi-eraser"></i>
                                        </button>
                                        @endif
                                        <button id="filterUsersBtn" class="btn btn-primary">
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
                                <table class="table table-bordered table-striped table-hover caption-top" style="min-width: 1450px;">
                                    <caption>List of users</caption>
                                    <thead class="table-primary">
                                        <tr>
                                            <th scope="col" style="width: 50px;">#</th>
                                            <th scope="col" style="width: 150px;">Username</th>
                                            <th scope="col" style="width: 200px">Group</th>
                                            <th scope="col" style="width: 200px;">Name</th>
                                            <th scope="col" style="width: 200px;">Email</th>
                                            <th scope="col" style="width: 150px;">Phone</th>
                                            <th scope="col" style="width: 250px;">Created At</th>
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
                                        <tr class="{{ $row->deleted_at ? 'table-danger' : '' }}">
                                            <td scope="row">{{ $i }}</td>
                                            <td>{{ $row->username }}</td>
                                            <td>{{ $row->group->name }}</td>
                                            <td>{{ $row->name }}</td>
                                            <td>{{ $row->email }}</td>
                                            <td>+62 {{ $row->phone_number }}</td>
                                            <td>{{ $row->created_at }}</td>
                                            <td style="text-align: center;"><i class="bi {{ $row->deleted_at ? 'bi-x-lg text-danger' : 'bi-check-lg text-success' }}"></i></td>
                                            <td style="text-align: center;">
                                                @if (in_array("UPDATE_USER", $privs))
                                                <button data-bs-toggle="modal" href="#usersModal" data-row="{{ $row }}" class="btn btn-sm btn-secondary">Edit</button>
                                                @endif
                                                @if (in_array("DELETE_USER", $privs))
                                                <button data-bs-toggle="modal" href="#chgStateModal" data-row="{{ $row }}" class="btn btn-sm {{ $row->deleted_at ? 'btn-success' : 'btn-danger' }}">
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
                                            <td colspan="9">No Data.</td>
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
    @include('modals.users_modal')
    <div id="chgStateModal" class="modal fade" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="chgStateModalHead"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="chgStateModalBody">
                    <p>Do you want to <span id="chgStateModalDetail"></span>?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">No</button>
                    <button type="button" id="chgStateHandler" class="btn btn-primary">Yes</button>
                </div>
            </div>
        </div>
    </div>

</body>

</html>

@include('template.footer')
@include('admin.users_js')