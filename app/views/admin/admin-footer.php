</main></div><div id="logoutModal" class="modal"><div class="modal-content" style="background:white;border-radius:15px;padding:2rem;text-align:center;max-width:400px;box-shadow:0 10px 40px rgba(0,0,0,0.2)"><h2 style="margin:0 0 1rem 0;color:#2C2820">Confirm Logout</h2><p style="color:#666;margin:0 0 2rem 0">Are you sure you want to logout?</p><div style="display:flex;gap:1rem;justify-content:center"><button onclick="closeLogoutModal()" style="padding:0.75rem 1.5rem;background:#e0e0e0;border:none;border-radius:8px;cursor:pointer;font-weight:500">Cancel</button><button onclick="confirmLogout()" style="padding:0.75rem 1.5rem;background:#8A7650;color:white;border:none;border-radius:8px;cursor:pointer;font-weight:500">Logout</button></div></div></div>
<style>.modal {display:none;position:fixed;z-index:1000;left:0;top:0;width:100%;height:100%;background-color:rgba(0,0,0,0.5);align-items:center;justify-content:center}.modal.show {display:flex}.modal-content {background-color:#fefefe;padding:2rem;border-radius:15px;box-shadow:0 4px 15px rgba(0,0,0,0.2)}</style>
<script>
function openLogoutModal(e){e.preventDefault();document.getElementById("logoutModal").classList.add("show")}
function closeLogoutModal(){document.getElementById("logoutModal").classList.remove("show")}
function confirmLogout(){window.location.href="/SINTA/public/index.php?route=logout"}
document.addEventListener("DOMContentLoaded",function(){var e=document.getElementById("logoutModal");e.addEventListener("click",function(t){t.target===this&&closeLogoutModal()})});
var mobileToggle=document.getElementById("mobileToggle"),adminSidebar=document.getElementById("adminSidebar");mobileToggle&&mobileToggle.addEventListener("click",function(){adminSidebar.classList.toggle("open")});
document.addEventListener("DOMContentLoaded",function(){var e=new URLSearchParams(window.location.search).get("route"),t=document.querySelectorAll(".admin-sidebar__link");t.forEach(function(n){n.getAttribute("href").includes("route="+e)&&n.classList.add("active")})});
</script>
</body>
</html>
