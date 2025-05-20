<div class="sidebar">
    <nav>
        <ul>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>">
                <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
            </li>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) === 'activities.php' ? 'active' : ''; ?>">
                <a href="activities.php"><i class="fas fa-running"></i> Attività</a>
            </li>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) === 'goals.php' ? 'active' : ''; ?>">
                <a href="goals.php"><i class="fas fa-bullseye"></i> Obiettivi</a>
            </li>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) === 'calendar.php' ? 'active' : ''; ?>">
                <a href="calendar.php"><i class="fas fa-calendar-alt"></i> Calendario</a>
            </li>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) === 'statistics.php' ? 'active' : ''; ?>">
                <a href="statistics.php"><i class="fas fa-chart-line"></i> Statistiche</a>
            </li>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) === 'profile.php' ? 'active' : ''; ?>">
                <a href="profile.php"><i class="fas fa-user"></i> Profilo</a>
            </li>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) === 'settings.php' ? 'active' : ''; ?>">
                <a href="settings.php"><i class="fas fa-cog"></i> Impostazioni</a>
            </li>
        </ul>
    </nav>
</div>