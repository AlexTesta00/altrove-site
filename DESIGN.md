---
name: "Altrove · Linea aperta"
description: "Una radio notturna per le storie oltre il visibile."
colors:
  night: "#110c2e"
  peach: "#ffc48a"
  peach-hover: "#ffe0bf"
  cream: "#fbefe3"
  lavender: "#b9abea"
  soft: "#d9cff5"
  panel: "#1a1442"
  text-secondary: "#e6ddf8"
  rose: "#f0a7cb"
  violet: "#8073d8"
  selected: "#231b58"
  error: "#ffb7b7"
  placeholder: "#9c8fcb"
  tone-8: "rgba(251,239,227,.14)"
  tone-19: "rgba(251,239,227,.12)"
  tone-28: "rgba(251,239,227,.24)"
typography:
  display:
    fontFamily: "Tenor Sans, serif"
    fontSize: "clamp(42px,11.5vw,92px)"
    fontWeight: 400
    lineHeight: 1.02
    letterSpacing: "-.01em"
  display-desktop:
    fontFamily: "Tenor Sans, serif"
    fontSize: "clamp(60px,5.6vw,84px)"
    fontWeight: 400
    lineHeight: 1.02
    letterSpacing: "-.01em"
  headline:
    fontFamily: "Tenor Sans, serif"
    fontSize: "clamp(32px,8.4vw,58px)"
    fontWeight: 400
    lineHeight: 1.06
  title:
    fontFamily: "Tenor Sans, serif"
    fontSize: "26px"
    fontWeight: 400
    lineHeight: 1.15
  body:
    fontFamily: "Hanken Grotesk, system-ui, sans-serif"
    fontSize: "18px"
    fontWeight: 400
    lineHeight: 1.55
  label:
    fontFamily: "Hanken Grotesk, system-ui, sans-serif"
    fontSize: "15px"
    fontWeight: 400
    lineHeight: 1.55
  control:
    fontFamily: "Hanken Grotesk, system-ui, sans-serif"
    fontSize: "17px"
    fontWeight: 600
rounded:
  field: "16px"
  option: "20px"
  panel: "28px"
  tuner: "32px"
  crown: "44px"
  capsule: "999px"
spacing:
  size-8: "8px"
  size-12: "12px"
  size-16: "16px"
  size-20: "20px"
  size-24: "24px"
  size-28: "28px"
  size-32: "32px"
  size-40: "40px"
  size-48: "48px"
  size-56: "56px"
  size-64: "64px"
  size-72: "72px"
components:
  button-primary:
    backgroundColor: "{colors.cream}"
    textColor: "{colors.night}"
    typography: "{typography.control}"
    rounded: "{rounded.capsule}"
    padding: "8px 24px 8px 8px"
  button-pill:
    backgroundColor: "{colors.cream}"
    textColor: "{colors.night}"
    rounded: "{rounded.capsule}"
    padding: "0 18px"
  button-pill-hover:
    backgroundColor: "{colors.peach-hover}"
    textColor: "{colors.night}"
  button-ghost:
    backgroundColor: "transparent"
    textColor: "{colors.cream}"
    rounded: "{rounded.capsule}"
    width: "100%"
  input:
    backgroundColor: "{colors.night}"
    textColor: "{colors.cream}"
    typography: "{typography.body}"
    rounded: "{rounded.field}"
    padding: "13px 16px"
    width: "100%"
  navigation:
    backgroundColor: "{colors.panel}"
    textColor: "{colors.cream}"
    rounded: "{rounded.capsule}"
    padding: "6px"
  fact-chip:
    backgroundColor: "{colors.panel}"
    textColor: "{colors.cream}"
    rounded: "{rounded.capsule}"
    padding: "8px 14px"
  form-panel:
    backgroundColor: "{colors.panel}"
    textColor: "{colors.cream}"
    rounded: "44px 44px 28px 28px"
    padding: "28px 20px 24px"
  option:
    backgroundColor: "{colors.night}"
    textColor: "{colors.cream}"
    rounded: "{rounded.option}"
    padding: "18px 58px 18px 18px"
  option-selected:
    backgroundColor: "{colors.selected}"
    textColor: "{colors.cream}"
    rounded: "{rounded.option}"
---

# Design System: Altrove · Linea aperta

## Overview

**Creative North Star: "Linea aperta"**

Altrove è una radio nella notte: una superficie indaco continua, comandi chiari e una luce pesca che indica dove intervenire. La scala di sintonia, l'ago e la lampada a eclissi traducono l'identità del podcast in strumenti riconoscibili. Tenor Sans dà respiro ai titoli; Hanken Grotesk sostiene una lettura lunga e personale.

La densità cresce attorno ai comandi, poi si allenta fra le sezioni. Il cielo resta dietro al contenuto; il movimento racconta accensione, sintonia e voce. Il sistema conserva un aspetto completo quando le animazioni sono ferme. La direzione approvata esclude la combinazione generica di galassia protagonista, titolo centrale e pannelli di vetro.

**Key Characteristics:**

- Notte continua, testi crema e lavanda, segnale pesca.
- Capsule operative, dischi ed eclissi; pannelli ampi e morbidi.
- Titoli a due toni allineati a sinistra e testo di lettura generoso.
- Sintonia e onda della voce come interazioni riconoscibili.
- Contenuto e controlli utilizzabili con tastiera, pausa e movimento ridotto.

Sistema estratto il 14 novembre 2026 da [CSS sorgente](src/css/app.css), [template](app/templates/layout.php), [pagine](app/templates/pages/page.php), [modulo](app/templates/partials/form.php), [sintonizzatore](app/templates/partials/tuner.php) e JavaScript di [movimento](src/js/motion.js), [sintonia](src/js/tuner.js) e [candidatura](src/js/form.js). Il [contratto approvato](.impeccable/surfaces/design-canvas-project-main-dc-html.md), i riferimenti [Home](design/canvas/project/Main.dc.html) e [Candidati](design/canvas/project/Candidati.dc.html) spiegano la direzione; il codice finale determina i valori. La [revisione conclusiva](.impeccable/review/finish-review.md) contiene il verdetto e i limiti della verifica. I token sopra raccolgono il vocabolario riusato, non ogni valore di costruzione presente nel CSS.

## Colors

La palette tiene insieme la notte blu-viola del podcast e il calore di una spia accesa; i valori normativi sono nel frontmatter.

### Primary

- **Segnale pesca** (`peach`): ago, lampada, dischi delle azioni, link, focus e selezione dei controlli. `peach-hover` schiarisce i link e le capsule al passaggio del puntatore.

### Secondary

- **Voce rosa** (`rose`): onda della storia ed equalizzatore; compare anche nell'anello Instagram insieme agli altri colori del brand.
- **Orizzonte viola** (`violet`): grande parola ALTROVE e dettagli circolari dell'identità.

### Neutral

- **Notte** (`night`): fondo continuo e interno dei campi; **pannello indaco** (`panel`): navigazione, invito, modulo e schede Instagram.
- **Crema** (`cream`): testo principale e superficie dei pulsanti. **Lavanda** (`lavender`): seconda voce dei titoli e informazioni di accompagnamento.
- **Lavanda chiara** (`text-secondary`): paragrafi; **lavanda morbida** (`soft`): descrizioni brevi, note e dettagli del cielo.
- **Indaco selezionato** (`selected`): fondo dell'opzione scelta; **rosa errore** (`error`): errori testuali e bordi dei campi invalidi; **lavanda segnaposto** (`placeholder`): esempi nei campi, mai sostituti delle etichette.
- **Filetti crema** (`tone-8`, `tone-19`, `tone-28`): trasparenze della stessa famiglia per navigazione, pannelli e campi. I bordi si leggono sul fondo scuro; non sono superfici opache.

### Named Rules

**The Segnale Rule.** Il pesca indica azione, posizione o stato; una selezione deve avere anche un segno riconoscibile, come ago, eclissi, bordo o riempimento.

## Typography

**Display Font:** Tenor Sans, con fallback serif. **Body Font:** Hanken Grotesk, con fallback system-ui e sans-serif. Entrambe sono servite localmente in WOFF2; Tenor Sans usa il peso regolare, Hanken Grotesk i pesi regolare, medio e semibold.

**Character:** titoli sottili, ampi e non solenni; testo quotidiano, leggibile e vicino alla persona. Il nome storico della classe `serif` non cambia la famiglia effettiva Tenor Sans.

### Hierarchy

- **Display:** ruolo fluido dei titoli principali; il desktop ha una scala distinta registrata nel frontmatter. La candidatura usa una variante osservata (`clamp(40px,11vw,76px)`, interlinea `1.04`; desktop `clamp(56px,5vw,76px)`).
- **Headline:** titoli di sezione con seconda frase lavanda e bilanciamento delle righe.
- **Title:** nomi delle frequenze. Le domande del modulo sono più ampie (`clamp(28px,7.6vw,44px)`, interlinea `1.1`); non diventano un nuovo stile per tutti i titoli.
- **Body:** base del testo e dei campi. La lettura estesa ha interlinea `1.65` e misura massima `65ch`; introduzioni e descrizioni adottano misure più corte fra `36ch` e `62ch`.
- **Label:** supporto e metadati, distinto dal testo di lettura. Alcuni contatori e nomi di stazione su telefono sono più piccoli; non usarli come misura dei paragrafi. Numeri di progresso e visualizzazioni usano cifre tabulari.

### Named Rules

**The Parola Intera Rule.** ALTROVE sull'orizzonte si adatta alla larghezza disponibile e resta intera; il suo trattamento monumentale non autorizza a tagliare il marchio.

## Layout

La composizione parte da una colonna su telefono, con margini del contenuto di `20px` e cornici esterne di `12px`. La navigazione è fissa: il corpo riserva spazio in alto e lo scorrimento verso le ancore tiene conto della sua altezza. Il breakpoint principale è `900px`: il contenitore raggiunge `1100px`, i margini interni diventano `24px` e le sezioni respirano maggiormente. Un adattamento fino a `420px` compatta solo i controlli e i metadati più stretti.

Sul desktop il titolo e il suo invito usano una griglia `7fr / 5fr`, ripresa dalla sezione video; le quattro frequenze diventano quattro colonne, i passi tre e Instagram due. Il ritratto e il racconto affiancati usano `5fr / 6fr`. I pannelli di candidatura e invito hanno una larghezza massima di `760px`, mentre il testo lungo conserva la misura di lettura. Nessuna griglia obbliga le altre pagine a riprodurre la sequenza della home.

La scala spaziale non è un multiplo unico: i valori raccolti nel frontmatter ricorrono nei ritmi interni. Le sezioni principali iniziano con `72px` di spazio su telefono e `120px` su desktop; il modulo usa spazi più serrati fra etichetta, campo, aiuto ed errore. Le aree dei principali comandi sono almeno `44px` in altezza; pulsanti principali e campi hanno ulteriore respiro.

## Elevation & Depth

La profondità viene soprattutto da fondi indaco, filetti trasparenti e sovrapposizioni circoscritte. L'ombra diffusa della navigazione (`0 10px 28px -10px rgba(5,3,20,.8)`) la stacca dal contenuto durante lo scroll. Le ombre interne pesca disegnano eclissi e selezioni: sono geometria del marchio, non elevazione dei pannelli. Il cielo e la luce radiale dell'orizzonte sono materiali del mondo scelto; i pannelli restano opachi, senza effetto vetro.

### Named Rules

**The Notte Continua Rule.** Conserva un fondo notturno continuo e usa il pannello indaco per delimitare un'azione o un gruppo; non aggiungere ombre diffuse a ogni contenitore.

## Shapes

Capsule per navigazione, pulsanti e brevi fatti; cerchi per lampada, disco, indicatore e passi. I campi sono più rettangolari, le opzioni hanno angoli più morbidi, i pannelli più ampi ancora. Invito e candidatura combinano gli angoli superiori `crown` con quelli inferiori `panel`. Foto e video sono ritagliati in cornici morbide; il ritratto è verticale (`4/5`) e le copertine video orizzontali (`16/9`).

Le tacche ripetute e i filetti sottili appartengono alla scala radio. L'ago pesca conserva una posizione precisa e un terminale riconoscibile. Le icone sono SVG a tratto arrotondato, non caratteri tipografici decorativi.

## Components

### Buttons

Il comando principale è una capsula crema con testo notte, disco pesca e icona. L'altezza minima è `56px`; il disco è `40px`. Il passaggio del puntatore solleva leggermente la capsula e sposta il disco; il focus usa un contorno pesca. Lo stato disabilitato mantiene la forma con opacità ridotta e nessun movimento.

La capsula compatta della navigazione ha altezza minima `44px` e schiarisce al passaggio. Il pulsante secondario del modulo è trasparente, con filetto crema, altezza minima `52px` e bordo pesca al passaggio. Nei passi del modulo il comando principale occupa tutta la larghezza su telefono; sul desktop condivide la riga con Indietro.

### Chips

I fatti brevi sono capsule indaco con filetto crema e testo compatto. Sono informazioni, non pulsanti. Non aggiungere interazione solo perché ne condividono la forma.

### Cards / Containers

Invito e candidatura hanno una sommità ampia, fondo indaco e filetto tenue. Le schede Instagram sono collegamenti completi con avatar, descrizione e comando visibile; il passaggio del puntatore colora il bordo e solleva appena la scheda. I video usano copertine vere e testo esterno alla cornice. I metadati restano parte del nome accessibile dei collegamenti che li contengono.

### Inputs / Fields

Fondo notte, testo crema, bordo trasparente e cursore pesca. Il campo ha altezza minima `52px`, la storia una textarea ridimensionabile con minimo `200px`. Etichette visibili, aiuti ed errori sono associati ai campi; il focus è un contorno pesca, l'errore un bordo rosa accompagnato dal messaggio e dallo stato `aria-invalid`.

Le opzioni sono radio nativi dentro un'etichetta estesa. Lo stato scelto cambia fondo e bordo e disegna un'eclissi nel controllo. La candidatura arricchita mostra un passo alla volta, porta il focus alla domanda e annuncia il progresso; la versione senza JavaScript conserva tutti i fieldset e il normale invio. L'eventuale verifica Turnstile configurata richiede JavaScript.

### Navigation

Capsula fissa con lampada, marchio, pausa delle animazioni e azione Candidati. La linea pesca inferiore e il piccolo ago seguono lo scorrimento. Il controllo pausa compare quando il movimento JavaScript è attivo e dichiara il proprio stato con `aria-pressed`. Il collegamento per saltare al contenuto diventa visibile al focus. Il footer mantiene la navigazione legale.

### Sintonizzatore e voce

La sintonia combina scala, ago, quattro stazioni, indicatore del segnale e collegamento alla puntata. Su telefono rimane nel fondo della pagina; sul desktop acquista una cornice indaco. L'ago si trascina, si aggancia alla stazione e risponde a frecce, Home ed End. La stazione corrente cambia anche riempimento e peso, oltre al colore. Sulle pagine tematiche il sintonizzatore è statico e i nomi sono collegamenti alle altre pagine.

La scansione automatica cambia stazione ogni `3600ms`, si interrompe all'interazione e può riprendere dopo `14000ms`. La molla è calcolata in JavaScript, mentre gli ingressi usano `cubic-bezier(.16,1,.3,1)` e i piccoli scatti `cubic-bezier(.34,1.56,.64,1)`. L'onda rosa della storia deriva dai caratteri scritti, senza registrare audio. La scala del modulo avanza con il passo effettivo.

### Movimento e accessibilità

Un solo ciclo di animazione coordina cielo e sintonia. Il cielo viene caricato in differita; resta una versione CSS se WebGL manca o il dispositivo è limitato. Il ciclo non continua a ridisegnare il cielo fuori vista; la scheda nascosta sospende il movimento. Le entrate legate allo scroll terminano mentre l'elemento entra in vista, così il testo finale rimane nitido.

Pausa e `prefers-reduced-motion` fermano animazioni e transizioni, stabilizzano l'ago e mantengono accessibili i contenuti. Con la preferenza di sistema attiva il controllo pausa è disabilitato e il canvas è nascosto. La scelta manuale di una stazione resta funzionante; lo scroll usa il comportamento immediato. Il focus non dipende da un effetto animato e la selezione non dipende solo dal colore.

## Do's and Don'ts

### Do:

- **Do** usa i colori per i ruoli registrati e conserva il fondo notturno fra le pagine.
- **Do** mantieni il testo di lettura alla misura body e i font locali della coppia approvata.
- **Do** accompagna selezione, errore e progresso con forma, testo o posizione riconoscibile.
- **Do** conserva ALTROVE intera e le icone SVG del sistema.
- **Do** verifica ogni nuova animazione con pausa, movimento ridotto, tastiera e contenuti completamente entrati in vista.
- **Do** mantieni etichette visibili e una candidatura utilizzabile senza l'arricchimento a passi.

### Don't:

- **Don't** trasformare il cielo nello sfondo protagonista di una hero generica con titolo centrale e pannelli di vetro.
- **Don't** introdurre nuove palette di accento, font display o stili di ombra per una singola sezione.
- **Don't** usare metadati piccoli come corpo del testo o segnaposto come unica etichetta.
- **Don't** rendere il movimento indispensabile per capire un contenuto o raggiungere un comando.
- **Don't** aggiungere etichette decorative sopra i titoli o icone ottenute da glifi: non appartengono al sistema implementato.
