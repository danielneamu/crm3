<div class="modal fade" id="projectModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-light border-bottom">
                <h5 class="modal-title fw-semibold" id="projectModalLabel">
                    <i class="bi bi-folder-plus me-2"></i>Add Project
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="projectForm">
                    <input type="hidden" id="projectId" name="id_project">

                    <!-- Basic Info Section -->
                    <div class="mb-3 pb-3 border-bottom">
                        <h6 class="mb-2 text-primary fw-semibold section-title">
                            <i class="bi bi-info-circle me-2"></i>Basic Information
                        </h6>

                        <!-- Row 1: Project & Company -->
                        <div class="row g-2 mb-2">
                            <div class="col-md-5">
                                <label class="form-label small">Project Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control tall-input" id="projectName" name="name_project" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small">Company <span class="text-danger">*</span></label>
                                <select class="form-select tall-input" id="company" name="company_project" required>
                                    <option value="">Select Company</option>
                                </select>
                            </div>
                        </div>

                        <!-- Row 2: Date, TCV, Duration -->
                        <div class="row g-2 mb-2">
                            <div class="col-md-3">
                                <label for="createDate" class="form-label small">Creation Date <span class="text-danger">*</span></label>
                                <input type="text" class="form-control tall-input" id="createDate" name="createDate_project" placeholder="dd-mm-yyyy" required readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">TCV (€)</label>
                                <input type="number" class="form-control tall-input" id="tcv" name="tcv_project">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Duration (months)</label>
                                <select class="form-select tall-input" id="contractDuration" name="contract_project">
                                    <option value="">Select</option>
                                    <option value="1" selected>1</option>
                                    <option value="12">12</option>
                                    <option value="24">24</option>
                                    <option value="36">36</option>
                                    <option value="48">48</option>
                                    <option value="60">60</option>
                                </select>
                            </div>
                        </div>

                        <!-- Row 3: Type & Partners -->
                        <div class="row g-2">
                            <div class="col-md-3">
                                <label class="form-label small">Project Type</label>
                                <select class="form-select tall-input" id="projectType" name="project_type">
                                    <option value="">Select Type</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">Partners</label>
                                <select class="form-select tall-input" id="partners" name="partners[]" multiple size="1">
                                    <!-- Options loaded via JS -->
                                </select>
                                <small class="form-text text-muted xs-text">Hold Ctrl/Cmd to select multiple</small>
                            </div>
                        </div>
                    </div>

                    <!-- Team Assignment Section -->
                    <div class="mb-3 pb-3 border-bottom">
                        <h6 class="mb-2 text-primary fw-semibold section-title">
                            <i class="bi bi-people me-2"></i>Team Assignment
                        </h6>
                        <div class="row g-2">
                            <div class="col-md-3">
                                <label class="form-label small">Team <span class="text-danger">*</span></label>
                                <select class="form-select tall-input" id="team" name="team" required>
                                    <option value="">Select Team</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Agent <span class="text-danger">*</span></label>
                                <select class="form-select tall-input" id="agent" name="agent_project" required disabled>
                                    <option value="">Select Team First</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <small class="form-text text-muted xs-text d-block" style="margin-top: 2rem;">
                                    <i class="bi bi-info-circle-fill me-1"></i>Select a team to enable agent selection
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- References Section -->
                    <div class="mb-3 pb-3 border-bottom">
                        <h6 class="mb-2 text-primary fw-semibold section-title">
                            <i class="bi bi-link-45deg me-2"></i>References
                        </h6>
                        <div class="row g-2">
                            <div class="col-md-2">
                                <label class="form-label small">PT</label>
                                <input type="text" class="form-control tall-input" id="pt" name="eft_command">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small">SD</label>
                                <input type="text" class="form-control tall-input" id="sd" name="solution_dev_number">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small">EFT</label>
                                <input type="text" class="form-control tall-input" id="eft" name="eft_case">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">SFDC</label>
                                <input type="text" class="form-control tall-input" id="sfdc" name="sfdc_opp">
                            </div>
                        </div>
                    </div>

                    <!-- Comments Section -->
                    <div class="mb-3">
                        <h6 class="mb-2 text-primary fw-semibold section-title">
                            <i class="bi bi-chat-left-text me-2"></i>Additional Notes
                        </h6>
                        <div class="row g-2">
                            <div class="col-md-9">
                                <label for="commentProject" class="form-label small">Comments</label>

                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <small class="form-text text-muted xs-text">Use log entries to track updates</small>
                                    <button type="button" id="addLogLine" class="btn btn-outline-secondary btn-sm py-0 px-2">
                                        <i class="bi bi-plus-circle me-1"></i>Add Log Entry
                                    </button>
                                </div>

                                <textarea class="form-control tall-input" id="commentProject" name="comment_project" rows="3"
                                    placeholder="Add any notes or remarks about this project..."></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="toggle-wrapper bg-light p-2 rounded d-flex align-items-center">
                        <label class="toggle me-2">
                            <input type="checkbox" id="active" name="active_project" value="1" checked>
                            <span class="slider"></span>
                        </label>
                        <span class="fw-semibold small" id="activeStatusText">
                            <i class="bi bi-check-circle me-1"></i>Active Project
                        </span>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light justify-content-center">
                <button type="button" class="btn btn-outline-danger btn-md px-4" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-outline-success btn-md px-4" id="btnSaveProject">
                    <i class="bi bi-save me-1"></i>Save Project
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .modal-header {
        padding: 1rem 1.25rem;
    }

    .form-label.small {
        font-size: 0.8rem;
        font-weight: 500;
        margin-bottom: 0.25rem;
    }

    .section-title {
        font-size: 0.85rem;
    }

    .xs-text {
        font-size: 0.75rem;
    }

    /* All inputs and selects have same height and readable text */
    .form-control.tall-input,
    .form-select.tall-input {
        height: 2.75rem;
        font-size: 0.95rem;
        padding: 0.5rem 0.75rem;
        line-height: 1.5;
    }

    textarea.form-control.tall-input {
        height: auto;
        min-height: 5rem;
    }

    .form-control:disabled,
    .form-select:disabled {
        background-color: #f8f9fa;
        opacity: 0.7;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
    }

    .border-bottom {
        border-bottom: 1px solid #dee2e6 !important;
    }

    /* Make modal content use more width inside modal-lg */
    #projectModal .modal-dialog {
        max-width: 1000px;
        /* widen slightly beyond modal-lg default (800px) */
    }

    #projectModal .modal-content {
        border-radius: 0.75rem;
    }

    /* Reduce inner padding of modal body to reclaim space */
    #projectModal .modal-body {
        padding-left: 2rem !important;
        padding-right: 2rem !important;
    }

    /* Ensure form rows stretch fully */
    #projectModal form .row>[class^="col-"] {
        flex: 1 1 0;
        /* stretch evenly */
    }

    /* Optional: slightly reduce margins for compactness */
    #projectModal .row.g-2 {
        margin-left: 0;
        margin-right: 0;
    }

    /***************************/
    /* Custom toggle switch */
    /**************************/
    .toggle {
        position: relative;
        display: inline-block;
        width: 45px;
        height: 24px;
    }

    .toggle input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #dee2e6;
        border-radius: 34px;
        transition: 0.4s;
    }

    .slider::before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        border-radius: 50%;
        transition: 0.4s;
    }

    .toggle input:checked+.slider {
        background-color: #0d6efd;
    }

    .toggle input:checked+.slider::before {
        transform: translateX(21px);
    }


    /*********************/
    /* Bottom buttons */
    /*********************/

    /* Center footer buttons and make them visually balanced */
    #projectModal .modal-footer {
        justify-content: center;
        gap: 1rem;
        /* space between buttons */
        padding-top: 1rem;
        padding-bottom: 1rem;
    }

    /* Make buttons slightly larger with rounded corners */
    #projectModal .btn {
        font-size: 0.95rem;
        padding: 0.6rem 1.4rem;
        border-radius: 0.4rem;
        transition: all 0.2s ease;
    }

    /* Subtle hover effects for outline buttons */
    #projectModal .btn-outline-success:hover {
        background-color: #198754;
        color: #fff;
    }

    #projectModal .btn-outline-danger:hover {
        background-color: #dc3545;
        color: #fff;
    }

    .log-entry textarea {
        font-size: 0.9rem;
        resize: vertical;
    }
</style>


    // Example: Toggle active status text based on checkbox
    <script defer >
        window.addEventListener('load', function() {
            if (typeof jQuery !== 'undefined') {
                $('#addLogLine').on('click', function() {
                    const textarea = $('#commentProject');
                    const now = new Date();

                    const timestamp = now.toLocaleString('en-GB', {
                        day: '2-digit',
                        month: '2-digit',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    }).replace(',', '');

                    const newLine = `${timestamp} - `;
                    const currentText = textarea.val();
                    const updatedText = currentText ? `${currentText}\n${newLine}` : newLine;

                    textarea.val(updatedText);
                    textarea.focus();
                    textarea[0].setSelectionRange(textarea.val().length, textarea.val().length);
                });
            } else {
                console.error('jQuery not found!');
            }
        });
</script>
