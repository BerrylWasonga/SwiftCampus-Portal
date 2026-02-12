
    <!-- Sidebar Overlay (Mobile) -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <nav class="sidebar-nav">
            <ul class="nav flex-column">
                <!-- Dashboard -->
                <li class="nav-item">
                    <a class="nav-link" href="/mini-auth-project/admin/admin.php">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>

                <!-- Student Management -->
                <li class="nav-item">
                    <a class="nav-link collapsed d-flex justify-content-between align-items-center" href="#" data-bs-toggle="collapse" data-bs-target="#studentSubmenu">
                        <span><i class="bi bi-people"></i> Student</span>
                        <i class="bi bi-chevron-down small"></i>
                    </a>
                    <div class="collapse" id="studentSubmenu">
                        <ul class="nav flex-column ms-3">
                            <li class="nav-item">
                                <a class="nav-link" href="/mini-auth-project/admin/student/dashboard.php">
                                    <i class="bi bi-diagram-3"></i> Student Directory
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="/mini-auth-project/admin/student/list.php">
                                    <i class="bi bi-list-ul"></i> All Students
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="/mini-auth-project/admin/student/documents.php">
                                    <i class="bi bi-file-earmark-arrow-up"></i> Documents
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="/mini-auth-project/admin/student/documents.php">
                                    <i class="bi bi-calendar"></i> Timetable
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="/mini-auth-project/admin/registrations.php">
                                    <i class="bi bi-check-circle"></i> Registrations
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <!-- Student Finance -->
                <li class="nav-item">
                    <a class="nav-link collapsed d-flex justify-content-between align-items-center" href="#" data-bs-toggle="collapse" data-bs-target="#financeSubmenu">
                        <span><i class="bi bi-cash-coin"></i> Finance</span>
                        <i class="bi bi-chevron-down small"></i>
                    </a>
                    <div class="collapse" id="financeSubmenu">
                        <ul class="nav flex-column ms-3">
                            <li class="nav-item">
                                <a class="nav-link" href="/mini-auth-project/admin/finance/payments.php">
                                    <i class="bi bi-credit-card"></i> Fee Payments
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <!-- Academics -->
                <li class="nav-item">
                    <a class="nav-link collapsed d-flex justify-content-between align-items-center" href="#" data-bs-toggle="collapse" data-bs-target="#academicsSubmenu">
                        <span><i class="bi bi-mortarboard"></i> Academics</span>
                        <i class="bi bi-chevron-down small"></i>
                    </a>
                    <div class="collapse" id="academicsSubmenu">
                        <ul class="nav flex-column ms-3">
                            <li class="nav-item">
                                <a class="nav-link" href="/mini-auth-project/admin/academics/transcripts.php">
                                    <i class="bi bi-file-earmark-text"></i> Transcripts
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="/mini-auth-project/admin/academics/requisitions.php">
                                    <i class="bi bi-card-checklist"></i> Requisitions
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <!-- Accommodation -->
                <li class="nav-item">
                    <a class="nav-link collapsed d-flex justify-content-between align-items-center" href="#" data-bs-toggle="collapse" data-bs-target="#accomSubmenu">
                        <span><i class="bi bi-house-door"></i> Accommodation</span>
                        <i class="bi bi-chevron-down small"></i>
                    </a>
                    <div class="collapse" id="accomSubmenu">
                        <ul class="nav flex-column ms-3">
                            <li class="nav-item">
                                <a class="nav-link" href="/mini-auth-project/admin/accommodation/manage.php">
                                    <i class="bi bi-building-gear"></i> Manage Hostels
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <!-- Examination -->
                <li class="nav-item">
                    <a class="nav-link collapsed d-flex justify-content-between align-items-center" href="#" data-bs-toggle="collapse" data-bs-target="#examSubmenu">
                        <span><i class="bi bi-journal-check"></i> Examination</span>
                        <i class="bi bi-chevron-down small"></i>
                    </a>
                    <div class="collapse" id="examSubmenu">
                        <ul class="nav flex-column ms-3">
                            <li class="nav-item">
                                <a class="nav-link" href="/mini-auth-project/admin/exams/manage.php">
                                    <i class="bi bi-pencil-square"></i> Manage Exams
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <!-- Staff Management -->
                <li class="nav-item">
                    <a class="nav-link collapsed d-flex justify-content-between align-items-center" href="#" data-bs-toggle="collapse" data-bs-target="#staffSubmenu">
                        <span><i class="bi bi-person-badge"></i> Staff</span>
                        <i class="bi bi-chevron-down small"></i>
                    </a>
                    <div class="collapse" id="staffSubmenu">
                        <ul class="nav flex-column ms-3">
                            <li class="nav-item">
                                <a class="nav-link" href="/mini-auth-project/admin/staff/manage.php">
                                    <i class="bi bi-list-ul"></i> Manage Staff
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="/mini-auth-project/admin/staff/add.php">
                                    <i class="bi bi-person-plus"></i> Add Staff
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <!-- Non-Teaching Staff -->
                <li class="nav-item">
                    <a class="nav-link collapsed d-flex justify-content-between align-items-center" href="#" data-bs-toggle="collapse" data-bs-target="#nonStaffSubmenu">
                        <span><i class="bi bi-person-workspace"></i> Non-Teaching Staff</span>
                        <i class="bi bi-chevron-down small"></i>
                    </a>
                    <div class="collapse" id="nonStaffSubmenu">
                        <ul class="nav flex-column ms-3">
                            <li class="nav-item">
                                <a class="nav-link" href="/mini-auth-project/admin/non_teaching/manage.php">
                                    <i class="bi bi-list-ul"></i> Manage Groups
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="/mini-auth-project/admin/non_teaching/add.php">
                                    <i class="bi bi-person-plus"></i> Add Staff
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <!-- Administration -->
                <li class="nav-item">
                    <a class="nav-link collapsed d-flex justify-content-between align-items-center" href="#" data-bs-toggle="collapse" data-bs-target="#adminSubmenu">
                        <span><i class="bi bi-gear"></i> Administration</span>
                        <i class="bi bi-chevron-down small"></i>
                    </a>
                    <div class="collapse" id="adminSubmenu">
                        <ul class="nav flex-column ms-3">
                            <li class="nav-item">
                                <a class="nav-link" href="/mini-auth-project/admin/faculties/manage.php">
                                    <i class="bi bi-building"></i> Faculties
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="/mini-auth-project/admin/courses/manage.php">
                                    <i class="bi bi-book"></i> Courses
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="/mini-auth-project/admin/semesters/manage.php">
                                    <i class="bi bi-calendar-event"></i> Semesters
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="/mini-auth-project/admin/units/manage.php">
                                    <i class="bi bi-journal-text"></i> Course Units
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="nav-item mt-4">
                    <a class="nav-link text-danger" href="/mini-auth-project/logout.php">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </li>
            </ul>
        </nav>
    </aside>
