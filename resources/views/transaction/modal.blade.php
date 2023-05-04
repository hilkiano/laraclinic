<div id="receiptModal" class="modal fade" data-bs-backdrop="true" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="receiptModalHead">Cart Content</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="receiptModalBody">
                <table class="table table-bordered table-striped table-hover caption-top w-100">
                    <thead class="table-primary">
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">SKU</th>
                            <th scope="col">Item Name</th>
                            <th scope="col">Price</th>
                            <th scope="col">Qty</th>
                            <th scope="col">Discount</th>
                            <th scope="col">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody id="itemRows">
                    </tbody>
                </table>
                <p class="fs-6 text-muted mb-0">Discount</p>
                <p class="fs-5 fw-bold mb-1" id="totalDiscount"></p>
                <p class="fs-6 text-muted mb-0">Total</p>
                <p class="fs-5 fw-bold mb-1" id="totalAmount"></p>
                <hr />
                <p class="fs-6 text-muted mb-0">Paid</p>
                <p class="fs-5 fw-bold mb-1" id="paidAmount"></p>
                <p class="fs-6 text-muted mb-0">Change</p>
                <p class="fs-5 fw-bold" id="changeAmount"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal"
                    id="receiptModalCloseBtn">Close</button>
                @if (in_array('PATIENT_PRESCRIPTION_CHECKOUT', $privs))
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal" id="receiptModalPrintBtn"><i
                            class="bi bi-printer me-2"></i>Print</button>
                @endif
            </div>
        </div>
    </div>
</div>
