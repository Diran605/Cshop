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
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                'primary-blue': '#1E88C9',
                'deep-blue': '#1565A8',
                'light-blue': '#3FA9F5',
                'app-bg': '#F4F6F8',
                'card-white': '#FFFFFF',
                'border-gray': '#E0E4E8',
                'muted-gray': '#8A94A6',
                'text-primary': '#2E3A59',
                'text-secondary': '#5F6C7B',
                'text-disabled': '#A0AEC0',
                'success': '#4CAF50',
                'warning': '#F4B740',
                'info': '#2F80ED',
                'error': '#E5533D',
                'chart-blue': '#2D9CDB',
                'chart-yellow': '#F2C94C',
                'chart-purple': '#9B51E0',
                'chart-teal': '#27AE60',
                'soft-blue': '#E3F2FD',
                'soft-yellow': '#FFF4D6',
                'soft-purple': '#F1E9FB',
                'soft-green': '#E6F4EA',
            },
        },
    },

    plugins: [forms],
};
