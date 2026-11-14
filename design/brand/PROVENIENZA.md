# Provenienza degli asset pubblicati

Verifica del 14 novembre 2026. Questo file documenta fonti e trasformazioni della fase 03; i materiali di origine restano invariati.

## Immagini

| Asset pubblicato | Origine | Derivazione |
| --- | --- | --- |
| `public/assets/images/alessia-ventura-portrait-{480,800,1000}.{avif,webp}` | Fotografia fornita da Alessia Ventura, `foto/web/alessia-ventura.jpg`, 1254 × 1254 px | Crop 4:5, focale 50% 30%; ritaglio nativo 1000 × 1250; uscite 480 × 600, 800 × 1000 e 1000 × 1250. Nessuna ricostruzione o ritocco del soggetto. |
| `public/assets/images/alessia-ventura-square-{480,800,1254}.{avif,webp}` | Stessa fotografia fornita | Derivati 1:1, senza ingrandimento oltre il file originale. |
| `public/assets/og/{home,nde,medium,channeling,mentalismo,alessia,candidati,privacy,cookie,grazie,404}.jpg` | `design/brand/og-sfondo.jpg`, Higgsfield, modello **gpt_image_2_6**, 14/11/2026, job `d55e463a-5bda-45bb-a3ee-acc4abeb6953` | Crop 1200 × 630, velatura scura e testo crema ricavato dai glifi reali Tenor Sans 400. Prompt originale integrale in `design/brand/og-sfondo.prompt.txt` e nei metadati di provenienza dei derivati. |
| `public/assets/og/altrove.jpg` | Derivato `home.jpg` | Alias di compatibilità. |
| `public/assets/favicon/favicon.svg` | `design/brand/favicon.svg` | Copia del simbolo disegnato in SVG. |
| `favicon-16x16.png`, `favicon-32x32.png`, `apple-touch-icon.png`, `icon-192.png`, `icon-512.png`, `favicon.ico`, nella stessa directory | SVG precedente | Rasterizzazione 16, 32, 180, 192 e 512 px. ICO con due risoluzioni, 16 e 32 px. |

Le PNG Higgsfield originali in `design/brand/higgsfield/` non vengono copiate nella webroot. Non vengono generate nuove fotografie, persone o testimonianze. Lo sfondo generato è illustrativo e compare esclusivamente nelle immagini di condivisione autorizzate.

La build `tools/build-assets.mjs` usa Sharp, conserva l'origine in EXIF e crea sidecar JSON compatibili con `impeccable embed-prompt` (inclusi AVIF e WebP, formati per i quali la CLI usa sidecar). Le immagini JPEG e PNG ricevono inoltre la registrazione tramite la CLI durante la verifica degli asset. `public/assets/asset-provenance.json` elenca sorgenti, trasformazioni e SHA-256 degli output. Una nuova build rigenera metadati e inventario senza rete.

## Font locali

- **Tenor Sans**, Denis Masharov: peso 400, sottoinsiemi latin e latin-ext. [Fontsource](https://fontsource.org/fonts/tenor-sans), pacchetto `@fontsource/tenor-sans` 5.3.0.
- **Hanken Grotesk**, Hanken Grotesk Project Authors: pesi 400, 500, 600, sottoinsiemi latin e latin-ext, file statici WOFF2. [Fontsource](https://fontsource.org/fonts/hanken-grotesk), pacchetto `@fontsource/hanken-grotesk` 5.3.0; [progetto degli autori](https://github.com/marcologous/hanken-grotesk).
- Entrambe le famiglie sono distribuite con **SIL Open Font License 1.1**; licenze complete copiate accanto ai font in `public/assets/fonts/tenor-sans-OFL.txt` e `hanken-grotesk-OFL.txt`. Nessuna richiesta runtime a Google Fonts o CDN. Fontkit estrae i tracciati dei glifi per le OG, senza dipendere dai font installati sul server.

## Galaxy e OGL

- Shader tratto da [Galaxy.jsx nel repository ufficiale DavidHDev/react-bits](https://github.com/DavidHDev/react-bits/blob/main/src/content/Backgrounds/Galaxy/Galaxy.jsx), consultato il 14/11/2026. Autore e copyright: **David Haz, 2026**. Porting in `src/js/effects/galaxy.js`, adattatore vanilla/OGL, senza React.
- Licenza verificata: **MIT + Commons Clause License Condition v1.0**, non MIT semplice. [Testo originale](https://github.com/DavidHDev/react-bits/blob/main/LICENSE.md), conservato integralmente in `tools/licenses/react-bits.txt` e pubblicato in `public/assets/licenses/react-bits.txt`. Uso integrato nel sito Altrove; nessuna vendita o redistribuzione del componente come prodotto autonomo.
- Modifiche: palette crema/lavanda, densità e luminosità contenute, scorrimento e rotazione lenti, sfumatura inferiore, uniforme `uStatic` collegata alla sintonizzazione, parallax minimo desktop. WebGL2 richiesto dallo shader ufficiale corrente; fallback CSS del sito quando non disponibile. DPR massimo 1,5 mobile e 2 desktop. Nessun `requestAnimationFrame`, timer o listener globale interno: il controller del sito governa il rendering e le pause.
- **OGL 1.0.11**, Nathan Gordon: [repository ufficiale](https://github.com/oframe/ogl), licenza **Unlicense**, riportata integralmente dal README del pacchetto installato in `public/assets/licenses/ogl.txt`. Non è una dipendenza React.

## Dati delle puntate

Fonte: [feed Atom pubblico del canale Altrove](https://www.youtube.com/feeds/videos.xml?channel_id=UCFj4xNoHNyjg0OPDFM0BvLg). Le visualizzazioni vengono lette dall'attributo reale `media:statistics/@views`, secondo il namespace [Media RSS](https://www.rssboard.org/media-rss). Valore assente, invalido o fuori intervallo: `null`, mai un numero inventato. Lo zero verificato resta zero.

La home ordina fino a cinque elementi per visualizzazioni fra quelli disponibili nel feed corrente, escludendo i link identificati come Shorts. Non è una classifica dell'intero archivio del canale e il feed non garantisce durata o natura editoriale del video. Anche le sezioni tematiche escludono gli Shorts identificabili. I contatori sono una fotografia della cache, aggiornata ogni sei ore con backoff dopo errore. Non sono dati promozionali inventati.

Per la preview Windows, `tools/bootstrap-youtube.ps1` può inizializzare la stessa cache da una richiesta HTTPS verificata attraverso il trust store del sistema, quando PHP locale non dispone del bundle CA. Non disabilita TLS. Il wrapper PHP CLI riusa il parser di produzione; la cache e l'XML di origine restano in `app/storage/cache/`, fuori dalla webroot e ignorati da Git.
