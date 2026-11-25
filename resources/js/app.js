// Mobile sidebar drawer
document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('sidebar');
    const backdrop = document.getElementById('sidebar-backdrop');
    const toggles = document.querySelectorAll('[data-sidebar-toggle]');

    const setOpen = (open) => {
        if (!sidebar) return;
        sidebar.classList.toggle('-translate-x-full', !open);
        backdrop?.classList.toggle('hidden', !open);
    };

    toggles.forEach((btn) => btn.addEventListener('click', () => setOpen(sidebar.classList.contains('-translate-x-full'))));
    backdrop?.addEventListener('click', () => setOpen(false));

    // Confirm destructive actions
    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (e) => {
            if (!window.confirm(form.dataset.confirm)) e.preventDefault();
        });
    });

    // Attendance: mark everyone present
    document.querySelectorAll('[data-mark-all]').forEach((btn) => {
        btn.addEventListener('click', () => {
            document.querySelectorAll(`input[type="radio"][value="${btn.dataset.markAll}"]`).forEach((r) => (r.checked = true));
        });
    });
});
