import { track } from './analytics.js';

export function initVideos(){
  let allowed=false;
  const originals=new Map();
  const revoke=document.querySelector('[data-video-revoke]');
  const status=document.querySelector('[data-video-status]');
  const reset=facade=>{const nodes=originals.get(facade);if(nodes)facade.replaceChildren(...nodes);};
  function play(link,facade){
    const frame=document.createElement('iframe');
    frame.src='https://www.youtube-nocookie.com/embed/'+link.dataset.video+'?autoplay=1';
    frame.title=link.getAttribute('aria-label')||link.textContent.trim()||'Puntata di Altrove';
    frame.allow='autoplay; encrypted-media; picture-in-picture';
    frame.allowFullscreen=true;
    frame.referrerPolicy='strict-origin-when-cross-origin';
    facade.replaceChildren(frame);
    if(revoke)revoke.hidden=false;
    if(status){status.hidden=true;status.textContent='';}
    frame.focus();
    track('puntata_play');
  }
  document.querySelectorAll('[data-video]').forEach(link=>{
    const facade=link.closest('.video-facade');
    if(!facade||!/^[A-Za-z0-9_-]{11}$/.test(link.dataset.video))return;
    originals.set(facade,[...facade.childNodes]);
    link.addEventListener('click',event=>{
      if(event.ctrlKey||event.metaKey||event.shiftKey||event.altKey)return;
      event.preventDefault();
      if(allowed){play(link,facade);return;}
      const panel=document.createElement('div');
      panel.className='video-consent';panel.setAttribute('role','group');panel.setAttribute('aria-label','Attivazione del lettore YouTube');
      const heading=document.createElement('h3');heading.className='serif';heading.textContent='Riproduci con YouTube';
      const explanation=document.createElement('p');explanation.textContent='Il lettore comunica dati a Google e può usare cookie o tecnologie simili. La tua scelta vale per i video di questa pagina; puoi revocarla dal fondo della pagina.';
      const info=document.createElement('a');info.href='/cookie/';info.target='_blank';info.rel='noopener';info.textContent='Leggi i dettagli sui video (nuova scheda)';
      const actions=document.createElement('div');actions.className='video-consent-actions';
      const accept=document.createElement('button');accept.type='button';accept.className='pill';accept.textContent='Consenti e riproduci';
      const cancel=document.createElement('button');cancel.type='button';cancel.className='ghost';cancel.textContent='Annulla';
      accept.addEventListener('click',()=>{allowed=true;play(link,facade);});
      cancel.addEventListener('click',()=>{reset(facade);link.focus();});
      const external=document.createElement('a');external.href=link.href;external.textContent='Apri direttamente su YouTube';
      actions.append(accept,cancel);panel.append(heading,explanation,info,actions,external);facade.replaceChildren(panel);accept.focus();
    });
  });
  revoke?.addEventListener('click',()=>{
    allowed=false;originals.forEach((_,facade)=>reset(facade));revoke.hidden=true;
    if(status){status.textContent='Video YouTube disattivati. Per riprodurli servirà una nuova scelta.';status.hidden=false;status.tabIndex=-1;status.focus();}
  });
}
