import { initForm } from './form.js';
import { track } from './analytics.js';
import { initMotion } from './motion.js';
import { initVideos } from './videos.js';
import { initPlans } from './plans.js';
initForm();
initMotion();
initVideos();
initPlans();
document.querySelectorAll('[data-whatsapp]').forEach(link=>link.addEventListener('click',()=>track('whatsapp_click',{posizione:link.dataset.whatsapp})));
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
