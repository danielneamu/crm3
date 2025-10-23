<div class="modal fade" id="projectModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="projectModalLabel">Add Project</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="projectForm">
                    <input type="hidden" id="projectId" name="id_project">

                    <!-- Basic Info Section -->
                    <h6 class="mb-3">Basic Information</h6>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Company *</label>
                            <select class="form-select" id="company" name="company_project" required>
                                <option value="">Select Company</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Project Name *</label>
                            <input type="text" class="form-control" id="projectName" name="name_project" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label for="createDate" class="form-label">Creation Date <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="createDate" name="createDate_project" placeholder="dd-mm-yyyy" required readonly>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label">Team *</label>
                            <select class="form-select" id="team" name="team" required>
                                <option value="">Select Team</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Agent *</label>
                            <select class="form-select" id="agent" name="agent_project" required disabled>
                                <option value="">Select Team First</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Project Type</label>
                            <select class="form-select" id="projectType" name="project_type">
                                <option value="">Select Type</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">TCV (€)</label>
                            <input type="number" class="form-control" id="tcv" name="tcv_project">
                        </div>
                    </div>

                    <div class="row mb-4">

                        <div class="col-md-9">
                            <label class="form-label">Partners</label>
                            <select class="form-select" id="partners" name="partners[]" multiple>
                                <!-- Options loaded via JS -->
                            </select>
                            <small class="form-text text-muted">Search and select multiple partners</small>
                        </div>


                        <div class="col-md-3">
                            <label class="form-label">Contract Duration (months)</label>
                            <select class="form-select" id="contractDuration" name="contract_project">
                                <option value="">Select Duration</option>
                                <option value="1" selected>1 </option>
                                <option value="12">12</option>
                                <option value="24">24</option>
                                <option value="36">36</option>
                                <option value="48">48</option>
                                <option value="60">60</option>
                            </select>
                        </div>
                    </div>

                    <!-- References Section -->
                    <h6 class="mb-3">References</h6>
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <label class="form-label">PT</label>
                            <input type="text" class="form-control" id="pt" name="eft_command">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">SD</label>
                            <input type="text" class="form-control" id="sd" name="solution_dev_number">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">EFT</label>
                            <input type="text" class="form-control" id="eft" name="eft_case">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">SFDC</label>
                            <input type="text" class="form-control" id="sfdc" name="sfdc_opp">
                        </div>
                    </div>

                    <!-- Comments Section -->
                    <h6 class="mb-3">Comments</h6>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="commentProject" class="form-label">Comment / Notes</label>
                            <textarea class="form-control" id="commentProject" name="comment_project" rows="3"
                                placeholder="Add any notes or remarks about this project..."></textarea>
                            <small class="text-muted">General project notes and comments</small>
                        </div>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="active" name="active_project" value="1" checked>
                        <label class="form-check-label" for="active">Active Project</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btnSaveProject">Save Project</button>
            </div>
        </div>
    </div>
</div>