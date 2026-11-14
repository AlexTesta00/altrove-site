import { initForm } from './form.js';
import { track } from './analytics.js';
import { initMotion } from './motion.js';
initForm();
initMotion();
document.querySelectorAll('[data-whatsapp]').forEach(link=>link.addEventListener('click',()=>track('whatsapp_click',{posizione:link.dataset.whatsapp})));
document.querySelectorAll('[data-video]').forEach(link=>link.addEventListener('click',event=>{
  const id=link.dataset.video;
  if(!/^[A-Za-z0-9_-]{11}$/.test(id))return;
  event.preventDefault();
  const frame=document.createElement('iframe');
  frame.src='https://www.youtube-nocookie.com/embed/'+id+'?autoplay=1';
  frame.title=link.getAttribute('aria-label')||'Puntata di Altrove';
  frame.allow='autoplay; encrypted-media; picture-in-picture';
  frame.allowFullscreen=true;
  frame.referrerPolicy='strict-origin-when-cross-origin';
  (link.closest('.video-facade')||link).replaceChildren(frame);
  track('puntata_play');
}));
const copyButton=document.querySelector('[data-copy-message]');
if(copyButton){
  copyButton.hidden=false;
  copyButton.addEventListener('click',async()=>{
    const text=document.querySelector('#messaggio-completo');
    const status=document.querySelector('[data-copy-status]');
    text.focus();text.select();
    try{
      if(navigator.clipboard?.writeText)await navigator.clipboard.writeText(text.value);
      else if(!document.execCommand('copy'))throw new Error('copy');
      status.textContent='Messaggio copiato. Apri WhatsApp, incollalo e premi Invia.';
    }catch{status.textContent='Il testo è selezionato: usa Copia nel menu del dispositivo, poi incollalo in WhatsApp.';}
  });
}
