/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./src/**/*.{php,html,js}",
  ],
  theme: {
    extend: {
      colors: {
        'spiral-blue': '#3b82f6',
        'spiral-purple': '#8b5cf6',
        'spiral-pink': '#ec4899',
        'spiral-indigo': '#6366f1',
      },
      animation: {
        'spiral-spin': 'spin 20s linear infinite',
        'fade-in': 'fadeIn 0.5s ease-in',
      },
      keyframes: {
        fadeIn: {
          '0%': { opacity: '0', transform: 'translateY(10px)' },
          '100%': { opacity: '1', transform: 'translateY(0)' },
        }
      }
    },
  },
  plugins: [],
}
