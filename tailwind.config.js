const defaultTheme = require('tailwindcss/defaultTheme')

module.exports = {
	purge: [
		'./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
		'./storage/framework/views/*.php',
		'./resources/views/**/*.blade.php',
		'./resources/js/**/*.svelte',
	],

	theme: {
		extend: {
			fontFamily: {
				sans: ['Nunito', ...defaultTheme.fontFamily.sans],
			},
			colors: {
				orangered: {
					100: '#ffccba',
					400: '#fd9774',
					500: '#fb7b50',
					600: '#ff6f3e',
					900: '#ff5019',
					DEFAULT: '#ffccba',
				},
			},
			textColor: {
				primary: {
					100: '#ffccba',
					400: '#fd9774',
					500: '#fb7b50',
					600: '#ff6f3e',
					900: '#ff5019',
					DEFAULT: '#4f46e5',
				},
				secondary: '#ffed4a',
				danger: '#e3342f',
			},
		},
	},

	variants: {
		extend: {
			opacity: ['disabled'],
		},
	},

}
