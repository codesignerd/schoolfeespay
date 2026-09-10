<section class="login-card">
    <div class="login-visual">
        <div class="brand mb-4">
            <span class="brand-mark"><i class="bi bi-mortarboard-fill"></i></span>
            <span><?= e(app_config('name')) ?></span>
        </div>
        <h1>Run the school day from one secure workspace.</h1>
        <p>Secure access for administrators, staff, families, and learners.</p>
    </div>
    <form class="login-form" action="<?= e(url('/login')) ?>" method="post" novalidate>
        <?= csrf_field() ?>
        <h2>Sign in</h2>
        <?php if (!empty($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger"><?= e($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
        <?php endif; ?>
        <div class="mb-3">
            <label class="form-label" for="email">Email</label>
            <input class="form-control form-control-lg" id="email" name="email" type="email" placeholder="e.g. admin@school.test" required autofocus>
        </div>
        <div class="mb-3">
            <label class="form-label" for="password">Password</label>
            <input class="form-control form-control-lg" id="password" name="password" type="password" placeholder="Enter password" required>
        </div>
        <button class="btn btn-primary btn-lg w-100" type="submit">Sign in</button>
        
        <div class="demo-accounts-card mt-4 p-2 bg-light rounded border">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="fw-semibold text-secondary small"><i class="bi bi-person-badge me-1"></i> Demo Accounts</span>
                <span class="badge text-bg-info">Quick Fill</span>
            </div>
            <p class="text-muted small mb-2">Click an account below to populate login credentials:</p>
            <div class="d-flex flex-wrap gap-2">
                <button type="button" class="btn btn-sm btn-outline-primary demo-fill-btn" data-email="admin@school.test" data-pass="Admin@12345">
                    <i class="bi bi-shield-lock me-1"></i> Admin
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary demo-fill-btn" data-email="student001@school.test" data-pass="Student@12345">
                    <i class="bi bi-person me-1"></i>Student 001
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary demo-fill-btn" data-email="student002@school.test" data-pass="Student@12345">
                    <i class="bi bi-person me-1"></i> Student 002
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary demo-fill-btn" data-email="student003@school.test" data-pass="Student@12345">
                    <i class="bi bi-person me-1"></i> Student 003
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary demo-fill-btn" data-email="student004@school.test" data-pass="Student@12345">
                    <i class="bi bi-person me-1"></i> Student 004
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary demo-fill-btn" data-email="student005@school.test" data-pass="Student@12345">
                    <i class="bi bi-person me-1"></i> Student 005
                </button>
            </div>
        </div>
    </form>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.demo-fill-btn').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    document.getElementById('email').value = this.getAttribute('data-email');
                    document.getElementById('password').value = this.getAttribute('data-pass');
                });
            });
        });
    </script>
</section>
