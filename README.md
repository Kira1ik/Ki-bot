# Katta AI Chat – Del 6

Dette er en videreutviklet versjon av Katta AI Chat med StudieBuddy som hovedagent.

## Funksjoner
- StudieBuddy med en mer gjennomarbeidet systemprompt for elever på videregående skole.
- Valg mellom StudieBuddy, Reisekompis og IT-hjelpen.
- Temperaturvalg: 0.2, 0.7 og 1.0.
- Ny samtale-knapp.
- Lokal lagring av samtalehistorikk per agent i nettleseren.
- Server-side kontroll av meldinger og temperatur.
- API-nøkkel leses fra miljøvariabel eller `config.local.php`.

## Oppsett
1. Last opp prosjektfilene til webhotellet.
2. Kopier `config.local.php.example` til `config.local.php`.
3. Legg en gyldig, ny OpenAI API-nøkkel i `config.local.php`, eller sett miljøvariabelen `OPENAI_API_KEY` på serveren.
4. Kontroller at PHP cURL er aktivert.
5. Åpne nettsiden og test chatten.

`config.local.php` skal aldri lastes opp til GitHub. Den er lagt i `.gitignore`.

## Temperatur
- **0.2:** lav variasjon og mer konsistente svar. Anbefalt startverdi for studiehjelp.
- **0.7:** mer variasjon, men fortsatt en moderat innstilling.
- **1.0:** større variasjon i formuleringer. Svarene bør kontrolleres ekstra nøye.

Temperatur påvirker variasjonen i svarene, ikke hvorvidt informasjon automatisk er korrekt.

## Viktig om API-nøkkel
Hvis en API-nøkkel tidligere har blitt delt eller lagt i en fil som er sendt videre, må den tilbakekalles og erstattes. Ikke legg nøkler i HTML, JavaScript, README eller GitHub.
