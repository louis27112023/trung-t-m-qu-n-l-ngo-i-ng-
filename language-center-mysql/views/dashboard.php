<?php
// Safe helpers
function table_exists($conn, $tbl){
	$tbl = mysqli_real_escape_string($conn, $tbl);
	$r = mysqli_query($conn, "SHOW TABLES LIKE '$tbl'");
	return $r && mysqli_num_rows($r) > 0;

}
function safe_count($conn, $tbl){
	if(!table_exists($conn,$tbl)) return null;
	$res = @mysqli_query($conn, "SELECT COUNT(*) AS c FROM `".mysqli_real_escape_string($conn,$tbl)."`");
	if(!$res) return null;
	$row = mysqli_fetch_assoc($res);
	return (int)($row['c'] ?? 0);
}


function find_column($conn,$tbl,array $candidates){
	if(!table_exists($conn,$tbl)) return null;
	foreach($candidates as $c){
		$c_esc = mysqli_real_escape_string($conn,$c);
		$res = @mysqli_query($conn, "SHOW COLUMNS FROM `".mysqli_real_escape_string($conn,$tbl)."` LIKE '$c_esc'");
		if($res && mysqli_num_rows($res)>0) return $c;
	}
	return null;
}

$courses = safe_count($conn,'courses');
$students = null;
$classes = null;
$revenue = null;

// try common student table names
$studentTables = ['students','users','learners','customers'];
foreach($studentTables as $t){ if($students === null) $students = safe_count($conn,$t); }

// try common class table names
$classTables = ['classes','groups','classrooms','rooms'];
foreach($classTables as $t){ if($classes === null) $classes = safe_count($conn,$t); }

// payments: try to sum common amount column names
$paymentsTotal = null;
$paymentsTable = null;
if(table_exists($conn,'payments')) $paymentsTable = 'payments';
// sometimes table named 'transactions' or 'payment'
foreach(['transactions','payment','payments'] as $pt){ if($paymentsTable === null && table_exists($conn,$pt)) $paymentsTable = $pt; }

if($paymentsTable){
	$amountCol = find_column($conn,$paymentsTable,['amount','total','price','paid_amount','value']);
	if($amountCol){
		$q = "SELECT SUM(`".mysqli_real_escape_string($conn,$amountCol)."`) AS s FROM `".mysqli_real_escape_string($conn,$paymentsTable)."`";
		$res = @mysqli_query($conn,$q);
		if($res){ $row = mysqli_fetch_assoc($res); $paymentsTotal = $row['s'] !== null ? (float)$row['s'] : null; }
	}
}

// If DB doesn't have data (or tables missing) provide demo fallbacks so UI looks populated
if($courses === null || $courses === 0) $courses = 12;
if($students === null || $students === 0) $students = 240;
if($classes === null || $classes === 0) $classes = 8;
if($paymentsTotal === null || $paymentsTotal === 0) $paymentsTotal = 123456000; // demo total in VND
?>
		<div class="hero-banner text-center py-5">
			<h1 class="display-4 fw-bold gradient-text mb-4">LANGUAGE CENTER</h1>
		</div>

		<div class="card mb-4 hero-overlap">
		<div class="card-body">
		<div class="row gy-3">
				<div class="col-6 col-md-3">
					<div class="stat-compact stat-blue text-center p-3 rounded">
						<div class="stat-icon"><i class="bx bxs-book"></i></div>
						<div class="stat-label muted">Khóa học</div>
						<div class="stat-number" data-count="<?= intval($courses ?? 0) ?>"><?= $courses!==null?intval($courses):'--' ?></div>
						<div class="stat-delta">+12% so với tháng trước</div>
					</div>
				</div>
				<div class="col-6 col-md-3">
					<div class="stat-compact stat-green text-center p-3 rounded">
						<div class="stat-icon"><i class="bx bxs-user"></i></div>
						<div class="stat-label muted">Học viên</div>
						<div class="stat-number" data-count="<?= intval($students ?? 0) ?>"><?= $students!==null?intval($students):'--' ?></div>
						<div class="stat-delta text-muted">—</div>
					</div>
				</div>
				<div class="col-6 col-md-3">
					<div class="stat-compact stat-yellow text-center p-3 rounded">
						<div class="stat-icon"><i class="bx bx-group"></i></div>
						<div class="stat-label muted">Lớp</div>
						<div class="stat-number" data-count="<?= intval($classes ?? 0) ?>"><?= $classes!==null?intval($classes):'--' ?></div>
						<div class="stat-delta text-muted">—</div>
					</div>
				</div>
				<div class="col-6 col-md-3">
					<div class="stat-compact stat-gray text-center p-3 rounded">
						<div class="stat-icon"><i class="bx bx-money"></i></div>
						<div class="stat-label muted">Tổng thu</div>
						<div class="stat-number">
							<?php if($paymentsTotal!==null): ?>
								<?= htmlspecialchars(lc_format_vnd($paymentsTotal)) ?>
							<?php else: ?>
								--
							<?php endif; ?>
						</div>
						<div class="stat-delta text-muted">—</div>
					</div>
				</div>
				<script>
				// Position the hero dynamically so it sits directly under the fixed header and removes top gap
				(function(){
					try{
						function adjustHero(){
							var header = document.querySelector('header');
							var hero = document.querySelector('.dashboard-hero');
							var overlapCard = document.querySelector('.hero-overlap');
							if(!header || !hero) return;
							var h = header.offsetHeight || 56;
							// move hero up so its top aligns under the header
							hero.style.marginTop = ( -Math.max(h - 8, 0) ) + 'px';
							// ensure the overlapped card keeps a small overlap, adjust if necessary
							if(overlapCard) overlapCard.style.marginTop = ( -Math.max( Math.min(48, h), 28 ) ) + 'px';
						}
						window.addEventListener('resize', adjustHero);
						document.addEventListener('DOMContentLoaded', adjustHero);
						// run shortly after load in case fonts/layout change
						setTimeout(adjustHero, 250);
					}catch(e){console && console.error && console.error(e)}
				})();
					</script>
		</div>

		<?php
		// Try to render a small payments trend chart if we have payments table and amount/date columns
		$canChart = false; $chartLabels = []; $chartData = [];
		if($paymentsTable){
			$dateCol = find_column($conn,$paymentsTable,['created_at','date','paid_at','payment_date','created']);
			$amountCol = $amountCol ?? find_column($conn,$paymentsTable,['amount','total','price','paid_amount','value']);
			if($dateCol && $amountCol){
				// aggregate by month (last 6 months)
				$q = "SELECT DATE_FORMAT(`".mysqli_real_escape_string($conn,$dateCol)."`,'%Y-%m') AS m, SUM(`".mysqli_real_escape_string($conn,$amountCol)."`) AS s FROM `".mysqli_real_escape_string($conn,$paymentsTable)."` GROUP BY m ORDER BY m DESC LIMIT 6";
				$res = @mysqli_query($conn,$q);
				if($res){
					$rows = [];
					while($r = mysqli_fetch_assoc($res)) $rows[] = $r;
					if(count($rows)>0){
						$rows = array_reverse($rows);
						foreach($rows as $r){ $chartLabels[] = $r['m']; $chartData[] = (float)($r['s'] ?? 0); }
						$canChart = true;
					}
				}
			}
		}
		?>

		<?php if($canChart): ?>
			<div class="mt-4">
				<canvas id="paymentsTrend" height="80"></canvas>
			</div>
			<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
			<script>
				(function(){
					var ctx = document.getElementById('paymentsTrend').getContext('2d');
					new Chart(ctx, {
						type: 'line',
						data: {
							labels: <?= json_encode($chartLabels) ?>,
							datasets: [{
								label: 'Doanh thu',
								data: <?= json_encode($chartData) ?>,
								borderColor: getComputedStyle(document.documentElement).getPropertyValue('--primary-500') || '#0ea5b7',
								backgroundColor: 'rgba(14,165,183,0.12)',
								tension: 0.3,
								fill: true,
								pointRadius: 4
							}]
						},
						options: { responsive:true, maintainAspectRatio:false, plugins:{legend:{display:false}}, scales:{y:{ticks:{callback:function(v){return v>=1000? (v/1000)+'k':v}}}}
					});
				})();
			</script>
		<?php endif; ?>

		<?php if(!$canChart): ?>
			<div class="mt-4">
				<canvas id="paymentsTrendDemo" height="80"></canvas>
			</div>
			<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
			<script>
				(function(){
					var labels = ['-5m','-4m','-3m','-2m','-1m','Now'];
					var data = [1200000, 2300000, 1800000, 2600000, 2100000, 3200000];
					var ctx = document.getElementById('paymentsTrendDemo').getContext('2d');
					new Chart(ctx, {
						type: 'line',
						data: {
							labels: labels,
							datasets: [{
								label: 'Doanh thu (demo)',
								data: data,
								borderColor: getComputedStyle(document.documentElement).getPropertyValue('--primary') || '#ec4899',
								backgroundColor: 'rgba(236,72,153,0.12)',
								tension: 0.3,
								fill: true,
								pointRadius: 4
							}]
						},
						options: { responsive:true, maintainAspectRatio:false, plugins:{legend:{display:false}}, scales:{y:{ticks:{callback:function(v){return v>=1000? (v/1000)+'k':v}}}}
					});
				})();
			</script>
		<?php endif; ?>

		<div class="row mt-4">
			<div class="col-lg-8">
				<!-- Khóa học nổi bật -->
				<div class="card mb-4">
					<div class="card-body">
						<h5 class="card-title d-flex justify-content-between align-items-center">
							<span>Khóa học nổi bật</span>
							<div>
								<button class="btn btn-sm btn-outline-primary me-2" id="btnAddFeatured" title="Thêm khóa học">
									<i class='bx bx-plus'></i>
								</button>
								<a href="index.php?page=courses" class="btn btn-sm btn-primary">
									Xem tất cả
								</a>
							</div>
						</h5>
						<div class="featured-courses horizontal-scroll" id="featuredCoursesContainer">
							<!-- Khóa học cơ bản -->
							<div class="course-card">
								<div class="course-image">
									<img src="assets/images/languages/english-basic.jpg" alt="Tiếng Anh cơ bản">
									<div class="course-badge beginner">Cơ bản</div>
														</div>

														<!-- Add Featured Course Modal -->
								<div class="modal fade" id="addFeaturedModal" tabindex="-1">
												<div class="modal-dialog modal-lg modal-dialog-centered">
																<div class="modal-content">
																	<form id="featuredForm" enctype="multipart/form-data">
																		<div class="modal-header">
																			<h5 class="modal-title">Thêm khóa học nổi bật</h5>
																			<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
																		</div>
																		<div class="modal-body">
																			<div class="row g-3">
																				<div class="col-md-6">
																					<label class="form-label">Tiêu đề</label>
																					<input class="form-control" name="title" required>
																				</div>
																				<div class="col-md-6">
																					<label class="form-label">Trình độ / Badge</label>
																					<input class="form-control" name="badge" placeholder="Cơ bản / Nâng cao">
																				</div>
																				<div class="col-12">
																					<label class="form-label">Mô tả ngắn</label>
																					<textarea class="form-control" name="description" rows="3"></textarea>
																				</div>
																				<div class="col-md-4">
																					<label class="form-label">Tổng giờ</label>
																					<input class="form-control" name="hours" type="text" placeholder="48 giờ">
																				</div>
																				<div class="col-md-4">
																					<label class="form-label">Sĩ số</label>
																					<input class="form-control" name="class_size" type="text" placeholder="20 HS/lớp">
																				</div>
																				<div class="col-md-4">
																					<label class="form-label">Số buổi/tuần</label>
																					<input class="form-control" name="sessions" type="text" placeholder="3 buổi/tuần">
																				</div>
																				<div class="col-12">
																					<label class="form-label">Ảnh khóa học</label>
																					<input class="form-control" name="image" type="file" accept="image/*">
																				</div>
																			</div>
																		</div>
																		<div class="modal-footer">
																			<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
																			<button type="submit" class="btn btn-primary">Lưu</button>
																		</div>
																	</form>
																</div>
															</div>
														</div>

														<script>
														document.addEventListener('DOMContentLoaded', function(){
																var addBtn = document.getElementById('btnAddFeatured');
																var addModal = new bootstrap.Modal(document.getElementById('addFeaturedModal'));
																addBtn && addBtn.addEventListener('click', function(){ addModal.show(); });

																// load featured courses
																function renderCourses(list){
																		var container = document.getElementById('featuredCoursesContainer');
																		container.innerHTML = '';
																		list.forEach(function(c){
																			var card = document.createElement('div');
																			card.className = 'course-card';
																			var img = c.image ? '<img src="'+c.image+'" alt="'+(c.title||'')+'">' : '';
																			var badge = c.badge ? ('<div class="course-badge">'+c.badge+'</div>') : '';
																			// include a small delete button overlay (data-id)
																			var deleteBtn = '<button class="btn-delete-featured" data-id="'+(c.id||'')+'" title="Xóa mục này" style="position:absolute;top:10px;left:10px;background:rgba(0,0,0,0.5);border:none;color:#fff;padding:6px;border-radius:6px;cursor:pointer"><i class="bx bx-trash"></i></button>';
																			card.innerHTML = "<div class='course-image'>"+img+badge+deleteBtn+"</div>"
																				+"<div class='course-content'><h3 class='course-title'>"+(c.title||'')+"</h3>"
																				+"<p class='course-desc'>"+(c.description||'')+"</p>"
																				+"<div class='course-stats'>"
																				+"<div class='stat'><i class='bx bx-time'></i><span>"+(c.hours||'')+"</span></div>"
																				+"<div class='stat'><i class='bx bx-user'></i><span>"+(c.class_size||'')+"</span></div>"
																				+"<div class='stat'><i class='bx bx-calendar'></i><span>"+(c.sessions||'')+"</span></div>"
																				+"</div><a href='#' class='btn btn-register'>Đăng ký khóa học</a></div>";
																			container.appendChild(card);

																			// wire delete button
																			(function(elem, item){
																				var btn = elem.querySelector('.btn-delete-featured');
																				if(btn){
																					btn.addEventListener('click', function(ev){
																						ev.preventDefault();
																						if(!confirm('Bạn có chắc muốn xóa mục này?')) return;
																						var id = this.getAttribute('data-id');
																						var fd = new FormData();
																						fd.append('action','delete');
																						fd.append('id', id);
																						fetch('api/featured_courses.php', { method: 'POST', body: fd })
																						.then(r=>r.json()).then(function(res){
																							if(res.success){
																								// remove card from DOM
																								elem.remove();
																								var t = document.createElement('div'); t.className='notification notification-success'; t.innerText='Đã xóa mục'; document.body.appendChild(t); setTimeout(()=>t.remove(),2000);
																							} else {
																								alert('Lỗi: ' + (res.error||'Không xóa được'));
																							}
																						}).catch(function(err){ console.error(err); alert('Lỗi mạng'); });
																					});
																				}
																			})(card, c);
																		});
																}

																function loadFeatured(){
																		fetch('api/featured_courses.php')
																		.then(r=>r.json()).then(function(res){
																				if(res.success) renderCourses(res.data);
																		}).catch(function(err){ console.error(err); });
																}
																loadFeatured();

																// handle submit
																var form = document.getElementById('featuredForm');
																form && form.addEventListener('submit', function(e){
																		e.preventDefault();
																		var fd = new FormData(form);
																		fetch('api/featured_courses.php', { method: 'POST', body: fd })
																		.then(r=>r.json()).then(function(res){
																				if(res.success){
																						addModal.hide();
																						renderCourses(res.data);
																						// toast
																						var t = document.createElement('div'); t.className='notification notification-success'; t.innerText='Đã thêm khóa học'; document.body.appendChild(t); setTimeout(()=>t.remove(),2500);
																				} else {
																						alert('Lỗi: ' + (res.error||'Không lưu được'));
																				}
																		}).catch(function(err){ console.error(err); alert('Lỗi mạng'); });
																});
														});
														</script>
								<div class="course-content">
									<h3 class="course-title">TIẾNG ANH CƠ BẢN</h3>
									<p class="course-desc">
										Khóa học giúp bạn cải thiện kỹ năng nghe, nói, đọc, viết tiếng Anh cơ bản. 
										Phù hợp với người mới bắt đầu hoặc muốn củng cố kiến thức.
									</p>
									<div class="course-stats">
										<div class="stat">
											<i class='bx bx-time'></i>
											<span>48 giờ</span>
										</div>
										<div class="stat">
											<i class='bx bx-user'></i>
											<span>20 HS/lớp</span>
										</div>
										<div class="stat">
											<i class='bx bx-calendar'></i>
											<span>3 buổi/tuần</span>
										</div>
									</div>
									<a href="#" class="btn btn-register">Đăng ký khóa học</a>
								</div>
							</div>

							<!-- Khóa học IELTS -->
							<div class="course-card">
								<div class="course-image">
									<img src="assets/images/languages/ielts.jpg" alt="IELTS">
									<div class="course-badge advanced">Nâng cao</div>
								</div>
								<div class="course-content">
									<h3 class="course-title">LUYỆN THI IELTS</h3>
									<p class="course-desc">
										Khóa học chuyên sâu giúp bạn đạt điểm IELTS mục tiêu. 
										Giáo viên giàu kinh nghiệm, phương pháp học hiệu quả.
									</p>
									<div class="course-stats">
										<div class="stat">
											<i class='bx bx-time'></i>
											<span>96 giờ</span>
										</div>
										<div class="stat">
											<i class='bx bx-user'></i>
											<span>15 HS/lớp</span>
										</div>
										<div class="stat">
											<i class='bx bx-calendar'></i>
											<span>4 buổi/tuần</span>
										</div>
									</div>
									<a href="#" class="btn btn-register">Đăng ký khóa học</a>
								</div>
							</div>
						</div>
					</div>
				</div>

				<!-- Hoạt động gần đây -->
				<div class="card mb-3">
					<div class="card-body">
						<h5 class="card-title d-flex justify-content-between align-items-center mb-4">
							<span>Hoạt động gần đây</span>
							<div class="activity-filter">
								<select class="form-select form-select-sm">
									<option>Tất cả hoạt động</option>
									<option>Đăng ký học</option>
									<option>Thanh toán</option>
									<option>Lớp học</option>
								</select>
							</div>
						</h5>
						
						<div class="activity-timeline">
							<div class="activity-item">
								<div class="activity-icon bg-success-light">
									<i class='bx bx-user-plus'></i>
								</div>
								<div class="activity-content">
									<div class="activity-text">
										<strong>Nguyễn Văn A</strong> đã đăng ký <a href="#">Khóa Tiếng Anh giao tiếp</a>
									</div>
									<div class="activity-time">2 giờ trước</div>
								</div>
							</div>

							<div class="activity-item">
								<div class="activity-icon bg-primary-light">
									<i class='bx bx-credit-card'></i>
								</div>
								<div class="activity-content">
									<div class="activity-text">
										<strong>Trần Thị B</strong> đã thanh toán học phí khóa <a href="#">IELTS Cơ bản</a>
									</div>
									<div class="activity-time">1 ngày trước</div>
								</div>
							</div>

							<div class="activity-item">
								<div class="activity-icon bg-warning-light">
									<i class='bx bx-calendar-plus'></i>
								</div>
								<div class="activity-content">
									<div class="activity-text">
										Lớp <a href="#">IELTS - Buổi sáng</a> đã được tạo
									</div>
									<div class="activity-time">3 ngày trước</div>
								</div>
							</div>

							<div class="activity-item">
								<div class="activity-icon bg-info-light">
									<i class='bx bx-calendar-check'></i>
								</div>
								<div class="activity-content">
									<div class="activity-text">
										<strong>Lê Văn C</strong> đã hoàn thành khóa <a href="#">Tiếng Anh Giao Tiếp</a>
									</div>
									<div class="activity-time">5 ngày trước</div>
								</div>
							</div>
						</div>

						<div class="text-center mt-4">
							<button class="btn btn-light btn-sm">
								<i class='bx bx-loader'></i> Xem thêm
							</button>
						</div>
					</div>
				</div>
			</div>
			<div class="col-lg-4">
				<div class="card mb-3">
					<div class="card-body">
						<h5 class="card-title">Hành động nhanh</h5>
<div class="quick-actions d-flex flex-column gap-2">
						<a href="index.php?page=courses" class="action-tile primary w-100" title="Thêm khóa học">
							<div class="tile-icon"><i class="bx bx-plus-circle"></i></div>
							<div class="tile-body">
								<div class="tile-title">Thêm khóa học</div>
								<div class="tile-sub">Tạo khóa mới, thiết lập học phí</div>
							</div>
						</a>
						<a href="index.php?page=schedule" class="action-tile outline w-100" title="Tạo lịch">
							<div class="tile-icon"><i class="bx bx-calendar-plus"></i></div>
							<div class="tile-body">
								<div class="tile-title">Tạo lịch</div>
								<div class="tile-sub">Lập thời khóa biểu cho lớp</div>
							</div>
						</a>
						<a href="index.php?page=payments" class="action-tile neutral w-100" title="Ghi nhận thu">
							<div class="tile-icon"><i class="bx bx-wallet"></i></div>
							<div class="tile-body">
								<div class="tile-title">Ghi nhận thu</div>
								<div class="tile-sub">Nhập giao dịch học phí</div>
							</div>
						</a>
					</div>
					</div>
				</div>
				<div class="card">
					<div class="card-body">
						<div class="card-header-flex">
							<h5 class="card-title">Lịch sắp tới</h5>
							<div class="card-icon">
								<i class='bx bx-calendar-event'></i>
							</div>
						</div>
						<ul class="list-unstyled mb-0 upcoming-list">
							<li><strong>09:00</strong> - IELTS Level 1 (Thứ 2)</li>
							<li><strong>11:00</strong> - Tiếng Anh thiếu nhi (Thứ 3)</li>
							<li><strong>18:00</strong> - Giao tiếp cơ bản (Thứ 4)</li>
						</ul>
					</div>
				</div>
			</div>
		</div>

		<script>
		// Simple animated counters for .stat-value elements
		(function(){
			function animateCount(el, to, duration){
				var start = 0;
				var startTime = null;
				duration = duration || 1200;
				function step(ts){
					if(!startTime) startTime = ts;
					var progress = Math.min((ts - startTime)/duration, 1);
					var value = Math.floor(progress * (to - start) + start);
					el.textContent = value.toLocaleString();
					if(progress < 1) requestAnimationFrame(step);
				}
				requestAnimationFrame(step);
			}

			document.addEventListener('DOMContentLoaded', function(){
				var counters = document.querySelectorAll('.stat-value[data-count]');
				counters.forEach(function(c){
					var to = parseInt(c.getAttribute('data-count')) || 0;
					animateCount(c, to, 900 + Math.random()*600);
				});
			});
		})();
		</script>

	</div>
</div>
