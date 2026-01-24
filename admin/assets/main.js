$(document).ready(function () {
    // Initialize Select2
    $('#courseSelect').select2({
        theme: 'bootstrap-5',
        placeholder: "-- Select Programme --",
        allowClear: true,
        width: '100%'
    });

    // Sidebar Toggle
    const sidebar = $('#sidebar');
    const mainContent = $('#mainContent');
    const sidebarOverlay = $('#sidebarOverlay');
    const sidebarToggle = $('#sidebarToggle');

    sidebarToggle.on('click', function () {
        if (window.innerWidth <= 991.98) {
            sidebar.toggleClass('show');
            sidebarOverlay.toggleClass('active');
        } else {
            sidebar.toggleClass('collapsed');
            mainContent.toggleClass('expanded');
        }
    });

    sidebarOverlay.on('click', function () {
        sidebar.removeClass('show');
        sidebarOverlay.removeClass('active');
    });

    $(window).on('resize', function () {
        if (window.innerWidth > 991.98) {
            sidebar.removeClass('show');
            sidebarOverlay.removeClass('active');
        }
    });

    // Section Navigation
    $('.sidebar-nav .nav-link').on('click', function (e) {
        e.preventDefault();
        const section = $(this).data('section');

        // Update active link
        $('.sidebar-nav .nav-link').removeClass('active');
        $(this).addClass('active');

        // Hide all sections
        $('.content-section').hide();

        // Show selected section
        $('#section-' + section).show();

        // Close sidebar on mobile
        if (window.innerWidth <= 991.98) {
            sidebar.removeClass('show');
            sidebarOverlay.removeClass('active');
        }
    });

    // Form validation
    $('#studentForm').on('submit', function (e) {
        var firstName = $('[name="first_name"]').val().trim();
        var lastName = $('[name="last_name"]').val().trim();
        var email = $('[name="email"]').val().trim();
        var courseId = $('#courseSelect').val();

        if (!firstName || !lastName || !email || !courseId) {
            e.preventDefault();
            alert('Please fill in all required fields');
            return false;
        }
    });
});