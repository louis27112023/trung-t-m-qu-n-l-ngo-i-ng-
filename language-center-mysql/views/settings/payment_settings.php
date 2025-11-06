<?php
// Check admin permission
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: index.php?page=dashboard');
    exit;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_payment_settings') {
        $settings = [
            'payment_methods' => $_POST['payment_methods'] ?? [],
            'bank_accounts' => $_POST['bank_accounts'] ?? [],
            'late_fee_percentage' => floatval($_POST['late_fee_percentage']),
            'grace_period_days' => intval($_POST['grace_period_days']),
            'minimum_deposit' => floatval($_POST['minimum_deposit']),
            'installment_allowed' => isset($_POST['installment_allowed']),
            'max_installments' => intval($_POST['max_installments'])
        ];
        
        file_put_contents(__DIR__ . '/../../config/payment_settings.json', json_encode($settings, JSON_PRETTY_PRINT));
        set_flash('Cập nhật cài đặt thanh toán thành công!');
    }
}

// Load current settings
$settings = [];
if (file_exists(__DIR__ . '/../../config/payment_settings.json')) {
    $settings = json_decode(file_get_contents(__DIR__ . '/../../config/payment_settings.json'), true);
}

// Default values
$settings = array_merge([
    'payment_methods' => ['cash', 'bank_transfer', 'momo'],
    'bank_accounts' => [
        ['bank' => 'Vietcombank', 'number' => '1234567890', 'holder' => 'NGUYEN VAN A'],
    ],
    'late_fee_percentage' => 1.0,
    'grace_period_days' => 5,
    'minimum_deposit' => 30.0,
    'installment_allowed' => true,
    'max_installments' => 3
], $settings);
?>

<div class="module-wrapper">
    <!-- Module Header -->
    <div class="module-header">
        <h2 class="module-title">
            <i class="bx bx-money"></i>
            Cài đặt thanh toán
        </h2>
        <p class="module-subtitle">Quản lý các phương thức và chính sách thanh toán</p>
    </div>

    <!-- Main Content -->
    <div class="module-content">
        <div class="row">
            <!-- Payment Methods -->
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5><i class="bx bx-credit-card me-2"></i>Phương thức thanh toán</h5>
                    </div>
                    <div class="card-body">
                        <form method="post" action="">
                            <input type="hidden" name="action" value="update_payment_settings">

                            <div class="mb-4">
                                <label class="form-label d-block">Chọn phương thức thanh toán</label>
                                
                                <div class="payment-method-grid">
                                    <!-- Cash -->
                                    <div class="payment-method-item">
                                        <input type="checkbox" class="btn-check" name="payment_methods[]" 
                                               id="method_cash" value="cash" autocomplete="off"
                                               <?= in_array('cash', $settings['payment_methods']) ? 'checked' : '' ?>>
                                        <label class="btn btn-outline-primary w-100 h-100" for="method_cash">
                                            <i class="bx bx-money"></i>
                                            <span>Tiền mặt</span>
                                        </label>
                                    </div>
                                    
                                    <!-- Bank Transfer -->
                                    <div class="payment-method-item">
                                        <input type="checkbox" class="btn-check" name="payment_methods[]" 
                                               id="method_bank" value="bank_transfer" autocomplete="off"
                                               <?= in_array('bank_transfer', $settings['payment_methods']) ? 'checked' : '' ?>>
                                        <label class="btn btn-outline-primary w-100 h-100" for="method_bank">
                                            <i class="bx bx-bank"></i>
                                            <span>Chuyển khoản</span>
                                        </label>
                                    </div>
                                    
                                    <!-- Momo -->
                                    <div class="payment-method-item">
                                        <input type="checkbox" class="btn-check" name="payment_methods[]" 
                                               id="method_momo" value="momo" autocomplete="off"
                                               <?= in_array('momo', $settings['payment_methods']) ? 'checked' : '' ?>>
                                        <label class="btn btn-outline-primary w-100 h-100" for="method_momo">
                                            <i class="bx bx-wallet"></i>
                                            <span>Momo</span>
                                        </label>
                                    </div>
                                    
                                    <!-- VNPay -->
                                    <div class="payment-method-item">
                                        <input type="checkbox" class="btn-check" name="payment_methods[]" 
                                               id="method_vnpay" value="vnpay" autocomplete="off"
                                               <?= in_array('vnpay', $settings['payment_methods']) ? 'checked' : '' ?>>
                                        <label class="btn btn-outline-primary w-100 h-100" for="method_vnpay">
                                            <i class="bx bx-credit-card"></i>
                                            <span>VNPay</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Bank Accounts -->
                            <div class="mb-3">
                                <label class="form-label">Tài khoản ngân hàng</label>
                                <div id="bankAccounts">
                                    <?php foreach ($settings['bank_accounts'] as $i => $account): ?>
                                    <div class="bank-account-item mb-2">
                                        <div class="input-group">
                                            <input type="text" class="form-control" name="bank_accounts[<?= $i ?>][bank]"
                                                   placeholder="Tên ngân hàng" value="<?= htmlspecialchars($account['bank']) ?>">
                                            <input type="text" class="form-control" name="bank_accounts[<?= $i ?>][number]"
                                                   placeholder="Số tài khoản" value="<?= htmlspecialchars($account['number']) ?>">
                                            <input type="text" class="form-control" name="bank_accounts[<?= $i ?>][holder]"
                                                   placeholder="Chủ tài khoản" value="<?= htmlspecialchars($account['holder']) ?>">
                                            <button type="button" class="btn btn-outline-danger" onclick="removeBank(this)">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <button type="button" class="btn btn-outline-secondary mt-2" onclick="addBank()">
                                    <i class="bx bx-plus"></i> Thêm tài khoản
                                </button>
                            </div>

                            <hr>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-save me-1"></i>Lưu thay đổi
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Payment Policies -->
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5><i class="bx bx-cog me-2"></i>Chính sách thanh toán</h5>
                    </div>
                    <div class="card-body">
                        <form method="post" action="">
                            <input type="hidden" name="action" value="update_payment_settings">

                            <div class="mb-3">
                                <label class="form-label">Phí trễ hạn</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="late_fee_percentage"
                                           value="<?= $settings['late_fee_percentage'] ?>" step="0.1" min="0" max="100">
                                    <span class="input-group-text">% / ngày</span>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Thời gian ân hạn</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="grace_period_days"
                                           value="<?= $settings['grace_period_days'] ?>" min="0" max="30">
                                    <span class="input-group-text">ngày</span>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Đặt cọc tối thiểu</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="minimum_deposit"
                                           value="<?= $settings['minimum_deposit'] ?>" step="1" min="0" max="100">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="installment_allowed" 
                                           name="installment_allowed" <?= $settings['installment_allowed'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="installment_allowed">
                                        Cho phép trả góp
                                    </label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Số đợt trả góp tối đa</label>
                                <input type="number" class="form-control" name="max_installments"
                                       value="<?= $settings['max_installments'] ?>" min="2" max="12">
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-save me-1"></i>Lưu thay đổi
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function addBank() {
    const container = document.getElementById('bankAccounts');
    const index = container.children.length;
    
    const html = `
        <div class="bank-account-item mb-2">
            <div class="input-group">
                <input type="text" class="form-control" name="bank_accounts[${index}][bank]"
                       placeholder="Tên ngân hàng">
                <input type="text" class="form-control" name="bank_accounts[${index}][number]"
                       placeholder="Số tài khoản">
                <input type="text" class="form-control" name="bank_accounts[${index}][holder]"
                       placeholder="Chủ tài khoản">
                <button type="button" class="btn btn-outline-danger" onclick="removeBank(this)">
                    <i class="bx bx-trash"></i>
                </button>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', html);
}

function removeBank(button) {
    button.closest('.bank-account-item').remove();
}
</script>

<style>
.payment-method-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 1rem;
    margin-bottom: 1rem;
}

.payment-method-item .btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    padding: 1rem;
    height: 100%;
}

.payment-method-item .btn i {
    font-size: 2rem;
}

.bank-account-item .input-group > * {
    border-radius: 0;
}

.bank-account-item .input-group > :first-child {
    border-top-left-radius: 0.375rem;
    border-bottom-left-radius: 0.375rem;
}

.bank-account-item .input-group > :last-child {
    border-top-right-radius: 0.375rem;
    border-bottom-right-radius: 0.375rem;
}

.form-check-input {
    width: 3rem !important;
    height: 1.5rem !important;
}

.form-switch .form-check-label {
    margin-left: 0.5rem;
}
</style>