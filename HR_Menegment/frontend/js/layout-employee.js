// Employee Layout Script
document.addEventListener('DOMContentLoaded', async () => {
    // Check session
    try {
        const session = await API.checkSession();
        if (!session.authenticated || session.role !== 'EMP') {
            window.location.href = '../../index.html';
            return;
        }
    } catch (e) {
        window.location.href = '../../index.html';
        return;
    }

    // Logout button
    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', async () => {
            try {
                await API.logout();
            } catch (e) {
                // ignore
            }
            window.location.href = '../../index.html';
        });
    }

    // Load unread notification count
    try {
        const count = await API.getUnreadCount();
        const badge = document.getElementById('notificationBadge');
        if (badge && count.count > 0) {
            badge.textContent = count.count;
            badge.style.display = 'inline-block';
        }
    } catch (e) {
        // ignore
    }

    // Highlight active menu item
    const currentPage = window.location.pathname.split('/').pop();
    document.querySelectorAll('.list-group-item').forEach(item => {
        if (item.getAttribute('href') === currentPage) {
            item.classList.add('active');
        } else {
            item.classList.remove('active');
        }
    });
});
