# Implementation Plan: Voetbal Lineup Unit Testing

Het doel van dit plan is om geautomatiseerde testen in je maatwerk PHP applicatie in te voeren om regressies (bugs die geïntroduceerd worden bij nieuwe edits) in de toekomst automatisch te detecteren in plaats van via "live bugs" of "witte pagina's".

## Fase 1: Test Omgeving Opzetten (Docker & Composer)
Momenteel beschikt het project niet over een formele *package manager*. We implementeren **PHPUnit** als dé industriestandaard voor testen in PHP.

1.  **Composer Introduceren:** 
    *   Toevoegen van `composer.json` in je root map om dependencies te beheren.
    *   Het commando `composer require --dev phpunit/phpunit` runnen om PHPUnit lokaal te installeren in je Docker `php-app`.
2.  **Mappenstructuur & PHPUnit Config:**
    *   Aanmaken map `tests/Unit` en `tests/Integration` in de `php/` map.
    *   Aanmaken van `phpunit.xml` waarin we filteren dat tests uitsluitend bestanden in `tests/` uitvoeren met de suffix `*Test.php`.
3.  **Docker Automatisatie (Optioneel):**
    *   Aanpassen van de `docker-compose.yml` of toevoegen van een handig bash shell-script om met één klik `docker exec php-app vendor/bin/phpunit` te runnen in je terminal.

## Fase 2: Code "Testable" Maken (Refactoring Kern Logica)
Veel onderdelen van de site leunen nu sterk op globale PHP-variabelen en procedurele code (bvb. `generator.php`). Echte *Unit Tests* moeten geïsoleerd zijn. Dit pakken we incrementeel aan:

1.  **Refactoring van de `Game` klasse:**
    *   Huidig probleem: De constructor gebruikt `global $player_scores, $global_playerinfo;` en `global $events;`.
    *   Oplossing: Deze variabelen moeten meegegeven worden in de parameters van de methode (Dependency Injection). Dit stelt ons in staat om de `Game` klasse perfect te testen met dummy data (Mock data) zónder dat die uit de reële database of bestanden hoeft te komen.
2.  **Scheiden van Logica en Output:**
    *   Drukke bestanden zoals `api_save_schema.php` doen alles tegelijk (valideren, scoren, theorie-arrays aanpassen, file saving én output afdrukken). 
    *   Onderdelen zoals het valideren en omzetten van de `schema_id` moeten losgetrokken worden in een helperfunctie of klasse zodat je het apart kunt testen zónder daadwerkelijk een disk-operatie uit te voeren.

## Fase 3: Eerste Unit Tests Schrijven (De "Low-Hanging Fruit")
We beginnen met het automatiseren van specifieke complexe logica waarvan je wil dat die in theorie nooit meer breekt.

1.  **`Tests/Unit/GameTest.php`**:
    *   *Test 1:* Controleer of `swapPlayers()` exact doet wat het belooft (namelijk integer keys mappen naar string IDs/Naam keys).
    *   *Test 2:* Controleer of de rating berekening in `setRunQuality()` exact het gewenste getal uitstoot gegeven een gefixeerde array van input (dus voorkomen dat de score plots door 0 kan delen).
    *   *Test 3:* Controleer theorieën met lege arrays of ontbrekende schema's, en test of de fallback-mechanismeregeling klopt.
2.  **`Tests/Unit/MatchManagerTest.php`**:
    *   Geef hier een in-memory SQLite simulatie database in mee via PDO (met gefixeerde 3 wedstrijden in).
    *   Test of the `getHistoricalPlaytime()` effectief correct totaliseert zónder dat je live database corrupt wordt of meewisselt.

## Fase 4: Integratie & Workflow Tests
Zodra de kleine deeltjes (classes) robuust getest zijn, gaan we de integratie-processen van begin tot eind afvinken.

1.  **Schema Editor Save-Flow Mocken (`api_save_schema.php`)**:
    *   Schrijf in `tests/Integration/` code die letterlijk payloads (POST requests) naar je API simuleert:
        *   Stuur een theorie Payload die *exact* overlapt = Test assertie moet controleren of hij `"is_duplicate": true` retourneert met het juiste id van de reeds gecompileerde file.
        *   Stuur een fake payload en test dat het opslaan slaagt of stoot op een exceptie zonder FATAL errors.
2.  **CI/CD Toekomst**: 
    *   Bij het opsporen en vastleggen van fouten als dit (Github Actions / lokale pre-commit hooks) mag code simpelweg niet gepusht of live gezet worden zolang `vendor/bin/phpunit` geen 100% SLA slaagt toont in groen licht.

---

> **Is dit plan naar wens om mee te starten?** 
> *Indien akkoord, kunnen we meteen met Fase 1 & Fase 2 beginnen door Composer te betrekken bij je Dockerfile en de `Game` class testbaar te ontkoppelen!*
