<div id="paymentContainer" style="z-index: 1;" class="col-12 px-4 py-4 sticky-bottom gap-3 align-items-center border-top">
    <form id="payment-form">
        <div class="row gy-2 gx-3 align-items-center p-2 pb-3 bg-body-secondary rounded w-100" id="option-0">
            <div class="col">
                <label for="amount" class="form-label">Payment
                    amount</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text">Rp</span>
                    <input type="text" id="payment-amount-0" name="payment-amount-0" class="form-control"
                        autocomplete="off">
                </div>
            </div>
            <div class="col-auto">
                <label for="payment" class="form-label">Payment
                    with</label>
                <div class="input-group input-group-sm">
                    <select class="form-select" id="payment-with-0" name="payment-with-0" autocomplete="off">
                        <option value="CASH">Cash</option>
                        <option value="CREDIT_CARD">Credit Card
                        </option>
                        <option value="DEBIT_CARD">Debit Card
                        </option>
                        <option value="BANK_TRANSFER">Bank Transfer
                        </option>
                    </select>
                </div>
            </div>
            <div class="col-auto d-none">
                <label for="totalDiscountPctg" class="form-label">Discount</label>
                <div class="input-group input-group-sm">
                    <select id="payment-discount-type-0" name="payment-discount-type-0" autocomplete="off"
                        class="form-select" style="max-width: 130px; z-index: 0">
                        <option value="pctg">Percentage</option>
                        <option value="amt">Amount</option>
                    </select>
                    <span id="item-payment-prefix-0" class="input-group-text">Rp</span>
                    <input type="text" style="max-width: 100px" id="payment-total-discount-0"
                        name="payment-total-discount-0" class="form-control" autocomplete="off">
                    <span id="item-payment-suffix-0" class="input-group-text">%</span>
                </div>
            </div>
        </div>
    </form>
    <div class="mt-2 pt-2" id="paymentController">
        <button id="add-payment-btn" class="btn btn-sm btn-success">Add Other Payment (MAX. 3)</button>
        <button id="remove-payment-btn" class="btn btn-sm btn-outline-danger">Remove Payment</button>
    </div>
</div>


@include('payment_js')
