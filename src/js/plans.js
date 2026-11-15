export function initPlans(){
  const form=document.querySelector('[data-plans]');
  if(!form)return;
  const current=form.querySelector('[data-plan-current]');
  const update=()=>{
    const selected=form.querySelector('[name=piano]:checked');
    if(!selected){current.textContent='Scegli un piano per preparare il messaggio.';return;}
    const option=selected.closest('.plan-option');
    current.textContent='Hai scelto '+option.querySelector('.plan-name').textContent+' · '+option.querySelector('.plan-price').textContent+' finali.';
  };
  form.addEventListener('change',update);
  update();
}
