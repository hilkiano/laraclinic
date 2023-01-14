<!DOCTYPE html>
<html>
@include('template.header', ['title' => 'Menus'])

<body>
    @include('template.navbar', ['title' => 'Menus'])
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
                                        @if (in_array("CREATE_MENU", $privs))
                                        <button class="btn btn-primary">
                                            <i class="bi bi-plus"></i>
                                            Add Menu
                                        </button>
                                        @endif
                                        <input placeholder="Filter results..." type="text" class="form-control" id="filterMenusField">
                                        @if ($data["hasFilter"])
                                        <button id="clearFilterMenusBtn" class="btn btn-secondary">
                                            <i class="bi bi-eraser"></i>
                                        </button>
                                        @endif
                                        <button id="filterMenusBtn" class="btn btn-primary">
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
                                <table class="table table-bordered table-striped table-hover caption-top" style="min-width: 850px;">
                                    <caption>List of menus</caption>
                                    <thead class="table-primary">
                                        <tr>
                                            <th scope="col" style="width: 50px;">#</th>
                                            <th scope="col" style="width: 150px;">Label</th>
                                            <th scope="col" style="width: 50px; text-align: center;">Icon</th>
                                            <th scope="col" style="width: 150px;">Name</th>
                                            <th scope="col" style="width: 75px; text-align: center;">Is Parent?</th>
                                            <th scope="col" style="width: 150px;">Parent</th>
                                            <th scope="col" style="width: 75px; text-align: center;">Is Active?</th>
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
                                            <th scope="row">{{ $i }}</th>
                                            <td>{{ $row->label }}</td>
                                            <td style="text-align: center;"><i class="bi {{ $row->icon }}"></i></td>
                                            <td>{{ $row->name }}</td>
                                            <td style="text-align: center;"><i class="bi {{ $row->is_parent ? 'bi-check-lg text-success' : 'bi-x-lg text-danger' }}"></i></td>
                                            <td>{{ $row->parent }}</td>
                                            <td style="text-align: center;"><i class="bi {{ $row->deleted_at ? 'bi-x-lg text-danger' : 'bi-check-lg text-success' }}"></i></td>
                                            <td style="text-align: center;">
                                                @if (in_array("UPDATE_MENU", $privs))
                                                <button class="btn btn-sm btn-secondary">Edit</button>
                                                @endif
                                                @if (in_array("DELETE_MENU", $privs))
                                                <button class="btn btn-sm btn-danger">Deactivate</button>
                                                @endif
                                            </td>
                                        </tr>
                                        @php
                                        $i++;
                                        @endphp
                                        @endforeach
                                        @else
                                        <tr>
                                            <td colspan="8">No Data.</td>
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
</body>

</html>

@include('template.footer')