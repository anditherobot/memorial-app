import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                serif: ['Crimson Text', ...defaultTheme.fontFamily.serif],
            },
            colors: {
                'gray-850': '#1a1a1a',
                'gray-950': '#0a0a0a',
            },
        },
    },
    plugins: [require('@tailwindcss/aspect-ratio')],
    corePlugins: {
        // Disable unused features to reduce bundle size
        float: false,
        objectFit: false,
        objectPosition: false,
        clear: false,
        skew: false,
        caretColor: false,
        sepia: false,
        filter: false,
        backdropFilter: false,
    },
};
