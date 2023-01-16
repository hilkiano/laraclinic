<div id="groupsModal" class="modal fade" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="groupsModalHead">Add Group</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="groupsModalBody">
                <form id="groupsForm">
                    <input type="hidden" name="id" value="" id="group_id" />
                    <div class="row gy-2">
                        <div class="col-sm-12">
                            <label for="name" class="form-label">Name</label>
                            <input placeholder="My Group" type="text" class="form-control" id="name" name="name" required>
                            <div class="invalid-feedback">This name is already exist.</div>
                        </div>
                        <div class="col-sm-12">
                            <label for="description" class="form-label">Description</label>
                            <textarea placeholder="Describe this group..." class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                        <div class="col-sm-12">
                            <label for="role_ids" class="form-label">Roles</label>
                            <select multiple placeholder="Select role..." id="role_ids" name="role_ids[]">
                                @foreach ($data["roles"] as $role)
                                <option value="{{ $role->id }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal" id="groupsModalCloseBtn">Cancel</button>
                <button type="submit" form="groupsForm" class="btn btn-primary" id="groupsModalSubmitBtn">Submit</button>
            </div>
        </div>
    </div>
</div>