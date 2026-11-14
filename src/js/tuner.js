const clamp=(n,a,b)=>Math.min(b,Math.max(a,n));
export function createTuner(root, { reduced, wake }) {
  if(!root || root.hasAttribute('data-static')) return null;
  const dial=root.querySelector('[data-dial]');
  const stations=[...root.querySelectorAll('[data-station]')];
  const labels=[...root.querySelectorAll('.rn')];
  const plays=[...root.querySelectorAll('[data-now-playing]')];
  const frequencies=[...document.querySelectorAll('[data-frequency]')];
  let active=Number(root.dataset.active)||0;
  let target=.125+active*.25,value=target,velocity=0;
  let dragging=false,pointerId=null,automatic=true;
  let lastInteract=-Infinity,lastScan=performance.now()+900;
  let sweepEnd=performance.now()+900;
  if(!reduced()) { value=.02; target=.93; }
  function paint() {
    root.style.setProperty('--nf',value.toFixed(5));
    const distance=Math.min(...[0,1,2,3].map(i=>Math.abs(value-(.125+i*.25))));
    const lock=reduced()?1:clamp(1-distance/.08,0,1);
    document.body.style.setProperty('--lock',lock.toFixed(3));
    return lock;
  }
  function choose(index,manual=true) {
    active=clamp(index,0,3);
    if(manual){lastInteract=performance.now();automatic=false;sweepEnd=0;}
    target=.125+active*.25;
    if(reduced()){value=target;velocity=0;}
    stations.forEach((station,i)=>{station.classList.toggle('on',i===active);if(i===active)station.setAttribute('aria-current','true');else station.removeAttribute('aria-current');});
    labels.forEach((label,i)=>{label.classList.toggle('on',i===active);label.classList.toggle('up',i<active);label.setAttribute('aria-hidden',String(i!==active));});
    plays.forEach((play,i)=>{play.hidden=i!==active;});
    frequencies.forEach((frequency,i)=>frequency.classList.toggle('on',i===active));
    dial.setAttribute('aria-valuenow',String(active));
    dial.setAttribute('aria-valuetext',labels[active]?.textContent||'');
    root.classList.toggle('auto',automatic&&!reduced());
    paint();wake();
  }
  const nearest=position=>clamp(Math.round((position-.125)/.25),0,3);
  function fraction(event){const bounds=dial.getBoundingClientRect();return clamp((event.clientX-bounds.left)/bounds.width,.01,.99);}
  function endDrag(){
    if(!dragging)return;
    dragging=false;root.classList.remove('drag');
    try{if(pointerId!==null&&dial.hasPointerCapture(pointerId))dial.releasePointerCapture(pointerId);}catch{}
    pointerId=null;choose(nearest(target));
  }
  dial.addEventListener('pointerdown',event=>{
    if(event.button!==0)return;
    dragging=true;pointerId=event.pointerId;root.classList.add('drag');
    dial.setPointerCapture(pointerId);lastInteract=performance.now();automatic=false;sweepEnd=0;
    target=fraction(event);
    const position=target;choose(nearest(target));target=position;
    wake();
  });
  dial.addEventListener('pointermove',event=>{
    if(!dragging||event.pointerId!==pointerId)return;
    const position=fraction(event);
    if(nearest(position)!==active)choose(nearest(position));
    target=position;lastInteract=performance.now();
    if(reduced()){value=target;paint();}
    wake();
  });
  dial.addEventListener('pointerup',endDrag);
  dial.addEventListener('pointercancel',endDrag);
  dial.addEventListener('lostpointercapture',endDrag);
  window.addEventListener('blur',endDrag);
  root.addEventListener('keydown',event=>{
    if(!['ArrowLeft','ArrowRight','Home','End'].includes(event.key))return;
    event.preventDefault();
    const next=event.key==='Home'?0:event.key==='End'?3:(active+(event.key==='ArrowRight'?1:3))%4;
    choose(next);
    if(event.target!==dial)stations[next].focus();
  });
  stations.forEach((station,i)=>station.addEventListener('click',event=>{
    event.preventDefault();choose(i);
    const destination=document.getElementById('frequenza-'+['nde','medium','channeling','mentalismo'][i]);
    window.setTimeout(()=>destination?.scrollIntoView({behavior:reduced()?'auto':'smooth',block:'start'}),reduced()?0:360);
  }));
  choose(active,false);
  if(!reduced()){value=.02;target=.93;}
  return {
    tick(now,delta){
      if(reduced()){value=.125+active*.25;velocity=0;root.classList.remove('auto');return paint();}
      if(sweepEnd&&now>=sweepEnd){sweepEnd=0;target=.125+active*.25;}
      if(!dragging&&!sweepEnd){
        if(!automatic&&now-lastInteract>14000){automatic=true;lastScan=now;root.classList.add('auto');}
        if(automatic&&now-lastScan>3600){lastScan=now;choose((active+1)%4,false);}
      }
      const dt=clamp(delta*60,.15,2);
      velocity=(velocity+(target-value)*.075*dt)*Math.pow(.78,dt);
      value+=velocity*dt;
      if(Math.abs(target-value)<.0001&&Math.abs(velocity)<.0001){value=target;velocity=0;}
      root.style.setProperty('--scan-progress',String(automatic?clamp((now-lastScan)/3600,0,1):0));
      return paint();
    },
    settle(){sweepEnd=0;dragging=false;value=target=.125+active*.25;velocity=0;paint();root.classList.remove('auto');},
    resume(){lastScan=performance.now();lastInteract=performance.now();automatic=false;},
    needsFrame(){return dragging||Math.abs(target-value)>.0001;}
  };
}
