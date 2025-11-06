<?php
require_once('../functions/auth.php');
require_once('../functions/expense.php');

// Ensure user is logged in
checkAuth();

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $result = addExpense($_POST);
                break;
            case 'update':
                $result = updateExpense($_POST['id'], $_POST);
                break;
            case 'delete':
                $result = deleteExpense($_POST['id']);
                break;
        }
    }
}

// Get filters from query string
$filters = [
    'category' => $_GET['category'] ?? null,
    'date_from' => $_GET['date_from'] ?? null,
    'date_to' => $_GET['date_to'] ?? null
];

// Get expenses
$expenses = getExpenses($filters);

// Get expense report
$report = getExpenseReport($filters);

// Get categories
$categories = getExpenseCategories();

// Include header
require_once('../views/partials/header.php');
?>

<!-- Main content -->
<div class="container-fluid px-4">
    <h1 class="mt-4">Quản lý Chi phí</h1>
    
    <!-- Summary Cards -->
    <div class="row mt-4">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <h4>Tổng chi phí</h4>
                    <h2><?php echo number_format($report['total_amount'], 0); ?> VNĐ</h2>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-info text-white mb-4">
                <div class="card-body">
                    <h4>Số khoản chi</h4>
                    <h2><?php echo $report['total_count']; ?> khoản</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Category Report -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-chart-pie"></i>
            Thống kê theo danh mục
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <canvas id="expenseChart"></canvas>
                </div>
                <div class="col-md-4">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Danh mục</th>
                                <th>Số tiền</th>
                                <th>%</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($report['by_category'] as $cat): ?>
                            <tr>
                                <td><?php echo $categories[$cat['category']] ?? $cat['category']; ?></td>
                                <td><?php echo number_format($cat['total_amount'], 0); ?></td>
                                <td><?php echo round(($cat['total_amount'] / $report['total_amount']) * 100, 1); ?>%</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-filter"></i>
            Bộ lọc
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Danh mục</label>
                    <select name="category" class="form-select">
                        <option value="">Tất cả</option>
                        <?php foreach ($categories as $key => $value): ?>
                        <option value="<?php echo $key; ?>" <?php echo $key === ($filters['category'] ?? '') ? 'selected' : ''; ?>>
                            <?php echo $value; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Từ ngày</label>
                    <input type="date" name="date_from" class="form-control" value="<?php echo $filters['date_from']; ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Đến ngày</label>
                    <input type="date" name="date_to" class="form-control" value="<?php echo $filters['date_to']; ?>">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Lọc</button>
                    <a href="expenses.php" class="btn btn-secondary">Đặt lại</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Expenses Table -->
    <div class="card mb-4">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-table"></i>
                    Danh sách chi phí
                </div>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
                    <i class="fas fa-plus"></i> Thêm mới
                </button>
            </div>
        </div>
        <div class="card-body">
            <table id="expenseTable" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Danh mục</th>
                        <th>Mô tả</th>
                        <th>Số tiền</th>
                        <th>Ngày chi</th>
                        <th>Người chi</th>
                        <th>Số hóa đơn</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($expenses as $expense): ?>
                    <tr>
                        <td><?php echo $expense['id']; ?></td>
                        <td><?php echo $categories[$expense['category']] ?? $expense['category']; ?></td>
                        <td><?php echo htmlspecialchars($expense['description']); ?></td>
                        <td><?php echo number_format($expense['amount'], 0); ?> VNĐ</td>
                        <td><?php echo date('d/m/Y', strtotime($expense['expense_date'])); ?></td>
                        <td><?php echo htmlspecialchars($expense['paid_by']); ?></td>
                        <td><?php echo htmlspecialchars($expense['receipt_no']); ?></td>
                        <td>
                            <button type="button" class="btn btn-sm btn-primary" onclick="editExpense(<?php echo htmlspecialchars(json_encode($expense)); ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" onclick="deleteExpense(<?php echo $expense['id']; ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Expense Modal -->
<div class="modal fade" id="addExpenseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="add">
                
                <div class="modal-header">
                    <h5 class="modal-title">Thêm chi phí mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Danh mục</label>
                        <select name="category" class="form-select" required>
                            <?php foreach ($categories as $key => $value): ?>
                            <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Mô tả</label>
                        <textarea name="description" class="form-control" required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Số tiền</label>
                        <input type="number" name="amount" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Ngày chi</label>
                        <input type="date" name="expense_date" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Phương thức thanh toán</label>
                        <select name="payment_method" class="form-select" required>
                            <option value="cash">Tiền mặt</option>
                            <option value="transfer">Chuyển khoản</option>
                            <option value="card">Thẻ</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Người chi</label>
                        <input type="text" name="paid_by" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Số hóa đơn</label>
                        <input type="text" name="receipt_no" class="form-control">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Ghi chú</label>
                        <textarea name="notes" class="form-control"></textarea>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary">Lưu</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Expense Modal - Similar to Add Modal but with id prefixes -->
<div class="modal fade" id="editExpenseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="edit_id">
                
                <div class="modal-header">
                    <h5 class="modal-title">Cập nhật chi phí</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body">
                    <!-- Similar fields as Add Modal -->
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary">Lưu</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script>
function editExpense(expense) {
    // Populate edit modal with expense data
    $('#edit_id').val(expense.id);
    $('#editExpenseModal [name="category"]').val(expense.category);
    $('#editExpenseModal [name="description"]').val(expense.description);
    $('#editExpenseModal [name="amount"]').val(expense.amount);
    $('#editExpenseModal [name="expense_date"]').val(expense.expense_date);
    $('#editExpenseModal [name="payment_method"]').val(expense.payment_method);
    $('#editExpenseModal [name="paid_by"]').val(expense.paid_by);
    $('#editExpenseModal [name="receipt_no"]').val(expense.receipt_no);
    $('#editExpenseModal [name="notes"]').val(expense.notes);
    
    $('#editExpenseModal').modal('show');
}

function deleteExpense(id) {
    if (confirm('Bạn có chắc chắn muốn xóa khoản chi này?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Initialize DataTable
$(document).ready(function() {
    $('#expenseTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/vi.json'
        }
    });
    
    // Initialize expense chart
    const ctx = document.getElementById('expenseChart').getContext('2d');
    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: <?php echo json_encode(array_map(function($cat) use ($categories) {
                return $categories[$cat['category']] ?? $cat['category'];
            }, $report['by_category'])); ?>,
            datasets: [{
                data: <?php echo json_encode(array_map(function($cat) {
                    return $cat['total_amount'];
                }, $report['by_category'])); ?>,
                backgroundColor: [
                    '#007bff', '#28a745', '#dc3545', '#ffc107', 
                    '#17a2b8', '#6c757d', '#6f42c1', '#e83e8c'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
});
</script>

<?php
// Include footer
require_once('../views/partials/footer.php');
?>