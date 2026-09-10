<div class="page-heading">
    <div>
        <p class="eyebrow">Payment record</p>
        <h1>Payment Receipt</h1>
    </div>
    <button class="btn btn-outline-secondary" type="button" onclick="window.print()"><i class="bi bi-printer me-2"></i>Print receipt</button>
</div>

<section class="panel receipt-sheet">
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h2 class="h4 mb-1"><?= e(app_config('name')) ?></h2>
            <p class="text-muted mb-0">Official payment record</p>
        </div>
        <span class="badge text-bg-<?= $payment['payment_status'] === 'verified' ? 'success' : ($payment['payment_status'] === 'rejected' ? 'danger' : 'warning') ?>">
            <?= e(ucfirst($payment['payment_status'])) ?>
        </span>
    </div>

    <dl class="row mb-4">
        <dt class="col-sm-4">Receipt number</dt>
        <dd class="col-sm-8"><?= e($payment['receipt_no']) ?></dd>
        <dt class="col-sm-4">Invoice number</dt>
        <dd class="col-sm-8"><?= e($payment['invoice_no']) ?></dd>
        <dt class="col-sm-4">Student</dt>
        <dd class="col-sm-8"><?= e(trim($payment['first_name'] . ' ' . ($payment['other_name'] ? $payment['other_name'] . ' ' : '') . $payment['last_name'])) ?></dd>
        <dt class="col-sm-4">Matriculation number</dt>
        <dd class="col-sm-8"><?= e($payment['matriculation_no'] ?? 'Pending') ?></dd>
        <dt class="col-sm-4">Description</dt>
        <dd class="col-sm-8"><?= e($payment['description']) ?></dd>
        <dt class="col-sm-4">Amount paid</dt>
        <dd class="col-sm-8">₦<?= number_format((float)$payment['amount_paid'], 2) ?></dd>
        <dt class="col-sm-4">Payment method</dt>
        <dd class="col-sm-8"><?= e(ucfirst($payment['payment_method'])) ?></dd>
        <dt class="col-sm-4">Reference</dt>
        <dd class="col-sm-8"><?= e($payment['payment_reference'] ?: '—') ?></dd>
        <dt class="col-sm-4">Submitted</dt>
        <dd class="col-sm-8"><?= e(date('M j, Y H:i', strtotime($payment['paid_at']))) ?></dd>
        <?php if ($payment['verified_at']): ?>
            <dt class="col-sm-4">Verified</dt>
            <dd class="col-sm-8"><?= e(date('M j, Y H:i', strtotime($payment['verified_at']))) ?></dd>
        <?php endif; ?>
    </dl>

    <?php if ($payment['payment_status'] !== 'verified'): ?>
        <div class="alert alert-warning mb-0">This record is not a verified receipt yet. It does not confirm settlement of the invoice.</div>
    <?php else: ?>
        <p class="text-muted mb-0">This payment has been verified by the bursary team.</p>
    <?php endif; ?>
</section>
