document.addEventListener('DOMContentLoaded', () => {
    // Set copyright year
    const yearEl = document.getElementById('year');
    if (yearEl) {
        yearEl.textContent = new Date().getFullYear().toString();
    }

    // Admin login button
    const btnAdmin = document.getElementById('btnAdmin');
    btnAdmin.addEventListener('click', async () => {
        btnAdmin.disabled = true;
        btnAdmin.innerHTML = '<div class="spinner-border spinner-border-sm me-2"></div>Logging in as Admin...';
        
        try {
            // Auto-login as admin
            const response = await fetch('../backend/index.php?action=auto-login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ role: 'HR' }),
            });

            const data = await response.json();

            if (data.success || response.ok) {
                showMessage('Redirecting to Admin Dashboard...', 'success');
                setTimeout(() => {
                    window.location.href = 'pages/admin/dashboard.html';
                }, 500);
            } else {
                showMessage('Auto-login failed. Redirecting anyway...', 'warning');
                setTimeout(() => {
                    window.location.href = 'pages/admin/dashboard.html';
                }, 1000);
            }
        } catch (error) {
            console.error('Login error:', error);
            // Redirect anyway for quick access
            showMessage('Redirecting to Admin Dashboard...', 'info');
            setTimeout(() => {
                window.location.href = 'pages/admin/dashboard.html';
            }, 500);
        }
    });

    // Employee login button
    const btnEmployee = document.getElementById('btnEmployee');
    btnEmployee.addEventListener('click', async () => {
        btnEmployee.disabled = true;
        btnEmployee.innerHTML = '<div class="spinner-border spinner-border-sm me-2"></div>Logging in as Employee...';
        
        try {
            // Auto-login as employee
            const response = await fetch('../backend/index.php?action=auto-login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ role: 'EMP' }),
            });

            const data = await response.json();

            if (data.success || response.ok) {
                showMessage('Redirecting to Employee Dashboard...', 'success');
                setTimeout(() => {
                    window.location.href = 'pages/employee/dashboard.html';
                }, 500);
            } else {
                showMessage('Auto-login failed. Redirecting anyway...', 'warning');
                setTimeout(() => {
                    window.location.href = 'pages/employee/dashboard.html';
                }, 1000);
            }
        } catch (error) {
            console.error('Login error:', error);
            // Redirect anyway for quick access
            showMessage('Redirecting to Employee Dashboard...', 'info');
            setTimeout(() => {
                window.location.href = 'pages/employee/dashboard.html';
            }, 500);
        }
    });
});

function showMessage(message, type = 'info') {
    const messageEl = document.getElementById('message');
    const alertClass = type === 'success' ? 'alert-success' : 
                      type === 'warning' ? 'alert-warning' : 'alert-info';
    
    messageEl.innerHTML = `<div class="alert ${alertClass} alert-dismissible fade show" role="alert">
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>`;
}

