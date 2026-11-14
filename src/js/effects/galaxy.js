/*!
 * Galaxy, da React Bits — Copyright (c) 2026 David Haz.
 * Fonte: https://github.com/DavidHDev/react-bits/blob/main/src/content/Backgrounds/Galaxy/Galaxy.jsx
 * Consultato il 14 novembre 2026. Shader originale, adattatore vanilla/OGL per Altrove.
 * Licenza MIT + Commons Clause: testo completo in /assets/licenses/react-bits.txt.
 * Uso integrato nel sito; nessuna distribuzione del componente come prodotto autonomo.
 */
import { Renderer, Program, Mesh, Triangle } from 'ogl';

const MAX_LAYERS = 8;
const MAX_COLORS = 4;

const VERTEX = `#version 300 es
in vec2 position;
void main() {
  gl_Position = vec4(position, 0.0, 1.0);
}`;

const FRAGMENT = `#version 300 es
precision highp float;
precision highp int;
#define PALETTE
uniform float uStatic;
uniform float uTime;
uniform vec2 uResolution;
uniform vec2 uFocal;
uniform vec2 uRotation;
uniform float uGloss;
uniform float uDepth;
uniform float uDensity;
uniform float uHueShift;
uniform float uSpeed;
uniform vec2 uMouse;
uniform float uStarGlow;
uniform float uFlareGain;
uniform float uSquash;
uniform float uSaturation;
uniform float uMouseRepulsion;
uniform float uTwinkleIntensity;
uniform float uRotationAngle;
uniform float uRepulsionStrength;
uniform float uMouseActiveFactor;
uniform float uAutoCenterRepulsion;
uniform int uLayers;
uniform vec3 uColors[${MAX_COLORS}];
uniform int uColorCount;
uniform float uWarp;
uniform float uReveal;
uniform float uMode;
uniform vec3 uBackground;
uniform float uOpacity;
out vec4 outColor;

#define STAR_COLOR_CUTOFF 0.2
#define MAT45 mat2(0.7071, -0.7071, 0.7071, 0.7071)
#define PERIOD 3.0

float Hash21(vec2 p) {
  p = fract(p * vec2(123.34, 456.21));
  p += dot(p, p + 45.32);
  return fract(p.x * p.y);
}

float tri(float x) {
  return abs(fract(x) * 2.0 - 1.0);
}

float tris(float x) {
  float t = fract(x);
  return 1.0 - smoothstep(0.0, 1.0, abs(2.0 * t - 1.0));
}

float trisn(float x) {
  float t = fract(x);
  return 2.0 * (1.0 - smoothstep(0.0, 1.0, abs(2.0 * t - 1.0))) - 1.0;
}

vec3 hsv2rgb(vec3 c) {
  vec4 K = vec4(1.0, 2.0 / 3.0, 1.0 / 3.0, 3.0);
  vec3 p = abs(fract(c.xxx + K.xyz) * 6.0 - K.www);
  return c.z * mix(K.xxx, clamp(p - K.xxx, 0.0, 1.0), c.y);
}

float Star(vec2 uv, float flare, vec2 radial, float invReach) {
#ifdef WARP
  float along = dot(uv, radial);
  float d = sqrt(max(dot(uv, uv) - uSquash * along * along, 0.0));
#else
  float d = length(uv);
#endif
  float m = uStarGlow / d;
  float rays = smoothstep(0.0, 1.0, 1.0 - abs(uv.x * uv.y * 1000.0));
  m += rays * flare * uFlareGain;
  uv *= MAT45;
  rays = smoothstep(0.0, 1.0, 1.0 - abs(uv.x * uv.y * 1000.0));
  m += rays * 0.3 * flare * uFlareGain;
  m *= smoothstep(1.0, 0.2, d * invReach);
#ifdef WARP
  m *= mix(1.0, smoothstep(1.0, 0.55, length(uv) * invReach), min(uWarp * 4.0, 1.0));
#endif
  return m;
}

vec3 StarLayer(vec2 uv, vec2 radial, vec2 repelAt, float pushCells, float spread) {
  vec3 col = vec3(0.0);
  vec2 gv = fract(uv) - 0.5;
  vec2 id = floor(uv);
  for (int y = -1; y <= 1; y++) {
    for (int x = -1; x <= 1; x++) {
      vec2 offset = vec2(float(x), float(y));
      vec2 si = id + offset;
      float seed = Hash21(si);
      float size = fract(seed * 345.32);
      float glossLocal = tri(uGloss / (PERIOD * seed + 1.0));
      float flareSize = smoothstep(0.9, 1.0, size) * glossLocal;

#ifdef PALETTE
      float pick = Hash21(si + 7.0) * float(uColorCount);
      vec3 base = uColors[int(min(floor(pick), float(uColorCount - 1)))];
#else
      float red = smoothstep(STAR_COLOR_CUTOFF, 1.0, Hash21(si + 1.0)) + STAR_COLOR_CUTOFF;
      float blu = smoothstep(STAR_COLOR_CUTOFF, 1.0, Hash21(si + 3.0)) + STAR_COLOR_CUTOFF;
      float grn = min(red, blu) * seed;
      vec3 base = vec3(red, grn, blu);
      float hue = atan(base.g - base.r, base.b - base.r) / (2.0 * 3.14159) + 0.5;
      hue = fract(hue + uHueShift / 360.0);
      float sat = length(base - vec3(dot(base, vec3(0.299, 0.587, 0.114)))) * uSaturation;
      float val = max(max(base.r, base.g), base.b);
      base = hsv2rgb(vec3(hue, sat, val));
#endif

      vec2 pad = vec2(tris(seed * 34.0 + uTime * uSpeed / 10.0), tris(seed * 38.0 + uTime * uSpeed / 30.0)) - 0.5;
      vec2 shift = vec2(0.0);
      float invReach = 1.0;
#ifdef REPEL
      vec2 away = si + 0.5 + pad - repelAt;
      float dist = length(away);
      float q = dist * spread;
      float cells = pushCells * exp(-q * q);
      float moved = cells * inversesqrt(1.0 + cells * cells * 2.78);
      shift = away * (moved / max(dist, 0.0001));
      invReach = 1.0 / (1.0 - moved);
#endif
      float star = Star(gv - offset - pad - shift, flareSize, radial, invReach);
      float twinkle = trisn(uTime * uSpeed + seed * 6.2831) * 0.5 + 1.0;
      twinkle = mix(1.0, twinkle, uTwinkleIntensity);
      col += star * twinkle * size * base;
    }
  }
  return col;
}

void main() {
  vec2 focalPx = uFocal * uResolution;
  vec2 uv = (gl_FragCoord.xy - focalPx) / uResolution.y;
  vec2 mouseNorm = uMouse - vec2(0.5);
  // Disturbo di sintonizzazione: minimo e continuo, mai flash a schermo intero.
  float grain = Hash21(floor(gl_FragCoord.xy * 0.65) + floor(uTime * 12.0));
  uv.x += sin(uv.y * 90.0 + uTime * 2.0) * uStatic * 0.0018;

  vec2 repelAt = vec2(0.0);
  float strength = 0.0;
  float push = 0.0;
  if (uAutoCenterRepulsion > 0.0) {
    strength = uAutoCenterRepulsion;
    push = strength * 0.05;
  } else if (uMouseRepulsion > 0.5) {
    repelAt = (uMouse * uResolution - focalPx) / uResolution.y;
    strength = max(uRepulsionStrength, 0.0);
    push = strength * 0.05 * uMouseActiveFactor;
  } else {
    uv += mouseNorm * 0.1 * uMouseActiveFactor;
  }
  float radius = 0.16 + 0.04 * strength;

  vec2 radial = length(uv) > 0.0001 ? normalize(uv) : vec2(1.0, 0.0);
  float angle = uRotationAngle;
  uv = mat2(cos(angle), -sin(angle), sin(angle), cos(angle)) * uv;
  uv = mat2(uRotation.x, -uRotation.y, uRotation.y, uRotation.x) * uv;
  radial = mat2(cos(angle), -sin(angle), sin(angle), cos(angle)) * radial;
  radial = mat2(uRotation.x, -uRotation.y, uRotation.y, uRotation.x) * radial;
  repelAt = mat2(cos(angle), -sin(angle), sin(angle), cos(angle)) * repelAt;
  repelAt = mat2(uRotation.x, -uRotation.y, uRotation.y, uRotation.x) * repelAt;

  vec3 col = vec3(0.0);
  float layers = float(uLayers);
  for (int k = 0; k < ${MAX_LAYERS}; k++) {
    if (k >= uLayers) break;
    float i = float(k) / layers;
    float depth = fract(i + uDepth);
    float scale = mix(20.0 * uDensity, 0.5 * uDensity, depth);
    float fade = depth * smoothstep(1.0, 0.9, depth);
    vec2 offset = vec2(i * 453.32);
    col += StarLayer(uv * scale + offset, radial, repelAt * scale + offset, push * scale, 1.0 / (scale * radius)) * fade;
  }
  col *= uReveal * (1.0 - uStatic * grain * 0.22);

  if (uMode > 1.5) {
    vec3 lit = col * smoothstep(0.0, 0.3, length(col));
    float energy = max(max(lit.r, lit.g), lit.b);
    vec3 tint = lit / max(energy, 0.0001);
    vec3 ink = tint * (0.16 / max(dot(tint, vec3(0.2126, 0.7152, 0.0722)), 0.16));
    float coverage = pow(min(energy * 1.3, 1.0), 1.3) * 0.92 * uOpacity;
    outColor = uMode > 2.5 ? vec4(ink * coverage, coverage) : vec4(mix(uBackground, ink, coverage), 1.0);
  } else if (uMode > 0.5) {
    float alpha = min(smoothstep(0.0, 0.3, length(col)), 1.0) * uOpacity;
    alpha *= smoothstep(0.0, 0.44, gl_FragCoord.y / uResolution.y);
    outColor = vec4(col * alpha, alpha);
  } else {
    outColor = vec4(mix(uBackground, col, uOpacity), 1.0);
  }
}`;


const clamp = (value, min, max) => Math.min(max, Math.max(min, Number.isFinite(value) ? value : min));

/**
 * Unico proprietario del canvas; nessun RAF, timer o listener globale.
 * Il controller centrale gestisce idle, visibilità, movimento ridotto e scroll.
 * time e delta sono secondi, pointer è normalizzato [-1, 1], lock=1 è segnale stabile.
 */
export function createGalaxy(canvas, { mobile = false } = {}) {
  if (!canvas || !canvas.getContext || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return null;
  // OGL setSize scrive anche lo stile inline: il layout resta di proprietà del CSS.
  const cssWidth = canvas.style.width;
  const cssHeight = canvas.style.height;
  const initialWidth = Math.max(1, canvas.clientWidth);
  const initialHeight = Math.max(1, canvas.clientHeight);
  let renderer;
  try {
    // Lo shader ufficiale corrente usa GLSL ES 3.00: il fallback CSS copre WebGL1.
    const context = canvas.getContext('webgl2', { alpha: true, premultipliedAlpha: true, antialias: false, depth: false, stencil: false, powerPreference: 'low-power' });
    if (!context) return null;
    renderer = new Renderer({ canvas, width: initialWidth, height: initialHeight, dpr: Math.min(window.devicePixelRatio || 1, mobile ? 1.5 : 2), alpha: true, premultipliedAlpha: true, antialias: false, depth: false, stencil: false, powerPreference: 'low-power', webgl: 2 });
    canvas.style.width = cssWidth;
    canvas.style.height = cssHeight;
  } catch { return null; }
  const gl = renderer.gl;
  const uniforms = {
    uTime: { value: 0 }, uResolution: { value: [1, 1] }, uFocal: { value: [0.5, 0.5] },
    uRotation: { value: [1, 0] }, uGloss: { value: 0 }, uDepth: { value: 0.22 },
    uDensity: { value: 0.8 }, uHueShift: { value: 0 }, uSpeed: { value: 0.15 },
    uMouse: { value: [0.5, 0.5] }, uStarGlow: { value: 0.009 }, uFlareGain: { value: 0.085 },
    uSquash: { value: 0 }, uSaturation: { value: 0.15 }, uMouseRepulsion: { value: 0 },
    uTwinkleIntensity: { value: 0.08 }, uRotationAngle: { value: 0 },
    uRepulsionStrength: { value: 0 }, uMouseActiveFactor: { value: mobile ? 0 : 0.12 },
    uAutoCenterRepulsion: { value: 0 }, uLayers: { value: mobile ? 2 : 3 },
    // OGL riconosce gli array GLSL con Array.isArray; un Float32Array viene scartato.
    uColors: { value: [251/255,239/255,227/255,185/255,171/255,234/255,0,0,0,0,0,0] },
    uColorCount: { value: 2 }, uWarp: { value: 0 }, uReveal: { value: 1 },
    uMode: { value: 1 }, uBackground: { value: [17/255,12/255,46/255] },
    uOpacity: { value: 0.64 }, uStatic: { value: 1 },
  };
  const geometry = new Triangle(gl);
  const program = new Program(gl, { vertex: VERTEX, fragment: FRAGMENT, uniforms, depthTest: false, depthWrite: false, cullFace: null });
  if (!gl.getProgramParameter(program.program, gl.LINK_STATUS)) {
    program.remove(); geometry.remove(); return null;
  }
  const mesh = new Mesh(gl, { geometry, program });
  let disposed = false;
  let elapsed = 0;
  function resize(width = canvas.clientWidth, height = canvas.clientHeight) {
    if (disposed) return;
    renderer.dpr = Math.min(window.devicePixelRatio || 1, mobile ? 1.5 : 2);
    renderer.setSize(Math.max(1, Math.round(width)), Math.max(1, Math.round(height)));
    canvas.style.width = cssWidth;
    canvas.style.height = cssHeight;
    uniforms.uResolution.value = [canvas.width, canvas.height];
  }
  function render(time = 0, delta = 0, state = {}) {
    if (disposed || gl.isContextLost()) return;
    elapsed += clamp(delta, 0, 0.05);
    uniforms.uTime.value = elapsed;
    uniforms.uGloss.value = elapsed * 0.008;
    uniforms.uDepth.value = (0.22 + elapsed * 0.0012) % 1;
    uniforms.uRotationAngle.value = elapsed * 0.007;
    uniforms.uStatic.value = 1 - clamp(state.lock ?? 0, 0, 1);
    const follow = 1 - Math.exp(-clamp(delta, 0, 0.05) / 0.35);
    const x = mobile ? 0.5 : (clamp(state.pointer?.x ?? 0, -1, 1) + 1) / 2;
    const y = mobile ? 0.5 : (1 - clamp(state.pointer?.y ?? 0, -1, 1)) / 2;
    uniforms.uMouse.value[0] += (x - uniforms.uMouse.value[0]) * follow;
    uniforms.uMouse.value[1] += (y - uniforms.uMouse.value[1]) * follow;
    uniforms.uOpacity.value = 0.64 * (1 - clamp(state.scroll ?? 0, 0, 1) * 0.85);
    renderer.render({ scene: mesh });
  }
  function dispose() {
    if (disposed) return;
    disposed = true;
    program.remove(); geometry.remove();
    // Il controller può riattivare gli effetti sullo stesso canvas dopo un cambio
    // della preferenza di movimento: non perdere un contesto ancora connesso.
    if (!canvas.isConnected) gl.getExtension('WEBGL_lose_context')?.loseContext();
  }
  resize();
  render(0, 0);
  return { render, resize, dispose };
}
