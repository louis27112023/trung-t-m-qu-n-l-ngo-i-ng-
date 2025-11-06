<?php // Footer partial: closing scripts and end-of-body content that were in index.php ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/sidebar.js"></script>
<style>
  /* Sidebar toggle animations kept in footer partial */
  .sidebar-toggle { transition: transform 0.3s ease; }
  .sidebar-toggle.rotate { transform: rotate(180deg); }
  @media (max-width: 768px) {
    body.sidebar-open::after { content: ''; position: fixed; inset: 0; background: rgba(0,0,0,0.18); z-index: 30; }
  }
</style>

<script>
  var STORAGE_KEY = 'lc_sidebar_collapsed';
  var body = document.body;
  var toggle = document.querySelector('.sidebar-toggle');
  try{ if(localStorage.getItem(STORAGE_KEY) === '1') body.classList.add('sidebar-collapsed'); }catch(e){}
  if(toggle){ toggle.addEventListener('click', function(e){ e.preventDefault(); body.classList.toggle('sidebar-collapsed'); var collapsed = body.classList.contains('sidebar-collapsed') ? '1' : '0'; try{ localStorage.setItem(STORAGE_KEY, collapsed); }catch(err){} }); }
  var mediaQuery = window.matchMedia('(max-width:900px)');
  function handleResize(){ if(mediaQuery.matches) document.body.classList.remove('sidebar-collapsed'); }
  mediaQuery.addEventListener && mediaQuery.addEventListener('change', handleResize);
  function adjustLayout(){ try{ var header = document.querySelector('header'); var content = document.querySelector('.content'); var sidebar = document.querySelector('.sidebar'); if(header){ var hh = 48; if(content) { content.style.marginTop = hh + 'px'; content.style.paddingTop = '0'; } if(sidebar) sidebar.style.top = hh + 'px'; } var hero = document.querySelector('.dashboard-hero'); if(hero) { hero.style.marginTop = (-hh) + 'px'; } var modals = Array.from(document.querySelectorAll('.modal')); modals.forEach(function(m){ if(m && m.parentNode !== document.body) document.body.appendChild(m); }); }catch(e){console.error(e)} }
  window.addEventListener('resize', adjustLayout);
  document.addEventListener('DOMContentLoaded', adjustLayout);
  setTimeout(adjustLayout,300);

  (function(){ var body = document.body; var prevSidebarOpen = false; document.addEventListener('show.bs.modal', function(e){ try{ prevSidebarOpen = body.classList.contains('sidebar-open'); if(prevSidebarOpen) body.classList.remove('sidebar-open'); var b = document.querySelectorAll('.modal-backdrop'); if(b && b.length>1){ for(var i=0;i<b.length-1;i++) b[i].parentNode && b[i].parentNode.removeChild(b[i]); } var last = document.querySelector('.modal-backdrop'); if(last) last.style.backgroundColor = 'rgba(2,6,23,0.35)'; }catch(err){console && console.error && console.error(err)} }); document.addEventListener('hidden.bs.modal', function(e){ try{ if(prevSidebarOpen) body.classList.add('sidebar-open'); var b = document.querySelectorAll('.modal-backdrop'); if(b && b.length>1){ for(var i=0;i<b.length-1;i++) b[i].parentNode && b[i].parentNode.removeChild(b[i]); } }catch(err){console && console.error && console.error(err)} }); })();
</script>
</body>
</html>
