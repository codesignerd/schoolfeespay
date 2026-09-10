<div class="page-heading">
    <div>
        <p class="eyebrow">Student portal</p>
        <h1>My Profile</h1>
        <p class="page-subtitle">Your approved student information and academic identity.</p>
    </div>
</div>

<section class="profile-hero panel mb-4">
    <div class="profile-avatar"><?= e(strtoupper(substr($student['first_name'], 0, 1) . substr($student['last_name'], 0, 1))) ?></div>
    <div>
        <p class="eyebrow mb-1">Student record</p>
        <h2><?= e(trim($student['first_name'] . ' ' . ($student['other_name'] ? $student['other_name'] . ' ' : '') . $student['last_name'])) ?></h2>
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <span class="badge text-bg-success">Active student</span>
            <span class="text-muted">Admission No: <strong><?= e($student['admission_no'] ?? '—') ?></strong></span>
            <span class="text-muted">·</span>
            <span class="text-muted">Matriculation No: <strong><?= e($student['matriculation_no'] ?? 'Pending') ?></strong></span>
        </div>
    </div>
</section>

<div class="row g-4">
    <div class="col-lg-6">
        <section class="panel h-100">
            <div class="panel-header"><div><h2>Academic Information</h2><p>Your current academic placement.</p></div><i class="bi bi-mortarboard text-primary fs-4"></i></div>
            <dl class="detail-list mb-0">
                <dt>Admission Number</dt><dd><?= e($student['admission_no'] ?? '—') ?></dd>
                <dt>Matriculation Number</dt><dd><?= e($student['matriculation_no'] ?? 'Pending') ?></dd>
                <dt>Department</dt><dd><?= e($student['department_name'] ?? '—') ?></dd>
                <dt>Programme</dt><dd><?= e($student['programme'] ?? '—') ?></dd>
                <dt>Level</dt><dd><?= e($student['level'] ?? '—') ?></dd>
                <dt>Academic Session</dt><dd><?= e($student['academic_session'] ?? '—') ?></dd>
            </dl>
        </section>
    </div>
    <div class="col-lg-6">
        <section class="panel h-100">
            <div class="panel-header"><div><h2>Personal Information</h2><p>Details submitted during registration.</p></div><i class="bi bi-person text-primary fs-4"></i></div>
            <dl class="detail-list mb-0">
                <dt>Surname</dt><dd><?= e($student['last_name']) ?></dd>
                <dt>First Name</dt><dd><?= e($student['first_name']) ?></dd>
                <dt>Other Name</dt><dd><?= e($student['other_name'] ?? '—') ?></dd>
                <dt>Email</dt><dd><?= e($student['email'] ?? '—') ?></dd>
                <dt>Phone</dt><dd><?= e($student['phone'] ?? '—') ?></dd>
            </dl>
        </section>
    </div>
</div>
