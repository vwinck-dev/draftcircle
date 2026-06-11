import eslint from '@eslint/js';
import prettier from 'eslint-config-prettier';
import tseslint from 'typescript-eslint';
import vue from 'eslint-plugin-vue';

export default tseslint.config(
    eslint.configs.recommended,
    ...tseslint.configs.recommended,
    ...vue.configs['flat/recommended'],
    prettier,
    {
        files: ['resources/js/**/*.{ts,vue}'],
        languageOptions: {
            globals: {
                document: 'readonly',
                Element: 'readonly',
                Event: 'readonly',
                File: 'readonly',
                HTMLImageElement: 'readonly',
                HTMLInputElement: 'readonly',
                IntersectionObserver: 'readonly',
                KeyboardEvent: 'readonly',
                localStorage: 'readonly',
                location: 'readonly',
                matchMedia: 'readonly',
                route: 'readonly',
                setTimeout: 'readonly',
                URL: 'readonly',
                URLSearchParams: 'readonly',
                window: 'readonly',
            },
            parserOptions: {
                parser: tseslint.parser,
            },
        },
        rules: {
            '@typescript-eslint/no-explicit-any': 'off',
            '@typescript-eslint/no-wrapper-object-types': 'off',
            'vue/attributes-order': 'off',
            'vue/multi-word-component-names': 'off',
            'vue/no-v-html': 'off',
            'vue/no-v-text-v-html-on-component': 'off',
            'vue/require-default-prop': 'off',
        },
    },
    {
        ignores: ['public/build/**', 'node_modules/**'],
        linterOptions: {
            reportUnusedDisableDirectives: false,
        },
    },
);
