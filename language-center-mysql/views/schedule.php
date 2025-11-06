<?php
// Clean calendar-only schedule view (CRUD removed).

$candidates = ['schedule','schedules','timetable'];
$tbl = find_table_for_candidates($conn, $candidates);
$rows = [];
if($tbl){
	$cols = get_table_columns($conn, $tbl);
	$rows = get_table_rows($conn, $tbl);
	$pk = get_primary_key_from_cols($cols);
}
?>

<div class="card mb-4">
	<div class="card-body p-0">
		<div id="calendar" style="max-width:1100px;margin:0 auto;"></div>
	</div>
</div>

<!-- FullCalendar (CDN for simplicity) -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>

<?php
// Build events array using simple heuristics for column names.
$fc_events = [];
foreach($rows as $r){
	$title = $r['title'] ?? $r['name'] ?? $r['course'] ?? $r['class'] ?? ($r['description'] ?? 'Lịch học');
	$start = '';
	$end = '';
	$startKeys = ['start','start_date','date','start_datetime','datetime','from','day','start_at','date_at'];
	$endKeys = ['end','end_date','end_datetime','to','until','end_at'];
	foreach($startKeys as $k) if(!empty($r[$k])){ $start = $r[$k]; break; }
	foreach($endKeys as $k) if(!empty($r[$k])){ $end = $r[$k]; break; }
	if(empty($start) && !empty($r['date']) && !empty($r['time'])){
		$start = $r['date'].'T'.(strpos($r['time'],':')!==false?$r['time']:$r['time'].':00');
	}
	$fc_events[] = [ 'id' => $r[$pk] ?? null, 'title' => $title, 'start' => $start, 'end' => $end ];
}
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
  var events = <?php echo json_encode($fc_events, JSON_UNESCAPED_UNICODE); ?>;
  var calendarEl = document.getElementById('calendar');
  if(!calendarEl) return;
  var calendar = new FullCalendar.Calendar(calendarEl, {
	height: 650,
	initialView: 'dayGridMonth',
	headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,timeGridDay' },
	events: events,
	locale: 'vi',
	eventTimeFormat: { hour: '2-digit', minute: '2-digit', hour12: false }
  });
  calendar.render();
});
</script>
 