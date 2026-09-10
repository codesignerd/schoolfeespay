<div class="page-heading">
    <div>
        <p class="eyebrow">Approvals</p>
        <h1>Pending Student Registrations</h1>
    </div>
</div>

<?php if ($students === []): ?>
    <section class="panel">
        <p class="text-muted mb-0">No pending registrations.</p>
    </section>
<?php else: ?>
    <section class="panel">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Department</th>
                        <th>Programme</th>
                        <th>Level</th>
                        <th>Session</th>
                        <th>Phone</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td>
                                <div class="fw-semibold"><?= e($student['first_name'] . ' ' . ($student['other_name'] ? $student['other_name'] . ' ' : '') . $student['last_name']) ?></div>
                                <div class="text-muted small"><?= e($student['email'] ?? '') ?></div>
                            </td>
                            <td><?= e($student['department_name'] ?? '—') ?></td>
                            <td><?= e($student['programme'] ?? '—') ?></td>
                            <td><?= e($student['level'] ?? '—') ?></td>
                            <td><?= e($student['academic_session'] ?? '—') ?></td>
                            <td><?= e($student['phone'] ?? '—') ?></td>
                            <td class="text-end">
                                <div class="d-flex gap-2 justify-content-end">
                                    <form method="post" action="<?= e(url('/students/approve')) ?>" class="d-inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= e($student['id']) ?>">
                                        <input class="form-control form-control-sm" type="text" name="matriculation_no" placeholder="e.g. CS/ND/26/001" required>
                                        <button class="btn btn-sm btn-success mt-1" type="submit">Approve</button>
                                    </form>
                                    <form method="post" action="<?= e(url('/students/reject')) ?>" class="d-inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= e($student['id']) ?>">
                                        <button class="btn btn-sm btn-outline-danger" type="submit">Reject</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
<?php endif; ?>
