<!DOCTYPE html>
<html>
@include('template.header', ['title' => 'Medicine List'])

<body>
    @include('template.navbar', ['title' => 'Medicine List'])
    <div class="container-fluid">
        <div class="row flex-nowrap">
            @include('template.sidebar')
            <div id="mainContent" class="d-flex flex-column">
                <div class="container-fluid mt-4">
                    <div class="row gy-4">
                        <div class="col-12">
                            <div class="accordion" id="settingsAccordion">
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="flush-headingOne">
                                        <button class="accordion-button show" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#collapseTableSettings">
                                            Table Settings or Filters
                                        </button>
                                    </h2>
                                    <div id="collapseTableSettings" class="accordion-collapse collapse show"
                                        data-bs-parent="#settingsAccordion">
                                        <div class="accordion-body" style="overflow-x: auto;">
                                            <form id="tableForm" style="min-width: 400px">
                                                <div class="row gy-4">
                                                    <div class="col-sm-12 col-md-8 col-lg-10">
                                                        <label for="filterVal" class="form-label">Filter</label>
                                                        <div class="input-group">
                                                            @if (in_array('MEDICINE_SERVICE_CREATE', $privs))
                                                                <button type="button" id="addMedicineBtn"
                                                                    class="btn btn-success">
                                                                    <i class="bi bi-plus"></i>
                                                                    Add Medicine
                                                                </button>
                                                            @endif
                                                            <select style="max-width: 200px" class="form-select"
                                                                id="filterCol" autocomplete="off">
                                                                <option value="label">Name</option>
                                                                <option value="sku">SKU</option>
                                                            </select>
                                                            <input autocomplete="off" type="text"
                                                                class="form-control" id="filterVal"
                                                                placeholder="Type anything...">
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-12 col-md-4 col-lg-2">
                                                        <label for="itemPerPage" class="form-label">Item Per
                                                            Page</label>
                                                        <select class="form-select" id="itemPerPage" autocomplete="off">
                                                            <option value="10">10</option>
                                                            <option value="25">25</option>
                                                            <option value="50">50</option>
                                                            <option value="100">100</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-12 d-flex justify-content-end">
                                                        <button id="resetFilterBtn" class="btn btn-light"
                                                            type="button"><i class="me-2 bi bi-eraser"></i>Reset
                                                            Filter</button>
                                                        <button id="applyFilterBtn" class="btn btn-primary ms-2"
                                                            type="submit"><i class="me-2 bi bi-check-lg"></i>Apply
                                                            Filter</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="col-12">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped table-hover caption-top"
                                        style="min-width: 1100px;">
                                        <thead class="table-primary">
                                            <tr>
                                                <th scope="col">#</th>
                                                <th scope="col">SKU</th>
                                                <th scope="col">Name</th>
                                                <th scope="col">Package</th>
                                                <th scope="col">Sell Price</th>
                                                <th scope="col">Stock</th>
                                                @if (in_array('MEDICINE_SERVICE_UPDATE', $privs) || in_array('MEDICINE_SERVICE_DELETE', $privs))
                                                    <th style="width: 170px" scope="col"></th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody id="medicineRows">
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-2 mb-5 d-flex row">
                                    <div class="col">
                                        <p class="small text-muted">Total <span class="fw-semibold"
                                                id="allCount">0</span></p>
                                    </div>
                                    <div class="col d-flex justify-content-end">
                                        <div id="pagination"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
@include('toasts.live_toast')
@include('medicines.modal')
@include('medicines.js.list_js', [
    'canEdit' => in_array('MEDICINE_SERVICE_UPDATE', $privs),
    'canDelete' => in_array('MEDICINE_SERVICE_DELETE', $privs),
])
@include('template.footer')
