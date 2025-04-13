<div class="top-nav">
    <ul>
        <a href="../admin/time-and-attendance-home.php">
            <h1 class="logopos">
                Paradise <br> Hotel
            </h1>
        </a>
        <li class="top">
            <a class="top1" href="#">
                <i class="fas fa-home"></i> <!-- Icon for Home -->
                Home
            </a>
            <div class="top1dropdown">
                <div class="dropdown-column">
                    <a href="../employee-information/employee-list.php">
                        <i class="fas fa-users"></i> Employee Information
                    </a>
                </div>
            </div>
        </li>
        <li class="top">
            <a class="top1" href="../admin/time-and-attendance-home.php">
                <i class="fas fa-chart-line"></i> <!-- Icon for Dashboard -->
                Dashboard
            </a>
        </li>
        <li class="top">
            <a class="top1" href="">
                <i class="fas fa-tasks"></i> <!-- Icon for Manage -->
                Manage
            </a>
            <div class="top1dropdown">
                <div class="dropdown-column">
                    <h3><b>Attendance Tracking</b></h3>
                    <a href="clocking-system.php">
                        <i class="fas fa-clock"></i> Clocking System
                    </a>
                    <a href="timesheet.php">
                        <i class="fas fa-calendar-alt"></i> Daily Record
                    </a>
                    <a href="attendance-summary.php">
                        <i class="fas fa-list"></i> Attendance Summary
                    </a>
                </div>
                <div class="dropdown-column">
                    <h3><b>Leave Management</b></h3>
                    <a href="leavemanagement.php">
                        <i class="fas fa-envelope-open-text"></i> Leave Requests
                    </a>
                    <a href="leave-record.php">
                        <i class="fas fa-file-alt"></i> Employee Leave Records
                    </a>
                    <a href="leave-type-list.php">
                        <i class="fas fa-list-alt"></i> List of Leave Types
                    </a>
                </div>
                <div class="dropdown-column">
                    <h3><b>Shift Management</b></h3>
                    <a href="manage-shift.php">
                        <i class="fas fa-calendar"></i> Manage Shift
                    </a>
                    <a href="shift-types.php">
                        <i class="fas fa-layer-group"></i> Shift Types
                    </a>
                </div>
                <div class="dropdown-column">
                    <h3><b>Compliance & Labor Law Adherence</b></h3>
                    <a href="../admin/compliance/violations.php">
                        <i class="fas fa-exclamation-triangle"></i> Violations
                    </a>
                    <a href="../admin/compliance/compliance-report.php">
                        <i class="fas fa-file-contract"></i> Compliance Report
                    </a>
                    <a href="../admin/compliance/labor-policies.php">
                        <i class="fas fa-book"></i> Labor Policies
                    </a>
                    <a href="../admin/compliance/adherence-monitoring.php">
                        <i class="fas fa-eye"></i> Adherence Monitoring
                    </a>
                </div>
            </div>
        </li>
    </ul>

    <button type="button" id="darkModeToggle" class="dark-mode-toggle" aria-label="Toggle Dark Mode">
        <i class="fas fa-moon"></i> <!-- Icon for dark mode toggle -->
    </button>

    <!-- USER -->
    <div class="admin-section">
        <div class="admin-name">
            <i class="fas fa-user"></i> User - <?php echo htmlspecialchars($username); ?>
            <div class="admin-dropdown-content">
                <a href="../manage_account.php">Manage Account</a>
            </div>
        </div>
    </div>

    <button type="button" class="logout" id="logout-button" style="margin-right: 10px;">
        <i class="fas fa-sign-out-alt"></i> <!-- Icon for logout -->
    </button>
</div>

<div id="dialog-overlay" class="dialog-overlay">
    <div class="dialog-content">
        <h3>Are you sure you want to sign out?</h3>
        <div class="dialog-buttons">
            <button id="confirm-button">Sign Out</button>
            <button class="cancel" id="cancel-button">Cancel</button>
        </div>
    </div>
</div>
