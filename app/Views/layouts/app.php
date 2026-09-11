<!doctype html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? app_config('name')) ?> - <?= e(app_config('name')) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= e(url('/assets/css/app.css')) ?>" rel="stylesheet">
</head>
<body>
<div class="app-shell">
    <aside class="sidebar" id="sidebar">
        <a href="<?= e(url('/dashboard')) ?>" class="brand">
            <span class="brand-mark"><i class="bi bi-mortarboard-fill"></i></span>
            <span><?= e(app_config('name')) ?></span>
        </a>
        <nav class="nav flex-column gap-1">
            <a class="nav-link <?= e(active_nav('/dashboard')) ?>" href="<?= e(url('/dashboard')) ?>">
                <i class="bi bi-grid-1x2-fill"></i><span>Dashboard</span>
            </a>
            <?php if ((current_user()['role_slug'] ?? '') === 'student'): ?>
                <a class="nav-link <?= e(active_nav('/fees')) ?>" href="<?= e(url('/fees')) ?>">
                    <i class="bi bi-wallet2"></i><span>Fees & Payments</span>
                </a>
                <a class="nav-link <?= e(active_nav('/profile')) ?>" href="<?= e(url('/profile')) ?>">
                    <i class="bi bi-person-circle"></i><span>Profile</span>
                </a>
            <?php endif; ?>
            <?php if (\App\Core\Auth::can('users.view')): ?>
                <a class="nav-link <?= e(active_nav('/users')) ?>" href="<?= e(url('/users')) ?>">
                    <i class="bi bi-people-fill"></i><span>Users & Roles</span>
                </a>
            <?php endif; ?>
            <?php if (\App\Core\Auth::can('students.view')): ?>
                <a class="nav-link <?= e(active_nav('/students')) ?>" href="<?= e(url('/students')) ?>"><i class="bi bi-person-vcard"></i><span>Students</span></a>
                <?php if (\App\Core\Auth::can('students.manage')): ?>
                    <a class="nav-link <?= e(active_nav('/students/pending')) ?>" href="<?= e(url('/students/pending')) ?>"><i class="bi bi-person-check"></i><span>Pending Approvals</span></a>
                <?php endif; ?>
            <?php endif; ?>
            <?php if ((current_user()['role_slug'] ?? '') !== 'student' && \App\Core\Auth::can('finance.view')): ?>
                <a class="nav-link <?= e(active_nav('/fees')) ?>" href="<?= e(url('/fees')) ?>"><i class="bi bi-cash-stack"></i><span>Fees</span></a>
            <?php endif; ?>
        </nav>
    </aside>

    <div class="main-shell">
        <header class="topbar">
            <button class="btn btn-icon d-lg-none" type="button" data-toggle-sidebar aria-label="Open menu">
                <i class="bi bi-list"></i>
            </button>
            <?php if ((current_user()['role_slug'] ?? '') !== 'student'): ?>
            <form class="search-box d-none d-md-flex" action="<?= e(url('/users')) ?>" method="get">
                <i class="bi bi-search"></i>
                <input type="search" name="search" placeholder="Search users, students, reports">
            </form>
            <?php endif; ?>
            <div class="ms-auto d-flex align-items-center gap-2">
                <button class="btn btn-icon" type="button" data-theme-toggle aria-label="Toggle theme">
                    <i class="bi bi-moon-stars"></i>
                </button>
                <button class="btn btn-icon" type="button" aria-label="Notifications">
                    <i class="bi bi-bell"></i>
                </button>
                <div class="dropdown">
                    <button class="profile-button" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="avatar"><?= e(substr(current_user()['name'] ?? 'U', 0, 1)) ?></span>
                        <span class="d-none d-sm-block text-start">
                            <strong><?= e(current_user()['name'] ?? 'User') ?></strong>
                            <small><?= e(current_user()['role'] ?? '') ?></small>
                        </span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end shadow">
                        <span class="dropdown-item-text small text-muted"><?= e(current_user()['email'] ?? '') ?></span>
                        <div class="dropdown-divider"></div>
                        <form action="<?= e(url('/logout')) ?>" method="post">
                            <?= csrf_field() ?>
                            <button class="dropdown-item text-danger" type="submit"><i class="bi bi-box-arrow-right me-2"></i>Sign out</button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <main class="content-shell">
            <?php if (!empty($_SESSION['flash_success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= e($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <?php if (!empty($_SESSION['flash_error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= e($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <?= $content ?>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="<?= e(url('/assets/js/app.js')) ?>"></script>
</body>
</html>
