<?php if($page !== 'login'): ?>
<div class="app-wrapper">
<header class="navbar py-2 border-bottom shadow-sm">
  <div class="container-fluid px-4">
    <div class="d-flex align-items-center">
      <a href="index.php?page=dashboard" class="text-decoration-none d-flex align-items-center text-dark">
        <img src="assets/images/language-center-logo.png" alt="Language Center" style="height: 40px; width: auto;" class="me-2">
        <span class="ms-2 d-none d-md-inline fw-semibold text-dark">Dashboard</span>
      </a>
    </div>

    <div class="d-flex align-items-center gap-3">
      <?php if(!empty($_SESSION['user'])): ?>
      <a href="index.php?page=notifications" class="btn btn-light btn-sm position-relative">
        <i class="bx bx-bell"></i>
        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">2</span>
      </a>
      <div class="dropdown">
        <button class="btn btn-light btn-sm dropdown-toggle d-flex align-items-center gap-2" type="button" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false">
          <i class="bx bx-user"></i>
          <span class="d-none d-md-inline"><?php echo htmlspecialchars($_SESSION['user']['name'] ?? 'Admin') ?></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenu">
          <li><a class="dropdown-item" href="index.php?page=profile"><i class="bx bx-user-circle me-2"></i>Tài khoản</a></li>
          <li><a class="dropdown-item" href="index.php?page=settings"><i class="bx bx-cog me-2"></i>Cài đặt</a></li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item" href="index.php?page=logout"><i class="bx bx-log-out me-2"></i>Đăng xuất</a></li>
        </ul>
      </div>
      <?php else: ?>
      <div class="d-flex align-items-center gap-2">
        <a id="debug-register-btn" href="index.php?page=register" class="btn btn-outline-primary d-flex align-items-center" style="padding: 8px 16px; font-size: 14px; border-radius: 8px; border:2px solid #ffcc00;">
          <i class="bx bx-user-plus" style="font-size:16px; margin-right:6px;"></i>
          <span>Đăng ký</span>
        </a>
        <a href="index.php?page=login" class="btn btn-primary d-flex align-items-center" style="padding: 8px 20px; font-size: 14px; font-weight: 500; border-radius: 8px; background: #0066FF; border: none;">
          <i class="bx bx-log-in" style="font-size: 18px; margin-right: 6px;"></i>
          <span>Đăng nhập</span>
        </a>
      </div>
      <?php endif; ?>
    </div>
  </div>
</header>
<?php endif; ?>
</div>
