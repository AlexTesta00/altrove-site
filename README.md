# Altrove · Linea aperta

Sito PHP di Alessia Ventura e Altrove Podcast, destinato ad **altrove.space**. Repository: [AlexTesta00/altrove-site](https://github.com/AlexTesta00/altrove-site). Il workflow verifica il codice e distribuisce il sito su Hostinger a ogni push a main.

La decisione aggiornata dell’utente sostituisce l’invio email del prompt iniziale: il modulo prepara un messaggio per **WhatsApp**, indirizzato al numero configurato in ambiente. Chi si candida lo rilegge e preme **Invia dentro WhatsApp**. Il sito non può attestare la consegna del messaggio. Non usa SMTP, PHPMailer, servizi di invio automatico o database.

Il design approvato “Linea aperta” usa Tenor Sans/Hanken Grotesk, un cielo animato e un sintonizzatore sui quattro temi. Il form vive su /candidati/; le altre pagine hanno la sezione di invito con i testi approvati, senza ripetere il modulo. I prezzi delle tre proposte approvate sono visibili soltanto nel passaggio successivo a una candidatura validata, su /proposte/, escluso dalla sitemap e non indicizzabile.

## Avvio locale

Servono PHP 8.2+ (hosting previsto PHP 8.3), Node 24 LTS e npm. Estensioni PHP: curl, intl, mbstring, openssl, sodium, SimpleXML, session. Non serve Composer.

1. Esegui npm ci e npm run build.
2. Esegui php tools/configura-locale.php. Crea .env fuori da public/ con segreti casuali locali e numero WhatsApp approvato. Se .env esiste, lo script si ferma: integra le variabili mancanti a mano, senza sostituire credenziali già presenti.
3. Avvia php -S localhost:8000 -t public tools/serve.php. Il router esplicito gestisce la sitemap anche sul server PHP di sviluppo Linux; gli asset statici restano serviti direttamente.
4. Apri http://localhost:8000. Il passaggio a WhatsApp non invia messaggi da solo.

Su questo computer PHP è presente senza un php.ini globale: è stato predisposto .runtime/php.ini, ignorato da Git, con le estensioni installate. Qui i comandi PHP diventano php -c .runtime/php.ini …, compreso il server. Su altre macchine usa il tuo php.ini e verifica con php -m.

APP_ENV=local è ammesso solo con APP_URL che punta a localhost/127.0.0.1. In locale il cookie si chiama altrove_locale e non impone HTTPS. In produzione: cookie __Host-altrove, Secure, HttpOnly, SameSite=Lax, host senza www e HTTPS. Non riutilizzare i segreti locali sul server.

## Struttura

- public/: unica webroot; router, endpoint, asset compilati, robots e manifest.
- app/: configurazione, librerie, contenuti in italiano, template, pulizia e storage privato.
- src/: CSS Tailwind v4 e moduli JavaScript per form, sintonia e movimento.
- tools/: build immagini/CSS/JS e utilità locali per le chiavi; mai da pubblicare.
- tests/: test unitari PHP e test HTTP senza dipendenze di test.
- analisi/: ricerca e proposta commerciale privata; mai nella webroot.
- .github/workflows/deploy.yml: verifica e distribuzione incrementale tramite FTPS.

La build produce CSS e JavaScript minificati con hash e manifest PHP, immagini AVIF/WebP responsive, favicon ICO/SVG/PNG e OG 1200×630 specifiche per pagina. Le derivazioni usano foto/web/alessia-ventura.jpg, design/brand/favicon.svg e design/brand/og-sfondo.jpg senza modificare gli originali. Font WOFF2 e relative licenze sono ospitati localmente; non vengono richiesti font a Google.

## Variabili di ambiente

Il file reale .env resta **fuori dalla webroot** e non viene mai distribuito dalle Actions.

| Variabile | Uso |
| --- | --- |
| APP_ENV | production sul server, local solo in locale |
| APP_URL | https://altrove.space in produzione |
| APP_SECRET | stringa casuale di almeno 32 caratteri, distinta dalle altre |
| IP_SALT | altro segreto casuale di almeno 32 caratteri per pseudonimizzare gli IP |
| WHATSAPP_NUMBER | 393272433925, numero approvato, senza segno + |
| TURNSTILE_SITE_KEY / TURNSTILE_SECRET | entrambe vuote per disattivarlo, entrambe valorizzate per attivarlo |
| BACKUP_PUBLIC_KEY | facoltativa: chiave pubblica base64 per copia cifrata |
| YOUTUBE_CHANNEL_ID | UCFj4xNoHNyjg0OPDFM0BvLg, verificato sul canale ufficiale |
| UMAMI_WEBSITE_ID / UMAMI_SRC | facoltative; UUID e URL HTTPS dello script del proprio servizio |
| TITOLARE_NOME | Alessia Ventura |
| TITOLARE_PIVA | 07446010485 |
| TITOLARE_EMAIL | eventuale recapito privacy aggiuntivo; non attiva invii email |

Il numero e la partita IVA sono dati pubblici, non credenziali. I token e i segreti non vanno nel codice. Il caricatore legge valori con o senza virgolette, senza interpolare variabili né eseguire codice. Le variabili impostate dal server hanno precedenza sul file.

## Candidatura e sicurezza

Ordine dei controlli: metodo POST; Content-Type URL-encoded; corpo entro 64 KB; Origin o Referer confrontato con APP_URL (schema, host, porta); token CSRF di sessione; honeypot; timestamp HMAC tra tre secondi e due ore; limite IP; Turnstile se configurato; validazione e normalizzazione Unicode; preparazione messaggio.

Il limite è **tre tentativi ogni dieci minuti e dieci nelle ventiquattro ore**, con blocco dal quarto tentativo ravvicinato: quindi anche il sesto è bloccato. Conta anche i tentativi arrivati alla validazione che non superano i campi obbligatori. Il file è bloccato con flock; l’IP diretto REMOTE_ADDR viene pseudonimizzato con HMAC. Il codice non si fida di X-Forwarded-For arbitrari: se si mette un proxy davanti al sito, configurare correttamente gli IP reali a livello di server, senza accettare header non verificati.

La storia ammette 4.000 codepoint Unicode; il limite di 64 KB del corpo comprende l’espansione URL-encoded, anche per caratteri multibyte ed emoji.

Con JavaScript, cinque passi progressivi, errori per campo e regione live. Dopo la validazione, /proposte/ presenta Essenziale (200 euro: YouTube e 1 Reel), Completo (250 euro: YouTube, Spotify e 1 Reel) e Amplifica (350 euro: YouTube, Spotify e 3 Reel), importi finali. Nessun piano è preselezionato. Il POST /api/piano accetta solo gli identificativi del catalogo server e verifica sessione, origine, CSRF e identificativo della candidatura; importi o contenuti inviati dal browser sono ignorati. Si passa quindi a /grazie/ per rileggere risposte e piano prima di aprire WhatsApp. Senza JavaScript il flusso usa moduli nativi e redirect 303. Si può tornare alle risposte o cambiare il piano; la scelta non effettua pagamenti né garantisce la partecipazione. I dati e l’anteprima scadono quindici minuti dopo la validazione iniziale, senza prolungare il termine cambiando piano. La copia cifrata facoltativa viene creata solo dopo la scelta del piano. Nessun dato personale viene messo negli URL interni. Il collegamento **esterno** wa.me contiene necessariamente il messaggio e lo comunica a WhatsApp quando viene aperto: questo è spiegato prima dell’azione e in privacy.

Turnstile richiede JavaScript: se attivato, il form informa il visitatore che deve abilitarlo per proseguire. Le chiavi sono entrambe necessarie. La risposta viene verificata lato server, inclusi hostname e action=candidatura. Non sono installate chiavi di test nel repository.

Il messaggio preparato è disponibile per quindici minuti; lo stato di sessione resta sul server fino alla scadenza tecnica massima di due ore e alla pulizia periodica. La sessione non è una conferma di consegna. Il consenso ha versione dell’informativa e data nella copia opzionale e nel testo preparato; una futura pubblicazione della storia richiede un’autorizzazione separata.

Il server imposta CSP con nonce, HSTS in produzione, nosniff, Referrer-Policy, Permissions-Policy e COOP. Il contenuto dinamico è escapato; le pagine con token o messaggi sono no-store. Display e log grezzi degli errori PHP sono disattivati: i log applicativi contengono solo data e codici evento. Non impostare il logging di body, cookie o query esterne contenenti candidature su reverse proxy o strumenti di monitoraggio.

## Copie cifrate facoltative e conservazione

Per il flusso WhatsApp non è obbligatoria una copia sul server. Con BACKUP_PUBLIC_KEY vuota non vengono creati backup persistenti. Se vuoi un recupero di emergenza:

1. Crea una cartella privata locale, fuori dalla webroot e preferibilmente fuori dal repository.
2. Esegui php tools/genera-chiavi.php PERCORSO_CARTELLA_PRIVATA.
3. Conserva altrove-private.key soltanto sul tuo computer, con una copia sicura separata. Il file contiene la coppia di chiavi e non deve mai arrivare sul server.
4. Copia solo il valore BACKUP_PUBLIC_KEY stampato nel .env del server.
5. Le candidature preparate vengono cifrate con sodium_crypto_box_seal in app/storage/candidature/*.seal. Non dimostrano che il visitatore abbia inviato il messaggio.
6. Per leggerle, scarica i .seal via FTPS e usa in locale php tools/leggi-candidature.php CHIAVE_PRIVATA FILE.seal OUTPUT.json.

L’output decifrato contiene dati sensibili: conservalo solo per il tempo necessario e non metterlo in Git. La copia è facoltativa e “best effort”: se fallisce, la procedura WhatsApp resta disponibile, con un evento tecnico senza dati personali nel log.

La pulizia avviene durante il salvataggio delle copie e deve essere assicurata anche senza nuove visite. Configura in hPanel un cron giornaliero, con il percorso assoluto PHP fornito dal piano:

php /home/UTENTE/domains/altrove.space/app/retention.php

Lo script è eseguibile solo da CLI e rimuove copie oltre novanta giorni, record IP oltre due giorni, sessioni oltre due ore e log applicativi oltre trenta giorni. Nel ripiego _app cambia il percorso. La rimozione delle conversazioni WhatsApp oltre dodici mesi dall’ultimo contatto è una procedura organizzativa di Alessia; il sito non può cancellare le chat dall’app. Adeguare anche i backup dell’hosting ai termini approvati.

## Feed YouTube e servizi esterni

Il canale [Altrove Podcast](https://www.youtube.com/channel/UCFj4xNoHNyjg0OPDFM0BvLg) è stato verificato il 14 novembre 2026. Il feed pubblico espone le ultime pubblicazioni e può includere Shorts; non è un archivio completo né fornisce la durata di ogni video.

La cache dura sei ore. Su guasto si conserva l’ultima cache, con timeout breve e backoff per evitare ritardi a ogni visita. Il parsing XML disabilita la rete e rifiuta DTD/entità. Il filtro tematico usa soltanto parole dei titoli. Gli elementi identificati dal feed come Shorts vengono esclusi dalle puntate. La home mostra fino a cinque video ordinati per le visualizzazioni effettive del feed; un dato assente rimane sconosciuto. Questa selezione non rappresenta l’intero archivio del canale. Miniature da i.ytimg.com; il player youtube-nocookie viene creato solo al clic. Senza JavaScript il collegamento apre la puntata su YouTube.

Nel runtime PHP locale la catena dei certificati CA non è configurata: il collegamento live cURL è fallito in modo sicuro, senza disattivare TLS. La cache di anteprima è stata inizializzata dal feed ufficiale con `tools/bootstrap-youtube.ps1`, che usa il trust store del sistema operativo. Lo script CLI PHP valida XML e ID del canale. Verificare il bundle CA sul PHP di Hostinger.

## Design Linea aperta

Tutte le pagine condividono il fondo notte, Tenor Sans e Hanken Grotesk ospitati localmente, i controlli a capsula e la scala del sintonizzatore. La home riprende il prototipo approvato: quattro temi, puntate YouTube, Alessia, Instagram, percorso e candidatura. Il numero complessivo di visualizzazioni non è pubblicato in assenza di conferma.

Il sintonizzatore risponde a trascinamento e tastiera, con molla e scansione automatica. Galaxy deriva dal componente ufficiale React Bits, con OGL, import differito e un solo ciclo `requestAnimationFrame` condiviso. La navigazione offre una pausa esplicita; `prefers-reduced-motion` usa il cielo statico. La licenza React Bits comprende la Commons Clause; i testi completi di licenza sono inclusi dalla build.

La build prepara i font latin/latin-ext, le fotografie in 4:5 e 1:1 AVIF/WebP, un’immagine OG per pagina e favicon 16/32/180/192/512 con ICO. Fonti e trasformazioni sono documentate in `design/brand/PROVENIENZA.md` e nei metadati dei derivati. Gli output in `public/assets` sono ricreabili e vengono generati anche da GitHub Actions.

Il messaggio WhatsApp contiene la storia completa. Oltre la soglia prudenziale di lunghezza del collegamento, la pagina finale propone copia e incolla, mantenendo l’intero testo. Il limite è quattromila codepoint Unicode; il body HTTP ammette l’espansione dovuta alla codifica del modulo. Non esistono link WhatsApp nelle altre pagine.

Umami è disattivato di default. Gli eventi ammessi sono form_inizio, form_inviato, whatsapp_click e puntata_play. Il nome storico form_inviato misura la **validazione del modulo e il passaggio ai piani**, non la consegna su WhatsApp. Gli eventi non contengono dati della candidatura. Per le statistiche senza banner va verificata la configurazione concreta del servizio e l’eventuale fornitore.

## Deploy FTP su Hostinger

Il workflow parte su push a main oppure manualmente, con concorrenza serializzata. Il collegamento FTPS è stato verificato con certificato valido sul nome server del piano; usare il nome DNS nei Secrets, non un IP che non corrisponde al certificato.

L’account FTP apre inizialmente `/public_html`: i Secrets dei percorsi usano quindi valori assoluti, `FTP_DIR_PUBLIC=/public_html/` e `FTP_DIR_APP=/app/`, per evitare cartelle annidate. La configurazione applicativa di produzione è distinta da quella locale e risiede in `/.env`, fuori dalla webroot, con permessi 600. Le Actions non la sovrascrivono.

Prima pubblicazione:

1. Collega altrove.space al piano e abilita HTTPS, poi seleziona PHP 8.3 con le estensioni indicate.
2. In hPanel consulta File Manager e FTP Accounts: identifica la directory fisica public_html del dominio e verifica il percorso **visto dall’account FTP**, che può differire dal percorso fisico.
3. Predisponi app/ come sorella di public_html/. Il router cerca ../app/. La directory FTP privata deve quindi corrispondere davvero a quel percorso.
4. Carica manualmente .env nella cartella che contiene app/ e public_html/, con APP_ENV=production, APP_URL corretto, segreti nuovi e WHATSAPP_NUMBER. Non inserire il .env locale del computer.
5. Crea i GitHub Secrets FTP_SERVER, FTP_USERNAME, FTP_PASSWORD, FTP_DIR_PUBLIC, FTP_DIR_APP nel repository e l’ambiente production. I percorsi devono finire con / ed essere dedicati al sito, mai la radice dell’account.
6. Il workflow compila, controlla PHP, esegue i test, poi sincronizza app/ e public/ separatamente tramite FTPS esplicito sulla porta 21, con certificato verificato (security: strict).
7. La sincronizzazione mantiene due file di stato separati e trasferisce/elimina soltanto differenze del codice. Non eliminare questi file senza capire l’effetto sulla successiva sincronizzazione.
8. Completa il cron di conservazione, controlla permessi e prova da telefono il passaggio reale a WhatsApp senza usare dati sensibili di prova.

Sul primo deploy di questo piano Hostinger il download del file di stato inesistente ha chiuso la connessione dati (`ECONNRESET`). Prima della prima sincronizzazione sono stati inizializzati `.ftp-deploy-sync-state-app.json` in `/app/` e `.ftp-deploy-sync-state-public.json` in `/public_html/`, con `version: "1.0.0"`, `generatedTime` in millisecondi e `data: []`. Operazione eseguita solo dopo aver verificato che non esistessero altri file applicativi o stati precedenti; poi il workflow è riuscito. Non azzerare stati di un sito già distribuito. [Segnalazione nel progetto FTP Deploy Action](https://github.com/SamKirkland/FTP-Deploy-Action/issues/489).

app/storage viene creato dal bootstrap se mancante. Il processo PHP deve poter scriverci; usare i permessi minimi compatibili col piano (directory private 700/750, file 600/640). Storage e .env sono esclusi dal deploy e non devono essere rimossi nemmeno da operazioni manuali di “pulizia”. Il deploy distribuisce prima app/ e poi public/: è incrementale, non atomico; in presenza di utenti reali programmare aggiornamenti incompatibili o usare una finestra di manutenzione.

Se l’FTP non consente di uscire dalla webroot, usa public_html/_app/ come FTP_DIR_APP. app/.htaccess vieta l’accesso diretto e public/.htaccess blocca _app/; verifica realmente una richiesta a /_app/config.php, che deve essere negata. Il sincronizzatore public esclude _app/** per non toccarla. **Il .env rimane comunque sopra public_html**: va creato col File Manager di Hostinger, non dentro _app. Se il piano non permette neppure questo, non procedere con tale layout.

Nessuna analisi, documento commerciale, file tools/, sorgente src/, node_modules, .git o chiave privata deve essere caricata nella webroot.

### Variante SSH, se disponibile

Hostinger usa normalmente la porta SSH 65002 per i piani che la includono; verifica i valori mostrati dal tuo piano. Esegui dopo la build, sostituendo utente, host e directory:

rsync -rlzc --delete-after --exclude='storage/' --exclude='.env*' --exclude='.git*' -e 'ssh -p 65002' app/ UTENTE@HOST:/PERCORSO/app/

rsync -rlzc --delete-after --exclude='_app/' --exclude='.env*' --exclude='.git*' -e 'ssh -p 65002' public/ UTENTE@HOST:/PERCORSO/public_html/

Non aggiungere --delete-excluded. Verifica le directory risolte e fai prima una prova con --dry-run. Le chiavi SSH appartengono al computer o ai GitHub Secrets, mai al repository.

## Verifiche e limiti

- npm run build: Tailwind, esbuild, immagini e manifest.
- php tests/unit.php: normalizzazione NFC/E.164, input array ostili, CSRF/HMAC, limiti temporali/IP, cifratura, decifratura e retention.
- npm test: avvia un server PHP temporaneo isolando i segreti di prova; controlla tutte le pagine, H1, JSON-LD, sitemap, header, 404/301, controlli endpoint, invio con JavaScript simulato via fetch, POST senza JavaScript e rate limit. Non apre e non invia WhatsApp.
- Il test HTTP usa localhost: in ambienti con sandbox di rete può richiedere autorizzazione all’esecuzione locale.
- Controllo sintattico su tutti i PHP; npm audit senza vulnerabilità al momento della build.
- 32 controlli PHP e 9 test HTTP superati. Impeccable detect: zero rilievi primari e dieci avvisi sulla molla, richiesta dal prototipo approvato. Audit renderizzato su undici route a 390 e 1440 px: zero violazioni A/AA rilevate da axe e nessun overflow.
- Browser: cinque passi, tastiera, conservazione dei valori tornando indietro, fallback senza JavaScript e storia lunga integra verificati senza inviare messaggi. Movimento: un solo ciclo requestAnimationFrame, pausa esplicita, arresto fuori viewport, riduzione del movimento e shader Galaxy verificati.
- Lighthouse mobile locale sul design Linea aperta: Performance 96, Accessibility 96, Best Practices 81, SEO 100, LCP 2,6 s, CLS 0,001. Il contrasto segnalato riguarda elementi fuori viewport durante l’ingresso animato; verificati leggibili una volta entrati e con riduzione del movimento. Il successivo controllo mirato dei nomi accessibili YouTube è passato dopo la correzione. Il test esclude richieste estranee iniettate da Kaspersky; Best Practices risente ancora delle richieste HTTP dell’antivirus. Nessuna modifica al software di protezione. Sono misure di laboratorio locali, non Core Web Vitals reali.

Deploy e verifica del dominio completati il 14 novembre 2026: undici route con stato HTTP corretto, immagini e cinque puntate reali caricate, redirect HTTPS/www/slash, HSTS/CSP e cookie Secure/HttpOnly/SameSite, percorsi privati negati. Form provato con dati sintetici e richiesta esterna WhatsApp intercettata prima dell’invio; eliminata la sessione di prova. La pagina iniziale Hostinger è stata conservata fuori dalla webroot. Non è stato usato il ripiego `_app`.

Lighthouse mobile su HTTPS: Performance 96, Accessibility 96, Best Practices 100, SEO 100; LCP 2,5 s, CLS 0,001 e TBT 10 ms. L’unico controllo binario non superato riguarda il contrasto durante l’ingresso degli elementi fuori viewport, già verificati dal revisore una volta visibili. Sono misure di laboratorio, non dati di utilizzo reali.

Il 15 novembre 2026 le informative privacy/cookie sono state riviste sulle fonti ufficiali e confrontate con codice, cookie e configurazione di produzione. Il titolare è contattabile per i diritti senza candidarsi. L’utente ha confermato dodici mesi dall’ultimo contatto per le candidature WhatsApp: l’eliminazione delle chat e degli allegati resta una procedura di Alessia. Il modulo e il messaggio riportano lo stesso consenso esplicito e la versione dell’informativa. Il lettore YouTube richiede una scelta informata separata ed è disattivabile dal footer; la scelta vive soltanto nella pagina aperta. Le miniature da i.ytimg.com continuano a generare richieste esterne, dichiarate nelle informative.

Resta da configurare il cron di conservazione in hPanel: la scadenza logica della sessione non garantisce la cancellazione fisica a un orario preciso. Accordi e accessi dei soggetti che gestiscono i dati, log e backup Hostinger e procedure effettive vanno mantenuti coerenti con l’informativa; la revisione tecnica ed editoriale non è una certificazione legale. Turnstile, Umami e copie persistenti sono disattivati. L’apertura nell’app WhatsApp sui telefoni del pubblico e i Core Web Vitals reali vanno verificati nell’uso. Il footer e la privacy includono la partita IVA approvata. Il credito Alex Testa usa una foto fornita direttamente dall’utente, con crop e formati ottimizzati ospitati sul sito; non carica widget Instagram.

Il nome di Alessia è accompagnato dalla descrizione del suo lavoro e da profili sameAs; l’omonima non viene nominata. JSON-LD FAQPage descrive solo domande effettivamente visibili: la validità strutturale non garantisce risultati avanzati nei motori di ricerca.

## Fonti tecniche consultate

- [Verifica Turnstile lato server](https://developers.cloudflare.com/turnstile/get-started/server-side-validation/).
- [FTP Deploy Action: configurazione e limiti](https://github.com/SamKirkland/FTP-Deploy-Action).
- [Garante: principi del trattamento](https://www.garanteprivacy.it/web/guest/home/principi-fondamentali-del-trattamento).
- [Garante: diritti dell’interessato](https://garanteprivacy.it/home/i-miei-diritti/diritti).
- [Hostinger: accordo sul trattamento](https://www.hostinger.com/legal/dpa).

Consultazione: 14 novembre 2026. I contratti e le condizioni applicabili vanno ricontrollati con la configurazione di produzione.

