module.exports = {
  content: [
    "./**/*.html",
    "./**/*.php",
    "./**/*.js"
  ],
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        'neon-blue': '#00f2ff',
        'neon-purple': '#8a2be2',
        'neon-pink': '#ff00ff',
        'neon-green': '#00ff00',
        'lighter': 'rgba(255, 255, 255, 0.1)',
        'light': 'rgba(255, 255, 255, 0.05)',
      },
      backgroundImage: {
        'gradient-radial': 'radial-gradient(var(--tw-gradient-stops))',
      },
      boxShadow: {
        'neon': '0 0 5px theme(colors.neon-blue), 0 0 20px theme(colors.neon-purple)',
      },
    },
  },
  plugins: [],
}
