import { track } from './analytics.js';
export function initForm(){
  const form=document.querySelector('[data-candidatura]');
  if(!form||!window.fetch)return;
  const steps=[...form.querySelectorAll('[data-step]')];
  const controls=form.querySelector('[data-step-controls]');
  const previous=form.querySelector('[data-back]');
  const next=form.querySelector('[data-next]');
  const status=form.querySelector('[data-step-status]');
  const scale=form.querySelector('[data-step-scale]');
  const summary=form.querySelector('#errori-modulo');
  const submit=form.querySelector('[data-submit]');
  const sending=form.querySelector('[data-send-status]');
  const story=form.querySelector('[name=esperienza]');
  let current=0,busy=false,prepared=false,started=false;
  form.noValidate=true;form.classList.add('enhanced');
  controls.hidden=false;status.hidden=false;scale.hidden=false;
  controls.prepend(submit);controls.after(sending);
  const fieldMessage=field=>{
    if(field.type==='radio')return form.querySelector('[name="'+field.name+'"]:checked')?'':field.name==='veste'?'Scegli in che veste vuoi partecipare.':'Scegli il tema più vicino alla tua storia.';
    if(field.name==='consenso')return field.checked?'':'Leggi l’informativa e dai il tuo consenso per continuare.';
    if(field.name==='esperienza')return [...field.value.trim()].length>=30&&[...field.value].length<=4000?'':'Racconta la tua storia in almeno trenta caratteri, fino a quattromila.';
    if(['nome','cognome'].includes(field.name))return /^[\p{L}\p{M}][\p{L}\p{M} '’\-]{1,59}$/u.test(field.value.normalize('NFC').trim())?'':'Scrivi almeno due lettere, usando solo lettere, spazi, apostrofi o trattini.';
    if(field.name==='telefono'){
      let phone=field.value.replace(/[\s().-]/g,'');
      if(phone.startsWith('00'))phone='+'+phone.slice(2);
      if(!phone.startsWith('+'))phone='+39'+phone;
      return /^\+[1-9][0-9]{7,14}$/.test(phone)?'':'Scrivi un numero di telefono valido, con il prefisso internazionale se non è italiano.';
    }
    return field.required&&!field.checkValidity()?'Completa questo campo per continuare.':'';
  };
  const fields=step=>[...step.querySelectorAll('input:not([type=hidden]),textarea,select')];
  function setError(name,message){
    const error=form.querySelector('[data-error="'+name+'"]');if(error)error.textContent=message;
    form.querySelectorAll('[name="'+name+'"]').forEach(input=>{if(message)input.setAttribute('aria-invalid','true');else input.removeAttribute('aria-invalid');});
  }
  function valid(step,display=false){
    let first=null;const done=new Set();
    fields(step).forEach(field=>{
      if(done.has(field.name))return;done.add(field.name);
      const message=fieldMessage(field);
      if(display)setError(field.name,message);
      if(message&&!first)first=field;
    });
    return first;
  }
  function update(){
    next.disabled=busy||prepared||Boolean(valid(steps[current]));
    submit.disabled=busy||prepared||Boolean(valid(steps[steps.length-1]));
    previous.disabled=busy||prepared;
  }
  function show(index,focus=true){
    current=index;
    steps.forEach((step,i)=>{step.hidden=i!==current;step.classList.remove('step-enter');});
    steps[current].classList.add('step-enter');
    previous.hidden=current===0;
    next.hidden=current===steps.length-1;
    submit.hidden=current!==steps.length-1;
    status.querySelector('[data-step-count]').textContent='Passo '+(current+1)+' di '+steps.length;
    status.querySelector('[data-step-percent]').textContent=((current+1)*20)+'%';
    form.querySelector('[data-step-needle]').style.left=((current+1)*20)+'%';
    update();
    if(focus)steps[current].querySelector('legend').focus();
  }
  function waveform(){
    const text=story.value;
    const factor=Math.min(1,[...text].length/240);
    form.querySelectorAll('.bar').forEach((bar,i)=>{
      const c=text.length?text.charCodeAt((i*7)%text.length):0;
      bar.style.height=Math.round(3+factor*((c*37+i*13)%36))+'px';
    });
    form.querySelector('[data-counter]').textContent=[...text].length.toLocaleString('it-IT')+' / 4.000 caratteri';
  }
  previous.addEventListener('click',()=>show(Math.max(0,current-1)));
  next.addEventListener('click',()=>{const invalid=valid(steps[current],true);if(!invalid)show(current+1);else invalid.focus();});
  form.addEventListener('input',event=>{
    if(!started){started=true;track('form_inizio');}
    if(event.target===story){const codepoints=[...story.value];if(codepoints.length>4000)story.value=codepoints.slice(0,4000).join('');waveform();}
    if(event.target.getAttribute('aria-invalid')==='true')setError(event.target.name,fieldMessage(event.target));
    update();
  });
  form.addEventListener('change',update);
  form.addEventListener('focusout',event=>{if(event.target.matches('.fld')&&event.target.value.trim())setError(event.target.name,fieldMessage(event.target));});
  function showErrors(errors){
    summary.hidden=false;summary.querySelector('p').textContent=errors.form||'Controlla i campi indicati e riprova.';
    const list=summary.querySelector('ul');list.replaceChildren();let targetStep=null;
    for(const [name,message]of Object.entries(errors)){
      if(name==='form')continue;
      const field=form.elements.namedItem(name);setError(name,message);
      const li=document.createElement('li');li.textContent=message;list.append(li);
      const element=field instanceof RadioNodeList?field[0]:field;
      if(targetStep===null&&element)targetStep=steps.indexOf(element.closest('[data-step]'));
    }
    if(targetStep!==null&&targetStep>=0)show(targetStep,false);
    summary.focus();
  }
  form.addEventListener('submit',async event=>{
    event.preventDefault();if(busy||prepared)return;
    if(current<steps.length-1){const invalid=valid(steps[current],true);if(!invalid)show(current+1);else invalid.focus();return;}
    for(let i=0;i<steps.length;i++){const invalid=valid(steps[i],true);if(invalid){show(i,false);invalid.focus();return;}}
    busy=true;update();summary.hidden=true;sending.textContent='Sto preparando la scelta del piano…';
    const controller=new AbortController();const timeout=setTimeout(()=>controller.abort(),25000);
    try{
      const response=await fetch(form.action,{method:'POST',body:new URLSearchParams(new FormData(form)),headers:{Accept:'application/json'},credentials:'same-origin',signal:controller.signal});
      const result=await response.json();
      if(!response.ok||!result.ok){
        showErrors(result.errors||{form:'Non riesco a proseguire ai piani. Riprova tra poco.'});
        window.turnstile?.reset();sending.textContent='';
      }else{
        prepared=true;track('form_inviato');
        window.location.assign('/proposte/');
      }
    }catch{showErrors({form:'La connessione si è interrotta. I dati sono ancora nel modulo: riprova tra poco.'});sending.textContent='';}
    finally{clearTimeout(timeout);busy=false;update();}
  });
  const firstError=[...form.querySelectorAll('[data-error]')].find(el=>el.textContent.trim());
  show(Math.max(0,firstError?steps.indexOf(firstError.closest('[data-step]')):0),false);waveform();
}
