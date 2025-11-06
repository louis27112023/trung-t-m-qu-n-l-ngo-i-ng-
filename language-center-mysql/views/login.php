<?php
// Modern minimalist login page
?>
<div class="login-page">
  <div class="login-wrapper">
      <div class="text-center mb-4">
        <img src="assets/images/language-center-logo.png" alt="Language Center" class="login-logo mb-3">
      </div>
      <?php if(function_exists('get_flash')){ $f=get_flash(); if($f): ?>
        <div class="alert alert-warning"><?= htmlspecialchars($f) ?></div>
      <?php endif; } ?>

      <form method="post" action="index.php?page=login" class="login-form">
        <input type="hidden" name="action" value="login">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">

        <div class="mb-4">
          <div class="input-with-icon">
            <span class="input-icon bx bx-user"></span>
            <input name="username" 
                   class="form-control form-control-lg" 
                   placeholder="Tên đăng nhập" 
                   autocomplete="username" 
                   required
                   autofocus>
          </div>
        </div>

        <div class="mb-4">
          <div class="input-with-icon">
            <span class="input-icon bx bx-lock-alt"></span>
            <input name="password" 
                   type="password" 
                   class="form-control form-control-lg" 
                   placeholder="Mật khẩu" 
                   autocomplete="current-password" 
                   required>
          </div>
        </div>

        <div class="form-group d-flex justify-content-between align-items-center mb-4">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" value="1" id="remember" name="remember">
            <label class="form-check-label small" for="remember">Ghi nhớ</label>
          </div>
          <a href="#" class="small text-primary text-decoration-none">Quên mật khẩu?</a>
        </div>

        <div class="d-grid gap-2 mb-4">
          <button type="submit" class="btn btn-primary btn-lg">
            <i class="bx bx-log-in me-2"></i>Đăng nhập
          </button>
          <a href="index.php?page=register" class="btn btn-light">
            <i class="bx bx-user-plus me-2"></i>Tạo tài khoản mới
          </a>
        </div>

        <div class="text-center">
          <div class="divider mb-4">
            <span class="divider-text text-muted">hoặc tiếp tục với</span>
          </div>

          <div class="d-flex gap-2 justify-content-center">
            <a class="btn btn-light btn-social" href="#" title="Đăng nhập bằng Google">
              <i class="bx bxl-google"></i>
            </a>
            <a class="btn btn-light btn-social" href="#" title="Đăng nhập bằng Facebook">
              <i class="bx bxl-facebook"></i>
            </a>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>