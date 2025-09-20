// notifications.js - Modern notification system for Blood Donation System
class NotificationSystem {
    constructor() {
        this.init();
        this.createToastContainer();
    }

    init() {
        // Inject notification styles
        this.injectStyles();
        // Initialize tooltip system
        this.initTooltips();
    }

    injectStyles() {
        const styles = `
        <style id="notification-styles">
            /* Toast Notifications */
            .toast-container {
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 9999;
                display: flex;
                flex-direction: column;
                gap: 10px;
                max-width: 400px;
            }

            .toast {
                background: white;
                border-radius: 12px;
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
                padding: 16px 20px;
                display: flex;
                align-items: center;
                gap: 12px;
                transform: translateX(100%);
                opacity: 0;
                transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
                border-left: 4px solid #e5e7eb;
                position: relative;
                overflow: hidden;
            }

            .toast.show {
                transform: translateX(0);
                opacity: 1;
            }

            .toast.success {
                border-left-color: #10b981;
                background: linear-gradient(135deg, #ecfdf5 0%, #f0fdf4 100%);
            }

            .toast.error {
                border-left-color: #ef4444;
                background: linear-gradient(135deg, #fef2f2 0%, #fef2f2 100%);
            }

            .toast.warning {
                border-left-color: #f59e0b;
                background: linear-gradient(135deg, #fffbeb 0%, #fefce8 100%);
            }

            .toast.info {
                border-left-color: #3b82f6;
                background: linear-gradient(135deg, #eff6ff 0%, #f0f9ff 100%);
            }

            .toast .icon {
                font-size: 20px;
                min-width: 20px;
            }

            .toast.success .icon {
                color: #10b981;
            }

            .toast.error .icon {
                color: #ef4444;
            }

            .toast.warning .icon {
                color: #f59e0b;
            }

            .toast.info .icon {
                color: #3b82f6;
            }

            .toast .content {
                flex: 1;
            }

            .toast .title {
                font-weight: 600;
                font-size: 14px;
                margin-bottom: 4px;
                color: #1f2937;
            }

            .toast .message {
                font-size: 13px;
                color: #6b7280;
                line-height: 1.4;
            }

            .toast .close {
                background: none;
                border: none;
                font-size: 16px;
                color: #9ca3af;
                cursor: pointer;
                padding: 0;
                margin-left: 8px;
                transition: color 0.2s;
            }

            .toast .close:hover {
                color: #4b5563;
            }

            .toast .progress {
                position: absolute;
                bottom: 0;
                left: 0;
                height: 3px;
                background: currentColor;
                opacity: 0.3;
                transition: width linear;
            }

            /* Inline Form Feedback */
            .form-feedback {
                margin-top: 8px;
                padding: 8px 12px;
                border-radius: 8px;
                font-size: 13px;
                display: flex;
                align-items: center;
                gap: 8px;
                opacity: 0;
                transform: translateY(-10px);
                transition: all 0.2s ease;
            }

            .form-feedback.show {
                opacity: 1;
                transform: translateY(0);
            }

            .form-feedback.success {
                background: #ecfdf5;
                color: #065f46;
                border: 1px solid #a7f3d0;
            }

            .form-feedback.error {
                background: #fef2f2;
                color: #991b1b;
                border: 1px solid #fecaca;
            }

            .form-feedback.warning {
                background: #fffbeb;
                color: #92400e;
                border: 1px solid #fed7aa;
            }

            /* Field Validation States */
            .field-success {
                border-color: #10b981 !important;
                box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1) !important;
            }

            .field-error {
                border-color: #ef4444 !important;
                box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1) !important;
            }

            /* Loading States */
            .btn-loading {
                position: relative;
                color: transparent !important;
            }

            .btn-loading::after {
                content: '';
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                width: 20px;
                height: 20px;
                border: 2px solid #ffffff;
                border-top: 2px solid transparent;
                border-radius: 50%;
                animation: spin 1s linear infinite;
            }

            @keyframes spin {
                0% { transform: translate(-50%, -50%) rotate(0deg); }
                100% { transform: translate(-50%, -50%) rotate(360deg); }
            }

            /* Tooltips */
            .tooltip {
                position: relative;
                display: inline-block;
            }

            .tooltip .tooltip-content {
                visibility: hidden;
                width: 200px;
                background-color: #1f2937;
                color: white;
                text-align: center;
                border-radius: 8px;
                padding: 8px 12px;
                position: absolute;
                z-index: 9999;
                bottom: 125%;
                left: 50%;
                margin-left: -100px;
                opacity: 0;
                transition: opacity 0.2s, visibility 0.2s;
                font-size: 12px;
                line-height: 1.3;
            }

            .tooltip .tooltip-content::after {
                content: '';
                position: absolute;
                top: 100%;
                left: 50%;
                margin-left: -5px;
                border-width: 5px;
                border-style: solid;
                border-color: #1f2937 transparent transparent transparent;
            }

            .tooltip:hover .tooltip-content {
                visibility: visible;
                opacity: 1;
            }

            /* Form Processing Overlay */
            .form-overlay {
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(255, 255, 255, 0.8);
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: inherit;
                opacity: 0;
                visibility: hidden;
                transition: all 0.2s ease;
            }

            .form-overlay.show {
                opacity: 1;
                visibility: visible;
            }

            .form-overlay .spinner {
                width: 40px;
                height: 40px;
                border: 4px solid #e5e7eb;
                border-top: 4px solid #dc2626;
                border-radius: 50%;
                animation: spin 1s linear infinite;
            }

            /* Mobile Responsive */
            @media (max-width: 640px) {
                .toast-container {
                    left: 20px;
                    right: 20px;
                    max-width: none;
                }

                .toast {
                    transform: translateY(-100%);
                }

                .toast.show {
                    transform: translateY(0);
                }
            }
        </style>
        `;

        if (!document.getElementById('notification-styles')) {
            document.head.insertAdjacentHTML('beforeend', styles);
        }
    }

    createToastContainer() {
        if (!document.querySelector('.toast-container')) {
            const container = document.createElement('div');
            container.className = 'toast-container';
            document.body.appendChild(container);
        }
    }

    showToast(type, title, message, duration = 5000) {
        const container = document.querySelector('.toast-container');
        const toast = document.createElement('div');

        const icons = {
            success: 'fas fa-check-circle',
            error: 'fas fa-exclamation-circle',
            warning: 'fas fa-exclamation-triangle',
            info: 'fas fa-info-circle'
        };

        toast.className = `toast ${type}`;
        toast.innerHTML = `
            <div class="icon">
                <i class="${icons[type]}"></i>
            </div>
            <div class="content">
                <div class="title">${title}</div>
                <div class="message">${message}</div>
            </div>
            <button class="close">
                <i class="fas fa-times"></i>
            </button>
            <div class="progress"></div>
        `;

        container.appendChild(toast);

        // Show toast
        setTimeout(() => toast.classList.add('show'), 100);

        // Progress bar animation
        const progress = toast.querySelector('.progress');
        progress.style.width = '100%';
        setTimeout(() => {
            progress.style.width = '0%';
            progress.style.transition = `width ${duration}ms linear`;
        }, 200);

        // Auto remove
        const removeToast = () => {
            toast.classList.remove('show');
            setTimeout(() => {
                if (toast.parentNode) {
                    container.removeChild(toast);
                }
            }, 300);
        };

        // Close button
        toast.querySelector('.close').addEventListener('click', removeToast);

        // Auto remove after duration
        if (duration > 0) {
            setTimeout(removeToast, duration);
        }

        return toast;
    }

    showFieldFeedback(field, type, message) {
        // Remove existing feedback
        const existingFeedback = field.parentNode.querySelector('.form-feedback');
        if (existingFeedback) {
            existingFeedback.remove();
        }

        // Remove field states
        field.classList.remove('field-success', 'field-error');

        if (message) {
            // Add field state
            field.classList.add(`field-${type}`);

            // Create feedback element
            const feedback = document.createElement('div');
            feedback.className = `form-feedback ${type}`;

            const icons = {
                success: 'fas fa-check',
                error: 'fas fa-exclamation-triangle',
                warning: 'fas fa-exclamation'
            };

            feedback.innerHTML = `
                <i class="${icons[type]}"></i>
                <span>${message}</span>
            `;

            field.parentNode.appendChild(feedback);

            // Show with animation
            setTimeout(() => feedback.classList.add('show'), 50);
        }
    }

    setFormLoading(form, loading = true) {
        const submitBtn = form.querySelector('button[type="submit"]');
        const overlay = form.querySelector('.form-overlay');

        if (loading) {
            // Add loading state to button
            if (submitBtn) {
                submitBtn.classList.add('btn-loading');
                submitBtn.disabled = true;
            }

            // Show overlay if exists
            if (overlay) {
                overlay.classList.add('show');
            } else {
                // Create overlay
                const newOverlay = document.createElement('div');
                newOverlay.className = 'form-overlay show';
                newOverlay.innerHTML = '<div class="spinner"></div>';
                form.style.position = 'relative';
                form.appendChild(newOverlay);
            }
        } else {
            // Remove loading state
            if (submitBtn) {
                submitBtn.classList.remove('btn-loading');
                submitBtn.disabled = false;
            }

            // Hide overlay
            const currentOverlay = form.querySelector('.form-overlay');
            if (currentOverlay) {
                currentOverlay.classList.remove('show');
                setTimeout(() => {
                    if (currentOverlay.parentNode) {
                        currentOverlay.remove();
                    }
                }, 200);
            }
        }
    }

    addTooltip(element, text) {
        element.classList.add('tooltip');

        const existingTooltip = element.querySelector('.tooltip-content');
        if (existingTooltip) {
            existingTooltip.remove();
        }

        const tooltip = document.createElement('span');
        tooltip.className = 'tooltip-content';
        tooltip.textContent = text;
        element.appendChild(tooltip);
    }

    initTooltips() {
        // Auto-initialize tooltips with data-tooltip attribute
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('[data-tooltip]').forEach(element => {
                this.addTooltip(element, element.getAttribute('data-tooltip'));
            });
        });
    }

    // Validation helpers
    validateField(field, rules) {
        const value = field.value.trim();
        let isValid = true;
        let message = '';

        if (rules.required && !value) {
            isValid = false;
            message = `${rules.label || 'This field'} is required`;
        } else if (rules.email && value && !this.isValidEmail(value)) {
            isValid = false;
            message = 'Please enter a valid email address';
        } else if (rules.minLength && value.length < rules.minLength) {
            isValid = false;
            message = `${rules.label || 'This field'} must be at least ${rules.minLength} characters`;
        } else if (rules.phone && value && !this.isValidPhone(value)) {
            isValid = false;
            message = 'Please enter a valid phone number';
        }

        if (isValid && value) {
            this.showFieldFeedback(field, 'success', '✓ Looks good');
        } else if (!isValid) {
            this.showFieldFeedback(field, 'error', message);
        } else {
            this.showFieldFeedback(field, '', '');
        }

        return { isValid, message };
    }

    isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    isValidPhone(phone) {
        return /^[\+]?[1-9][\d]{0,15}$/.test(phone.replace(/[\s\-\(\)]/g, ''));
    }
}

// Initialize global notification system
window.notifications = new NotificationSystem();

// Export for module use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = NotificationSystem;
}
