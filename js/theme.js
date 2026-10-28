(() => {
  let mode;
  try { mode = localStorage.getItem('eateasy-theme'); } catch {}
  document.documentElement.dataset.mode = mode || (matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
})();
