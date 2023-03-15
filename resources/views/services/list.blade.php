<!DOCTYPE html>
<html>
@include('template.header', ['title' => 'Service List'])

<body>
    @include('template.navbar', ['title' => 'Service List'])
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
                                        <button class="accordion-button show" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTableSettings">
                                            Table Settings or Filters
                                        </button>
                                    </h2>
                                    <div id="collapseTableSettings" class="accordion-collapse collapse show" data-bs-parent="#settingsAccordion">
                                        <div class="accordion-body" style="overflow-x: auto;">
                                            <form id="tableForm" style="min-width: 700px">
                                                <div class="row gy-4">
                                                    <div class="col-sm-12">
                                                        <label for="filter" class="form-label">Filter</label>
                                                        <div class="input-group">
                                                            <button type="button" data-bs-toggle="modal" href="#addServiceModal" class="btn btn-success">
                                                                <i class="bi bi-plus"></i>
                                                                Add Service
                                                            </button>
                                                            <select style="max-width: 200px" class="form-select" id="filterCol" autocomplete="off">
                                                                <option value="label">Name</option>
                                                                <option value="sku">SKU</option>
                                                            </select>
                                                            <input autocomplete="off" type="text" class="form-control" id="filterVal" placeholder="Type anything...">
                                                        </div>
                                                    </div>
                                                    <div class="col-12 d-flex justify-content-end">
                                                        <button id="resetFilterBtn" class="btn btn-light" type="button"><i class="me-2 bi bi-eraser"></i>Reset Filter</button>
                                                        <button id="applyFilterBtn" class="btn btn-primary ms-2" type="submit"><i class="me-2 bi bi-check-lg"></i>Apply Filter</button>
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
                                    <table class="table table-bordered table-striped table-hover caption-top" style="min-width: 1100px;">
                                        <thead class="table-primary">
                                            <tr>
                                                <th scope="col">#</th>
                                                <th scope="col">SKU</th>
                                                <th scope="col">Name</th>
                                                <th scope="col">Package</th>
                                                <th scope="col">Category</th>
                                            </tr>
                                        </thead>
                                        <tbody id="serviceRows">
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-2 mb-5 d-flex row">
                                    <div class="col">
                                        <p class="small text-muted">Total <span class="fw-semibold" id="allCount">0</span></p>
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
@include('services.js.list_js')
@include('template.footer')