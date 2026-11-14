import { track } from './analytics.js';
export function initForm() {
  const form = document.querySelector('[data-candidatura]');
  if (!form || !window.fetch) return;
  const steps = [...form.querySelectorAll('[data-step]')];
  const controls = form.querySelector('[data-step-controls]');
  const previous = form.querySelector('[data-back]');
  const next = form.querySelector('[data-next]');
  const status = form.querySelector('[data-step-status]');
  const summary = form.querySelector('#errori-modulo');
  const submit = form.querySelector('[data-submit]');
  const sending = form.querySelector('[data-send-status]');
  let current = 0;
  let busy = false;
  let prepared = false;
  let started = false;
  form.noValidate = true;
  controls.hidden = false;
  status.hidden = false;
  function show(index, focus = true) {
    current = index;
    steps.forEach((step, i) => { step.hidden = i !== current; });
    previous.hidden = current === 0;
    next.hidden = current === steps.length - 1;
    status.textContent = 'Passo ' + (current + 1) + ' di ' + steps.length;
    if (focus) steps[current].querySelector('legend').focus();
  }
  function setError(name, message) {
    const error = form.querySelector('[data-error="' + name + '"]');
    if (error) error.textContent = message;
    form.querySelectorAll('[name="' + name + '"]').forEach(input => {
      if (message) input.setAttribute('aria-invalid', 'true');
      else input.removeAttribute('aria-invalid');
    });
  }
  function validate(step) {
    const fields = [...step.querySelectorAll('input:not([type=hidden]),select,textarea')];
    let first = null;
    const done = new Set();
    fields.forEach(field => {
      if (done.has(field.name)) return;
      done.add(field.name);
      let message = '';
      if (field.name === 'veste' && !form.querySelector('[name=veste]:checked')) message = 'Scegli in che veste vuoi partecipare.';
      else if (field.name === 'consenso' && !field.checked) message = 'Leggi l’informativa e dai il tuo consenso per continuare.';
      else if (field.name === 'esperienza' && ([...field.value.trim()].length < 30 || [...field.value].length > 4000)) message = 'Scrivi da trenta a quattromila caratteri.';
      else if (['nome','cognome'].includes(field.name) && !/^[\p{L}\p{M}][\p{L}\p{M} '’\-]{1,59}$/u.test(field.value.trim())) message = 'Scrivi almeno due lettere, usando solo lettere, spazi, apostrofi o trattini.';
      else if (field.name === 'telefono' && !/^\+?[0-9().\s-]{8,30}$/.test(field.value.trim())) message = 'Scrivi un numero di telefono valido.';
      else if (field.required && !field.checkValidity()) message = 'Completa questo campo per continuare.';
      setError(field.name, message);
      if (message && !first) first = field;
    });
    if (first) first.focus();
    return !first;
  }
  previous.addEventListener('click', () => show(Math.max(0, current - 1)));
  next.addEventListener('click', () => { if (validate(steps[current])) show(Math.min(steps.length - 1, current + 1)); });
  form.addEventListener('input', () => { if (!started) { started = true; track('form_inizio'); } });
  const story = form.querySelector('[name=esperienza]');
  story.addEventListener('input', () => { form.querySelector('[data-counter]').textContent = [...story.value].length.toLocaleString('it-IT') + ' / 4.000 caratteri'; });
  function showErrors(errors) {
    summary.hidden = false;
    summary.querySelector('p').textContent = errors.form || 'Controlla i campi indicati e riprova.';
    const list = summary.querySelector('ul');
    list.replaceChildren();
    let targetStep = null;
    for (const [name, message] of Object.entries(errors)) {
      if (name === 'form') continue;
      const field = form.elements.namedItem(name);
      setError(name, message);
      const li = document.createElement('li');
      li.textContent = message;
      list.append(li);
      const element = field instanceof RadioNodeList ? field[0] : field;
      if (targetStep === null && element) targetStep = steps.indexOf(element.closest('[data-step]'));
    }
    if (targetStep !== null && targetStep >= 0) show(targetStep, false);
    summary.focus();
  }
  form.addEventListener('submit', async event => {
    event.preventDefault();
    if (busy || prepared) return;
    if (current < steps.length - 1) { if (validate(steps[current])) show(current + 1); return; }
    for (let i = 0; i < steps.length; i++) {
      if (!validate(steps[i])) { show(i, false); steps[i].querySelector('[aria-invalid=true]')?.focus(); return; }
    }
    busy = true;
    submit.disabled = true;
    previous.disabled = true;
    summary.hidden = true;
    sending.textContent = 'Sto preparando il messaggio per WhatsApp…';
    try {
      const response = await fetch(form.action, {
        method: 'POST',
        body: new URLSearchParams(new FormData(form)),
        headers: { Accept: 'application/json' },
        credentials: 'same-origin',
        signal: AbortSignal.timeout(25000)
      });
      const result = await response.json();
      if (!response.ok || !result.ok) {
        showErrors(result.errors || { form: 'Non riesco a preparare il messaggio. Riprova tra poco.' });
        window.turnstile?.reset();
        sending.textContent = '';
      } else {
        prepared = true;
        track('form_inviato'); // Evento storico: indica preparazione, non consegna in WhatsApp.
        if (result.whatsapp && /^https:\/\/wa\.me\/[1-9][0-9]{7,14}\?text=/.test(result.whatsapp)) {
          track('whatsapp_click', { posizione: 'modulo' });
          window.location.assign(result.whatsapp);
          sending.textContent = 'Apri WhatsApp e premi Invia per completare la candidatura.';
          const fallback = document.createElement('a');
          fallback.href = '/grazie/';
          fallback.textContent = 'WhatsApp non si è aperto? Rileggi e apri il messaggio';
          sending.append(' ', fallback);
        } else window.location.assign('/grazie/');
      }
    } catch {
      showErrors({ form: 'La connessione si è interrotta. I dati sono ancora nel modulo: riprova, oppure scrivimi su WhatsApp.' });
      sending.textContent = '';
    } finally {
      busy = false;
      submit.disabled = prepared;
      previous.disabled = prepared;
    }
  });
  const firstError = [...form.querySelectorAll('[data-error]')].find(el => el.textContent.trim());
  const initial = firstError ? steps.indexOf(firstError.closest('[data-step]')) : 0;
  show(Math.max(0, initial), false);
}

