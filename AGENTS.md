# Istruzioni per Codex

Progetto: sito di Alessia Ventura e del podcast Altrove (dominio altrove.space).

- Prima di tutto leggi `CLAUDE.md` (diario del progetto, decisioni, dati) e `PRODUCT.md` (verità di prodotto). Il compito di ogni sessione è nel prompt che ti passa l'utente, preso da `prompts/`.
- Lingua dei testi e dei commit: italiano.
- Non modificare `CLAUDE.md`, `PRODUCT.md`, `DOMANDE.md`, `meeting.md`, `prompts/`, `design/`, `inspo/`, `statistiche/`, `foto/` (salvo dove il prompt lo chiede esplicitamente). Scrivi un riepilogo di ciò che hai fatto in `codex-log.md` (aggiungi in fondo, con data).
- **Prezzi**: Essenziale 300 €, Completo 350 €, Amplifica 450 €, importi finali (dal 19/11/2026). Sul sito compaiono solo in `/proposte/`, il passaggio dopo una candidatura valida. Le altre pagine restano senza prezzi né cifre: lì le uniche frasi su costi e contributi sono i testi dell'utente elencati in `CLAUDE.md`, "Decisioni" ("Racconta la tua storia", "Partecipa ad ALTROVE Podcast", la nota sulla candidatura), da usare parola per parola.
- L'unica fonte dei prezzi è `app/lib/ParticipationPlans.php`. Se cambiano, aggiorna insieme catalogo, differenze scritte nelle schede (+50 € e +100 €), README e test, poi rigenera la brochure `output/pdf/altrove-collaborazione.pdf` con gli script in `.runtime/pdf/`.
- Mai segreti nel codice o nel repository: solo `.env` (fuori dalla webroot) e GitHub Secrets.
- Il `.env` nella radice esiste già: contiene le credenziali FTP del deploy (le stesse dei GitHub Secrets). Non sovrascriverlo e non cancellarlo; per le prove in locale aggiungi in fondo le variabili dell'app. `.gitignore` deve sempre escludere `.env` e `.env.*` (tranne `.env.example`).
- Deploy su Hostinger: la webroot è la cartella `public_html` che esiste già. Il workflow carica il contenuto di `public/` dentro `public_html/` (secret `FTP_DIR_PUBLIC`) e `app/` accanto a `public_html` (secret `FTP_DIR_APP`). Mai creare un'altra cartella pubblica né `public_html/public_html`.
- Per il lavoro visivo usa la skill `$impeccable` (in `.agents/skills/impeccable`).
- Non creare repository remoti, non fare push e non pubblicare nulla: lo fa l'utente.
