<?php
require_once('../functions/auth.php');
require_once('../functions/report.php');

// Ensure user is logged in
checkAuth();

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'save_report') {
        $result = saveFinancialReport($_POST);
        // Handle result
    }
}

// Get filters from query string
$filters = [
    'date_from' => $_GET['date_from'] ?? date('Y-m-d', strtotime('-1 year')),
    'date_to' => $_GET['date_to'] ?? date('Y-m-d'),
    'report_type' => $_GET['report_type'] ?? null
];

// Get financial report data
$report = getFinancialReport($filters);

// Include header
require_once('../views/partials/header.php');
?>

<!-- Main content -->
<div class="container-fluid px-4">
    <h1 class="mt-4">Báo cáo Tài chính</h1>
    
    <!-- Summary Cards -->
    <div class="row mt-4">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <h4>Tổng thu</h4>
                    <h2><?php echo number_format($report['income']['total'], 0); ?> VNĐ</h2>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-danger text-white mb-4">
                <div class="card-body">
                    <h4>Tổng chi</h4>
                    <h2><?php echo number_format($report['expenses']['total'], 0); ?> VNĐ</h2>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card <?php echo $report['profit'] >= 0 ? 'bg-success' : 'bg-danger'; ?> text-white mb-4">
                <div class="card-body">
                    <h4>Lợi nhuận</h4>
                    <h2><?php echo number_format($report['profit'], 0); ?> VNĐ</h2>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-info text-white mb-4">
                <div class="card-body">
                    <h4>Học viên</h4>
                    <h2><?php echo $report['statistics']['students']; ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-filter"></i>
            Thời gian báo cáo
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Từ ngày</label>
                    <input type="date" name="date_from" class="form-control" value="<?php echo $filters['date_from']; ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Đến ngày</label>
                    <input type="date" name="date_to" class="form-control" value="<?php echo $filters['date_to']; ?>">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Xem báo cáo</button>
                    <button type="button" class="btn btn-success" onclick="exportReport()">
                        <i class="fas fa-file-excel"></i> Xuất Excel
                    </button>
                    <button type="button" class="btn btn-danger" onclick="printReport()">
                        <i class="fas fa-print"></i> In báo cáo
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Income & Expense Chart -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-line"></i>
                    Biểu đồ Thu - Chi theo tháng
                </div>
                <div class="card-body">
                    <canvas id="financialChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-pie"></i>
                    Cơ cấu chi phí
                </div>
                <div class="card-body">
                    <canvas id="expenseChart"></canvas>
                    <div class="mt-3">
                        <table class="table table-sm">
                            <tbody>
                                <tr>
                                    <td>Chi lương:</td>
                                    <td class="text-end"><?php echo number_format($report['expenses']['salary'], 0); ?> VNĐ</td>
                                    <td class="text-end"><?php echo round(($report['expenses']['salary'] / $report['expenses']['total']) * 100, 1); ?>%</td>
                                </tr>
                                <tr>
                                    <td>Chi hoạt động:</td>
                                    <td class="text-end"><?php echo number_format($report['expenses']['operation'], 0); ?> VNĐ</td>
                                    <td class="text-end"><?php echo round(($report['expenses']['operation'] / $report['expenses']['total']) * 100, 1); ?>%</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-bar"></i>
                    Thống kê chi tiết
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Tháng</th>
                                    <th>Thu</th>
                                    <th>Chi</th>
                                    <th>Lợi nhuận</th>
                                    <th>% Tăng trưởng</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $previousProfit = 0;
                                foreach ($report['trends'] as $month => $data):
                                    $growth = $previousProfit != 0 ? 
                                        (($data['profit'] - $previousProfit) / abs($previousProfit)) * 100 : 0;
                                    $previousProfit = $data['profit'];
                                ?>
                                <tr>
                                    <td><?php echo date('m/Y', strtotime($month . '-01')); ?></td>
                                    <td><?php echo number_format($data['income'], 0); ?> VNĐ</td>
                                    <td><?php echo number_format($data['expenses'], 0); ?> VNĐ</td>
                                    <td><?php echo number_format($data['profit'], 0); ?> VNĐ</td>
                                    <td>
                                        <span class="badge bg-<?php echo $growth >= 0 ? 'success' : 'danger'; ?>">
                                            <?php echo round($growth, 1); ?>%
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script>
// Initialize financial chart
const ctx = document.getElementById('financialChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode(array_map(function($month) {
            return date('m/Y', strtotime($month . '-01'));
        }, array_keys($report['trends']))); ?>,
        datasets: [{
            label: 'Thu',
            data: <?php echo json_encode(array_map(function($data) {
                return $data['income'];
            }, $report['trends'])); ?>,
            borderColor: '#007bff',
            tension: 0.1
        }, {
            label: 'Chi',
            data: <?php echo json_encode(array_map(function($data) {
                return $data['expenses'];
            }, $report['trends'])); ?>,
            borderColor: '#dc3545',
            tension: 0.1
        }, {
            label: 'Lợi nhuận',
            data: <?php echo json_encode(array_map(function($data) {
                return $data['profit'];
            }, $report['trends'])); ?>,
            borderColor: '#28a745',
            tension: 0.1
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
        }
    }
});

// Initialize expense chart
const expenseCtx = document.getElementById('expenseChart').getContext('2d');
new Chart(expenseCtx, {
    type: 'pie',
    data: {
        labels: ['Chi lương', 'Chi hoạt động'],
        datasets: [{
            data: [
                <?php echo $report['expenses']['salary']; ?>,
                <?php echo $report['expenses']['operation']; ?>
            ],
            backgroundColor: ['#007bff', '#dc3545']
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

function exportReport() {
    // Add export to Excel functionality
    alert('Chức năng xuất Excel đang được phát triển');
}

function printReport() {
    window.print();
}
</script>

<?php
// Include footer
require_once('../views/partials/footer.php');
?>