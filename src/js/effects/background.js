// Punto di estensione per il prompt 03. La base resta statica e leggibile.
export function prepareBackground(canvas, createEffect = null) {
  const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
  const constrained = navigator.connection?.saveData || (navigator.deviceMemory && navigator.deviceMemory < 4) || (navigator.hardwareConcurrency && navigator.hardwareConcurrency < 4);
  if (reduced.matches || constrained || typeof createEffect !== 'function') return () => {};
  let effect;
  try {
    const context = canvas.getContext('webgl2', { alpha: true, antialias: false }) || canvas.getContext('webgl', { alpha: true, antialias: false });
    if (!context) return () => {};
    effect = createEffect(canvas, context);
  } catch { return () => {}; }
  let visible = false;
  const sync = () => {
    if (visible && !document.hidden && !reduced.matches) effect.start?.();
    else effect.stop?.();
  };
  const observer = new IntersectionObserver(entries => { visible = entries[0]?.isIntersecting === true; sync(); });
  observer.observe(canvas);
  document.addEventListener('visibilitychange', sync);
  reduced.addEventListener('change', sync);
  return () => { observer.disconnect(); document.removeEventListener('visibilitychange', sync); reduced.removeEventListener('change', sync); effect.stop?.(); effect.destroy?.(); };
}

