<?php
require_once __DIR__ . '/../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save_capital') {
        $capital = max(0, (float) ($_POST['capital'] ?? 0));
        set_setting($pdo, 'capital', (string) $capital);
        set_setting($pdo, 'current_capital', (string) $capital);
        set_flash('success', 'Capital saved successfully.');
        redirect('modules/settings/index.php');
    }

    if ($action === 'add_expense') {
        $expenseAmount = max(0, (float) ($_POST['expense_amount'] ?? 0));
        $expenseNote = trim($_POST['expense_note'] ?? 'Manual expense');
        $currentCapital = (float) get_setting($pdo, 'current_capital', '0');

        if ($expenseAmount <= 0) {
            set_flash('danger', 'Expense amount must be greater than zero.');
            redirect('modules/settings/index.php');
        }

        $pdo->beginTransaction();
        try {
            $expenseStmt = $pdo->prepare('INSERT INTO capital_expenses (amount, note) VALUES (:amount, :note)');
            $expenseStmt->execute([
                'amount' => $expenseAmount,
                'note' => $expenseNote,
            ]);

            set_setting($pdo, 'current_capital', (string) max(0, $currentCapital - $expenseAmount));
            $pdo->commit();
            set_flash('success', 'Expense recorded and deducted from capital.');
        } catch (Throwable $e) {
            $pdo->rollBack();
            set_flash('danger', 'Unable to save expense.');
        }

        redirect('modules/settings/index.php');
    }

    if ($action === 'save_audit_alert') {
        $enabled = isset($_POST['audit_enabled']) ? '1' : '0';
        $nextAt = trim($_POST['audit_next_at'] ?? '');
        $recurrenceDays = max(0, (int) ($_POST['audit_recurrence_days'] ?? 0));

        set_setting($pdo, 'audit_enabled', $enabled);
        set_setting($pdo, 'audit_recurrence_days', (string) $recurrenceDays);
        set_setting($pdo, 'audit_snooze_until', '');

        if ($nextAt !== '') {
            $dt = DateTime::createFromFormat('Y-m-d\TH:i', $nextAt);
            if ($dt !== false) {
                set_setting($pdo, 'audit_next_at', $dt->format('Y-m-d H:i:s'));
            }
        }

        set_flash('success', 'Audit alert settings saved.');
        redirect('modules/settings/index.php');
    }

    if ($action === 'snooze_audit') {
        $snoozeUntil = date('Y-m-d H:i:s', strtotime('+1 minute'));
        set_setting($pdo, 'audit_snooze_until', $snoozeUntil);
        set_flash('info', 'Audit reminder snoozed for 1 minute.');
        redirect('modules/settings/index.php');
    }

    if ($action === 'complete_audit') {
        $recurrenceDays = (int) get_setting($pdo, 'audit_recurrence_days', '0');
        $nextAt = null;

        if ($recurrenceDays > 0) {
            $nextAt = date('Y-m-d H:i:s', strtotime('+' . $recurrenceDays . ' days'));
        }

        set_setting($pdo, 'audit_snooze_until', '');

        if ($nextAt !== null) {
            set_setting($pdo, 'audit_next_at', $nextAt);
            set_flash('success', 'Audit checked. Next reminder scheduled automatically.');
        } else {
            set_setting($pdo, 'audit_enabled', '0');
            set_flash('success', 'Audit checked. Reminder disabled because recurrence is 0 days.');
        }

        redirect('modules/settings/index.php');
    }
}

$capital = (float) get_setting($pdo, 'capital', '0');
$currentCapital = (float) get_setting($pdo, 'current_capital', '0');
$auditEnabled = get_setting($pdo, 'audit_enabled', '0') === '1';
$auditNextAt = get_setting($pdo, 'audit_next_at', '');
$auditRecurrenceDays = (int) get_setting($pdo, 'audit_recurrence_days', '7');

$auditNextAtInput = '';
if ($auditNextAt !== '') {
    $auditTs = strtotime($auditNextAt);
    if ($auditTs !== false) {
        $auditNextAtInput = date('Y-m-d\TH:i', $auditTs);
    }
}

$totalExpenses = (float) $pdo->query('SELECT COALESCE(SUM(amount), 0) FROM capital_expenses')->fetchColumn();
$expenseRows = $pdo->query('SELECT * FROM capital_expenses ORDER BY created_at DESC LIMIT 15')->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="row g-3 mb-3">
    <div class="col-md-4"><div class="card app-card h-100"><div class="card-body"><p class="text-secondary mb-1">Initial Capital</p><h4 class="mb-0"><?= e(format_currency($capital)) ?></h4></div></div></div>
    <div class="col-md-4"><div class="card app-card h-100"><div class="card-body"><p class="text-secondary mb-1">Current Capital</p><h4 class="mb-0"><?= e(format_currency($currentCapital)) ?></h4></div></div></div>
    <div class="col-md-4"><div class="card app-card h-100"><div class="card-body"><p class="text-secondary mb-1">Total Expenses</p><h4 class="mb-0"><?= e(format_currency($totalExpenses)) ?></h4></div></div></div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card app-card h-100">
            <div class="card-body">
                <h4 class="section-title mb-3"><i class="bi bi-wallet2 me-2"></i>Capital & Expense Settings</h4>
                <form method="POST" class="row g-3 mb-4 border-bottom pb-4">
                    <input type="hidden" name="action" value="save_capital">
                    <div class="col-12">
                        <label class="form-label">Set Capital</label>
                        <input type="number" class="form-control" name="capital" min="0" step="0.01" value="<?= e((string) $capital) ?>" required>
                        <small class="text-secondary">This resets current capital to the same value.</small>
                    </div>
                    <div class="col-12">
                        <button class="btn btn-primary">Save Capital</button>
                    </div>
                </form>

                <form method="POST" class="row g-3">
                    <input type="hidden" name="action" value="add_expense">
                    <div class="col-md-5">
                        <label class="form-label">Expense Amount</label>
                        <input type="number" class="form-control" name="expense_amount" min="0" step="0.01" required>
                    </div>
                    <div class="col-md-7">
                        <label class="form-label">Note</label>
                        <input type="text" class="form-control" name="expense_note" placeholder="e.g. shelves, packaging, etc.">
                    </div>
                    <div class="col-12">
                        <button class="btn btn-outline-primary">Add Expense</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card app-card h-100">
            <div class="card-body">
                <h4 class="section-title mb-3"><i class="bi bi-bell me-2"></i>Physical Inventory Audit Alert</h4>
                <form method="POST" class="row g-3">
                    <input type="hidden" name="action" value="save_audit_alert">
                    <div class="col-12 form-check form-switch ms-1">
                        <input class="form-check-input" type="checkbox" role="switch" id="audit_enabled" name="audit_enabled" <?= $auditEnabled ? 'checked' : '' ?>>
                        <label class="form-check-label" for="audit_enabled">Enable audit reminder alert</label>
                    </div>
                    <div class="col-12">
                        <label class="form-label">First Reminder Date & Time</label>
                        <input type="datetime-local" class="form-control" name="audit_next_at" value="<?= e($auditNextAtInput) ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Recurrence (days)</label>
                        <input type="number" class="form-control" name="audit_recurrence_days" min="0" value="<?= (int) $auditRecurrenceDays ?>" required>
                        <small class="text-secondary">Set to 0 to disable recurring reminders after one completed check.</small>
                    </div>
                    <div class="col-12">
                        <button class="btn btn-primary">Save Alert Settings</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="card app-card mt-3">
    <div class="card-body">
        <h4 class="section-title mb-3"><i class="bi bi-table me-2"></i>Expense History</h4>
        <div class="table-responsive">
            <table class="table table-striped table-bordered align-middle mb-0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Note</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$expenseRows): ?>
                        <tr><td colspan="3" class="text-center">No expenses yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($expenseRows as $expense): ?>
                            <tr>
                                <td><?= e($expense['created_at']) ?></td>
                                <td><?= e(format_currency((float) $expense['amount'])) ?></td>
                                <td><?= e($expense['note'] ?? '') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
