<?php
/**
 * Session Timer Component
 * Shows actual time remaining until PHP session expires
 * 
 * Usage: require_once '../includes/components/session-timer/session-timer.php';
 */

// Track session start time if not already set
if (!isset($_SESSION['session_created'])) {
    $_SESSION['session_created'] = time();
}

// Get session lifetime and calculate actual expiry time
$sessionLifetime = ini_get('session.gc_maxlifetime');
$sessionCreated = $_SESSION['session_created'];
$sessionExpires = $sessionCreated + $sessionLifetime;
$timeRemaining = $sessionExpires - time();

$timeSinceRegen = isset($_SESSION['last_regeneration']) ? time() - $_SESSION['last_regeneration'] : 0;

?>

<!-- Session Timer HTML -->
<div id="sessionTimer" class="session-timer">
    <i class="bi bi-clock-history"></i>
    <span id="sessionCountdown">--:--</span>
    <span id="regenTimer"></span>
</div>

<!-- Session Timer Styles & Script (Self-Contained) -->
<style>
    /* Session Timer Component Styles */
    .session-timer {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: rgba(255, 255, 255, 0.95);
        border: 2px solid #dee2e6;
        border-radius: 8px;
        padding: 10px 16px;
        font-size: 14px;
        font-weight: 500;
        color: #495057;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        z-index: 9998;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
    }

    .session-timer i {
        font-size: 18px;
        color: #0d6efd;
    }

    .session-timer.warning {
        border-color: #ffc107;
        background: rgba(255, 243, 205, 0.95);
    }

    .session-timer.warning i {
        color: #ffc107;
        animation: sessionTimerPulse 1.5s ease-in-out infinite;
    }

    .session-timer.critical {
        border-color: #dc3545;
        background: rgba(255, 193, 193, 0.95);
    }

    .session-timer.critical i {
        color: #dc3545;
        animation: sessionTimerPulse 0.8s ease-in-out infinite;
    }

    @keyframes sessionTimerPulse {

        0%,
        100% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.15);
        }
    }

    @media (max-width: 768px) {
        .session-timer {
            bottom: 10px;
            right: 10px;
            font-size: 12px;
            padding: 8px 12px;
        }
    }

    @media (max-width: 576px) {
        .session-timer {
            padding: 8px;
        }

        .session-timer #sessionCountdown {
            display: none;
        }
    }
</style>

<script>
    (function() {
        'use strict';

        let remainingSeconds = <?= max(0, $timeRemaining) ?>;
        let sinceRegenSeconds = <?= max(0, $timeSinceRegen) ?>;

        const WARNING_THRESHOLD = 10 * 60; // 10 min
        const CRITICAL_THRESHOLD = 2 * 60; // 2 min

        const timerElement = document.getElementById('sessionCountdown');
        let regenElement = document.getElementById('regenCountdown');

        if (!regenElement) {
            // Add a small span below the main timer for regen
            regenElement = document.createElement('div');
            regenElement.id = 'regenCountdown';
            regenElement.style.fontSize = '12px';
            regenElement.style.color = '#6c757d';
            timerElement.parentNode.appendChild(regenElement);
        }

        function formatTime(seconds) {
            const hours = Math.floor(seconds / 3600);
            const minutes = Math.floor((seconds % 3600) / 60);
            const secs = seconds % 60;
            const pad = (n) => n.toString().padStart(2, '0');
            return hours > 0 ? `${hours}:${pad(minutes)}:${pad(secs)}` : `${minutes}:${pad(secs)}`;
        }

        function updateTimer() {
            if (remainingSeconds > 0) {
                remainingSeconds--;
                timerElement.textContent = formatTime(remainingSeconds);

                // Update styles
                timerElement.parentNode.classList.remove('warning', 'critical');
                if (remainingSeconds <= CRITICAL_THRESHOLD) {
                    timerElement.parentNode.classList.add('critical');
                } else if (remainingSeconds <= WARNING_THRESHOLD) {
                    timerElement.parentNode.classList.add('warning');
                }
            } else {
                timerElement.textContent = '0:00';
                timerElement.parentNode.classList.add('critical');
                // Optional: redirect or show toast
            }

            // Update regen counter
            sinceRegenSeconds++;
            regenElement.textContent = 'Since last regen: ' + formatTime(sinceRegenSeconds);
        }

        setInterval(updateTimer, 1000);
        updateTimer();
    })();
</script>