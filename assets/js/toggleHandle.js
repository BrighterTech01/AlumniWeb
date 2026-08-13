document.addEventListener('DOMContentLoaded', function() {
    // Sidebar toggle for mobile
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebarBackdrop = document.getElementById('sidebar-backdrop');
    const mainContent = document.getElementById('main-content');

    function openSidebar() {
        sidebar.classList.add('open', 'flex');
        sidebar.classList.remove('hidden');
        sidebarBackdrop.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }
    function closeSidebar() {
        sidebar.classList.remove('open', 'flex');
        sidebar.classList.add('hidden');
        sidebarBackdrop.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }
    sidebarToggle.addEventListener('click', openSidebar);
    sidebarBackdrop.addEventListener('click', closeSidebar);
    // Close sidebar on resize to md and up
    window.addEventListener('resize', function() {
        if(window.innerWidth >= 768) {
            closeSidebar();
        }
    });
});