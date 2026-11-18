# StudieBuddy

StudieBuddy er en KI-chatbot med flere agenter, utviklet i PHP og JavaScript med OpenAI API. Hovedagenten, StudieBuddy, er en pedagogisk veileder for elever på videregående skole. Den er utformet for å støtte elevens egen læring ved å veilede og stille spørsmål, i stedet for å levere ferdige svar.

Prosjektet er utviklet i faget Informasjonsteknologi og medieproduksjon

**Publisert chatbot:** `https://zhasli09.imkatta.no/KI-bot/login.php`

## Formål og målgruppe
Chatboten er laget for elever på Vg1–Vg3 som trenger støtte til lekser, prøveforberedelser og studieteknikk. Formålet er at eleven skal forstå stoffet, ikke bare få svaret.

## Funksjonalitet
- Sju agenter med egne systemprompter, hilsener og fargeprofiler
- Valg av temperatur (0.2, 0.7 eller 1.0), med 0.2 som standard
- Valg av språk: norsk, engelsk eller russisk
- Mulighet for å starte en ny samtale
- Samtalehistorikk lagret i nettleseren (localStorage), separat for hver agent
- Lyst og mørkt tema
- Korrekt visning av kodeblokker, fet og kursiv tekst i svarene
- Skriveindikator, feilmeldinger og innlogging

## Agenter
| Agent | Rolle |
|---|---|
| StudieBuddy | Pedagogisk veileder for elever (standardagent) |
| Matte-Magikeren | Matematikk og logikk |
| Språkpartner | Øving i fremmedspråk |
| Karriereveileder | CV, søknader og studievalg |
| Reisekompis | Reiseguide |
| IT-hjelpen | IT-support |
| Katta Skole-Info | Skoleinformasjon (demo uten reelle skoledata) |

## Systemprompt
Systemprompten til StudieBuddy bygger på fire pedagogiske prinsipper:

1. **Sokratisk metode:** eleven får ledende spørsmål i stedet for et ferdig svar.
2. **Stillasbygging:** komplekse oppgaver deles opp i små, overkommelige steg.
3. **Tilpasset opplæring:** forklaringene tilpasses elevens nivå.
4. **Metakognisjon:** eleven oppfordres til å reflektere over egen læring.

I tillegg gjelder tre regler: agenten gir ikke ferdige besvarelser, er alltid oppmuntrende og anmoder eleven om å kontrollere pensumspesifikke detaljer med læreren når den er usikker. Hele prompten ligger i `config.php`.

## Temperatur
| Verdi | Egenskaper |
|---|---|
| 0.2 | Mest stabile og forutsigbare svar. Standard for studiehjelp. |
| 0.7 | Moderat variasjon. |
| 1.0 | Størst variasjon. Svarene bør kontrolleres ekstra nøye. |

Temperatur styrer variasjonen i svarene, ikke om innholdet er faglig riktig.

## Teknologi
PHP (cURL), JavaScript, HTML, CSS og OpenAI API (modell `gpt-4o-mini`).

## Filstruktur
```
Katta_AI_Chat/
├── index.php                 # Chatgrensesnitt og JavaScript
├── config.php                # Agenter, prompter, farger, temperatur og språk
├── config.local.php.example  # Mal for API-nøkkel
├── api/chat.php              # Kommunikasjon med OpenAI
├── auth.php, login.php, logout.php
├── css/style.css
├── docs.html
├── .gitignore
└── README.md
```

## Videre arbeid
- Lagring av samtalehistorikk i database
- Forbedret autentisering med flere brukere og hashede passord
- Strømming av svar slik at teksten vises fortløpende
