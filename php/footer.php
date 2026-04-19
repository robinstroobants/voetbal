    <?php if (isset($exec_time_ms) && isset($mem_usage_mb)): ?>
    <footer class="d-print-none text-center text-muted mt-4 mb-3" style="font-size: 0.75rem;">
        <i class="fa-solid fa-bolt text-warning"></i> Gegegenereerd in <strong><?= number_format($exec_time_ms, 2) ?> ms</strong> 
        &bull; Piekgeheugen: <strong><?= number_format($mem_usage_mb, 2) ?> MB</strong>
    </footer>
    <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-datepicker@1.10.0/dist/js/bootstrap-datepicker.min.js"></script>
    <script>
        $(function () {
            if ($('.datepicker').length) {
                $('.datepicker').datepicker({
                    format: 'yyyy-mm-dd',
                    autoclose: true,
                    todayHighlight: true
                });
            }
        });
    </script>
</body>
</html>
