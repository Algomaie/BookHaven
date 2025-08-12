<?php
// views/admin/includes/admin-footer.php
?>

<!-- Logout Modal-->
<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="logoutModalLabel">Ready to Leave?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Select "Logout" below if you are ready to end your current session.
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
                <a class="btn btn-primary" href="../logout.php">Logout</a>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap core JavaScript-->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Core plugin JavaScript-->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js"></script>

<!-- Custom scripts for all pages-->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle sidebar on small screens
        const sidebarToggle = document.getElementById('sidebarToggle');
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function() {
                document.body.classList.toggle('sidebar-toggled');
                document.querySelector('.sidebar').classList.toggle('toggled');
            });
        }
        
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
        
        // Close sidebar on small screens when clicking outside
        const handleWindowResize = () => {
            if (window.innerWidth < 768) {
                document.body.classList.add('sidebar-toggled');
                document.querySelector('.sidebar').classList.add('toggled');
            } else {
                document.body.classList.remove('sidebar-toggled');
                document.querySelector('.sidebar').classList.remove('toggled');
            }
        };
        
        window.addEventListener('resize', handleWindowResize);
        handleWindowResize();
        
        // Handle form validations
        const forms = document.querySelectorAll('.needs-validation');
        Array.from(forms).forEach(form => {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                
                form.classList.add('was-validated');
            }, false);
        });
    });
</script>

<!-- Page-specific scripts -->
<?php if (isset($page_scripts)): ?>
    <?php foreach ($page_scripts as $script): ?>
        <script src="<?php echo $script; ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>

</body>
</html>