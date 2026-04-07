<!-- Partner Modal -->
<div class="modal fade" id="partnerModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="partnerModalLabel">Add Partner</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="partnerForm">
                    <input type="hidden" id="partnerId" name="id_parteneri">

                    <!-- Partner Info Section -->
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label for="partnerName" class="form-label">Partner Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="partnerName" name="name_parteneri" required>
                        </div>
                        <div class="col-md-4">
                            <label for="partnerType" class="form-label">Type</label>
                            <input type="text" class="form-control" id="partnerType" name="type_parteneri" list="typesList">
                            <datalist id="typesList"></datalist>
                        </div>
                    </div>

                    <!-- Tags Section -->
                    <div class="mb-3">
                        <label class="form-label">Tags</label>
                        <div id="tagsContainer" class="border rounded p-2" style="min-height: 60px;">
                            <!-- Tag badges will be added here -->
                        </div>
                        <select class="form-select form-select-sm mt-2" id="tagSelect">
                            <option value="">+ Add tag</option>
                        </select>
                    </div>

                    <!-- Contacts Section -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label mb-0">Contacts</label>
                            <button type="button" class="btn btn-sm btn-success" id="btnAddContact">
                                <i class="bi bi-plus-circle"></i> Add Contact
                            </button>
                        </div>
                        <table class="table table-sm table-bordered" id="contactsTable">
                            <thead>
                                <tr>
                                    <th style="width: 20%">Name</th>
                                    <th style="width: 15%">Role</th>
                                    <th style="width: 15%">Phone</th>
                                    <th style="width: 20%">Email</th>
                                    <th style="width: 25%">Comments</th>
                                    <th style="width: 5%"></th>
                                </tr>
                            </thead>
                            <tbody id="contactsBody">
                                <!-- Contact rows will be added here -->
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btnSavePartner">Save Partner</button>
            </div>
        </div>
    </div>
</div>


<!-- Tag Management Modal -->
<div class="modal fade" id="tagModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tagModalLabel">Manage Tags</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="tagForm" class="mb-3">
                    <input type="hidden" id="tagId">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <input type="text" class="form-control form-control-sm" id="tagName" placeholder="Tag name" required>
                        </div>
                        <div class="col-md-6">
                            <input type="text" class="form-control form-control-sm" id="tagComment" placeholder="Description">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-sm btn-success w-100">
                                <i class="bi bi-plus-circle"></i>
                            </button>
                        </div>
                    </div>
                </form>

                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Tag</th>
                            <th>Description</th>
                            <th style="width: 80px"></th>
                        </tr>
                    </thead>
                    <tbody id="tagsListBody">
                        <!-- Tags will be loaded here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>