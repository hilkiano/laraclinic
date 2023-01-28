<div id="rolesModal" class="modal fade" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rolesModalHead">Add Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="rolesModalBody">
                <form id="rolesForm">
                    <input type="hidden" name="id" value="" id="role_id" />
                    <div class="row gy-2">
                        <div class="col-sm-12">
                            <label for="name" class="form-label">Name</label>
                            <input placeholder="Super Admin" type="text" class="form-control" id="name" name="name" required>
                            <div class="invalid-feedback">This name is already exist.</div>
                        </div>
                        <div class="col-sm-12">
                            <label for="description" class="form-label">Description</label>
                            <textarea placeholder="Describe this role..." class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                        <div class="col-sm-12">
                            <label for="menu_ids" class="form-label">Menus</label>
                            <select multiple placeholder="Select menu..." id="menu_ids" name="menu_ids[]">
                                @foreach ($data["menus"] as $menu)
                                <option value="{{ $menu->id }}">{{ "($menu->name) " . $menu->label }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">This field is required.</div>
                        </div>
                        <div class="col-sm-12">
                            <label for="privilege_ids" class="form-label">Privileges</label>
                            <select multiple placeholder="Select privilege..." id="privilege_ids" name="privilege_ids[]">
                                @foreach ($data["privileges"] as $priv)
                                <option value="{{ $priv->id }}">{{ $priv->name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">This field is required.</div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal" id="rolesModalCloseBtn">Cancel</button>
                <button type="submit" form="rolesForm" class="btn btn-primary" id="rolesModalSubmitBtn">Submit</button>
            </div>
        </div>
    </div>
</div>