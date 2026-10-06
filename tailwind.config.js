module.exports = {
  prefix: 'tw-',
  content: ['./app/Views/**/*.php'],
  corePlugins: { preflight: false },
  darkMode: ['selector', 'html[data-mode="dark"]'],
  theme: { extend: { colors: { brand: 'var(--brand)', 'brand-dark': 'var(--brand-dark)', 'brand-soft': 'var(--brand-soft)', ink: 'var(--ink)', muted: 'var(--muted)', line: 'var(--line)', paper: 'var(--paper)', canvas: 'var(--canvas)' } } },
};
