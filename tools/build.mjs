import { mkdir, readFile, writeFile, readdir, unlink } from 'node:fs/promises';
import { resolve, dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';
import { spawnSync } from 'node:child_process';
import { createHash } from 'node:crypto';
import { build } from 'esbuild';
import { buildAssets } from './build-assets.mjs';

const root = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const out = join(root, 'public/assets/build');
await mkdir(out, { recursive: true });
// Si eliminano solo vecchi file compilati nella directory nota, mai storage o sorgenti.
for (const file of await readdir(out)) if (/^(app\.[\w-]+\.(css|js)|chunk-[\w-]+\.js|background-[\w-]+\.js|manifest\.json|app\.tmp\.css)$/.test(file)) await unlink(join(out, file));
const cli = join(root, 'node_modules/@tailwindcss/cli/dist/index.mjs');
const result = spawnSync(process.execPath, [cli, '-i', join(root,'src/css/app.css'), '-o', join(out,'app.tmp.css'), '--minify'], { stdio: 'inherit', cwd: root });
if (result.status !== 0) process.exit(result.status || 1);
const css = await readFile(join(out,'app.tmp.css'));
const cssName = 'app.' + createHash('sha256').update(css).digest('hex').slice(0,12) + '.css';
await writeFile(join(out,cssName),css);
await unlink(join(out,'app.tmp.css'));
const js = await build({ entryPoints: { app: join(root,'src/js/app.js') }, outdir: out, bundle:true, minify:true, splitting:true, format:'esm', target:['es2022'], entryNames:'[name].[hash]', chunkNames:'chunk-[hash]', metafile:true });
const entry = Object.entries(js.metafile.outputs).find(([,value]) => value.entryPoint?.replaceAll('\\','/').endsWith('src/js/app.js'))?.[0];
if (!entry) throw new Error('Manifest JavaScript mancante');
const jsName = entry.replaceAll('\\','/').split('/').at(-1);
await writeFile(join(out,'manifest.json'), JSON.stringify({ css:'/assets/build/'+cssName, js:'/assets/build/'+jsName },null,2));
await buildAssets(root);
console.log('Build completata: CSS/JS con hash, font locali, crop responsive, favicon e OG per pagina.');
