<?php
// Generic CRUD UI for payments-like tables
$candidates = ['payments','payment','transactions'];
$tbl = find_table_for_candidates($conn,$candidates);
if(!$tbl){ ?>
	<div class="card mb-4"><div class="card-body">
		<h3 class="card-title">Thanh toán</h3>
		<p class="card-text">Chưa có bảng thanh toán trong DB. Tạo bảng `payments` hoặc `transactions` để sử dụng chức năng CRUD.</p>
	</div></div>
<?php } else {
	$cols = get_table_columns($conn,$tbl);
	$rows = get_table_rows($conn,$tbl);
	$pk = get_primary_key_from_cols($cols);
?>
	<div class="card mb-4">
		<div class="card-body">
			<div class="d-flex justify-content-between align-items-center mb-3">
				<h3 class="card-title mb-0">Thanh toán (<?= htmlspecialchars($tbl) ?>)</h3>
				<button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addPaymentModal">Thêm giao dịch</button>
			</div>
			<?php if(function_exists('get_flash')){ $f=get_flash(); if($f): ?><div class="alert alert-success"><?= htmlspecialchars($f) ?></div><?php endif; } ?>
			<div class="table-responsive">
				<table class="table table-striped table-hover">
					<thead><tr><?php foreach($cols as $c) echo '<th>'.htmlspecialchars($c['Field']).'</th>'; ?><th>Hành động</th></tr></thead>
					<tbody>
						<?php foreach($rows as $r): ?><tr>
							<?php foreach($cols as $c){ $f=$c['Field']; echo '<td>'.htmlspecialchars($r[$f] ?? '').'</td>'; } ?>
							<td>
								<button class="btn btn-sm btn-outline-secondary me-1" data-bs-toggle="modal" data-bs-target="#editPaymentModal" <?php foreach($cols as $c){ $n=$c['Field']; echo ' data-'.htmlspecialchars($n).'="'.htmlspecialchars($r[$n] ?? '').'"'; } ?>>Sửa</button>
								<form method="post" action="index.php?page=payments" class="d-inline" onsubmit="return confirm('Xóa bản ghi này?');">
									<input type="hidden" name="action" value="delete_payments">
									<input type="hidden" name="id" value="<?= htmlspecialchars($r[$pk]) ?>">
									<input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
									<button class="btn btn-sm btn-outline-danger">Xóa</button>
								</form>
							</td>
						</tr><?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>

	<!-- Add Modal -->
	<div class="modal fade" id="addPaymentModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content">
		<form method="post" action="index.php?page=payments">
			<input type="hidden" name="action" value="add_payments">
			<input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
			<div class="modal-header"><h5 class="modal-title">Thêm giao dịch</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
			<div class="modal-body">
				<?php foreach($cols as $c){ if(strpos($c['Extra'],'auto_increment')!==false) continue; $name=$c['Field']; $type='text'; if(stripos($name,'date')!==false) $type='date'; elseif(stripos($name,'amount')!==false|| stripos($name,'price')!==false || stripos($c['Type'],'int')!==false) $type='number'; ?>
					<div class="mb-2"><label class="form-label"><?= htmlspecialchars($name) ?></label>
						<input name="<?= htmlspecialchars($name) ?>" class="form-control" type="<?= $type ?>"></div>
				<?php } ?>
			</div>
			<div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button><button class="btn btn-primary">Lưu</button></div>
		</form>
	</div></div></div>

	<!-- Edit Modal -->
	<div class="modal fade" id="editPaymentModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content">
		<form method="post" action="index.php?page=payments">
			<input type="hidden" name="action" value="edit_payments">
			<input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
			<input type="hidden" name="<?= htmlspecialchars($pk) ?>" id="edit-payment-pk" value="">
			<div class="modal-header"><h5 class="modal-title">Sửa giao dịch</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
			<div class="modal-body">
				<?php foreach($cols as $c){ if($c['Field']===$pk) continue; $name=$c['Field']; $type='text'; if(stripos($name,'date')!==false) $type='date'; elseif(stripos($name,'amount')!==false|| stripos($name,'price')!==false || stripos($c['Type'],'int')!==false) $type='number'; ?>
					<div class="mb-2"><label class="form-label"><?= htmlspecialchars($name) ?></label>
						<input name="<?= htmlspecialchars($name) ?>" id="edit-<?= htmlspecialchars($name) ?>" class="form-control" type="<?= $type ?>"></div>
				<?php } ?>
			</div>
			<div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button><button class="btn btn-primary">Lưu</button></div>
		</form>
	</div></div></div>

	<script>
		var em = document.getElementById('editPaymentModal');
		if(em) em.addEventListener('show.bs.modal', function(ev){
			var btn = ev.relatedTarget; if(!btn) return;
			<?php foreach($cols as $c){ $n=$c['Field']; if($n===$pk) continue; ?>
				var v = btn.getAttribute('data-<?= htmlspecialchars($n) ?>') || '';
				var el = document.getElementById('edit-<?= htmlspecialchars($n) ?>'); if(el) el.value = v;
			<?php } ?>
			document.getElementById('edit-payment-pk').value = btn.getAttribute('data-<?= htmlspecialchars($pk) ?>') || '';
		});
	</script>
<?php } ?>