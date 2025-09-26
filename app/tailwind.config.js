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
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },
    plugins: [],
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
