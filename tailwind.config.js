import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],

    theme: {
        extend: {
            colors: {
                'deep-teal': '#102A2A',
                'kilat': '#19A974',
                'amber': '#F2C14E',
                'paper': '#F7F5EF',
                'kilat-dark': '#118A5D',
                'amber-dark': '#D4A238',
                'teal-soft': '#1E4040',
                'paper-dark': '#ECE8DC',
                'error': '#D9534F',
            },
            fontFamily: {
                sans: ['"Plus Jakarta Sans"', ...defaultTheme.fontFamily.sans],
                serif: ['"Literata"', ...defaultTheme.fontFamily.serif],
            },
            borderRadius: {
                'xl': '16px',
                '2xl': '20px',
                '3xl': '24px',
            },
            boxShadow: {
                // Macha strictly avoids soft blur shadows; depth is created via chunky borders.
                'none': 'none',
            },
        },
    },

    plugins: [forms],
};
