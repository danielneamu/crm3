<!-- Status History Modal -->
<div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary bg-opacity-10">
                <h5 class="modal-title" id="statusModalLabel">
                    <i class="bi bi-clock-history"></i> Project Status History
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Project Info Banner -->
                <div class="alert alert-info d-flex align-items-center mb-3">
                    <i class="bi bi-info-circle fs-4 me-2"></i>
                    <div>
                        <strong id="statusProjectName"></strong>
                        <br>
                        <small class="text-muted">Project ID: <span id="statusProjectId"></span></small>
                    </div>
                </div>

                <!-- Add Status Button -->
                <button class="btn btn-primary mb-3" type="button" data-bs-toggle="collapse" data-bs-target="#addStatusForm" aria-expanded="false" aria-controls="addStatusForm">
                    <i class="bi bi-plus-circle"></i> Add Status
                </button>

                <!-- Add Status Form (Collapsible) -->
                <div class="collapse mb-3" id="addStatusForm">
                    <div class="card shadow-sm">
                        <div class="card-body bg-light">
                            <form id="statusForm">
                                <input type="hidden" id="statusProjectIdInput" name="project_id">
                                <div class="row g-3">
                                    <div class="col-md-2">
                                        <label class="form-label fw-semibold">Date</label>
                                        <input type="date" class="form-control" name="changed_at" id="statusDate" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label fw-semibold">Status</label>
                                        <select class="form-select" name="status_name" id="statusName" required>
                                            <option value="">Select</option>
                                            <option value="New">New</option>
                                            <option value="Qualifying">Qualifying</option>
                                            <option value="Design">Design</option>
                                            <option value="Completed">Completed</option>
                                            <option value="Pending">Pending</option>
                                            <option value="Contract Signed">Contract Signed</option>
                                            <option value="No Solution">No Solution</option>
                                            <option value="Offer Refused">Offer Refused</option>
                                            <option value="Cancelled">Cancelled</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label fw-semibold">Due Date</label>
                                        <input type="date" class="form-control" name="deadline" id="statusDeadline">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label fw-semibold">Responsible</label>
                                        <select class="form-select" name="responsible_party" id="statusResponsible">
                                            <option value="">Select</option>
                                            <option value="Presales">Presales</option>
                                            <option value="Sales">Sales</option>
                                            <option value="Engineer">Engineer</option>
                                            <option value="Customer">Customer</option>
                                            <option value="Partner">Partner</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Comments</label>
                                        <input type="text" class="form-control" name="comments" id="statusComments" placeholder="Optional notes">
                                    </div>
                                    <div class="col-md-1 d-flex align-items-end gap-1">
                                        <button type="submit" class="btn btn-success" id="btnSaveStatus" title="Save">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger" id="btnCancelStatus" title="Cancel">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Status History Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle" id="statusHistoryTable">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 10%;" class="text-center">Date</th>
                                <th style="width: 15%;" class="text-center">Status</th>
                                <th style="width: 10%;" class="text-center">Due Date</th>
                                <th style="width: 15%;">Responsible</th>
                                <th style="width: 45%;">Comments</th>
                                <th style="width: 5%;" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="statusHistoryBody">
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <div class="spinner-border spinner-border-sm me-2" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    Loading status history...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>