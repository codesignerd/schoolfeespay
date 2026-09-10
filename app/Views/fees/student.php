<div class="page-heading">
    <div>
        <p class="eyebrow">Student portal</p>
        <h1>Fees & Payments</h1>
        <p class="page-subtitle">View your fee balance, make payments, and track payment status.</p>
    </div>
    <a class="btn btn-outline-secondary" href="<?= e(url('/dashboard')) ?>">Back to dashboard</a>
</div>

<section class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="metric-card small">
            <p>Total Fee</p>
            <strong>₦<?= number_format((float)$summary['total_fee'], 2) ?></strong>
        </div>
    </div>
    <div class="col-md-4">
        <div class="metric-card small">
            <p>Verified Paid</p>
            <strong>₦<?= number_format((float)$summary['verified_amount'], 2) ?></strong>
        </div>
    </div>
    <div class="col-md-4">
        <div class="metric-card small">
            <p>Outstanding</p>
            <strong>₦<?= number_format((float)$summary['outstanding_balance'], 2) ?></strong>
        </div>
    </div>
</section>

<div class="row g-4">
    <div class="col-xl-5">
        <section class="panel">
            <div class="panel-header"><div><h2>Make a Payment</h2><p>Submit a payment for verification.</p></div><i class="bi bi-send text-primary fs-4"></i></div>
            <form class="row g-3" method="post" action="<?= e(url('/fees/payment')) ?>">
                <?= csrf_field() ?>
                <div class="col-12">
                    <label class="form-label">Invoice</label>
                    <select class="form-select" name="invoice_id">
                        <?php foreach ($invoices as $invoice): ?>
                            <option value="<?= e($invoice['id']) ?>"><?= e($invoice['invoice_no'] . ' · ₦' . number_format((float)$invoice['balance'], 2) . ' remaining') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Receipt No</label>
                    <input class="form-control" name="receipt_no" value="<?= e('RCT-' . date('Y') . '-' . random_int(1000, 9999)) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Amount</label>
                    <input class="form-control" type="number" step="0.01" min="1" name="amount_paid" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Payment Method</label>
                    <select class="form-select" name="payment_method">
                        <?php foreach (['cash', 'bank', 'card'] as $method): ?>
                            <option value="<?= e($method) ?>"><?= e(ucfirst($method)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Reference</label>
                    <input class="form-control" name="payment_reference" placeholder="Bank ref / transfer ID">
                </div>
                <div class="col-12">
                    <button class="btn btn-primary" type="submit">Submit Payment</button>
                </div>
            </form>
        </section>
    </div>

    <div class="col-xl-7">
        <section class="panel">
            <div class="panel-header"><div><h2>Current Fees</h2><p>Your official invoice and outstanding balance.</p></div><i class="bi bi-receipt text-primary fs-4"></i></div>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Invoice</th>
                            <th>Amount</th>
                            <th>Due date</th>
                            <th>Balance</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($invoices as $invoice): ?>
                            <tr>
                                <td><?= e($invoice['invoice_no']) ?></td>
                                <td>₦<?= number_format((float)$invoice['amount'], 2) ?></td>
                                <td><?= e(date('M j, Y', strtotime($invoice['due_date']))) ?></td>
                                <td>₦<?= number_format((float)$invoice['balance'], 2) ?></td>
                                <td><span class="badge text-bg-secondary"><?= e($invoice['status']) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <div class="col-12">
        <section class="panel">
            <div class="panel-header"><div><h2>Payment History</h2><p>Track pending, verified, and rejected payments.</p></div><i class="bi bi-clock-history text-primary fs-4"></i></div>
            <?php if ($payments === []): ?>
                <div class="empty-state"><i class="bi bi-receipt"></i><strong>No payments yet</strong><span>Your payment history will appear here after you make a payment.</span></div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Receipt</th>
                            <th>Invoice</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Reference</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payments as $payment): ?>
                            <tr>
                                <td><a href="<?= e(url('/fees/receipt?id=' . $payment['id'])) ?>"><?= e($payment['receipt_no']) ?></a></td>
                                <td><?= e($payment['invoice_no']) ?></td>
                                <td>₦<?= number_format((float)$payment['amount_paid'], 2) ?></td>
                                <td><?= e($payment['payment_method']) ?></td>
                                <td><?= e($payment['payment_reference'] ?: '—') ?></td>
                                <td><span class="badge text-bg-<?= $payment['payment_status'] === 'verified' ? 'success' : ($payment['payment_status'] === 'rejected' ? 'danger' : 'warning') ?>"><?= e($payment['payment_status']) ?></span></td>
                                <td><?= e(date('M j, Y H:i', strtotime($payment['paid_at']))) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </section>
    </div>
</div>
