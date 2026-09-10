<div class="page-heading">
    <div><p class="eyebrow">Phase 2</p><h1>Students</h1></div>
    <?php if (\App\Core\Auth::can('students.manage')): ?><a class="btn btn-primary" href="<?= e(url('/students/create')) ?>"><i class="bi bi-plus-lg me-2"></i>New Student</a><?php endif; ?>
</div>
<section class="panel">
    <form class="row g-3 align-items-end mb-4" method="get">
        <div class="col-md-6"><label class="form-label">Search</label><input class="form-control" name="search" value="<?= e($search) ?>" placeholder="Admission no, name, email"></div>
        <div class="col-md-3"><label class="form-label">Class</label><select class="form-select" name="class_id"><option value="">All classes</option><?php foreach ($classes as $class): ?><option value="<?= e($class['id']) ?>" <?= (string)$class['id'] === (string)$classId ? 'selected' : '' ?>><?= e($class['name'] . ' ' . $class['stream']) ?></option><?php endforeach; ?></select></div>
        <div class="col-md-3 d-flex gap-2"><button class="btn btn-outline-secondary flex-fill" type="submit"><i class="bi bi-funnel me-2"></i>Filter</button><a class="btn btn-outline-secondary" href="<?= e(url('/students')) ?>"><i class="bi bi-x-lg"></i></a></div>
    </form>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Student</th><th>Class</th><th>Gender</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            <?php foreach ($students['items'] as $student): ?>
                <tr>
                    <td><div class="fw-semibold"><?= e($student['first_name'] . ' ' . $student['last_name']) ?></div><div class="text-muted small">Adm: <?= e($student['admission_no'] ?? '—') ?><?= !empty($student['matriculation_no']) ? ' · Mat: ' . e($student['matriculation_no']) : '' ?><?= !empty($student['email']) ? ' · ' . e($student['email']) : '' ?></div></td>
                    <td><?= e($student['class_name'] ?? 'Unassigned') ?></td>
                    <td><?= e(ucfirst($student['gender'])) ?></td>
                    <td><span class="badge text-bg-<?= $student['status'] === 'active' ? 'success' : 'secondary' ?>"><?= e($student['status']) ?></span></td>
                    <td class="text-end">
                        <?php if (\App\Core\Auth::can('students.manage')): ?>
                            <a class="btn btn-sm btn-outline-secondary" href="<?= e(url('/students/edit?id=' . $student['id'])) ?>"><i class="bi bi-pencil"></i></a>
                            <form class="d-inline" action="<?= e(url('/students/delete')) ?>" method="post" data-confirm="Delete this student?"><?= csrf_field() ?><input type="hidden" name="id" value="<?= e($student['id']) ?>"><button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="d-flex justify-content-between align-items-center"><p class="text-muted small mb-0">Showing <?= count($students['items']) ?> of <?= e($students['total']) ?></p><nav><ul class="pagination mb-0"><?php for ($i = 1; $i <= $students['pages']; $i++): ?><li class="page-item <?= $i === $students['page'] ? 'active' : '' ?>"><a class="page-link" href="<?= e(url('/students?page=' . $i . '&search=' . urlencode($search) . '&class_id=' . urlencode($classId))) ?>"><?= e($i) ?></a></li><?php endfor; ?></ul></nav></div>
</section>
