    </div> <!-- End main-content -->
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        // Initialize DataTables
        $(document).ready(function() {
            $('.data-table').DataTable({
                "pageLength": 25,
                "responsive": true,
                "order": [[ 0, "desc" ]]
            });
        });
        
        // Toggle sidebar on mobile
        function toggleSidebar() {
            $('.sidebar').toggleClass('show');
        }
        
        // Close sidebar when clicking outside on mobile
        $(document).click(function(e) {
            if ($(window).width() <= 768) {
                if (!$(e.target).closest('.sidebar, .btn').length) {
                    $('.sidebar').removeClass('show');
                }
            }
        });
        
        // Confirm delete actions
        function confirmDelete(message = 'Are you sure you want to delete this item?') {
            return confirm(message);
        }
        
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
        
        // Form validation
        function validateForm(formId) {
            var form = document.getElementById(formId);
            var inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
            var valid = true;
            
            inputs.forEach(function(input) {
                if (!input.value.trim()) {
                    input.classList.add('is-invalid');
                    valid = false;
                } else {
                    input.classList.remove('is-invalid');
                }
            });
            
            return valid;
        }
        
        // Email validation
        function validateEmail(email) {
            var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }
        
        // Phone validation
        function validatePhone(phone) {
            var re = /^[\+]?[1-9][\d]{0,15}$/;
            return re.test(phone.replace(/[\s\-\(\)]/g, ''));
        }
    </script>
</body>
</html>
