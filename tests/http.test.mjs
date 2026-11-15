import test from 'node:test';
import assert from 'node:assert/strict';
import { spawn } from 'node:child_process';
import { randomBytes, createHmac } from 'node:crypto';
import { access, unlink, readFile, writeFile } from 'node:fs/promises';
import { resolve } from 'node:path';
import net from 'node:net';
const root=resolve(import.meta.dirname,'..');
const pause=ms=>new Promise(resolve=>setTimeout(resolve,ms));
const secret=randomBytes(32).toString('hex');
const salt=randomBytes(32).toString('hex');
let server, base;
const cookies=new Set();
async function form(path='/candidati/', cookieHeader='') {
  const response=await fetch(base+path,{headers:cookieHeader?{Cookie:cookieHeader}:{}});
  assert.equal(response.status,200);
  const html=await response.text();
  const cookie=response.headers.get('set-cookie')?.split(';')[0]||cookieHeader;
  cookies.add(cookie.split('=')[1]);
  return { cookie, csrf:html.match(/name="csrf" value="([^"]+)"/)[1], stamp:html.match(/name="stamp" value="([^"]+)"/)[1], html };
}
const values={nome:'Maria',cognome:'Prova',telefono:'3331234567',esperienza:'Questa è una storia sintetica usata soltanto per verificare il modulo.',veste:'esperienza',tema:'nde',origine:'home',consenso:'1',sito_web:''};
function stamp(csrf,offset=5) {
  const time=String(Math.floor(Date.now()/1000)-offset);
  return time+'.'+createHmac('sha256',secret).update(time+':'+csrf).digest('hex');
}
function submit(session,override={},headers={}) {
  return fetch(base+'/api/candidatura',{method:'POST',redirect:'manual',headers:{Origin:base,Cookie:session.cookie,Accept:'application/json',...headers},body:new URLSearchParams({...values,csrf:session.csrf,stamp:stamp(session.csrf),...override})});
}
function nextCookie(response,previous){const cookie=response.headers.get('set-cookie')?.split(';')[0]||previous;cookies.add(cookie.split('=')[1]);return cookie;}
async function plans(cookie){
  const response=await fetch(base+'/proposte/',{headers:{Cookie:cookie}});
  const html=await response.text();
  return {cookie,html,csrf:html.match(/name="csrf" value="([^"]+)"/)?.[1],application:html.match(/name="candidatura" value="([^"]+)"/)?.[1]};
}
function choosePlan(session,override={},headers={}){
  return fetch(base+'/api/piano',{method:'POST',redirect:'manual',headers:{Origin:base,Cookie:session.cookie,Accept:'application/json',...headers},body:new URLSearchParams({csrf:session.csrf,candidatura:session.application,piano:'completo',...override})});
}
async function resetLimit(){for(const ip of ['::1','127.0.0.1'])await unlink(resolve(root,'app/storage/ratelimit',createHmac('sha256',salt).update(ip).digest('hex')+'.json')).catch(()=>{});}
test('HTTP: pagine, sicurezza e candidatura WhatsApp', async t=>{
  const port=await new Promise(resolve=>{const probe=net.createServer();probe.listen(0,'127.0.0.1',()=>{const p=probe.address().port;probe.close(()=>resolve(p));});});
  base='http://localhost:'+port;
  const args=[];
  try{await access(resolve(root,'.runtime/php.ini'));args.push('-c',resolve(root,'.runtime/php.ini'));}catch{}
  args.push('-S','localhost:'+port,'-t','public','tools/serve.php');
  server=spawn(process.env.PHP_BINARY||'php',args,{cwd:root,env:{...process.env,APP_ENV:'local',APP_URL:base,APP_SECRET:secret,IP_SALT:salt,WHATSAPP_NUMBER:'393272433925',YOUTUBE_CHANNEL_ID:'',BACKUP_PUBLIC_KEY:'',TURNSTILE_SITE_KEY:'',TURNSTILE_SECRET:'',UMAMI_WEBSITE_ID:''},stdio:'ignore'});
  try {
    for(let i=0;i<100;i++){try{await fetch(base);break;}catch{await pause(100);}}
    await t.test('Pagine, H1, canonical, JSON-LD, header e noindex',async()=>{
      for(const path of ['/','/candidati/','/esperienze-pre-morte/','/medium-e-sensitivi/','/channeling-e-coscienza-stellare/','/mentalismo-e-ipnosi/','/alessia-ventura/','/privacy/','/cookie/','/grazie/','/proposte/']){
        const response=await fetch(base+path);
        assert.equal(response.status,200,path);
        const html=await response.text();
        assert.equal((html.match(/<h1[\s>]/g)||[]).length,1,path);
        assert.match(html,/<html lang="it">/);
        assert.match(html,/rel="canonical"/);
        const json=html.match(/<script type="application\/ld\+json"[^>]*>([\s\S]*?)<\/script>/)[1];
        assert.equal(JSON.parse(json)['@context'],'https://schema.org');
        assert.match(response.headers.get('content-security-policy'),/frame-ancestors 'none'/);
        assert.equal(response.headers.get('x-content-type-options'),'nosniff');
        if(['/grazie/','/proposte/'].includes(path))assert.match(html,/noindex, follow/);
        assert.doesNotMatch(html,/200 €|250 €|350 €/);
      }
      const sitemapResponse=await fetch(base+'/sitemap.xml');
      assert.equal(sitemapResponse.status,200);
      const sitemap=await sitemapResponse.text();
      assert.match(sitemap,/<urlset/);assert.doesNotMatch(sitemap,/\/(grazie|proposte)\//);
      const robots=await fetch(base+'/robots.txt');
      assert.equal(robots.status,200);assert.match(await robots.text(),/User-agent/);
      assert.equal((await fetch(base+'/inesistente/')).status,404);
      assert.equal((await fetch(base+'/candidati',{redirect:'manual'})).status,301);
    });
    await t.test('Metodo, origine, contenuto, CSRF, honeypot e tempo',async()=>{
      assert.equal((await fetch(base+'/api/candidatura')).status,405);
      const session=await form();
      assert.equal((await submit(session,{}, {Origin:'https://example.org'})).status,403);
      assert.equal((await submit(session,{csrf:'errato'})).status,419);
      const noJsError=await submit(session,{csrf:'errato',nome:'Maria conservata'},{Accept:'text/html'});
      assert.equal(noJsError.status,303);
      assert.equal(noJsError.headers.get('location'),'/candidati/#candidatura');
      const restored=await (await fetch(base+'/candidati/',{headers:{Cookie:session.cookie}})).text();
      assert.match(restored,/value="Maria conservata"/);
      assert.match(restored,/La pagina è scaduta/);
      assert.equal((await submit(session,{stamp:session.stamp})).status,422);
      assert.equal((await submit(session,{sito_web:'https://spam.example'})).status,200);
      assert.equal((await submit(session,{esperienza:'x'.repeat(66000)})).status,413);
      assert.equal((await fetch(base+'/api/candidatura',{method:'POST',headers:{Origin:base,'Content-Type':'application/json'},body:'{}'})).status,415);
    });
    await t.test('Errori campo e nessun dato personale in URL',async()=>{
      const session=await form();
      const response=await submit(session,{nome:'',telefono:'errato',esperienza:'breve',consenso:''});
      assert.equal(response.status,422);
      const result=await response.json();
      assert.ok(result.errors.nome&&result.errors.telefono&&result.errors.esperienza&&result.errors.consenso);
      assert.equal(result.redirect,null);
    });
    await t.test('Il modulo conduce ai piani senza aprire WhatsApp; importo autorevole dal server',async()=>{
      const session=await form();
      const response=await submit(session);
      assert.equal(response.status,200);
      const result=await response.json();
      assert.equal(result.ok,true);
      assert.equal(result.redirect,'/proposte/');
      assert.equal(result.whatsapp,undefined);
      const cookie=nextCookie(response,session.cookie);
      const step=await plans(cookie);
      assert.match(step.html,/200 €/);assert.match(step.html,/250 €/);assert.match(step.html,/350 €/);
      assert.doesNotMatch(step.html,/name="piano"[^>]*checked/);
      assert.equal((await fetch(base+'/grazie/',{headers:{Cookie:cookie},redirect:'manual'})).headers.get('location'),'/proposte/');
      const planResponse=await choosePlan(step,{prezzo:'1',nome:'Intruso',contenuti:'Tutto gratis'});
      assert.equal(planResponse.status,200);
      assert.equal((await planResponse.json()).redirect,'/grazie/');
      const preview=await(await fetch(base+'/grazie/',{headers:{Cookie:cookie}})).text();
      assert.match(preview,/Maria Prova/);assert.match(preview,/Completo · 250 € \(importo finale\)/);
      assert.match(preview,/Puntata completa su YouTube e Spotify/);assert.doesNotMatch(preview,/Intruso|Tutto gratis/);
      assert.ok(!response.headers.get('location'));
    });
    await t.test('POST senza JavaScript: PRG e anteprima protetta',async()=>{
      const session=await form();
      const response=await submit(session,{}, {Accept:'text/html'});
      assert.equal(response.status,303);
      assert.equal(response.headers.get('location'),'/proposte/');
      const cookie=nextCookie(response,session.cookie);
      const step=await plans(cookie);
      const selected=await choosePlan(step,{piano:'essenziale'},{Accept:'text/html'});
      assert.equal(selected.status,303);assert.equal(selected.headers.get('location'),'/grazie/');
      const preview=await (await fetch(base+'/grazie/',{headers:{Cookie:cookie}})).text();
      assert.match(preview,/Maria Prova/);
      assert.match(preview,/Apri WhatsApp e invia/);
      assert.match(preview,/Essenziale · 200 €/);
    });
    await t.test('Limite tentativi: blocco dal quarto, quindi anche il sesto',async()=>{
      const session=await form();
      for(let i=0;i<3;i++)assert.equal((await submit(session)).status,429);
    });
    await t.test('Tema preselezionato e nessun contatto WhatsApp prima del finale',async()=>{
      const session=await form('/candidati/?tema=medium');
      assert.match(session.html,/name="tema" id="tema-medium" value="medium" checked/);
      for(const path of ['/','/candidati/','/privacy/','/cookie/','/alessia-ventura/']){
        const html=await(await fetch(base+path)).text();
        assert.doesNotMatch(html,/href="https:\/\/wa\.me\//,path);
      }
    });
    await t.test('Storia completa Unicode, copia per URL lunghi e honeypot senza dati precedenti',async()=>{
      for(const ip of ['::1','127.0.0.1']){
        const hash=createHmac('sha256',salt).update(ip).digest('hex');
        await unlink(resolve(root,'app/storage/ratelimit',hash+'.json')).catch(()=>{});
      }
      const longStory='🌙漢é '.repeat(1000);
      assert.equal([...longStory].length,4000);
      const session=await form();
      const response=await submit(session,{esperienza:longStory});
      assert.equal(response.status,200);
      const result=await response.json();
      assert.equal(result.redirect,'/proposte/');
      const cookie=nextCookie(response,session.cookie);
      assert.equal((await choosePlan(await plans(cookie),{piano:'amplifica'})).status,200);
      const preview=await(await fetch(base+'/grazie/',{headers:{Cookie:cookie}})).text();
      assert.match(preview,/Copia il messaggio completo/);
      assert.ok(preview.includes(longStory.trim()));
      assert.match(preview,/Amplifica · 350 €/);assert.match(preview,/3 Reel totali/);
      assert.match(preview,/href="https:\/\/wa.me\/393272433925\?text="/);
      const refreshed=await form('/candidati/',cookie);
      assert.equal((await submit(refreshed,{esperienza:longStory+'x'})).status,422);
      const honey=await submit(refreshed,{sito_web:'bot'});
      assert.equal(honey.status,200);
      const honeyResult=await honey.json();
      assert.equal(honeyResult.redirect,'/proposte/');
      const honeyPreview=await(await fetch(base+'/grazie/',{headers:{Cookie:cookie}})).text();
      assert.ok(!honeyPreview.includes(longStory.trim()));
    });
    await t.test('Piani: richieste invalide, cambio scelta, ritorno al form e scadenza',async()=>{
      await resetLimit();
      const session=await form();
      const empty=await choosePlan({cookie:session.cookie,csrf:session.csrf,application:'mancante'});
      assert.equal(empty.status,410);
      const response=await submit(session);
      const cookie=nextCookie(response,session.cookie);
      let step=await plans(cookie);
      assert.equal((await fetch(base+'/api/piano')).status,405);
      assert.equal((await choosePlan(step,{}, {Origin:'https://example.org'})).status,403);
      assert.equal((await choosePlan(step,{csrf:'falso'})).status,419);
      assert.equal((await choosePlan(step,{candidatura:'precedente'})).status,409);
      for(const piano of ['', 'gratuito', ['completo']]){
        const override=Array.isArray(piano)?{piano:'','piano[]':'completo'}:{piano};
        assert.equal((await choosePlan(step,override)).status,422);
      }
      assert.equal((await choosePlan(step,{piano:''},{Accept:'text/html'})).status,303);
      assert.match((await plans(cookie)).html,/Scegli uno dei tre piani/);
      assert.equal((await choosePlan(step,{extra:'x'.repeat(4200)})).status,413);
      assert.equal((await fetch(base+'/api/piano',{method:'POST',headers:{Origin:base,'Content-Type':'application/json'},body:'{}'})).status,415);
      for(const [id,name,price] of [['essenziale','Essenziale',200],['completo','Completo',250],['amplifica','Amplifica',350]]){
        step=await plans(cookie);
        assert.equal((await choosePlan(step,{piano:id})).status,200);
        const html=await(await fetch(base+'/grazie/',{headers:{Cookie:cookie}})).text();
        assert.ok(html.includes(`${name} · ${price} €`));assert.match(html,/Maria Prova/);
      }
      const restored=await form('/candidati/',cookie);
      assert.match(restored.html,/value="Maria"/);assert.match(restored.html,/name="consenso" value="1" required checked/);
      const file=resolve(root,'app/storage/sessions','sess_'+cookie.split('=')[1]);
      const expired=(await readFile(file,'utf8')).replace(/s:7:"expires";i:\d+;/g,'s:7:"expires";i:1;');
      await writeFile(file,expired);
      assert.equal((await choosePlan(await plans(cookie),{csrf:restored.csrf})).status,410);
      const expiredHtml=(await plans(cookie)).html;
      assert.doesNotMatch(expiredHtml,/200 €|250 €|350 €|Maria Prova/);
      assert.match(expiredHtml,/sessione potrebbe essere scaduta/);
    });
  } finally {
    server?.kill();
    await pause(250);
    const hashes=['::1','127.0.0.1'].map(ip=>createHmac('sha256',salt).update(ip).digest('hex'));
    for(const hash of hashes)await unlink(resolve(root,'app/storage/ratelimit',hash+'.json')).catch(()=>{});
    for(const cookie of cookies)if(/^[a-zA-Z0-9,-]+$/.test(cookie))await unlink(resolve(root,'app/storage/sessions','sess_'+cookie)).catch(()=>{});
  }
});

