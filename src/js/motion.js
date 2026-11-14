import { createTuner } from './tuner.js';
export function initMotion() {
  const body=document.body;
  const reducedQuery=window.matchMedia('(prefers-reduced-motion: reduce)');
  const fine=window.matchMedia('(pointer:fine)').matches;
  const toggle=document.querySelector('[data-motion-toggle]');
  const canvas=document.querySelector('[data-galaxy]');
  let paused=false,visible=true,raf=0,lastTime=0,galaxy=null,dirty=true,initialized=false;
  const pointer={x:0,y:0};
  const reduced=()=>reducedQuery.matches||paused;
  const wake=()=>{if(!raf&&!document.hidden)raf=requestAnimationFrame(frame);};
  const tuner=createTuner(document.querySelector('[data-tuner]'),{reduced,wake});
  let nextShot=performance.now()+5500,shotStart=0,shot=null;
  const supportedTimeline=CSS.supports('animation-timeline:view()');
  body.classList.add('js-motion');
  function updateProgress(){
    const range=Math.max(1,document.documentElement.scrollHeight-window.innerHeight);
    body.style.setProperty('--page-progress',String(Math.min(1,window.scrollY/range)));
  }
  function frame(now) {
    raf=0;
    const delta=lastTime?Math.min(.05,(now-lastTime)/1000):1/60;
    lastTime=now;
    if(dirty){updateProgress();dirty=false;}
    if(!reduced()&&!document.hidden){
      const lock=tuner?.tick(now,delta)??1;
      if(visible){
        const scroll=Math.min(1,window.scrollY/Math.max(window.innerHeight,canvas?.clientHeight||1000));
        galaxy?.render(now/1000,delta,{lock,scroll,pointer});
        if(!galaxy){
          body.style.setProperty('--sky-x',String(pointer.x*12)+'px');
          body.style.setProperty('--sky-y',String(pointer.y*9-window.scrollY*.08)+'px');
        }
        if(now>nextShot&&!shotStart){
          if(!shot){shot=document.createElement('span');shot.className='shooting-star';shot.setAttribute('aria-hidden','true');(canvas?.parentElement||body).append(shot);}
          shot.style.left=String(60+Math.random()*25)+'%';shot.style.top=String(110+Math.random()*150)+'px';
          shotStart=now;nextShot=now+5000+Math.random()*4000;
        }
        if(shot&&shotStart){
          const p=(now-shotStart)/900;
          if(p>=1){shotStart=0;shot.style.opacity='0';}
          else{shot.style.transform='translate('+(-p*260)+'px,'+(p*120)+'px) rotate(-25deg)';shot.style.opacity=String(Math.sin(Math.PI*p)*.7);}
        }
      }
    }
    if(!reduced()&&!document.hidden&&(visible||tuner?.needsFrame()))wake();
  }
  function setMotionState(){
    if(raf){cancelAnimationFrame(raf);raf=0;}
    body.classList.toggle('motion-paused',paused||reducedQuery.matches);
    body.classList.toggle('galaxy-ready',Boolean(galaxy)&&!reduced());
    if(toggle){toggle.setAttribute('aria-pressed',String(paused));toggle.setAttribute('aria-label',paused?'Riprendi le animazioni':'Metti in pausa le animazioni');toggle.disabled=reducedQuery.matches;}
    if(reduced()){tuner?.settle();if(shot)shot.style.opacity='0';body.style.setProperty('--lock','1');}
    else{if(initialized)tuner?.resume();lastTime=0;wake();loadGalaxy();}
    initialized=true;
    document.querySelectorAll('.reveal-pending').forEach(el=>{if(reduced()){el.classList.remove('reveal-pending');el.classList.add('reveal-in');}});
  }
  if(toggle){toggle.hidden=false;toggle.addEventListener('click',()=>{paused=!paused;setMotionState();});}
  reducedQuery.addEventListener('change',setMotionState);
  const observer=new IntersectionObserver(entries=>{visible=entries[0]?.isIntersecting===true;if(visible){lastTime=0;wake();}},{threshold:0});
  if(canvas)observer.observe(canvas);
  window.addEventListener('scroll',()=>{dirty=true;wake();},{passive:true});
  window.addEventListener('resize',()=>{dirty=true;galaxy?.resize();wake();},{passive:true});
  document.addEventListener('visibilitychange',()=>{
    body.classList.toggle('motion-hidden',document.hidden);
    if(document.hidden){if(raf)cancelAnimationFrame(raf);raf=0;}
    else{lastTime=0;tuner?.resume();wake();}
  });
  if(fine)window.addEventListener('pointermove',event=>{
    if(reduced())return;
    pointer.x=event.clientX/window.innerWidth*2-1;
    pointer.y=event.clientY/window.innerHeight*2-1;
    wake();
  },{passive:true});
  if(fine)document.querySelectorAll('[data-magnet]').forEach(button=>{
    button.addEventListener('pointermove',event=>{
      if(reduced())return;
      const bounds=button.getBoundingClientRect();
      button.style.setProperty('--mx',((event.clientX-bounds.left-bounds.width/2)*.12).toFixed(1)+'px');
      button.style.setProperty('--my',((event.clientY-bounds.top-bounds.height/2)*.18).toFixed(1)+'px');
    });
    button.addEventListener('pointerleave',()=>{button.style.setProperty('--mx','0px');button.style.setProperty('--my','0px');});
  });
  if(!supportedTimeline&&!reduced()){
    const reveal=new IntersectionObserver(entries=>{
      entries.forEach(entry=>{if(entry.isIntersecting){entry.target.classList.remove('reveal-pending');entry.target.classList.add('reveal-in');reveal.unobserve(entry.target);}});
    },{threshold:.12});
    document.querySelectorAll('.sec .h2,.fq,.facts li,.steps li,.ytf,.ytl li,.igc,.ph,.linea').forEach(el=>{
      if(el.getBoundingClientRect().top>window.innerHeight){el.classList.add('reveal-pending');reveal.observe(el);}
    });
  }
  let requested=false;
  function loadGalaxy(){
    const constrained=navigator.connection?.saveData||(navigator.deviceMemory&&navigator.deviceMemory<4)||(navigator.hardwareConcurrency&&navigator.hardwareConcurrency<4);
    if(requested||reduced()||constrained||!canvas)return;
    requested=true;
    const schedule=window.requestIdleCallback||((fn)=>setTimeout(fn,1200));
    schedule(async()=>{
      if(reduced()){requested=false;return;}
      try{
        const {createGalaxy}=await import('./effects/galaxy.js');
        if(reduced()){requested=false;return;}
        galaxy=createGalaxy(canvas,{mobile:!fine||window.innerWidth<900});
        if(galaxy){body.classList.add('galaxy-ready');wake();}
      }catch{body.classList.remove('galaxy-ready');}
    },{timeout:2500});
  }
  updateProgress();setMotionState();
  window.addEventListener('pagehide',event=>{
    if(raf)cancelAnimationFrame(raf);raf=0;
    if(!event.persisted){galaxy?.dispose();galaxy=null;requested=false;body.classList.remove('galaxy-ready');}
  });
  window.addEventListener('pageshow',event=>{
    if(!event.persisted)return;
    body.classList.remove('motion-hidden');dirty=true;lastTime=0;galaxy?.resize();setMotionState();
  });
}
