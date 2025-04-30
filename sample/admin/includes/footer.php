<!-- Bootstrap JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            // Sidebar toggle functionality
            const sidebarToggle = document.getElementById("sidebarToggle");
            const sidebar = document.getElementById("sidebar");
            const contentWrapper = document.querySelector(".content-wrapper");
            
            if (sidebarToggle) {
                sidebarToggle.addEventListener("click", () => {
                    sidebar.classList.toggle("show");
                    contentWrapper.classList.toggle("sidebar-open");
                });
            }
            
            // Close sidebar when clicking outside on mobile
            document.addEventListener("click", (event) => {
                const isClickInsideSidebar = sidebar && sidebar.contains(event.target);
                const isClickOnToggler = sidebarToggle && sidebarToggle.contains(event.target);
                
                if (
                    window.innerWidth <= 768 &&
                    !isClickInsideSidebar &&
                    !isClickOnToggler &&
                    sidebar &&
                    sidebar.classList.contains("show")
                ) {
                    sidebar.classList.remove("show");
                    contentWrapper.classList.remove("sidebar-open");
                }
            });
        });
    </script>
</body>
</html>
