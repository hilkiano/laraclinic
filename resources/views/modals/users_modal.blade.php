<div id="usersModal" class="modal fade" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="usersModalHead">Add User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="usersModalBody">
                <form id="usersForm">
                    <input type="hidden" name="id" value="" id="user_id" />
                    <div class=" row mb-2">
                        <div class="col-sm-6">
                            <label for="username" class="form-label">Username</label>
                            <input placeholder="john.doe" type="text" class="form-control" id="username" name="username" required>
                            <div class="invalid-feedback">This username already taken.</div>
                        </div>
                        <div class="col-sm-6">
                            <label for="name" class="form-label">Full Name</label>
                            <input placeholder="John Doe" type="text" class="form-control" id="name" name="name">
                            <div class="invalid-feedback">Please fill this field.</div>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <label for="email" class="form-label">Email</label>
                            <div class="input-group">
                                <span class="input-group-text" id="basic-addon1"><i class="bi bi-envelope"></i></span>
                                <input placeholder="john_doe@mail.com" type="text" class="form-control" id="email" name="email">
                                <div class="invalid-feedback">Email you entered is not valid.</div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <label for="phone_number" class="form-label">Phone Number</label>
                            <div class="input-group">
                                <span class="input-group-text" id="basic-addon1">+62</span>
                                <input placeholder="81212121212" type="text" class="form-control" id="phone_number" name="phone_number" maxlength="15">
                                <div class="invalid-feedback">Phone number you entered is not valid.</div>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-12">
                            <label for="group" class="form-label">Group</label>
                            <select class="form-select" name="group" id="group" required>
                                <option value="">Please select group...</option>
                                @foreach ($data['groups'] as $group)
                                <option value="{{ $group->id }}">{{ $group->name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">Please fill this field.</div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal" id="usersModalCloseBtn">Cancel</button>
                <button type="submit" form="usersForm" class="btn btn-primary" id="usersModalSubmitBtn">Submit</button>
            </div>
        </div>
    </div>
</div>