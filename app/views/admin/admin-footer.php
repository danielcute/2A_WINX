</div><!-- .admin-content -->
</main>
</div><!-- .admin-wrapper -->

<!-- Logout Modal -->
<div id="logoutModal" class="modal">
    <div class="modal-content" style="background:white;border-radius:15px;padding:2rem;text-align:center;max-width:400px;box-shadow:0 10px 40px rgba(0,0,0,0.2)">
        <h2 style="margin:0 0 1rem 0;color:#2C2820">Confirm Logout</h2>
        <p style="color:#666;margin:0 0 2rem 0">Are you sure you want to logout?</p>
        <div style="display:flex;gap:1rem;justify-content:center">
            <button onclick="closeLogoutModal()" style="padding:0.75rem 1.5rem;background:#e0e0e0;border:none;border-radius:8px;cursor:pointer;font-weight:500">Cancel</button>
            <button onclick="confirmLogout()" style="padding:0.75rem 1.5rem;background:#8A7650;color:white;border:none;border-radius:8px;cursor:pointer;font-weight:500">Logout</button>
        </div>
    </div>
</div>

<style>
.modal {display:none;position:fixed;z-index:50;left:0;top:0;width:100%;height:100%;background-color:rgba(0,0,0,0.5);align-items:center;justify-content:center;pointer-events:none}
.modal.show {display:flex;z-index:1000;pointer-events:auto}
.modal-content {background-color:#fefefe;padding:2rem;border-radius:15px;box-shadow:0 4px 15px rgba(0,0,0,0.2);pointer-events:auto}
</style>

<script>
function openLogoutModal(e) {
    if(e) e.preventDefault();
    document.getElementById("logoutModal").classList.add("show");
}
function closeLogoutModal() {
    document.getElementById("logoutModal").classList.remove("show");
}
function confirmLogout() {
    window.location.href = "/index.php?route=logout";
}
document.addEventListener("DOMContentLoaded", function() {
    var modal = document.getElementById("logoutModal");
    if(modal) {
        modal.addEventListener("click", function(e) {
            if(e.target === modal) closeLogoutModal();
        });
    }
    // Active link highlighting - improved to handle query parameters and routes
    var currentUrl = window.location.href;
    var pathname = window.location.pathname;
    var search = window.location.search;
    
    document.querySelectorAll('.admin-sidebar__link').forEach(link => {
        var linkUrl = link.href;
        // Check for exact match
        if(linkUrl === currentUrl) {
            link.classList.add('active');
            return;
        }
        // Check for route parameter match
        if(search) {
            var currentRoute = new URLSearchParams(search).get('route');
            var linkRoute = new URLSearchParams(new URL(linkUrl).search).get('route');
            if(currentRoute && linkRoute && currentRoute === linkRoute) {
                link.classList.add('active');
                return;
            }
        }
    });
    // Mobile toggle
    var mobileToggle = document.getElementById('mobileToggle');
    var adminSidebar = document.getElementById('adminSidebar');
    if(mobileToggle) {
        mobileToggle.addEventListener('click', function() {
            adminSidebar.classList.toggle('open');
        });
    }
});
</script>
</body>
</html>