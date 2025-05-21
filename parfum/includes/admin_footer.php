            </div> <!-- End content-wrapper -->
        </div> <!-- End main-content -->
    </div> <!-- End d-flex -->

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="<?php echo SITE_URL; ?>/assets/js/admin.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.querySelector('.sidebar');
        const mainContent = document.querySelector('.main-content');
        
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('show');
            if (window.innerWidth <= 768) {
                mainContent.classList.toggle('expanded');
            }
        });

        // Fermer le menu si on clique en dehors sur mobile
        document.addEventListener('click', function(event) {
            if (window.innerWidth <= 768) {
                const isClickInsideSidebar = sidebar.contains(event.target);
                const isClickOnToggle = sidebarToggle.contains(event.target);
                
                if (!isClickInsideSidebar && !isClickOnToggle && sidebar.classList.contains('show')) {
                    sidebar.classList.remove('show');
                    mainContent.classList.add('expanded');
                }
            }
        });

        // Gérer le redimensionnement de la fenêtre
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                sidebar.classList.remove('show');
                mainContent.classList.remove('expanded');
            }
        });

        // Active link in sidebar
        const currentPath = window.location.pathname;
        const navLinks = document.querySelectorAll('.sidebar .nav-link');
        
        navLinks.forEach(link => {
            if (currentPath.includes(link.getAttribute('href'))) {
                link.classList.add('active');
            }
        });
    });
    </script>
</body>
</html> 