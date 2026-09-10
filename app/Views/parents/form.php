<?php $isEdit = !empty($parent['id']); ?>
<div class="page-heading"><div><p class="eyebrow">Family</p><h1><?= $isEdit ? 'Edit Parent' : 'Create Parent' ?></h1></div><a class="btn btn-outline-secondary" href="<?= e(url('/parents')) ?>">Back</a></div>
<section class="panel"><form class="row g-3" method="post" action="<?= e($isEdit ? url('/parents/update') : url('/parents')) ?>"><?= csrf_field() ?><?php if ($isEdit): ?><input type="hidden" name="id" value="<?= e($parent['id']) ?>"><?php endif; ?>
<div class="col-md-4"><label class="form-label">First Name</label><input class="form-control" name="first_name" value="<?= e($parent['first_name'] ?? '') ?>"></div>
<div class="col-md-4"><label class="form-label">Last Name</label><input class="form-control" name="last_name" value="<?= e($parent['last_name'] ?? '') ?>"></div>
<div class="col-md-4"><label class="form-label">Relationship</label><input class="form-control" name="relationship" value="<?= e($parent['relationship'] ?? 'Guardian') ?>"></div>
<div class="col-md-4"><label class="form-label">Phone</label><input class="form-control" name="phone" value="<?= e($parent['phone'] ?? '') ?>"></div>
<div class="col-md-4"><label class="form-label">Email</label><input class="form-control" type="email" name="email" value="<?= e($parent['email'] ?? '') ?>"></div>
<div class="col-md-4"><label class="form-label">Address</label><input class="form-control" name="address" value="<?= e($parent['address'] ?? '') ?>"></div>
<div class="col-12 d-flex justify-content-end gap-2"><a class="btn btn-outline-secondary" href="<?= e(url('/parents')) ?>">Cancel</a><button class="btn btn-primary">Save Parent</button></div></form></section>
