# 📝 Roadmap & Braindump: Jeugdvoetbal Opstellingen

> **System Note for Antigravity (AI Context):**
> Dit bestand bevat de roadmap en ideeën voor de applicatie. **Start niet autonoom met het uitwerken of coderen van deze items.** Gebruik dit uitsluitend als achtergrondcontext om de visie van de app te begrijpen. Wanneer je bezig bent met een feature of bugfix die aan één van deze secties raakt, mag je proactief vragen of je een specifiek item uit deze lijst moet meenemen.

---

## 🌟 0. App Beschrijving & Actuele Features
**Elevator Pitch:**
"Lineup" is een slimme SaaS-applicatie ontworpen voor jeugd-voetbalcoaches. Het neemt de wekelijkse kopzorgen rond eerlijke speelminuten, wisselschema's en tactische balansen volledig weg door middel van een geavanceerd wiskundig algoritme.

**Actuele Feature Lijst:**
* **Auth & Multi-Tenancy:** Team/Club workspaces, Superadmin paneel met impersonatie, abonnementsbeheer, en wachtlijst functionaliteit.
* **Match & Speler Beheer:** Spelers dashboard, Match kalender, afwezigheidsbeheer en historische speelminuten (statistieken).
* **Schema & Tactiek Engine:** Drag-and-drop "Rank & Drop" Matrix voor positie rating (plus aparte weging voor doelmannen). Mathematische schema generator voor 100% eerlijke wissels. Visuele Schema Builder om manueel opstellingen in te delen. Theory Wizard voor maatwerk periodes.
* **Communicatie:** 'Magic Links' (publieke, afgeschermde schema's voor ouders), WhatsApp integratie voor selecties, en strakke PDF/print exports.

---

## 🚨 1. Hotfix Items (Prioriteit)
*Context voor Antigravity: Kritieke aanpassingen die bugs voorkomen, flow breken of urgente database vervuiling tegengaan.*

* **Payee account bescherming:** Voorkom absoluut dat een 'payee' (degene die betaalt voor de tenant) verwijderd kan worden door een uitgenodigde coach. Dit voorkomt onbeheerbare wees-accounts na een abonnementsaankoop.
* **Cleanup onbevestigde accounts:** Verwijder accounts periodiek uit de database als de e-mail na registratie niet is bevestigd.
* **Parenting Mode Access Warning:** Als een coach (die al ingelogd is met full access) een public share-URL voor ouders in zijn eigen browser opent, denkt hij ten onrechte dat ouders ook alle info zien. Bouw een duidelijke UI-waarschuwing in voor ingelogde gebruikers die zo'n URL bezoeken.

---

## 🖥️ 2. UI & UX Verbeteringen
*Context voor Antigravity: Zaken die de gebruikerservaring en acquisitie vergroten.*

* **Signup Lure Content:** De huidige signup-pagina is te leeg. Voeg overtuigende content (lure/teasers) toe om conversie (account creatie) te stimuleren.
* **Optimalisatie Generatie Flow:** Momenteel worden opstellingen direct gegenereerd na het toewijzen van een selectie (kost onnodig rekenkracht). Pas de flow aan: 
    * Stuur de gebruiker na selectie eerst naar een overzichtspagina.
    * Toon daar twee lijsten: 1. Andere wedstrijden met exact dezelfde selectie + team rating. 2. Wedstrijden met hetzelfde format (aantal spelers/keepers) maar andere selecties.
    * Plaats daar pas de effectieve "Genereer Opstellingen" knop.
* **Legacy ID printen:** Print het legacy schema ID mee op de gegenereerde PDF, zodat dit makkelijker terug te vinden en te gebruiken is in de app.

---

## ⚙️ 3. Module Secties: Opstellingen & Core Logica
*Context voor Antigravity: Functionaliteiten die direct impact hebben op het hart van de applicatie (het genereren en bouwen).*

* **Schema Builder / Lineup Builder:**
    * Een drag & drop builder waar een coach zelf de blokjes (spelers) in een format sleept. Terwijl dit gebeurt, moeten de individuele wedstrijd- én seizoensstatistieken live updaten.
    * **Slimme Sortering:** De spelerslijst (als buttons) wordt dynamisch gesorteerd. De speler die op basis van de balans de hoogste prioriteit heeft om ingezet te worden, staat bovenaan.
    * **UI Copy:** Bovenaan de spelerslijst staat de tekst: *"Aanbevolen volgorde op basis van speelminuten"*. Dit maakt voor de coach direct duidelijk waarom de volgorde afwijkt van alfabetisch.
    * **Gelaagde Sortering Logica:**
        1. **Wedstrijdstatistieken:** Sortering op minuten gespeeld / totaal minuten in huidige match (min/min totaal).
        2. **Periodestatistieken:** Indien een periode is ingesteld, is dit de tweede sorteerfactor (weergave in percentage).
        3. **Seizoensstatistieken:** Sortering op seizoenspercentage als laatste factor.
* **Uitgebreide Schema Selectie:** Maak het mogelijk om een wisselschema te starten zónder bestaande wedstrijd. Selecteer een format, aantal wedstrijdjes, wisseltijden (bijv. 7, 5, of 10 min) en kies spelers via een modal.
* **Periodisering van Statistieken (USP):** * Behoud het standaard seizoen (1 juli t/m 30 juni).
    * Voeg de optie toe om 'periodes' te maken (Voorbereiding, Heenronde, Terugronde), wat cruciaal is voor o.a. IP en P3 jeugdvoetbal.
    * Zorg voor naadloze validatie: periodes moeten op elkaar aansluiten en het hele jaar overbruggen.
    * Voeg een algoritme-setting toe per team om te bepalen hoe ver de tool terugkijkt voor het balanceren van speelminuten (gekoppeld aan deze periodes).

---

## 💳 4. Monetization Info
*Context voor Antigravity: Architectuur rondom betalingen, rechten en limitaties.*

* **Token Systeem & Prijzen:** * Verkoop toegang op basis van tijd, inclusief een token-pool die periodiek herstelt (tegen server hammering).
    * Maak tokens 'gewogen' o.b.v. processorbelasting (bijv. 8v8 zonder scores vs. mét scores).
    * Zorg voor een token-refund (geen tokens afschrijven) als het genereren faalt door onmogelijke constraints.
* **Abonnementsvormen:** Denk aan *7-day pass*, *Fortnight pass*, *Monthly*, en *Season*.
* **Verschillende Reken-niveaus:** Bied abonnementen aan zónder scoreberekening voor gebruikers die dat niet nodig hebben (bespaart server resources).
* **Trial Mode:** Een prachtig begeleide onboarding waar gebruikers alles kunnen invoeren. Als teaser worden er éénmalig (en random) drie opstellingen gegenereerd (bijv. 8, 9 en 10 spelers bij 8v8). Daarna zit de boel op slot (paywall).
* **Stripe Integratie:** Opzetten van de effectieve payment gateway.
* **Signup Waitlist:** Limiteer openbare signups tijdelijk. Plaats gebruikers op een wachtlijst na mailbevestiging, stuur een notificatie naar admin, en bouw een widget in het admin dashboard om deze handmatig goed te keuren.

---

## 📈 5. Analytics, Logging & Server Health
*Context voor Antigravity: Interne tools en dataverzameling voor de beheerder om bottlenecks en conversie te meten.*

* **Server Belasting & Logging:** Identificeer welke gebruikers/account-types de zwaarste belasting veroorzaken (belangrijk voor token-prijzen).
* **Sessie & Activiteit:** Toon in het tenant overzicht welke gebruikers online zijn en wanneer hun laatste activiteit was.
* **Conversie Tracking:** Meet hoeveel invites er gestuurd en geaccepteerd worden, en of hier (betaalde) accounts uit voortvloeien (eventueel belonen met credits).
* **Externe Tooling:** Onderzoek of deze performance monitoring deels met gratis Google Workspace/One tools kan, in plaats van alles in de app te bouwen.


---

## 👨‍👩‍👧 6. Growth & Engagement (Ouders / Gast Modus)
*Context voor Antigravity: Virale loop en interactie met toeschouwers.*

* [x] **Magic Share Link:** Coaches delen een URL met ouders om de opstelling live te volgen (zonder ratings/totale minuten).
* **Data Captatie via Sessie:** Vraag naam en e-mail (met consent) bij het openen van de share-URL. Sla op in de sessie en koppel aan latere events.
* **Crowdsourced Wedstrijd Events:** Laat ouders timestamps en events doorgeven ("Match gestart", "Wissel gedaan", goals, assists) om in een `game_events` tabel te plaatsen. 
    * *Waarschuwing:* Voorkom dubbele ingaves door timestamps te checken.
    * Laat ouders hun eigen foute ingaves flaggen/verbergen (soft-delete).
    * Dit verhoogt app-gebruik sterk en zorgt voor mond-tot-mondreclame.

---

## 💡 7. Braindump & Future Modules
*Context voor Antigravity: Ideeën voor de lange termijn. Nog niet in scope voor actieve ontwikkeling.*

* als je maar 1 doelman in je selectie hebt dan heeft het weinig zin om in het overzicht op het dashboard de hand icon te tonen. 


* **Reverse Engineer Schema (Legacy):** De mogelijkheid om via een interface (bijv. door selecties in volgorde te slepen of speelminuten in te geven) oude legacy schema id's te achterhalen (zoals id '777' van de match tegen Wellen).
* **Legacy Database Tabellen:** Een extra tabel bouwen voor oude schema-logica, met `format`, `legacy_key`, en `new_key`.
* **Gast-Coach Systeem:** Manier bedenken om een tijdelijke coach aan te duiden (voor statistieken), zonder dat dit vloekt met een eventueel coach-limiet in het betaalmodel (aangezien er per 'team' betaald wordt).
* **Extra Rol - Afgevaardigde:** Een nieuwe rol die wel opstellingen kan draaien, maar de achterliggende score matrix van de spelers niet kan zien.





- https://lineup.webbit.be/cron_cleanup.php



- default wedstrijd moment thuiswedstrijden

- feedback systeem bouwen
- ouders events laten ingeven







## feedback
- bij feedback zou ik ook de 10 logregels willen zien (in een modal ofzo), kan dat?



## match tracker
### functioneel
- event van ander ouder kwam precies niet binnen, wordt de pagina ververst? kan dat inpage zodat dit mijn google analytics views niet verstoort?
- auto wissel moment mag ook maar max 1x afgevuurd worden. auto wissel mag ook NOOIT een blok starten want als er dan 4 ouders tegelijk bezig zijn dan gaan hun calls de wedstrijd beeindigen


### info:
- de helft wissel gebeurd automatisch als xxx tijd verstreken is voor een gepland wisselmoment. je kan enkel spelers aanduiden die op dat moment op het veld staat. dus als je wisselspeler in komt en dadelijk scoort kan je die in principe niet aanduiden als doelpuntenmaker


### Wedstrijdverslag en wachtkamer:
-- ook daar mag je de label tonen (papa van Thibo) ipv email. toon email pas tonen na een hover