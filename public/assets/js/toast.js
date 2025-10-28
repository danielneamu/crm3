/**
 * Display a global toast notification with progress bar
 * @param {string} title - Toast title
 * @param {string} message - Toast message body
 * @param {string} type - 'success', 'error', 'warning', or 'info'
 * @param {number} delay - Auto-hide delay in ms (default: 5000)
 */
function showToast(title, message, type = 'success', delay = 5000) {
    const toastEl = document.getElementById('globalToast');
    const icon = document.getElementById('toastIcon');
    const header = document.querySelector('#globalToast .toast-header');
    const titleEl = document.getElementById('toastTitle');
    const messageEl = document.getElementById('toastMessage');
    const progressBar = document.getElementById('toastProgressBar');

    // Update content
    titleEl.textContent = title;
    messageEl.textContent = message;

    // Toast type configurations
    const types = {
        success: {
            icon: 'bi-check-circle-fill',
            iconColor: 'text-success',
            headerBg: 'bg-success',
            progressColor: '#198754'
        },
        error: {
            icon: 'bi-x-circle-fill',
            iconColor: 'text-danger',
            headerBg: 'bg-danger',
            progressColor: '#dc3545'
        },
        warning: {
            icon: 'bi-exclamation-triangle-fill',
            iconColor: 'text-warning',
            headerBg: 'bg-warning',
            progressColor: '#ffc107'
        },
        info: {
            icon: 'bi-info-circle-fill',
            iconColor: 'text-info',
            headerBg: 'bg-info',
            progressColor: '#0dcaf0'
        }
    };

    const config = types[type] || types.success;

    // Apply styling
    icon.className = `bi ${config.icon} ${config.iconColor} me-2`;
    header.className = `toast-header ${config.headerBg} bg-opacity-10`;
    progressBar.style.backgroundColor = config.progressColor;

    // Reset progress bar
    progressBar.style.transition = 'none';
    progressBar.style.width = '100%';
    progressBar.offsetHeight; // Force reflow

    setTimeout(() => {
        progressBar.style.transition = `width ${delay}ms linear`;
        progressBar.style.width = '0%';
    }, 10);

    // Show toast
    const toast = new bootstrap.Toast(toastEl, {
        autohide: true,
        delay: delay
    });

    toast.show();

    // Reset on hide
    toastEl.addEventListener('hidden.bs.toast', () => {
        progressBar.style.transition = 'none';
        progressBar.style.width = '100%';
    }, { once: true });
}
