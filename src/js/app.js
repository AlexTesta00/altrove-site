import { initForm } from './form.js';
import { track } from './analytics.js';
initForm();
document.querySelectorAll('[data-whatsapp]').forEach(link => link.addEventListener('click', () => track('whatsapp_click', { posizione: link.dataset.whatsapp })));
document.querySelectorAll('[data-video]').forEach(link => link.addEventListener('click', event => {
  const id = link.dataset.video;
  if (!/^[A-Za-z0-9_-]{11}$/.test(id)) return;
  event.preventDefault();
  const frame = document.createElement('iframe');
  frame.src = 'https://www.youtube-nocookie.com/embed/' + id + '?autoplay=1';
  frame.title = link.getAttribute('aria-label') || 'Puntata di Altrove';
  frame.allow = 'autoplay; encrypted-media; picture-in-picture';
  frame.allowFullscreen = true;
  frame.referrerPolicy = 'strict-origin-when-cross-origin';
  link.replaceWith(frame);
  track('puntata_play');
}));
const canvas = document.querySelector('[data-background]');
if (canvas) {
  const schedule = window.requestIdleCallback || (fn => window.setTimeout(fn, 1000));
  schedule(() => import('./effects/background.js').then(module => module.prepareBackground(canvas)).catch(() => {}));
}

