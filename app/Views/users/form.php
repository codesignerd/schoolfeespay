<?php $isEdit = !empty($user['id']); ?>
<div class="page-heading">
    <div>
        <p class="eyebrow">Administration</p>
        <h1><?= $isEdit ? 'Edit User' : 'Create User' ?></h1>
    </div>
    <a class="btn btn-outline-secondary" href="<?= e(url('/users')) ?>"><i class="bi bi-arrow-left me-2"></i>Back</a>
</div>

<section class="panel">
    <form method="post" action="<?= e($isEdit ? url('/users/update') : url('/users')) ?>" class="row g-3" novalidate>
        <?= csrf_field() ?>
        <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?= e($user['id']) ?>">
        <?php endif; ?>
        <div class="col-md-6">
            <label class="form-label" for="first_name">First name</label>
            <input class="form-control <?= isset($errors['first_name']) ? 'is-invalid' : '' ?>" id="first_name" name="first_name" value="<?= e($user['first_name'] ?? '') ?>" required>
            <div class="invalid-feedback"><?= e($errors['first_name'] ?? '') ?></div>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="last_name">Last name</label>
            <input class="form-control <?= isset($errors['last_name']) ? 'is-invalid' : '' ?>" id="last_name" name="last_name" value="<?= e($user['last_name'] ?? '') ?>" required>
            <div class="invalid-feedback"><?= e($errors['last_name'] ?? '') ?></div>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="email">Email</label>
            <input class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" id="email" name="email" type="email" value="<?= e($user['email'] ?? '') ?>" required>
            <div class="invalid-feedback"><?= e($errors['email'] ?? '') ?></div>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="phone">Phone</label>
            <input class="form-control" id="phone" name="phone" value="<?= e($user['phone'] ?? '') ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label" for="role_id">Role</label>
            <select class="form-select <?= isset($errors['role_id']) ? 'is-invalid' : '' ?>" id="role_id" name="role_id" required>
                <option value="">Choose role</option>
                <?php foreach ($roles as $role): ?>
                    <option value="<?= e($role['id']) ?>" <?= (string)($user['role_id'] ?? '') === (string)$role['id'] ? 'selected' : '' ?>><?= e($role['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <div class="invalid-feedback"><?= e($errors['role_id'] ?? '') ?></div>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="status">Status</label>
            <select class="form-select" id="status" name="status">
                <?php foreach (['active', 'inactive', 'suspended'] as $status): ?>
                    <option value="<?= e($status) ?>" <?= ($user['status'] ?? 'active') === $status ? 'selected' : '' ?>><?= e(ucfirst($status)) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="password"><?= $isEdit ? 'New password' : 'Password' ?></label>
            <input class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" id="password" name="password" type="password" <?= $isEdit ? '' : 'required' ?>>
            <div class="invalid-feedback"><?= e($errors['password'] ?? '') ?></div>
            <?php if ($isEdit): ?><div class="form-text">Leave blank to keep the current password.</div><?php endif; ?>
        </div>
        <div class="col-12 d-flex justify-content-end gap-2">
            <a class="btn btn-outline-secondary" href="<?= e(url('/users')) ?>">Cancel</a>
            <button class="btn btn-primary" type="submit"><?= $isEdit ? 'Save Changes' : 'Create User' ?></button>
        </div>
    </form>
</section>
