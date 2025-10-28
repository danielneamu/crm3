            </div>
            <!-- Global Toast Notification -->
            <div class="toast-container position-fixed top-0 start-50 translate-middle-x p-3">
                <div id="globalToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="toast-header">
                        <i class="bi bi-check-circle-fill text-success me-2" id="toastIcon" aria-hidden="true"></i>
                        <strong class="me-auto" id="toastTitle">Notification</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                    <div class="toast-body" id="toastMessage">
                        <!-- Message will be inserted here -->
                    </div>
                    <div class="toast-progress-container">
                        <div id="toastProgressBar"></div>
                    </div>
                </div>
            </div>
            
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

            <!-- Global Toast Function -->
            <script src="../assets/js/toast.js"></script>

            </body>

            </html>