<?php
require_once('../functions/auth.php');
require_once('../functions/salary.php');

// Ensure user is logged in
checkAuth();

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $result = addSalary($_POST);
                break;
            case 'update':
                $result = updateSalary($_POST['id'], $_POST);
                break;
            case 'delete':
                $result = deleteSalary($_POST['id']);
                break;
            case 'calculate':
                $result = calculateSalary($_POST['teacher_id'], $_POST['month'], $_POST['year']);
                echo json_encode($result);
                exit;
        }
    }
}

// Get filters from query string
$filters = [
    'teacher_id' => $_GET['teacher_id'] ?? null,
    'month' => $_GET['month'] ?? date('n'),
    'year' => $_GET['year'] ?? date('Y'),
    'payment_status' => $_GET['payment_status'] ?? null
];

// Get salaries
$salaries = getSalaries($filters);

// Get salary report
$report = getSalaryReport(['year' => $filters['year']]);

// Include header
require_once('../views/partials/header.php');
?>

<!-- Main content -->
<div class="container-fluid px-4">
    <h1 class="mt-4">Quản lý Lương</h1>
    
    <!-- Summary Cards -->
    <div class="row mt-4">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <h4>Tổng chi lương</h4>
                    <h2><?php echo number_format($report['total_paid'], 0); ?> VNĐ</h2>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <h4>Tổng giờ dạy</h4>
                    <h2><?php echo number_format($report['total_hours'], 1); ?> giờ</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Teacher Report -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-chart-bar"></i>
            Thống kê theo giáo viên
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <canvas id="salaryChart"></canvas>
                </div>
                <div class="col-md-4">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Giáo viên</th>
                                <th>Lương TB</th>
                                <th>Giờ dạy</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($report['by_teacher'] as $teacher): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($teacher['teacher_name']); ?></td>
                                <td><?php echo number_format($teacher['average_salary'], 0); ?></td>
                                <td><?php echo number_format($teacher['total_hours'], 1); ?></td>
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
                <div class="col-md-3">
                    <label class="form-label">Giáo viên</label>
                    <select name="teacher_id" class="form-select">
                        <option value="">Tất cả</option>
                        <!-- Add PHP code to populate teachers -->
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tháng</label>
                    <select name="month" class="form-select">
                        <?php for ($i = 1; $i <= 12; $i++): ?>
                        <option value="<?php echo $i; ?>" <?php echo $i == $filters['month'] ? 'selected' : ''; ?>>
                            Tháng <?php echo $i; ?>
                        </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Năm</label>
                    <select name="year" class="form-select">
                        <?php for ($i = date('Y'); $i >= date('Y')-2; $i--): ?>
                        <option value="<?php echo $i; ?>" <?php echo $i == $filters['year'] ? 'selected' : ''; ?>>
                            <?php echo $i; ?>
                        </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Trạng thái</label>
                    <select name="payment_status" class="form-select">
                        <option value="">Tất cả</option>
                        <option value="pending" <?php echo $filters['payment_status'] === 'pending' ? 'selected' : ''; ?>>
                            Chưa thanh toán
                        </option>
                        <option value="paid" <?php echo $filters['payment_status'] === 'paid' ? 'selected' : ''; ?>>
                            Đã thanh toán
                        </option>
                    </select>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Lọc</button>
                    <a href="salaries.php" class="btn btn-secondary">Đặt lại</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Salaries Table -->
    <div class="card mb-4">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-table"></i>
                    Danh sách lương
                </div>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSalaryModal">
                    <i class="fas fa-plus"></i> Thêm mới
                </button>
            </div>
        </div>
        <div class="card-body">
            <table id="salaryTable" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Giáo viên</th>
                        <th>Tháng/Năm</th>
                        <th>Giờ dạy</th>
                        <th>Lương cơ bản</th>
                        <th>Phụ cấp</th>
                        <th>Khấu trừ</th>
                        <th>Tổng lương</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($salaries as $salary): ?>
                    <tr>
                        <td><?php echo $salary['id']; ?></td>
                        <td><?php echo htmlspecialchars($salary['teacher_name']); ?></td>
                        <td><?php echo sprintf('%02d/%d', $salary['month'], $salary['year']); ?></td>
                        <td><?php echo number_format($salary['teaching_hours'], 1); ?></td>
                        <td><?php echo number_format($salary['base_salary'], 0); ?></td>
                        <td><?php echo number_format($salary['bonus'], 0); ?></td>
                        <td><?php echo number_format($salary['deductions'], 0); ?></td>
                        <td><?php echo number_format($salary['total_salary'], 0); ?></td>
                        <td>
                            <span class="badge bg-<?php echo $salary['payment_status'] === 'paid' ? 'success' : 'warning'; ?>">
                                <?php echo $salary['payment_status'] === 'paid' ? 'Đã thanh toán' : 'Chưa thanh toán'; ?>
                            </span>
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-primary" onclick="editSalary(<?php echo htmlspecialchars(json_encode($salary)); ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" onclick="deleteSalary(<?php echo $salary['id']; ?>)">
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

<!-- Add Salary Modal -->
<div class="modal fade" id="addSalaryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="addSalaryForm">
                <input type="hidden" name="action" value="add">
                
                <div class="modal-header">
                    <h5 class="modal-title">Thêm lương mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label">Giáo viên</label>
                            <select name="teacher_id" class="form-select" required>
                                <!-- Add PHP code to populate teachers -->
                            </select>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Tháng</label>
                            <select name="month" class="form-select" required>
                                <?php for ($i = 1; $i <= 12; $i++): ?>
                                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Năm</label>
                            <select name="year" class="form-select" required>
                                <?php for ($i = date('Y'); $i >= date('Y')-2; $i--): ?>
                                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                    
                    <button type="button" class="btn btn-info mb-3" onclick="calculateSalary()">
                        Tính lương
                    </button>
                    
                    <div class="mb-3">
                        <label class="form-label">Lương cơ bản</label>
                        <input type="number" name="base_salary" class="form-control" required>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Số giờ dạy</label>
                            <input type="number" step="0.1" name="teaching_hours" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Lương theo giờ</label>
                            <input type="number" name="hourly_rate" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Phụ cấp</label>
                            <input type="number" name="bonus" class="form-control" value="0" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Khấu trừ</label>
                            <input type="number" name="deductions" class="form-control" value="0" required>
                        </div>
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

<!-- Edit Salary Modal - Similar to Add Modal -->
<div class="modal fade" id="editSalaryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="edit_id">
                
                <div class="modal-header">
                    <h5 class="modal-title">Cập nhật lương</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body">
                    <!-- Similar fields as Add Modal -->
                    <div class="mb-3">
                        <label class="form-label">Trạng thái thanh toán</label>
                        <select name="payment_status" class="form-select" required>
                            <option value="pending">Chưa thanh toán</option>
                            <option value="paid">Đã thanh toán</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Ngày thanh toán</label>
                        <input type="date" name="payment_date" class="form-control">
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
function calculateSalary() {
    const form = document.getElementById('addSalaryForm');
    const data = {
        action: 'calculate',
        teacher_id: form.elements['teacher_id'].value,
        month: form.elements['month'].value,
        year: form.elements['year'].value
    };
    
    fetch('salaries.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams(data)
    })
    .then(response => response.json())
    .then(data => {
        form.elements['base_salary'].value = data.base_salary;
        form.elements['teaching_hours'].value = data.teaching_hours;
        form.elements['hourly_rate'].value = data.hourly_rate;
        form.elements['bonus'].value = data.bonus;
        form.elements['deductions'].value = data.deductions;
    });
}

function editSalary(salary) {
    // Populate edit modal with salary data
    $('#edit_id').val(salary.id);
    $('#editSalaryModal [name="payment_status"]').val(salary.payment_status);
    $('#editSalaryModal [name="payment_date"]').val(salary.payment_date);
    
    $('#editSalaryModal').modal('show');
}

function deleteSalary(id) {
    if (confirm('Bạn có chắc chắn muốn xóa bảng lương này?')) {
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
    $('#salaryTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/vi.json'
        }
    });
    
    // Initialize salary chart
    const ctx = document.getElementById('salaryChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_map(function($teacher) {
                return $teacher['teacher_name'];
            }, $report['by_teacher'])); ?>,
            datasets: [{
                label: 'Tổng lương',
                data: <?php echo json_encode(array_map(function($teacher) {
                    return $teacher['total_paid'];
                }, $report['by_teacher'])); ?>,
                backgroundColor: '#007bff'
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return new Intl.NumberFormat('vi-VN').format(value);
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
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