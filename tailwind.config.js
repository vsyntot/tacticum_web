module.exports = {
  content: [
    './local/templates/tacticum/**/*.php',
    './local/templates/tacticum/**/*.js',
    './local/templates/tacticum/**/*.html',
    './local/templates/tacticum/**/*.css',
  ],
  theme: {
    extend: {
      colors: {
        primary: '#0066CC',
        secondary: '#001F3F',
      },
      borderRadius: {
        none: '0px',
        sm: '4px',
        DEFAULT: '8px',
        md: '12px',
        lg: '16px',
        xl: '20px',
        '2xl': '24px',
        '3xl': '32px',
        full: '9999px',
        button: '8px',
      },
    },
  },
  plugins: [],
};
