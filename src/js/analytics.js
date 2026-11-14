export function track(name, properties = {}) {
  const allowed = ['form_inizio', 'form_inviato', 'whatsapp_click', 'puntata_play'];
  if (!allowed.includes(name)) return;
  if (window.umami?.track) window.umami.track(name, properties);
}

