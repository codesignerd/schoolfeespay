<section class="auth-card registration-card">
    <div class="auth-panel registration-intro">
        <div class="brand mb-4">
            <span class="brand-mark"><i class="bi bi-mortarboard-fill"></i></span>
            <span><?= e(app_config('name')) ?></span>
        </div>
        <p class="eyebrow text-white-50">Student registration</p>
        <h1>Create Your Student Account</h1>
        <p class="lead">Submit your details for school review. Portal access begins after approval and matriculation assignment.</p>
        <div class="registration-steps"><span><b>1</b> Register</span><span><b>2</b> School review</span><span><b>3</b> Student access</span></div>
    </div>
    <form class="auth-form registration-form" method="post" action="<?= e(url('/register')) ?>">
        <?= csrf_field() ?>
        <div class="form-heading"><div><p class="eyebrow">Application details</p><h2>Start your application</h2></div><i class="bi bi-person-plus text-primary fs-3"></i></div>
        <?php if (!empty($_SESSION['flash_success'])): ?>
            <div class="alert alert-success"><?= e($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?></div>
        <?php endif; ?>
        <fieldset><legend><i class="bi bi-person me-2"></i>Personal information</legend><div class="row g-3">
            <?php foreach ([['first_name','First Name','col-md-6'],['last_name','Surname','col-md-6'],['other_name','Other Name','col-md-12']] as [$name,$label,$column]): ?><div class="<?= e($column) ?>"><label class="form-label" for="<?= e($name) ?>"><?= e($label) ?></label><input id="<?= e($name) ?>" class="form-control <?= isset($errors[$name]) ? 'is-invalid' : '' ?>" name="<?= e($name) ?>" value="<?= e($student[$name] ?? '') ?>" <?= in_array($name, ['first_name','last_name'], true) ? 'required' : '' ?>><?php if (isset($errors[$name])): ?><div class="invalid-feedback d-block"><?= e($errors[$name]) ?></div><?php endif; ?></div><?php endforeach; ?>
        </div></fieldset>
        <fieldset><legend><i class="bi bi-mortarboard me-2"></i>Academic information</legend><div class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="department_id">Department</label>
                <select id="department_id" class="form-select <?= isset($errors['department_id']) ? 'is-invalid' : '' ?>" name="department_id" required>
                    <option value="">Select department</option>
                    <?php foreach ($departments as $department): ?>
                        <option value="<?= e($department['id']) ?>" <?= (string)($student['department_id'] ?? '') === (string)$department['id'] ? 'selected' : '' ?>><?= e($department['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['department_id'])): ?><div class="invalid-feedback d-block"><?= e($errors['department_id']) ?></div><?php endif; ?>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="programme">Programme</label>
                <select id="programme" class="form-select <?= isset($errors['programme']) ? 'is-invalid' : '' ?>" name="programme" required>
                    <option value="">Select programme</option>
                    <?php foreach ($programmes as $prog): ?>
                        <option value="<?= e($prog) ?>" <?= ($student['programme'] ?? '') === $prog ? 'selected' : '' ?>><?= e($prog) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['programme'])): ?><div class="invalid-feedback d-block"><?= e($errors['programme']) ?></div><?php endif; ?>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="level">Level</label>
                <select id="level" class="form-select <?= isset($errors['level']) ? 'is-invalid' : '' ?>" name="level" required>
                    <option value="">Select level</option>
                    <?php foreach ($levels as $lvl): ?>
                        <option value="<?= e($lvl) ?>" <?= ($student['level'] ?? '') === $lvl ? 'selected' : '' ?>><?= e($lvl) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['level'])): ?><div class="invalid-feedback d-block"><?= e($errors['level']) ?></div><?php endif; ?>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="academic_session">Academic Session</label>
                <select id="academic_session" class="form-select <?= isset($errors['academic_session']) ? 'is-invalid' : '' ?>" name="academic_session" required>
                    <option value="">Select academic session</option>
                    <?php foreach ($sessions as $sess): ?>
                        <option value="<?= e($sess) ?>" <?= ($student['academic_session'] ?? '') === $sess ? 'selected' : '' ?>><?= e($sess) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['academic_session'])): ?><div class="invalid-feedback d-block"><?= e($errors['academic_session']) ?></div><?php endif; ?>
            </div>
        </div></fieldset>
        <fieldset><legend><i class="bi bi-telephone me-2"></i>Contact information</legend><div class="row g-3">
            <?php foreach ([['email','Email','email'],['phone','Phone','text']] as [$name,$label,$type]): ?><div class="col-md-6"><label class="form-label" for="<?= e($name) ?>"><?= e($label) ?></label><input id="<?= e($name) ?>" class="form-control <?= isset($errors[$name]) ? 'is-invalid' : '' ?>" name="<?= e($name) ?>" type="<?= e($type) ?>" value="<?= e($student[$name] ?? '') ?>" required><?php if (isset($errors[$name])): ?><div class="invalid-feedback d-block"><?= e($errors[$name]) ?></div><?php endif; ?></div><?php endforeach; ?>
        </div></fieldset>
        <fieldset><legend><i class="bi bi-shield-lock me-2"></i>Account security</legend><div class="row g-3">
            <?php foreach ([['password','Password'],['password_confirmation','Confirm Password']] as [$name,$label]): ?><div class="col-md-6"><label class="form-label" for="<?= e($name) ?>"><?= e($label) ?></label><input id="<?= e($name) ?>" class="form-control <?= isset($errors[$name]) ? 'is-invalid' : '' ?>" name="<?= e($name) ?>" type="password" required><?php if (isset($errors[$name])): ?><div class="invalid-feedback d-block"><?= e($errors[$name]) ?></div><?php endif; ?></div><?php endforeach; ?>
        </div></fieldset>
        <button class="btn btn-primary btn-lg w-100 mt-2" type="submit"><i class="bi bi-send me-2"></i>Submit Registration</button>
        <p class="mt-3 mb-0 text-muted small text-center">Already registered? <a href="<?= e(url('/login')) ?>">Sign in to your account</a></p>
    </form>
</section>
