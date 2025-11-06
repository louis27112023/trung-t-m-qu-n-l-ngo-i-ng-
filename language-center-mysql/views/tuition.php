<?php
require_once('../functions/auth.php');
require_once('../functions/tuition.php');

// Ensure user is logged in
checkAuth();

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $result = addTuitionFee($_POST);
                break;
            case 'update':
                $result = updateTuitionFee($_POST['id'], $_POST);
                break;
            case 'delete':
                $result = deleteTuitionFee($_POST['id']);
                break;
        }
    }
}

// Get filters from query string
$filters = [
    'student_id' => $_GET['student_id'] ?? null,
    'course_id' => $_GET['course_id'] ?? null,
    'payment_status' => $_GET['payment_status'] ?? null,
    'date_from' => $_GET['date_from'] ?? null,
    'date_to' => $_GET['date_to'] ?? null
];

// Get tuition fees
$tuitionFees = getTuitionFees($filters);

// Get report summary
$report = getTuitionReport($filters);

// Include header
require_once('../views/partials/header.php');
?>

<!-- Main content -->
<div class="container-fluid px-4">
    <h1 class="mt-4">Quản lý Học phí</h1>
    
    <!-- Summary Cards -->
    <div class="row mt-4">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <h4>Tổng thu</h4>
                    <h2><?php echo number_format($report['total_paid'], 0); ?> VNĐ</h2>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white mb-4">
                <div class="card-body">
                    <h4>Chưa thu</h4>
                    <h2><?php echo number_format($report['total_pending'], 0); ?> VNĐ</h2>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <h4>Đã thanh toán</h4>
                    <h2><?php echo $report['paid_fees']; ?> khoản</h2>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-danger text-white mb-4">
                <div class="card-body">
                    <h4>Chưa thanh toán</h4>
                    <h2><?php echo $report['pending_fees']; ?> khoản</h2>
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
                <div class="col-md-3">
                    <label class="form-label">Học viên</label>
                    <select name="student_id" class="form-select">
                        <option value="">Tất cả</option>
                        <!-- Add PHP code to populate students -->
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Khóa học</label>
                    <select name="course_id" class="form-select">
                        <option value="">Tất cả</option>
                        <!-- Add PHP code to populate courses -->
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Trạng thái</label>
                    <select name="payment_status" class="form-select">
                        <option value="">Tất cả</option>
                        <option value="pending">Chưa thanh toán</option>
                        <option value="partial">Thanh toán một phần</option>
                        <option value="paid">Đã thanh toán</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Từ ngày</label>
                    <input type="date" name="date_from" class="form-control" value="<?php echo $filters['date_from']; ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Đến ngày</label>
                    <input type="date" name="date_to" class="form-control" value="<?php echo $filters['date_to']; ?>">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Lọc</button>
                    <a href="tuition.php" class="btn btn-secondary">Đặt lại</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tuition Fees Table -->
    <div class="card mb-4">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-table"></i>
                    Danh sách học phí
                </div>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTuitionModal">
                    <i class="fas fa-plus"></i> Thêm mới
                </button>
            </div>
        </div>
        <div class="card-body">
            <table id="tuitionTable" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Học viên</th>
                        <th>Khóa học</th>
                        <th>Số tiền</th>
                        <th>Đã trả</th>
                        <th>Hạn nộp</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tuitionFees as $fee): ?>
                    <tr>
                        <td><?php echo $fee['id']; ?></td>
                        <td><?php echo htmlspecialchars($fee['student_name']); ?></td>
                        <td><?php echo htmlspecialchars($fee['course_name']); ?></td>
                        <td><?php echo number_format($fee['amount'], 0); ?> VNĐ</td>
                        <td><?php echo number_format($fee['paid_amount'], 0); ?> VNĐ</td>
                        <td><?php echo date('d/m/Y', strtotime($fee['due_date'])); ?></td>
                        <td>
                            <?php
                            $statusClass = [
                                'pending' => 'badge bg-danger',
                                'partial' => 'badge bg-warning',
                                'paid' => 'badge bg-success'
                            ];
                            $statusText = [
                                'pending' => 'Chưa thanh toán',
                                'partial' => 'Thanh toán một phần',
                                'paid' => 'Đã thanh toán'
                            ];
                            ?>
                            <span class="<?php echo $statusClass[$fee['payment_status']]; ?>">
                                <?php echo $statusText[$fee['payment_status']]; ?>
                            </span>
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-primary" onclick="editTuition(<?php echo $fee['id']; ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" onclick="deleteTuition(<?php echo $fee['id']; ?>)">
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

<!-- Add Tuition Modal -->
<div class="modal fade" id="addTuitionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="add">
                
                <div class="modal-header">
                    <h5 class="modal-title">Thêm học phí mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Học viên</label>
                        <select name="student_id" class="form-select" required>
                            <!-- Add PHP code to populate students -->
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Khóa học</label>
                        <select name="course_id" class="form-select" required>
                            <!-- Add PHP code to populate courses -->
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Số tiền</label>
                        <input type="number" name="amount" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Hạn nộp</label>
                        <input type="date" name="due_date" class="form-control" required>
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

<!-- Edit Tuition Modal -->
<div class="modal fade" id="editTuitionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="edit_id">
                
                <div class="modal-header">
                    <h5 class="modal-title">Cập nhật học phí</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body">
                    <!-- Similar fields as Add Modal, but with id prefix 'edit_' -->
                    <div class="mb-3">
                        <label class="form-label">Trạng thái thanh toán</label>
                        <select name="payment_status" class="form-select" required>
                            <option value="pending">Chưa thanh toán</option>
                            <option value="partial">Thanh toán một phần</option>
                            <option value="paid">Đã thanh toán</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Số tiền đã trả</label>
                        <input type="number" name="paid_amount" class="form-control">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Ngày thanh toán</label>
                        <input type="date" name="payment_date" class="form-control">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Phương thức thanh toán</label>
                        <select name="payment_method" class="form-select">
                            <option value="cash">Tiền mặt</option>
                            <option value="transfer">Chuyển khoản</option>
                            <option value="card">Thẻ</option>
                        </select>
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

<!-- JavaScript -->
<script>
function editTuition(id) {
    // Add code to populate edit modal
    $('#edit_id').val(id);
    $('#editTuitionModal').modal('show');
}

function deleteTuition(id) {
    if (confirm('Bạn có chắc chắn muốn xóa khoản học phí này?')) {
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
    $('#tuitionTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/vi.json'
        }
    });
});
</script>

<?php
// Include footer
require_once('../views/partials/footer.php');
?>