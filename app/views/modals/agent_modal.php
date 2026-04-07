<!-- Agent Modal -->
<div class="modal fade" id="agentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="agentModalLabel">Add Agent</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="agentForm">
                    <input type="hidden" id="agentId" name="id_agent">
                    <input type="hidden" id="oldTeam" name="old_team">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="agentName" class="form-label">Agent Name *</label>
                            <input type="text" class="form-control" id="agentName" name="nume_agent" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="agentCode" class="form-label">Agent Code *</label>
                            <input type="text" class="form-control" id="agentCode" name="cod_agent" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="currentTeam" class="form-label">Team *</label>
                            <select class="form-select" id="currentTeam" name="current_team" required>
                                <option value="">Select Team</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label d-block">Status</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="statusAgent" name="status_agent" value="1" checked>
                                <label class="form-check-label" for="statusAgent">Active</label>
                            </div>
                        </div>
                    </div>

                    <!-- Team Change Fields (shown only when editing and team changes) -->
                    <div id="teamChangeFields" style="display: none;">
                        <hr>
                        <h6 class="text-warning"><i class="bi bi-exclamation-triangle"></i> Team Change Detected</h6>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="effectiveDate" class="form-label">Effective Date *</label>
                                <input type="date" class="form-control" id="effectiveDate" name="effective_date">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="changeNotes" class="form-label">Reason for Change</label>
                            <textarea class="form-control" id="changeNotes" name="change_notes" rows="2" placeholder="e.g., Promotion, Transfer, Restructure"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btnSaveAgent">Save Agent</button>
            </div>
        </div>
    </div>
</div>