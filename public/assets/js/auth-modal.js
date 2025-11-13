// ═══════════════════════════════════════════════════════════
// AUTH MODAL - Login/Register Toggle
// ═══════════════════════════════════════════════════════════

const AuthModal = {
    modal: null,
    loginForm: null,
    registerForm: null,
    modalTitle: null,

    init() {
        this.modal = document.getElementById('auth-modal');
        this.loginForm = document.getElementById('login-form');
        this.registerForm = document.getElementById('register-form');
        this.modalTitle = document.getElementById('auth-modal-title');

        if (!this.modal) return;

        // Open modal button
        document.getElementById('open-auth-modal')?.addEventListener('click', () => {
            this.openModal('login');
        });

        // Close modal button
        document.getElementById('close-auth-modal')?.addEventListener('click', () => {
            this.closeModal();
        });

        // Close on overlay click
        this.modal.addEventListener('click', (e) => {
            if (e.target === this.modal) {
                this.closeModal();
            }
        });

        // Close on ESC key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.modal.classList.contains('active')) {
                this.closeModal();
            }
        });

        // Toggle to register
        document.getElementById('show-register')?.addEventListener('click', (e) => {
            e.preventDefault();
            this.showRegisterForm();
        });

        // Toggle to login
        document.getElementById('show-login')?.addEventListener('click', (e) => {
            e.preventDefault();
            this.showLoginForm();
        });

        // Handle login form submission
        this.loginForm?.addEventListener('submit', (e) => {
            e.preventDefault();
            this.handleLoginSubmit();
        });

        // Handle register form submission
        this.registerForm?.addEventListener('submit', (e) => {
            e.preventDefault();
            this.handleRegisterSubmit();
        });
    },

    openModal(mode = 'login') {
        this.modal.classList.add('active');
        document.body.style.overflow = 'hidden'; // Prevent background scroll

        if (mode === 'login') {
            this.showLoginForm();
        } else {
            this.showRegisterForm();
        }

        // Focus first input
        setTimeout(() => {
            const firstInput = mode === 'login'
                ? document.getElementById('modal-login')
                : document.getElementById('modal-username');
            firstInput?.focus();
        }, 100);
    },

    closeModal() {
        this.modal.classList.remove('active');
        document.body.style.overflow = ''; // Restore scroll
        this.clearErrors();
        this.clearInputs();
    },

    showLoginForm() {
        this.loginForm.style.display = 'block';
        this.registerForm.style.display = 'none';
        this.modalTitle.textContent = 'Přihlášení';
        this.clearErrors();
    },

    showRegisterForm() {
        this.loginForm.style.display = 'none';
        this.registerForm.style.display = 'block';
        this.modalTitle.textContent = 'Registrace';
        this.clearErrors();
    },

    clearErrors() {
        // Clear all error messages
        document.querySelectorAll('.auth-form-modal .error').forEach(el => {
            el.textContent = '';
        });

        // Remove error state from inputs
        document.querySelectorAll('.auth-form-modal input').forEach(input => {
            input.style.borderColor = '';
        });
    },

    clearInputs() {
        // Clear all form inputs
        this.loginForm?.reset();
        this.registerForm?.reset();
    },

    showError(fieldId, message) {
        // Determine which form is active
        const isRegister = this.registerForm.style.display !== 'none';

        // Map field IDs for register form
        let errorId = fieldId;
        let inputId = fieldId;

        if (isRegister) {
            // For register form, prefix with 'register-'
            if (!fieldId.startsWith('register-')) {
                errorId = `register-${fieldId}`;
            }
        } else {
            // For login form, use 'login-password' instead of just 'password'
            if (fieldId === 'password') {
                errorId = 'login-password';
                inputId = 'login-password';
            } else if (fieldId === 'login') {
                inputId = 'login';
                errorId = 'login';
            }
        }

        const errorElement = document.getElementById(`${errorId}-error`);
        const inputElement = document.getElementById(`modal-${inputId}`);

        if (errorElement) {
            errorElement.textContent = message;
        }

        if (inputElement) {
            inputElement.style.borderColor = 'var(--danger)';
        }
    },

    async handleLoginSubmit() {
        this.clearErrors();

        const formData = new FormData(this.loginForm);
        const data = {
            login: formData.get('login'),
            password: formData.get('password')
        };

        // Basic validation
        if (!data.login || !data.password) {
            if (!data.login) this.showError('login', 'Toto pole je povinné');
            if (!data.password) this.showError('login-password', 'Toto pole je povinné');
            return;
        }

        // Disable submit button
        const submitBtn = this.loginForm.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = 'Přihlašuji...';

        try {
            const response = await fetch(`${window.BASE_URL}/login`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new URLSearchParams(data)
            });

            const result = await response.json();

            if (result.success) {
                // Successful login, redirect
                window.location.href = result.redirect;
            } else {
                // Show error message
                this.showError('login', result.error || 'Došlo k chybě při přihlašování');
            }

        } catch (error) {
            console.error('Login error:', error);
            this.showError('login', 'Došlo k chybě při přihlašování');
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    },

    async handleRegisterSubmit() {
        this.clearErrors();

        const formData = new FormData(this.registerForm);
        const data = {
            username: formData.get('username'),
            email: formData.get('email'),
            password: formData.get('password'),
            password_confirm: formData.get('password_confirm')
        };

        // Basic validation
        let hasError = false;

        if (!data.username) {
            this.showError('username', 'Toto pole je povinné');
            hasError = true;
        } else if (data.username.length < 3) {
            this.showError('username', 'Uživatelské jméno musí mít alespoň 3 znaky');
            hasError = true;
        }

        if (!data.email) {
            this.showError('email', 'Toto pole je povinné');
            hasError = true;
        }

        if (!data.password) {
            this.showError('register-password', 'Toto pole je povinné');
            hasError = true;
        } else if (data.password.length < 6) {
            this.showError('register-password', 'Heslo musí mít alespoň 6 znaků');
            hasError = true;
        }

        if (!data.password_confirm) {
            this.showError('password-confirm', 'Toto pole je povinné');
            hasError = true;
        } else if (data.password !== data.password_confirm) {
            this.showError('password-confirm', 'Hesla se neshodují');
            hasError = true;
        }

        if (hasError) return;

        // Disable submit button
        const submitBtn = this.registerForm.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = 'Registruji...';

        try {
            const response = await fetch(`${window.BASE_URL}/register`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new URLSearchParams(data)
            });

            const result = await response.json();

            if (result.success) {
                // Successful registration
                window.toast?.success(result.message || 'Registrace úspěšná! Můžete se přihlásit.');
                this.clearInputs();
                this.showLoginForm();
            } else {
                // Show error messages
                if (result.errors) {
                    // Multiple errors from validation
                    Object.keys(result.errors).forEach(field => {
                        const errorField = field === 'password_confirm' ? 'password-confirm' : field;
                        this.showError(errorField, result.errors[field]);
                    });
                } else if (result.error) {
                    // Single error message
                    this.showError('username', result.error);
                }
            }

        } catch (error) {
            console.error('Registration error:', error);
            this.showError('username', 'Došlo k chybě při registraci');
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    }
};

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    AuthModal.init();
});

// Export for use in other scripts
window.AuthModal = AuthModal;
