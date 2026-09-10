<div class="page-heading">
    <div>
        <p class="eyebrow">Administration</p>
        <h1>Users & Roles</h1>
    </div>
    <?php if (\App\Core\Auth::can('users.manage')): ?>
        <a class="btn btn-primary" href="<?= e(url('/users/create')) ?>"><i class="bi bi-plus-lg me-2"></i>New User</a>
    <?php endif; ?>
</div>

<section class="panel">
    <form class="row g-3 align-items-end mb-4" method="get" action="<?= e(url('/users')) ?>">
        <div class="col-md-6">
            <label class="form-label" for="search">Search</label>
            <input class="form-control" id="search" name="search" value="<?= e($search) ?>" placeholder="Name or email">
        </div>
        <div class="col-md-3">
            <label class="form-label" for="role_id">Role</label>
            <select class="form-select" id="role_id" name="role_id">
                <option value="">All roles</option>
                <?php foreach ($roles as $role): ?>
                    <option value="<?= e($role['id']) ?>" <?= (string)$role['id'] === (string)$roleId ? 'selected' : '' ?>><?= e($role['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button class="btn btn-outline-secondary flex-fill" type="submit"><i class="bi bi-funnel me-2"></i>Filter</button>
            <a class="btn btn-outline-secondary" href="<?= e(url('/users')) ?>" aria-label="Clear filters"><i class="bi bi-x-lg"></i></a>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Last Login</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users['items'] as $user): ?>
                    <tr>
                        <td>
                            <div class="fw-semibold"><?= e($user['first_name'] . ' ' . $user['last_name']) ?></div>
                            <div class="text-muted small"><?= e($user['email']) ?><?= $user['phone'] ? ' · ' . e($user['phone']) : '' ?></div>
                        </td>
                        <td><?= e($user['role_name']) ?></td>
                        <td><span class="badge text-bg-<?= $user['status'] === 'active' ? 'success' : 'secondary' ?>"><?= e($user['status']) ?></span></td>
                        <td><?= $user['last_login_at'] ? e(date('M j, Y H:i', strtotime($user['last_login_at']))) : '<span class="text-muted">Never</span>' ?></td>
                        <td class="text-end">
                            <?php if (\App\Core\Auth::can('users.manage')): ?>
                                <a class="btn btn-sm btn-outline-secondary" href="<?= e(url('/users/edit?id=' . $user['id'])) ?>"><i class="bi bi-pencil"></i></a>
                                <form class="d-inline" action="<?= e(url('/users/delete')) ?>" method="post" data-confirm="Delete this user?">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= e($user['id']) ?>">
                                    <button class="btn btn-sm btn-outline-danger" type="submit"><i class="bi bi-trash"></i></button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-between align-items-center">
        <p class="text-muted small mb-0">Showing <?= count($users['items']) ?> of <?= e($users['total']) ?> users</p>
        <nav aria-label="User pages">
            <ul class="pagination mb-0">
                <?php for ($i = 1; $i <= $users['pages']; $i++): ?>
                    <li class="page-item <?= $i === $users['page'] ? 'active' : '' ?>">
                        <a class="page-link" href="<?= e(url('/users?page=' . $i . '&search=' . urlencode($search) . '&role_id=' . urlencode($roleId))) ?>"><?= e($i) ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>
</section>
