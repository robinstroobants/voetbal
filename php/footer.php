    <footer class="d-print-none text-center text-muted mt-5 mb-4" style="font-size: 0.75rem;">
        <?php
            // Bepaal globale rendertijd en geheugenpiek van de volledige pagina
            $global_load_ms = round((microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]) * 1000, 2);
            $mem_peak_mb = round(memory_get_peak_usage() / 1024 / 1024, 2);

            // Haal de Git App Version tag op
            $app_version = 'v0.0.0';
            // Probeer versie uit Git te halen (Docker compatibel)

            $versionFile1 = __DIR__ . '/site_version.txt';
            $versionFile2 = __DIR__ . '/version.txt';

            // Controleer of het bestand bestaat en lees het uit (prioriteit aan site_version)
            $raw_version = '';
            foreach ([$versionFile1, $versionFile2] as $file) {
                if (file_exists($file)) {
                    $raw_version = trim(file_get_contents($file));
                    if (!empty($raw_version)) {
                        break;
                    }
                }
            }

            if (!empty($raw_version)) {
                // Trim whitespace en sanitize de output tegen XSS (Cross-Site Scripting)
                // Zelfs als je het bestand zelf beheert, is dit 'best practice'.
                $version = htmlspecialchars(trim($raw_version), ENT_QUOTES, 'UTF-8');
                if ($version && trim($version)) {
                    $app_version = trim($version);
                }
            }
            
            
            // Als de Generator specifieke back-tracking solver tijden heeft berekend, toon die dan mee:
            // Verborgen op aanvraag gebruiker
        ?>
        <div class="text-secondary opacity-75">
            <i class="fa-solid fa-code-branch me-1"></i> <span class="fw-bold" style="letter-spacing: 0.5px;"><?= htmlspecialchars($app_version) ?></span> 
            <span class="mx-2">&middot;</span> <i class="fa-solid fa-stopwatch me-1"></i> <?= $global_load_ms ?> ms
            <span class="mx-2">&middot;</span> <i class="fa-solid fa-memory me-1"></i> <?= $mem_peak_mb ?> MB
        </div>
    </footer>

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
            // Initialize all popovers globally
            const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
            const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl, { sanitize: false }));
        });
    </script>
</body>
</html>
