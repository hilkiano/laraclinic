<!DOCTYPE html>
<html>
@include('template.header', ['title' => 'Stocks'])

<body>
    @include('template.navbar', ['title' => 'Stocks'])
    <div class="container-fluid">
        <div class="row flex-nowrap">
            @include('template.sidebar')
            <div id="mainContent" class="d-flex flex-column">
                <div class="container-fluid mt-4">
                    <div class="row gy-4">
                        {{-- REGISTRATION AND TABLE SETTINGS --}}
                        <div class="d-flex flex-column col-md-6 border border-primary rounded-4 p-4">
                            <h4 class="fw-semibold">Register Stock</h4>
                            <p class="lead">1. Download template file and fill with other application that supports
                                XLSX file format.</p>
                            <button id="downloadTemplateBtn" type="button"
                                class="btn btn-primary btn-lg align-self-start"><i
                                    class="bi bi-file-earmark-arrow-down"></i>&nbsp;Download Template Here</button>
                            <p class="lead mt-4">2. Select the file and click submit to begin stock registration</p>
                            <div class="input-group">
                                <input type="file" class="form-control" id="templateFile" aria-label="Upload"
                                    accept=".xlsx">
                                <button disabled class="btn btn-primary" type="button" id="uploadBtn"><i
                                        class="bi bi-cloud-upload"></i>&nbsp;Submit</button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h4 class="fw-semibold">Table Settings</h4>
                            <form id="filterForm" class="d-flex flex-column">
                                <div class="mb-3">
                                    <label for="filterName" class="form-label">Medicine name</label>
                                    <input type="input" class="form-control" id="filterName"
                                        placeholder="Type anything...">
                                </div>
                                {{-- <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="filterHide">
                                    <label class="form-check-label" for="filterHide">Hide empty stocks</label>
                                </div> --}}
                                <div class="d-flex gap-2 align-self-end">
                                    <button type="button" class="btn btn-secondary" id="resetFilterBtn"><i
                                            class="bi bi-arrow-counterclockwise"></i>&nbsp;Reset Filter</button>
                                    <button type="submit" class="btn btn-primary"><i
                                            class="bi bi-check2"></i>&nbsp;Filter</button>
                                </div>
                            </form>
                        </div>
                        {{-- TABLE --}}
                        <div class="col-12">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover caption-top"
                                    style="min-width: 1100px;">
                                    <thead class="table-primary">
                                        <tr>
                                            <th scope="col">#</th>
                                            <th scope="col">Registered At</th>
                                            <th scope="col">Medicine Name</th>
                                            <th scope="col">Stock In</th>
                                            <th scope="col">Stock Out</th>
                                        </tr>
                                    </thead>
                                    <tbody id="stockRows"></tbody>
                                </table>
                            </div>
                            <div class="mt-2 mb-5 d-flex row">
                                <div class="col">
                                    <p class="small text-muted">Total <span class="fw-semibold" id="allCount">0</span>
                                    </p>
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
</body>

</html>
@include('toasts.live_toast')
@include('stocks.js.stocks_js')
@include('template.footer')
