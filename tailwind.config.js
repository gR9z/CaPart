/** @type {import("tailwindcss").Config} */
module.exports = {
  content: [
    "./assets/**/*.js",
    "./templates/**/*.html.twig",
  ],
  theme: {
    extend: {
      colors: {
        'custom-pink': '#fd00c5',
      },
    },
  },
  variants: {
    extend: {
      ringColor: ['focus'],
      borderColor: ['focus'],
    },
  },
  plugins: [
    require('flowbite/plugin')
  ]
}