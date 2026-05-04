<?php
$page_title = 'Privacybeleid — Lineup Heroes';
require_once __DIR__ . '/header.php';
?>
<div class="container py-5" style="max-width: 800px;">
    <h1 class="fw-bold mb-1">Privacybeleid</h1>
    <p class="text-muted small mb-5">Laatste update: mei 2025</p>

    <section class="mb-5">
        <h2 class="h5 fw-bold">1. Wie zijn we?</h2>
        <p>Lineup Heroes is een webapplicatie voor jeugdvoetbalcoaches om opstellingen en speeltijden te beheren.</p>
        <p>Contact: <a href="mailto:info@lineupheroes.com">info@lineupheroes.com</a></p>
    </section>

    <section class="mb-5">
        <h2 class="h5 fw-bold">2. Welke gegevens verzamelen we?</h2>

        <h3 class="h6 fw-semibold mt-3">Coaches (ingelogde gebruikers)</h3>
        <ul>
            <li><strong>Account­gegevens</strong>: naam, e-mailadres (bij registratie)</li>
            <li><strong>Team­gegevens</strong>: spelers, wedstrijden, opstellingen (ingevoerd door de coach zelf)</li>
            <li><strong>Gebruik­sdata</strong>: welke functies worden gebruikt, hoe vaak de generator wordt aangeroepen</li>
            <li><strong>Technische logs</strong>: paginalaadtijden, geheugengebruik — enkel voor prestatie­optimalisatie</li>
        </ul>

        <h3 class="h6 fw-semibold mt-3">Ouders / bezoekers van de live match­tracker</h3>
        <ul>
            <li><strong>E-mailadres en naam</strong>: optioneel, enkel lokaal opgeslagen in je browser (localStorage) om je te identificeren bij het loggen van doelpunten. Wordt <em>nooit</em> permanent op onze servers bewaard zonder je actie.</li>
            <li><strong>IP-adres</strong>: tijdelijk gelogd voor technische doeleinden (rate limiting, misbruikpreventie). Wordt niet gekoppeld aan je identiteit.</li>
        </ul>
    </section>

    <section class="mb-5">
        <h2 class="h5 fw-bold">3. Cookies en vergelijkbare technologieën</h2>
        <div class="table-responsive">
            <table class="table table-bordered table-sm small">
                <thead class="table-light">
                    <tr>
                        <th>Naam</th>
                        <th>Type</th>
                        <th>Doel</th>
                        <th>Verplicht?</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>PHPSESSID</code></td>
                        <td>Sessie­cookie</td>
                        <td>Ingelogd blijven als coach</td>
                        <td><span class="badge bg-success">Ja</span></td>
                    </tr>
                    <tr>
                        <td><code>_ga</code>, <code>_ga_*</code></td>
                        <td>Cookie (Google)</td>
                        <td>Gebruiksanalyse via Google Analytics 4 — helpt ons de app verbeteren</td>
                        <td><span class="badge bg-warning text-dark">Enkel met toestemming</span></td>
                    </tr>
                    <tr>
                        <td><code>parent_email</code>, <code>parent_name</code></td>
                        <td>localStorage</td>
                        <td>Je naam/e-mail onthouden op de live match­tracker (enkel lokaal in je browser)</td>
                        <td><span class="badge bg-success">Ja (functioneel)</span></td>
                    </tr>
                    <tr>
                        <td><code>lh_consent</code></td>
                        <td>localStorage</td>
                        <td>Je cookie­voorkeur onthouden</td>
                        <td><span class="badge bg-success">Ja (functioneel)</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <section class="mb-5">
        <h2 class="h5 fw-bold">4. Google Analytics</h2>
        <p>We gebruiken Google Analytics 4 (GA4) om te begrijpen hoe de app wordt gebruikt — welke functies populair zijn,
        hoe snel pagina's laden, en hoe we de gebruikerservaring kunnen verbeteren.</p>
        <p>GA4 plaatst cookies (<code>_ga</code>, <code>_ga_*</code>) die je browser kunnen identificeren.
        Wij activeren deze cookies <strong>enkel na je expliciete toestemming</strong>.</p>
        <p>Meer info: <a href="https://policies.google.com/privacy" target="_blank" rel="noopener">Google Privacy Policy</a></p>
    </section>

    <section class="mb-5">
        <h2 class="h5 fw-bold">5. Hoe lang bewaren we je gegevens?</h2>
        <ul>
            <li>Account­gegevens: zolang je account actief is</li>
            <li>Team- en speler­gegevens: tot je ze zelf verwijdert of je account verwijderd wordt</li>
            <li>Technische logs: maximaal 30 dagen</li>
            <li>IP-adressen in event­logs: maximaal 90 dagen</li>
        </ul>
    </section>

    <section class="mb-5">
        <h2 class="h5 fw-bold">6. Jouw rechten (AVG / GDPR)</h2>
        <p>Je hebt het recht om:</p>
        <ul>
            <li><strong>Inzage</strong> te vragen in de gegevens die we over jou bewaren (Art. 15)</li>
            <li><strong>Onjuiste gegevens</strong> te laten corrigeren (Art. 16)</li>
            <li><strong>Je gegevens te laten verwijderen</strong> ("recht op vergetelheid", Art. 17)</li>
            <li><strong>Je toestemming voor analytics</strong> op elk moment in te trekken (zie "Cookie-instellingen" onderaan)</li>
        </ul>
        <div class="alert alert-light border small">
            <i class="fa-solid fa-envelope me-2 text-primary"></i>
            Stuur je verzoek naar <a href="mailto:info@lineupheroes.com" class="fw-bold">info@lineupheroes.com</a>
            met vermelding van je naam en e-mailadres. We reageren binnen <strong>30 dagen</strong>.
        </div>
    </section>

    <section class="mb-5">
        <h2 class="h5 fw-bold">7. Cookie­instellingen aanpassen</h2>
        <p>Je kunt je keuze op elk moment herzien:</p>
        <div class="d-flex gap-2 mt-2">
            <button class="btn btn-sm btn-primary" onclick="resetAndShowConsent()">
                <i class="fa-solid fa-cookie-bite me-1"></i>Cookie­voorkeur aanpassen
            </button>
        </div>
        <script>
            function resetAndShowConsent() {
                localStorage.removeItem('lh_consent');
                var bar = document.getElementById('cookieConsentBar');
                if (bar) bar.classList.remove('d-none');
                window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
            }
        </script>
    </section>

    <section class="mb-5">
        <h2 class="h5 fw-bold">8. Wijzigingen</h2>
        <p>We kunnen dit privacybeleid aanpassen. Bij ingrijpende wijzigingen informeren we je via de app.</p>
    </section>
</div>
<?php require_once __DIR__ . '/footer.php'; ?>
