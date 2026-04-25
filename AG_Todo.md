# ⚠️ BELANGRIJK: NIET GEBRUIKEN VOOR HET GENEREREN VAN CODE
*Dit document (A.G. TODO) is puur een inspiratie- en brainstorm-bestand voor toekomstige integraties. Deze lijst mag momenteel NIET als actieve programmeertaak beschouwd of aangesproken worden door Agentic systemen, tenzij de user er expliciet om vraagt.*

---

## 1. Monetization & Onboarding Flow
- **Freemium "Hook" Model:** We vermijden het bouwen van een losse, onechte "Sandbox" omgeving voor the trial mode. In plaats daarvan geven we elk nieuw team 3 gratis "Generaties" aan de hand van een ingebouwde credits-limiet. Coaches bouwen hun effectieve team op met eigen échte spelers. Tegen de tijd dat credits op zijn, zit de data ingebakken in the app en is the conversie aannemelijker dan generieke demo-data.
- **Stripe & Abonnementen:** Beperk je tot Maand- en Jaarabonnementen via the Stripe Checkout Portal (om lokaal abonnementsbeheer te voorkomen).
- **Probeerpasjes:** Integreer een coupon/kortingscode mechanisme om coaches gratis een seizoen of proef-maand te geven op marketing-events.

## 2. Analytics & Server Load (Outsourcing the Heavy Lifting)
- **Drop the In-App dashboards:** Geen tijd verliezen aan het bouwen van eigen dashboard grafieken rond registraties. Integreer **Google Analytics 4** of **PostHog** als Javascript-snippet. Hieruit valt onmiddellijk the registratie-funnel en uitvalratio op the landingspages the trekken.
- **Server Load Watcher:** Het bestaande loggen van `execution_time_ms` in the database is meer dan voldoende. Wanneer the performance hapert, kunnen ruwe Database SQL queries the meest belastende users / query patronen filteren, eventueel met integraties richting *New Relic*.

## 3. Delen met "Magic Links" (User Management Alternatief)
- **De Afgevaardigde Puzzel:** In plaats van een rol "Afgevaardigde" uit te werken – waarbij mensen zonder technische achtergrond emailadressen, passwoorden en team uitnodigingen the baas moeten kunnen – werken we met *Magic Links*. 
- **Uitwerking:** De coach maakt de opstelling en drukt op "Genereer Deel Link". Hij dropt simpelweg the hash-token link (`/games/34/public?view=xyw8z...`) in de Whatsappgroep. Iedereen kan the sheet via mobiel read-only zien en afprinten zonder zich aan te melden. Enorme UX en ROI win.

## 4. UI / Lure Landing
- Een strakke, authenticiatie-vrije `/` landingpage is cruciaal. Plaats hierop een teaser van de User Interface of gegenereerde PDF, de 'Value Proposition' ("Wekelijkse tijd besparen") en een call-to-action naar the `signup.php`.

## 5. De Visuele (Handmatige) Schema Builder
De manuele builder moet niet concurreren met the zware achterliggende matrix restricties, maar eerder als een "begeleide interface" dienen voor admins/coaches om nieuwe ploegenopstellingen per shift bijeen te swipen.

**Intelligente Drag-and-Drop Flow:**
- **Stap 1 (Setup):** Coach kiest format (8v8, 4x20 min) en distilleert 10 van the totaal selecteerbare spelers als the huidige match-selectie.
- **Stap 2 (Interface):** Er tekent zich een groen veld met 8 posities (waaronder doelman vast gepind) en een bankzone voor the 2 invallers. Onder het veld staat een real-time Stats-Bord (actuele minuut-tellers per speler).
- **Stap 3 (Helft/Kwart 1):** Coach sleept spelers op the posities. Minuten-tellers onderin reflecteren de posbilites realtime. Pas bij een volledige 'bezetting' van the veld slots is het helftje vastgelegd.
- **Stap 4 (Kopiëren & Slim Sorteren):** Zodra helft 1 opgeslagen is ter visualisatie, laadt helft/kwart 2 (die aanvankelijk kopieert wat er al stond op The Pitch). 
- **De Magie (Sorteren):** De linker speellijst met targets ordent zich voortaan automatisch "slim". Reserve-spelers van The Bench in de vorige shift verschijnen bovenaan de lijst als absolute prioriteit, eventueel geflankeerd door the achtergrondkleur naargelang hun historisch (vorig wekend) spelvolume The user (coach) hoeft hier enkel twee bankzitters naar in-game spelers op the veld the slepen, en de shift is gepiept.
- In deze fase laten we de coach sturen zonder hem een hard "Error: Matrix Exception" venster the geven. Er wordt in the layout enkel pro-actief gewaarschuwd aan de hand van ordentraining en stoplicht-kleurtjes ("Let op, speler mag historisch gezien niet 2x dalen").

## 6. Historische Herbruikbaarheid
- **Kopieer Vorige Selecties:** Met "Eén klik" dezelfde speler-selecties naar of schema's overnemen van de voorgaande wedstrijd week om invullen the mitigeren (= super ROI met 2 backend code regels).
