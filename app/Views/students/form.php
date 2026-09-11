<?php $isEdit = !empty($student['id']); $selectedParents = $student['parent_ids'] ?? []; ?>
<div class="page-heading">
    <div>
        <p class="eyebrow">Student Management</p>
        <h1><?= $isEdit ? 'Edit Student Record' : 'Create Student & User Account' ?></h1>
    </div>
    <a class="btn btn-outline-secondary" href="<?= e(url('/students')) ?>"><i class="bi bi-arrow-left me-2"></i>Back</a>
</div>

<?php if (isset($errors['system'])): ?>
    <div class="alert alert-danger mb-4"><?= e($errors['system']) ?></div>
<?php endif; ?>

<form method="post" action="<?= e($isEdit ? url('/students/update') : url('/students')) ?>">
    <?= csrf_field() ?>
    <?php if ($isEdit): ?><input type="hidden" name="id" value="<?= e($student['id']) ?>"><?php endif; ?>

    <!-- ACADEMIC INFORMATION -->
    <section class="panel mb-4">
        <div class="panel-header">
            <div>
                <h2>Academic Placement</h2>
                <p>Polytechnic identity numbers, department, level, and session assignment.</p>
            </div>
            <i class="bi bi-mortarboard text-primary fs-4"></i>
        </div>
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label" for="admission_no">Admission Number <span class="text-danger">*</span></label>
                <input class="form-control <?= isset($errors['admission_no']) ? 'is-invalid' : '' ?>" id="admission_no" name="admission_no" value="<?= e($student['admission_no'] ?? '') ?>" required>
                <?php if (isset($errors['admission_no'])): ?><div class="invalid-feedback"><?= e($errors['admission_no']) ?></div><?php endif; ?>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="matriculation_no">Matriculation Number</label>
                <input class="form-control <?= isset($errors['matriculation_no']) ? 'is-invalid' : '' ?>" id="matriculation_no" name="matriculation_no" value="<?= e($student['matriculation_no'] ?? '') ?>" placeholder="e.g. CS/ND/26/001">
                <?php if (isset($errors['matriculation_no'])): ?><div class="invalid-feedback"><?= e($errors['matriculation_no']) ?></div><?php endif; ?>
                <div class="form-text">Assigned manually upon approval or enrollment.</div>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="department_id">Department</label>
                <select class="form-select <?= isset($errors['department_id']) ? 'is-invalid' : '' ?>" id="department_id" name="department_id">
                    <option value="">Select Department</option>
                    <?php foreach ($departments as $dept): ?>
                        <option value="<?= e($dept['id']) ?>" <?= (string)($student['department_id'] ?? '') === (string)$dept['id'] ? 'selected' : '' ?>><?= e($dept['name']) ?> (<?= e($dept['code']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="programme">Programme</label>
                <select class="form-select <?= isset($errors['programme']) ? 'is-invalid' : '' ?>" id="programme" name="programme">
                    <option value="">Select Programme</option>
                    <?php foreach ($programmes as $prog): ?>
                        <option value="<?= e($prog) ?>" <?= ($student['programme'] ?? '') === $prog ? 'selected' : '' ?>><?= e($prog) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="level">Academic Level</label>
                <select class="form-select <?= isset($errors['level']) ? 'is-invalid' : '' ?>" id="level" name="level">
                    <option value="">Select Level</option>
                    <?php foreach ($levels as $lvl): ?>
                        <option value="<?= e($lvl) ?>" <?= ($student['level'] ?? '') === $lvl ? 'selected' : '' ?>><?= e($lvl) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="academic_session">Academic Session</label>
                <select class="form-select <?= isset($errors['academic_session']) ? 'is-invalid' : '' ?>" id="academic_session" name="academic_session">
                    <option value="">Select Session</option>
                    <?php foreach ($sessions as $sess): ?>
                        <option value="<?= e($sess) ?>" <?= ($student['academic_session'] ?? '') === $sess ? 'selected' : '' ?>><?= e($sess) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </section>

    <!-- PERSONAL INFORMATION -->
    <section class="panel mb-4">
        <div class="panel-header">
            <div>
                <h2>Personal Information</h2>
                <p>Basic biodata and demographic details.</p>
            </div>
            <i class="bi bi-person text-primary fs-4"></i>
        </div>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label" for="first_name">First Name <span class="text-danger">*</span></label>
                <input class="form-control <?= isset($errors['first_name']) ? 'is-invalid' : '' ?>" id="first_name" name="first_name" value="<?= e($student['first_name'] ?? '') ?>" required>
                <?php if (isset($errors['first_name'])): ?><div class="invalid-feedback"><?= e($errors['first_name']) ?></div><?php endif; ?>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="last_name">Last Name (Surname) <span class="text-danger">*</span></label>
                <input class="form-control <?= isset($errors['last_name']) ? 'is-invalid' : '' ?>" id="last_name" name="last_name" value="<?= e($student['last_name'] ?? '') ?>" required>
                <?php if (isset($errors['last_name'])): ?><div class="invalid-feedback"><?= e($errors['last_name']) ?></div><?php endif; ?>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="gender">Gender <span class="text-danger">*</span></label>
                <select class="form-select" id="gender" name="gender">
                    <?php foreach (['male','female','other'] as $value): ?>
                        <option value="<?= e($value) ?>" <?= ($student['gender'] ?? 'male') === $value ? 'selected' : '' ?>><?= e(ucfirst($value)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="date_of_birth">Date of Birth</label>
                <input class="form-control" id="date_of_birth" type="date" name="date_of_birth" value="<?= e($student['date_of_birth'] ?? '') ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="nationality">Nationality</label>
                <input class="form-control" id="nationality" name="nationality" value="<?= e($student['nationality'] ?? 'Nigerian') ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="religion">Religion</label>
                <input class="form-control" id="religion" name="religion" value="<?= e($student['religion'] ?? '') ?>">
            </div>
        </div>
    </section>

    <!-- CONTACT & LOGIN ACCOUNT INFORMATION -->
    <section class="panel mb-4">
        <div class="panel-header">
            <div>
                <h2>Contact & Portal Login Credentials</h2>
                <p>System account email, phone, and initial authentication credentials.</p>
            </div>
            <i class="bi bi-shield-lock text-primary fs-4"></i>
        </div>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label" for="email">Email Address (Login Username) <span class="text-danger">*</span></label>
                <input class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" id="email" name="email" type="email" value="<?= e($student['email'] ?? '') ?>" required>
                <?php if (isset($errors['email'])): ?><div class="invalid-feedback"><?= e($errors['email']) ?></div><?php endif; ?>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="phone">Phone Number</label>
                <input class="form-control" id="phone" name="phone" value="<?= e($student['phone'] ?? '') ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="password"><?= $isEdit ? 'New Password (Optional)' : 'Portal Password' ?></label>
                <input class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" id="password" name="password" type="password" placeholder="<?= $isEdit ? 'Leave blank to keep existing' : 'Default: Student@12345' ?>">
                <?php if (isset($errors['password'])): ?><div class="invalid-feedback"><?= e($errors['password']) ?></div><?php endif; ?>
                <div class="form-text"><?= $isEdit ? 'Only enter a password if resetting.' : 'Defaults to <code>Student@12345</code> if left empty.' ?></div>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="status">Account Status</label>
                <select class="form-select" id="status" name="status">
                    <?php foreach (['active','transferred','graduated','suspended','inactive'] as $value): ?>
                        <option value="<?= e($value) ?>" <?= ($student['status'] ?? 'active') === $value ? 'selected' : '' ?>><?= e(ucfirst($value)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </section>

    <!-- GUARDIAN & EMERGENCY INFORMATION -->
    <section class="panel mb-4">
        <div class="panel-header">
            <div>
                <h2>Guardian & Emergency Details</h2>
                <p>Linked parents or emergency contact persons.</p>
            </div>
            <i class="bi bi-people text-primary fs-4"></i>
        </div>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="parent_ids">Parents / Guardians</label>
                <select class="form-select" id="parent_ids" name="parent_ids[]" multiple size="4">
                    <?php foreach ($parents as $parent): ?>
                        <option value="<?= e($parent['id']) ?>" <?= in_array($parent['id'], $selectedParents) ? 'selected' : '' ?>><?= e($parent['name'] . ' · ' . $parent['phone']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="emergency_contact">Emergency Contact Person & Phone</label>
                <input class="form-control" id="emergency_contact" name="emergency_contact" value="<?= e($student['emergency_contact'] ?? '') ?>" placeholder="Name, Relationship & Phone Number">
                <label class="form-label mt-3" for="previous_school">Previous Institution / School</label>
                <input class="form-control" id="previous_school" name="previous_school" value="<?= e($student['previous_school'] ?? '') ?>">
            </div>
            <div class="col-12">
                <label class="form-label" for="medical_notes">Medical Notes / Conditions</label>
                <textarea class="form-control" id="medical_notes" name="medical_notes" rows="2"><?= e($student['medical_notes'] ?? '') ?></textarea>
            </div>
        </div>
    </section>

    <div class="d-flex justify-content-end gap-2 mb-4">
        <a class="btn btn-outline-secondary" href="<?= e(url('/students')) ?>">Cancel</a>
        <button class="btn btn-primary" type="submit"><i class="bi bi-check-lg me-1"></i> <?= $isEdit ? 'Update Student Record' : 'Create Student & User Account' ?></button>
    </div>
</form>
