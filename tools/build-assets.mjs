import { mkdir, readFile, writeFile, copyFile } from 'node:fs/promises';
import { join } from 'node:path';
import { createHash } from 'node:crypto';
import sharp from 'sharp';
import { create as createFont } from 'fontkit';

const pages = {
  home: ['Storie oltre il visibile'],
  nde: ['Esperienze di premorte'],
  medium: ['Medianità'],
  channeling: ['Channeling'],
  mentalismo: ['Mentalismo'],
  alessia: ['Alessia Ventura'],
  candidati: ['Partecipa ad Altrove Podcast'],
  privacy: ['Informativa privacy'],
  cookie: ['Informativa cookie'],
  grazie: ['Ultimo passo: WhatsApp'],
  '404': ['Una frequenza da ritrovare'],
};
const photoOrigin = 'Fonte: fotografia fornita da Alessia Ventura, foto/web/alessia-ventura.jpg. Derivazione tecnica con Sharp: crop e conversione; nessuna generazione o modifica del soggetto. Focale 50% 30%.';
const iconOrigin = 'Fonte: design/brand/favicon.svg, simbolo Altrove disegnato in SVG. Rasterizzazione tecnica con Sharp; nessuna generazione AI.';

// Metadati incorporati anche nelle build CI, senza dipendere dalla CLI Impeccable.
function provenance(pipeline, origin) {
  return pipeline.withExifMerge({ IFD0: { ImageDescription: origin, Software: 'Altrove asset build / Sharp' } });
}

function pathText(font, text, x, baseline, size, spacing = 0) {
  const scale = size / font.unitsPerEm;
  const run = font.layout(text);
  let cursor = 0;
  return run.glyphs.map((glyph, index) => {
    if (!glyph.id && text.trim()) throw new Error('Glifo mancante nel font OG: ' + text);
    const position = run.positions[index];
    const path = '<path d="' + glyph.path.toSVG() + '" transform="translate(' + (x + cursor + position.xOffset * scale).toFixed(3) + ' ' + (baseline - position.yOffset * scale).toFixed(3) + ') scale(' + scale + ' ' + -scale + ')"/>';
    cursor += position.xAdvance * scale + spacing;
    return path;
  }).join('');
}

function measure(font, text, size, spacing = 0) {
  const run = font.layout(text);
  return run.positions.reduce((sum, position) => sum + position.xAdvance * size / font.unitsPerEm, 0) + Math.max(0, run.glyphs.length - 1) * spacing;
}

function ico(pngs) {
  const header = Buffer.alloc(6 + 16 * pngs.length);
  header.writeUInt16LE(1, 2);
  header.writeUInt16LE(pngs.length, 4);
  let offset = header.length;
  pngs.forEach(({ size, data }, index) => {
    const at = 6 + index * 16;
    header[at] = size === 256 ? 0 : size;
    header[at + 1] = size === 256 ? 0 : size;
    header.writeUInt16LE(1, at + 4);
    header.writeUInt16LE(32, at + 6);
    header.writeUInt32LE(data.length, at + 8);
    header.writeUInt32LE(offset, at + 12);
    offset += data.length;
  });
  return Buffer.concat([header, ...pngs.map(png => png.data)]);
}

export async function buildAssets(root) {
  const dirs = Object.fromEntries(['images', 'fonts', 'og', 'favicon', 'licenses'].map(name => [name, join(root, 'public/assets', name)]));
  await Promise.all(Object.values(dirs).map(directory => mkdir(directory, { recursive: true })));
  const manifest = [];
  const record = async (file, source, operation, origin = 'Fonte: ' + source + '. Trasformazione: ' + operation + '.') => {
    const data = await readFile(file);
    // Lo stesso formato sidecar usato da Impeccable per AVIF/WebP; ricreabile in CI.
    await writeFile(file + '.json', JSON.stringify({ prompt: origin, source, transformation: operation }, null, 2) + '\n');
    manifest.push({ file: file.slice(join(root, 'public').length).replaceAll('\\', '/'), source, operation, sha256: createHash('sha256').update(data).digest('hex') });
  };

  for (const [family, weights] of [['tenor-sans', [400]], ['hanken-grotesk', [400, 500, 600]]]) {
    for (const subset of ['latin', 'latin-ext']) for (const weight of weights) {
      const name = `${family}-${subset}-${weight}-normal.woff2`;
      await copyFile(join(root, 'node_modules/@fontsource', family, 'files', name), join(dirs.fonts, name));
    }
    await copyFile(join(root, 'node_modules/@fontsource', family, 'LICENSE'), join(dirs.fonts, `${family}-OFL.txt`));
  }
  await copyFile(join(root, 'tools/licenses/react-bits.txt'), join(dirs.licenses, 'react-bits.txt'));
  const oglReadme = await readFile(join(root, 'node_modules/ogl/README.md'), 'utf8');
  const license = oglReadme.slice(oglReadme.indexOf('## Unlicense'));
  if (!license.startsWith('## Unlicense')) throw new Error('Licenza OGL mancante');
  await writeFile(join(dirs.licenses, 'ogl.txt'), 'OGL 1.0.11 — Nathan Gordon\nhttps://github.com/oframe/ogl\n\n' + license);

  const photo = join(root, 'foto/web/alessia-ventura.jpg');
  const photoMeta = await sharp(photo).metadata();
  const cropHeight = Math.floor(Math.min(photoMeta.height, photoMeta.width / 0.8) / 5) * 5;
  const cropWidth = cropHeight * 4 / 5;
  const crop = { left: Math.round((photoMeta.width - cropWidth) * 0.5), top: Math.round((photoMeta.height - cropHeight) * 0.3), width: cropWidth, height: cropHeight };
  for (const [shape, sizes] of [['portrait', [480, 800, 1000]], ['square', [480, 800, 1254]]]) {
    for (const width of sizes) for (const format of ['avif', 'webp']) {
      const file = join(dirs.images, `alessia-ventura-${shape}-${width}.${format}`);
      let pipeline = sharp(photo).rotate();
      if (shape === 'portrait') pipeline = pipeline.extract(crop);
      pipeline = pipeline.resize(width, shape === 'portrait' ? width * 5 / 4 : width, { fit: 'cover', withoutEnlargement: true });
      await provenance(pipeline, photoOrigin).toFormat(format, { quality: format === 'avif' ? 52 : 82 }).toFile(file);
      await record(file, 'foto/web/alessia-ventura.jpg', `${shape}, ${width}px, focale 50% 30%, ${format}`, photoOrigin);
    }
  }

  const icon = await readFile(join(root, 'design/brand/favicon.svg'));
  await writeFile(join(dirs.favicon, 'favicon.svg'), icon);
  const iconNames = new Map([[16, 'favicon-16x16'], [32, 'favicon-32x32'], [180, 'apple-touch-icon'], [192, 'icon-192'], [512, 'icon-512']]);
  const icoPngs = [];
  for (const [size, name] of iconNames) {
    const file = join(dirs.favicon, name + '.png');
    const data = await provenance(sharp(icon).resize(size, size), iconOrigin).png().toBuffer();
    await writeFile(file, data);
    if (size <= 32) icoPngs.push({ size, data });
    await record(file, 'design/brand/favicon.svg', `Rasterizzazione ${size} × ${size}`, iconOrigin);
  }
  await writeFile(join(dirs.favicon, 'favicon.ico'), ico(icoPngs));

  const tenor = createFont(await readFile(join(dirs.fonts, 'tenor-sans-latin-400-normal.woff2')));
  if (tenor.familyName !== 'Tenor Sans') throw new Error('Font OG inatteso');
  const background = join(root, 'design/brand/og-sfondo.jpg');
  const prompt = await readFile(join(root, 'design/brand/og-sfondo.prompt.txt'), 'utf8');
  for (const [id, lines] of Object.entries(pages)) {
    const titleSize = lines.reduce((size, title) => Math.min(size, 720 / measure(tenor, title, 1)), 38);
    const brand = pathText(tenor, 'ALTROVE', 72, 285, 106, 14);
    const title = lines.map((line, index) => pathText(tenor, line, 76, 365 + index * 48, titleSize)).join('');
    const svg = Buffer.from(`<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="630"><defs><linearGradient id="shade"><stop stop-color="#110C2E" stop-opacity=".65"/><stop offset=".7" stop-color="#110C2E" stop-opacity=".45"/><stop offset="1" stop-color="#110C2E" stop-opacity=".12"/></linearGradient></defs><rect width="1200" height="630" fill="url(#shade)"/><g fill="#FBEFE3">${brand}${title}</g></svg>`);
    const file = join(dirs.og, id + '.jpg');
    const origin = 'Fonte: design/brand/og-sfondo.jpg, Higgsfield gpt_image_2_6, 14 novembre 2026. Derivazione OG 1200x630; tracciati del font reale Tenor Sans 400 OFL; titolo: ' + lines.join(' ') + '. Prompt originale: ' + prompt.trim();
    await provenance(sharp(background).resize(1200, 630, { fit: 'cover' }).composite([{ input: svg }]), origin).jpeg({ quality: 88, mozjpeg: true }).toFile(file);
    await record(file, 'design/brand/og-sfondo.jpg + Tenor Sans 400', 'OG 1200 × 630, titolo ' + lines.join(' '), origin);
  }
  await copyFile(join(dirs.og, 'home.jpg'), join(dirs.og, 'altrove.jpg'));
  await record(join(dirs.og, 'altrove.jpg'), 'public/assets/og/home.jpg', 'Alias di compatibilità');
  await writeFile(join(root, 'public/assets/asset-provenance.json'), JSON.stringify({ version: 1, sourcesDocumented: '2026-11-14', assets: manifest }, null, 2) + '\n');
}
