<?php
// cookie_consent.php — Lichtgewicht cookie consent banner
// Wordt geïnclude onderaan header.php (enkel op niet-PUBLIC_SHARE_MODE pagina's)
// Consent-state: localStorage 'lh_consent' = 'granted' | 'denied'
if (defined('PUBLIC_SHARE_MODE')) return; // Share-pagina laadt geen GA4
?>
<!-- Cookie Consent Banner -->
<div id="cookieConsentBar"
     class="d-none position-fixed bottom-0 start-0 end-0 bg-white border-top shadow-lg d-print-none"
     style="z-index: 2000; padding: 14px 20px;"
     role="dialog" aria-live="polite" aria-label="Cookie-toestemming">
    <div class="container d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
        <div class="small text-secondary" style="max-width: 700px;">
            <i class="fa-solid fa-cookie-bite text-warning me-1"></i>
            <strong class="text-dark">Cookies</strong> — We gebruiken Google Analytics om het gebruik van de app te meten.
            Dit helpt ons de app te verbeteren. Lees meer in ons
            <a href="/privacy" class="text-primary text-decoration-underline">privacybeleid</a>.
        </div>
        <div class="d-flex gap-2 flex-shrink-0">
            <button id="cookieDenyBtn"
                    class="btn btn-sm btn-outline-secondary"
                    onclick="setConsent('denied')">
                Weigeren
            </button>
            <button id="cookieAcceptBtn"
                    class="btn btn-sm btn-primary fw-bold"
                    onclick="setConsent('granted')">
                <i class="fa-solid fa-check me-1"></i>Accepteren
            </button>
        </div>
    </div>
</div>

<script>
    (function () {
        var CONSENT_KEY = 'lh_consent';

        function setConsent(val) {
            localStorage.setItem(CONSENT_KEY, val);
            document.getElementById('cookieConsentBar').classList.add('d-none');
            if (typeof gtag === 'function') {
                gtag('consent', 'update', {
                    analytics_storage:    val === 'granted' ? 'granted' : 'denied',
                    ad_storage:           'denied',
                    ad_user_data:         'denied',
                    ad_personalization:   'denied'
                });
                // Stuur alsnog een pageview nu consent gegeven is
                if (val === 'granted') {
                    gtag('event', 'page_view');
                }
            }
        }
        window.setConsent = setConsent;

        // Toon banner als nog geen keuze gemaakt
        var stored = localStorage.getItem(CONSENT_KEY);
        if (!stored) {
            document.getElementById('cookieConsentBar').classList.remove('d-none');
        }
    })();
</script>
