/** @type {import('tailwindcss').Config} */
export default {
  content: ['./resources/**/*.blade.php', './app/Livewire/**/*.php'],
  theme: {
    extend: {
      colors: {
        teal: {
          50: '#f2faf9',
          100: '#e6f6f3',
          200: '#cbeee6',
          300: '#9fe0d3',
          400: '#66cbbd',
          500: '#2aa79f',
          600: '#1f8b80',
          700: '#16655f',
          800: '#104e48',
          900: '#08332a',
        },
        slate: {
          50: '#f8fafb',
          100: '#f1f5f9',
          200: '#e2e8f0',
          300: '#cbd5e1',
          400: '#94a3b8',
          500: '#64748b',
          600: '#475569',
          700: '#364152',
          800: '#26303b',
          900: '#0f1724',
        },
        offwhite: '#fbfbfa',
        accentSoftRose: '#f9e9ea',
        accentSoftGold: '#fbf6ea'
      },
    },
  },
  plugins: [],
};
