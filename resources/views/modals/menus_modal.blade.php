<div id="menusModal" class="modal fade" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="menusModalHead">Add Menu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="menusModalBody">
                <form id="menusForm">
                    <input type="hidden" name="id" value="" id="menu_id" />
                    <div class="row gy-2">
                        <div class="col-sm-12">
                            <label for="name" class="form-label">Name</label>
                            <input placeholder="dashboard" type="text" class="form-control" id="name" name="name" required>
                            <div class="invalid-feedback">This name is already exist.</div>
                        </div>
                        <div class="col-sm-12">
                            <label for="label" class="form-label">Label</label>
                            <input placeholder="Dashboard" type="text" class="form-control" id="label" name="label" required>
                            <div class="invalid-feedback">Please fill this field.</div>
                        </div>
                        <div class="col-sm-12">
                            <label for="icon" class="form-label">Icon</label>
                            <div class="input-group">
                                <span class="input-group-text" id="basic-addon1"><i id="menuIconPreview" class="bi bi-house"></i></span>
                                <input placeholder="bi-house" type="text" class="form-control" id="icon" name="icon">
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <label for="route" class="form-label">Route</label>
                            <div class="input-group">
                                <span class="input-group-text" id="basic-addon1">{{ $data["hostname"] }}</span>
                                <input placeholder="/" type="text" class="form-control" id="route" name="route">
                            </div>
                        </div>
                        <div class="col-sm-12 gy-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="is_parent" name="is_parent">
                                <label class="form-check-label" for="is_parent">Is Parent Menu</label>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <label for="parent" class="form-label">Parent</label>
                            <select class="form-select" name="parent" id="parent">
                                <option value="">Please choose parent...</option>
                                @foreach ($data['parents'] as $parent)
                                <option value="{{ $parent->name }}">{{ $parent->label }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">Please fill this field.</div>
                        </div>
                        <div class="col-sm-12">
                            <label for="order" class="form-label">Order</label>
                            <input placeholder="1" type="text" class="form-control" id="order" name="order" required>
                            <div class="invalid-feedback">Please fill this field.</div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal" id="menusModalCloseBtn">Cancel</button>
                <button type="submit" form="menusForm" class="btn btn-primary" id="menusModalSubmitBtn">Submit</button>
            </div>
        </div>
    </div>
</div>