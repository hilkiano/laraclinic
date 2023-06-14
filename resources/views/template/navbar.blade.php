<nav class="navbar navbar-expand-lg sticky-top bg-primary">
    <div class="container-fluid">
        <div class="d-flex flex-grow-0">
            <button class="btn btn-primary d-lg-none me-2" type="button" data-bs-toggle="offcanvas"
                data-bs-target="#mobileSidebar" aria-controls="mobileSidebar">
                <i class="bi bi-list" style="font-size: 1rem;"></i>
            </button>
            <div class="d-flex align-items-center">
                <span class="navbar-brand text-white d-inline-block text-truncate">{{ $title }}</span>
                @if (request()->path() === 'cashier' || request()->path() === 'transactions')
                    <div class="alert alert-warning p-1 px-2 mb-0 d-lg-block d-none" role="alert">
                        <i class="bi bi-info-circle me-2"></i>Open <span class="fw-bold">Laraclinic POS</span> on a new
                        tab
                        to
                        print receipt.
                    </div>
                @endif
            </div>

        </div>

        <div class="d-flex flex-grow-0">
            <div class="btn-group">
                <a href="/user-configs/{{ $user->id }}" class="btn btn-primary btn-sm" type="button">
                    <i class="bi bi-person-fill-gear me-2"></i>
                    {{ $user->username }}
                </a>
                <button type="button" class="btn btn-sm btn-primary dropdown-toggle dropdown-toggle-split"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="visually-hidden">Toggle Dropdown</span>
                </button>
                <ul style="z-index: 2" class="dropdown-menu dropdown-menu-end">
                    <li>
                        <button class="dropdown-item" type="button" data-bs-toggle="modal"
                            data-bs-target="#logoutModal">
                            Logout
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Logout</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-danger" id="logoutButton">Logout</button>
            </div>
        </div>
    </div>
</div>
