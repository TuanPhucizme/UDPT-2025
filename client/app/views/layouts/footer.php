    </main>

    <footer class="bg-light py-4 mt-auto">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>Bệnh Viện ABC</h5>
                    <p class="mb-0">Địa chỉ: 123 Đường ABC, Quận 1, TP.HCM</p>
                    <p class="mb-0">Điện thoại: (028) 1234 5678</p>
                    <p class="mb-0">Email: info@benhvienabc.com</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">© <?= date('Y') ?> Bệnh Viện ABC</p>
                    <p class="mb-0">Hệ thống quản lý bệnh viện</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Custom Scripts -->
    <script>
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            $('.alert').alert('close');
        }, 5000);

        // Enable tooltips everywhere
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });

        // Active nav links
        $(document).ready(function() {
            const currentPath = window.location.pathname;
            $('.nav-link').each(function() {
                if ($(this).attr('href') === currentPath) {
                    $(this).addClass('active');
                }
            });
        });
    </script>
</body>
</html>