import { mkdir, readFile, writeFile, readdir, unlink, copyFile } from 'node:fs/promises';
import { resolve, dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';
import { spawnSync } from 'node:child_process';
import { createHash } from 'node:crypto';
import { build } from 'esbuild';
import sharp from 'sharp';

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
const imgDir = join(root,'public/assets/img');
const ogDir = join(root,'public/assets/og');
const iconDir = join(root,'public/assets/favicon');
await Promise.all([imgDir,ogDir,iconDir].map(path=>mkdir(path,{recursive:true})));
const photo = join(root,'foto/web/alessia-ventura.jpg');
for (const width of [480,800,1254]) {
  await sharp(photo).rotate().resize(width,width).avif({quality:52}).toFile(join(imgDir,'alessia-'+width+'.avif'));
  await sharp(photo).rotate().resize(width,width).webp({quality:80}).toFile(join(imgDir,'alessia-'+width+'.webp'));
}
await sharp(photo).rotate().resize(800,800).jpeg({quality:84,mozjpeg:true}).toFile(join(imgDir,'alessia-800.jpg'));
// Asset approvati già esistenti: sola derivazione, nessuna modifica dei file di design.
await copyFile(join(root,'design/brand/favicon.svg'),join(iconDir,'favicon.svg'));
const icon = await readFile(join(iconDir,'favicon.svg'));
for (const [name,size] of [['apple-touch-icon',180],['icon-192',192],['icon-512',512]]) await sharp(icon).resize(size,size).png().toFile(join(iconDir,name+'.png'));
const png = await sharp(icon).resize(32,32).png().toBuffer();
const ico = Buffer.alloc(22);
ico.writeUInt16LE(1,2); ico.writeUInt16LE(1,4); ico[6]=32; ico[7]=32; ico.writeUInt16LE(1,10); ico.writeUInt16LE(32,12); ico.writeUInt32LE(png.length,14); ico.writeUInt32LE(22,18);
await writeFile(join(iconDir,'favicon.ico'),Buffer.concat([ico,png]));
const overlay = Buffer.from('<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="630"><rect width="1200" height="630" fill="#14151b" fill-opacity=".45"/><text x="72" y="310" fill="#f5f3ef" font-size="105" font-family="sans-serif">ALTROVE</text><text x="78" y="378" fill="#f5f3ef" font-size="34" font-family="sans-serif">Podcast · Storie oltre il visibile</text></svg>');
await sharp(join(root,'design/brand/og-sfondo.jpg')).resize(1200,630,{fit:'cover'}).composite([{input:overlay}]).jpeg({quality:85,mozjpeg:true}).toFile(join(ogDir,'altrove.jpg'));
console.log('Build completata: CSS/JS con hash, immagini responsive, favicon e OG.');

