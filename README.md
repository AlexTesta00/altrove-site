# Altrove · fondamenta del sito

Sito PHP di Alessia Ventura e Altrove Podcast, destinato ad **altrove.space**. Repository esistente: [AlexTesta00/altrove-site](https://github.com/AlexTesta00/altrove-site). Questa consegna prepara i file e il deploy: non esegue pubblicazioni.

La decisione aggiornata dell’utente sostituisce l’invio email del prompt iniziale: il modulo prepara un messaggio per **WhatsApp**, indirizzato al numero configurato in ambiente. Chi si candida lo rilegge e preme **Invia dentro WhatsApp**. Il sito non può attestare la consegna del messaggio. Non usa SMTP, PHPMailer, servizi di invio automatico o database.

La veste è volutamente neutra, scura e leggibile. Il design approvato “Linea aperta”, i font Tenor Sans/Hanken Grotesk e lo sfondo animato appartengono al prompt 03. Il form vive su /candidati/; le altre pagine hanno la sezione di invito con i testi approvati, senza ripetere il modulo.

## Avvio locale

Servono PHP 8.2+ (hosting previsto PHP 8.3), Node 24 LTS e npm. Estensioni PHP: curl, intl, mbstring, openssl, sodium, SimpleXML, session. Non serve Composer.

1. Esegui npm ci e npm run build.
2. Esegui php tools/configura-locale.php. Crea .env fuori da public/ con segreti casuali locali e numero WhatsApp approvato. Se .env esiste, lo script si ferma: integra le variabili mancanti a mano, senza sostituire credenziali già presenti.
3. Avvia php -S localhost:8000 -t public.
4. Apri http://localhost:8000. Il passaggio a WhatsApp non invia messaggi da solo.

Su questo computer PHP è presente senza un php.ini globale: è stato predisposto .runtime/php.ini, ignorato da Git, con le estensioni installate. Qui i comandi PHP diventano php -c .runtime/php.ini …, compreso il server. Su altre macchine usa il tuo php.ini e verifica con php -m.

APP_ENV=local è ammesso solo con APP_URL che punta a localhost/127.0.0.1. In locale il cookie si chiama altrove_locale e non impone HTTPS. In produzione: cookie __Host-altrove, Secure, HttpOnly, SameSite=Lax, host senza www e HTTPS. Non riutilizzare i segreti locali sul server.

## Struttura

- public/: unica webroot; router, endpoint, asset compilati, robots e manifest.
- app/: configurazione, librerie, contenuti in italiano, template, pulizia e storage privato.
- src/: CSS Tailwind v4 e moduli JavaScript; gli effetti hanno un punto di estensione inattivo.
- tools/: build immagini/CSS/JS e utilità locali per le chiavi; mai da pubblicare.
- tests/: test unitari PHP e test HTTP senza dipendenze di test.
- analisi/: ricerca e proposta commerciale privata; mai nella webroot.
- .github/workflows/deploy.yml: verifica e distribuzione incrementale tramite FTPS.

La build produce CSS e JavaScript minificati con hash e manifest PHP, immagini AVIF/WebP responsive, JPEG di riserva, favicon ICO/SVG/PNG e OG 1200×630. Le derivazioni usano foto/web/alessia-ventura.jpg, design/brand/favicon.svg e design/brand/og-sfondo.jpg senza modificare gli originali. L’OG è una composizione provvisoria comune alle pagine, da affinare nel prompt 03. In questa fase i font sono di sistema, quindi nessuna richiesta a Google Fonts o preload inutile.

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

Ordine dei controlli: metodo POST; Content-Type URL-encoded; corpo entro 16 KB; Origin o Referer confrontato con APP_URL (schema, host, porta); token CSRF di sessione; honeypot; timestamp HMAC tra tre secondi e due ore; limite IP; Turnstile se configurato; validazione e normalizzazione Unicode; preparazione messaggio.

Il limite è **tre tentativi ogni dieci minuti e dieci nelle ventiquattro ore**, con blocco dal quarto tentativo ravvicinato: quindi anche il sesto è bloccato. Conta anche i tentativi arrivati alla validazione che non superano i campi obbligatori. Il file è bloccato con flock; l’IP diretto REMOTE_ADDR viene pseudonimizzato con HMAC. Il codice non si fida di X-Forwarded-For arbitrari: se si mette un proxy davanti al sito, configurare correttamente gli IP reali a livello di server, senza accettare header non verificati.

Il limite di 4.000 caratteri della storia convive con il limite di 16 KB del corpo HTTP: testi molto ricchi di caratteri multibyte possono raggiungere prima il limite in byte del modulo URL-encoded.

Con JavaScript, cinque passi progressivi, errori per campo e regione live; dopo la validazione si apre il collegamento WhatsApp con il testo precompilato. Senza JavaScript, tutti i campi sono visibili e il POST risponde 303 a /grazie/, dove si può rileggere il testo e premere il collegamento WhatsApp. Nessun dato personale viene messo negli URL interni. Il collegamento **esterno** wa.me contiene necessariamente il messaggio e lo comunica a WhatsApp quando viene aperto: questo è spiegato prima dell’azione e in privacy.

Turnstile richiede JavaScript: se attivato, un visitatore senza JavaScript deve usare il contatto WhatsApp diretto. Le chiavi sono entrambe necessarie. La risposta viene verificata lato server, inclusi hostname e action=candidatura. Non sono installate chiavi di test nel repository.

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

La cache dura sei ore. Su guasto si conserva l’ultima cache, con timeout breve e backoff per evitare ritardi a ogni visita; senza cache la sezione non compare. Il parsing XML disabilita la rete e rifiuta DTD/entità. Il filtro tematico usa soltanto parole dei titoli. Miniature da i.ytimg.com; il player youtube-nocookie viene creato solo al clic. Senza JavaScript il collegamento apre la puntata su YouTube.

Nel runtime PHP locale la catena dei certificati CA non è configurata: il collegamento live cURL è fallito in modo sicuro, senza disattivare TLS. Verificare il bundle CA sul PHP di Hostinger; il feed ufficiale è stato verificato separatamente dal coordinatore.

Umami è disattivato di default. Gli eventi ammessi sono form_inizio, form_inviato, whatsapp_click e puntata_play. Il nome storico form_inviato misura la **preparazione**, non la consegna su WhatsApp. Gli eventi non contengono dati della candidatura. Per le statistiche senza banner va verificata la configurazione concreta del servizio e l’eventuale fornitore.

## Deploy FTP su Hostinger

Nessun deploy è stato eseguito. L’utente gestisce dominio, piano, SSL, credenziali e primo push. Il workflow parte su push a main oppure manualmente, con concorrenza serializzata.

Prima pubblicazione:

1. Collega altrove.space al piano e abilita HTTPS, poi seleziona PHP 8.3 con le estensioni indicate.
2. In hPanel consulta File Manager e FTP Accounts: identifica la directory fisica public_html del dominio e verifica il percorso **visto dall’account FTP**, che può differire dal percorso fisico.
3. Predisponi app/ come sorella di public_html/. Il router cerca ../app/. La directory FTP privata deve quindi corrispondere davvero a quel percorso.
4. Carica manualmente .env nella cartella che contiene app/ e public_html/, con APP_ENV=production, APP_URL corretto, segreti nuovi e WHATSAPP_NUMBER. Non inserire il .env locale del computer.
5. Crea i GitHub Secrets FTP_SERVER, FTP_USERNAME, FTP_PASSWORD, FTP_DIR_PUBLIC, FTP_DIR_APP nel repository e l’ambiente production. I percorsi devono finire con / ed essere dedicati al sito, mai la radice dell’account.
6. Il workflow compila, controlla PHP, esegue i test, poi sincronizza app/ e public/ separatamente tramite FTPS esplicito sulla porta 21, con certificato verificato (security: strict).
7. La sincronizzazione mantiene due file di stato separati e trasferisce/elimina soltanto differenze del codice. Non eliminare questi file senza capire l’effetto sulla successiva sincronizzazione.
8. Completa il cron di conservazione, controlla permessi e prova da telefono il passaggio reale a WhatsApp senza usare dati sensibili di prova.

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
- Impeccable detect sui sorgenti CSS/template/JS: nessun rilievo meccanico. Audit renderizzato su otto route a 390 e 1440 px: zero violazioni A/AA rilevate da axe, nessun overflow, flusso browser con e senza JavaScript riuscito.
- Lighthouse mobile dopo la correzione del ritratto LCP: Performance 100, Accessibility 100, Best Practices 81, SEO 100, LCP 1,4 s, CLS 0. Il test esclude soltanto richieste estranee iniettate da Kaspersky; Best Practices risente ancora delle richieste HTTP dell’antivirus. Nessuna modifica al software di protezione. Ripetere su HTTPS in produzione.

Restano da verificare sul dominio reale: redirect HTTPS/www e slash via LiteSpeed, cookie Secure/HSTS, permessi FTP, protezione _app se usata, certificato FTPS, configurazione Turnstile/Umami eventuali, cron, Core Web Vitals con rete reale e apertura WhatsApp sui dispositivi del pubblico. Le bozze privacy/cookie richiedono revisione professionale delle basi giuridiche, fornitori, trasferimenti e tempi organizzativi effettivi. Il footer e la privacy includono la partita IVA approvata.

Il nome di Alessia è accompagnato dalla descrizione del suo lavoro e da profili sameAs; l’omonima non viene nominata. JSON-LD FAQPage descrive solo domande effettivamente visibili: la validità strutturale non garantisce risultati avanzati nei motori di ricerca.

## Fonti tecniche consultate

- [Verifica Turnstile lato server](https://developers.cloudflare.com/turnstile/get-started/server-side-validation/).
- [FTP Deploy Action: configurazione e limiti](https://github.com/SamKirkland/FTP-Deploy-Action).
- [Garante: principi del trattamento](https://www.garanteprivacy.it/web/guest/home/principi-fondamentali-del-trattamento).
- [Garante: diritti dell’interessato](https://garanteprivacy.it/home/i-miei-diritti/diritti).
- [Hostinger: accordo sul trattamento](https://www.hostinger.com/legal/dpa).

Consultazione: 14 novembre 2026. I contratti e le condizioni applicabili vanno ricontrollati con la configurazione di produzione.

