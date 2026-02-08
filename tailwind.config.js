import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Sora', ...defaultTheme.fontFamily.sans],
                display: ['"Space Grotesk"', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                moka: {
                    bg: '#FAF7F2',
                    card: '#FFFDF9',
                    line: '#E7DED2',
                    ink: '#2A2018',
                    muted: '#766454',
                    primary: '#6F4E37',
                    accent: '#C89F7A',
                    success: '#2F5D50',
                    soft: '#F1E7DB',
                },
            },
            boxShadow: {
                card: '0 18px 40px -22px rgba(111, 78, 55, 0.30)',
            },
        },
    },

    plugins: [forms],
};
