<div class="page-heading">
    <div>
        <p class="eyebrow">Student portal</p>
        <h1>Welcome back, <?= e($student['first_name'] ?? 'Student') ?></h1>
        <p class="page-subtitle">Here’s a quick overview of your school fee account.</p>
    </div>
    <a class="btn btn-primary" href="<?= e(url('/fees')) ?>"><i class="bi bi-wallet2 me-2"></i>Fees & Payments</a>
</div>

<?php if (!$student): ?>
    <section class="panel">
        <p class="mb-0 text-muted">Your student profile is not available yet.</p>
    </section>
<?php else: ?>
    <section class="row g-4">
        <div class="col-xl-4">
            <section class="panel h-100">
                <div class="panel-header"><div><h2>Student Summary</h2><p>Your academic identity.</p></div><i class="bi bi-person-vcard text-primary fs-4"></i></div>
                <ul class="summary-list mb-3">
                    <li><span>Name</span><strong><?= e(trim($student['first_name'] . ' ' . ($student['other_name'] ? $student['other_name'] . ' ' : '') . $student['last_name'])) ?></strong></li>
                    <li><span>Admission No</span><strong><?= e($student['admission_no'] ?? '—') ?></strong></li>
                    <li><span>Matriculation</span><strong><?= e($student['matriculation_no'] ?? 'Pending') ?></strong></li>
                    <li><span>Programme</span><strong><?= e($student['programme'] ?? '—') ?></strong></li>
                    <li><span>Level</span><strong><?= e($student['level'] ?? '—') ?></strong></li>
                    <li><span>Session</span><strong><?= e($student['academic_session'] ?? '—') ?></strong></li>
                </ul>
                <a class="btn btn-outline-secondary btn-sm" href="<?= e(url('/profile')) ?>">View profile</a>
            </section>
        </div>
        <div class="col-xl-8">
            <section class="panel h-100">
                <div class="panel-header"><div><h2>Fee Overview</h2><p>Your current account position.</p></div><i class="bi bi-cash-stack text-primary fs-4"></i></div>
                <div class="row g-3">
                    <div class="col-md-4"><div class="metric-card small"><p>Total Fee</p><strong>₦<?= number_format((float)$summary['total_fee'], 2) ?></strong></div></div>
                    <div class="col-md-4"><div class="metric-card small"><p>Verified Paid</p><strong>₦<?= number_format((float)$summary['verified_amount'], 2) ?></strong></div></div>
                    <div class="col-md-4"><div class="metric-card small"><p>Outstanding</p><strong>₦<?= number_format((float)$summary['outstanding_balance'], 2) ?></strong></div></div>
                </div>
                <div class="status-line mt-4"><span class="text-muted">Payment status</span><span class="badge text-bg-<?= $summary['status'] === 'Paid' ? 'success' : ($summary['status'] === 'Part-paid' ? 'warning' : 'secondary') ?>"><?= e($summary['status']) ?></span></div>
            </section>
        </div>
    </section>

    <section class="panel mt-4">
        <div class="panel-header"><div><h2>Recent Payments</h2><p>Your latest payment activity.</p></div><a class="btn btn-outline-secondary btn-sm" href="<?= e(url('/fees')) ?>">View all payments</a></div>
        <?php if ($payments === []): ?>
            <div class="empty-state"><i class="bi bi-receipt"></i><strong>No payments yet</strong><span>Your payment history will appear here after you make a payment.</span></div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Receipt</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_slice($payments, 0, 3) as $payment): ?>
                        <tr>
                            <td><?= e($payment['receipt_no']) ?></td>
                            <td>₦<?= number_format((float)$payment['amount_paid'], 2) ?></td>
                            <td><span class="badge text-bg-<?= $payment['payment_status'] === 'verified' ? 'success' : ($payment['payment_status'] === 'rejected' ? 'danger' : 'warning') ?>"><?= e($payment['payment_status']) ?></span></td>
                            <td><?= e(date('M j, Y', strtotime($payment['paid_at']))) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </section>
<?php endif; ?>
