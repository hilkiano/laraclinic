<div id="medicineModal" class="modal fade" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="medicineModalHead"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="medicineModalBody">
                <form id="medicineForm">
                    <input type="hidden" name="id" value="" id="id" />
                    <div class="row gy-2">
                        <div class="col-sm-12">
                            <label for="name" class="form-label">Name</label>
                            <input placeholder="Medicine name" type="text" class="form-control" id="name" name="label" required>
                            <div class="invalid-feedback" id="invalidNameFeedback"></div>
                        </div>
                        <div class="col-sm-12 col-md-6">
                            <label for="package" class="form-label">Package</label>
                            <input placeholder="Medicine package" type="text" class="form-control" id="package" name="package" required>
                            <div class="invalid-feedback" id="invalidPackageFeedback"></div>
                        </div>
                        <div class="col-sm-12">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3" placeholder="Description"></textarea>
                        </div>
                    </div>
                    <hr />
                    <div class="row gy-2">
                        <div class="col-sm-12 col-md-6">
                            <label for="name" class="form-label">Buy Price</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input placeholder="Buy Price" type="text" class="form-control" id="buy_price" name="buy_price" autocomplete="off">
                                <div class="invalid-feedback" id="invalidBuyPriceFeedback"></div>
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-6">
                            <label for="name" class="form-label">Sell Price</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input placeholder="Sell Price" type="text" class="form-control" id="sell_price" name="sell_price" autocomplete="off" required>
                                <div class="invalid-feedback" id="invalidSellPriceFeedback"></div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal" id="medicineModalCloseBtn">Cancel</button>
                <button type="submit" form="medicineForm" class="btn btn-primary" id="medicineModalSubmitBtn">Submit</button>
            </div>
        </div>
    </div>
</div>

<div id="confirmModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmModalHead"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="confirmModalBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal" id="confirmModalCloseBtn">No</button>
                <button type="button" class="btn btn-primary" id="confirmModalSubmitBtn">Yes</button>
            </div>
        </div>
    </div>
</div>