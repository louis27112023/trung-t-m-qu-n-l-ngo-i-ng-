<?php if($page !== 'login'): ?>
<div class="sidebar" style="padding-top: 80px;">
  <nav class="nav-menu">
    <div class="nav-section">
      <a href="index.php?page=dashboard" class="nav-item <?= $page==='dashboard'?'active':'' ?>">
          <i class="bx bxs-dashboard"></i> Dashboard
      </a>
    </div>

    <div class="nav-section">
      <div class="nav-section-title">Quản lý học tập</div>
      <a href="index.php?page=courses" class="nav-item <?= $page==='courses'?'active':'' ?>">
          <i class="bx bxs-book"></i> Khóa học
      </a>
      <a href="index.php?page=schedule" class="nav-item <?= $page==='schedule'?'active':'' ?>">
          <i class="bx bx-calendar"></i> Lịch học
      </a>
      <a href="index.php?page=classes" class="nav-item <?= $page==='classes'?'active':'' ?>">
          <i class="bx bx-group"></i> Lớp học
      </a>
      <a href="index.php?page=students" class="nav-item <?= $page==='students'?'active':'' ?>">
          <i class="bx bxs-user-detail"></i> Học viên
      </a>
      <a href="index.php?page=teachers" class="nav-item <?= $page==='teachers'?'active':'' ?>">
          <i class="bx bxs-user-voice"></i> Giáo viên
      </a>
    </div>

    <div class="nav-section">
      <div class="nav-section-title">Tài chính</div>
      <a href="index.php?page=payments" class="nav-item <?= $page==='payments'?'active':'' ?>">
          <i class="bx bx-money"></i> Học phí
      </a>
      <a href="index.php?page=expenses" class="nav-item <?= $page==='expenses'?'active':'' ?>">
          <i class="bx bx-receipt"></i> Chi phí
      </a>
      <a href="index.php?page=salary" class="nav-item <?= $page==='salary'?'active':'' ?>">
          <i class="bx bx-wallet"></i> Lương
      </a>
    </div>

    <div class="nav-section">
      <div class="nav-section-title">Hệ thống</div>
      <a href="index.php?page=reports" class="nav-item <?= $page==='reports'?'active':'' ?>">
          <i class="bx bxs-report"></i> Báo cáo
      </a>
      <a href="index.php?page=notifications" class="nav-item <?= $page==='notifications'?'active':'' ?>">
          <i class="bx bx-bell"></i> Thông báo
      </a>
      <a href="index.php?page=settings" class="nav-item <?= $page==='settings'?'active':'' ?>">
          <i class="bx bx-cog"></i> Cài đặt
      </a>
    </div>
  </nav>
</div>
<?php endif; ?>
