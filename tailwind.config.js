/** @type {import('tailwindcss').Config} */
import defaultTheme from 'tailwindcss/defaultTheme'

export default {
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.{js,ts,jsx,tsx}',
    './app/Http/**/*.php',
  ],
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        factory: {
          50: '#f0f9ff',
          100: '#e0f2fe',
          500: '#0ea5e9',
          900: '#0c4a6e',
        },
      },
      fontFamily: {
        sans: ['Inter', ...defaultTheme.fontFamily.sans],
        mono: ['JetBrains Mono', ...defaultTheme.fontFamily.mono],
      },
    },
  },
  plugins: [],
}