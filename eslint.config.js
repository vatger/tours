import js from '@eslint/js';
import prettier from 'eslint-config-prettier';
import vue from 'eslint-plugin-vue';
import globals from 'globals';
import typescript from 'typescript-eslint';

/** @type {import('eslint').Linter.Config[]} */
export default [
    js.configs.recommended,
    ...typescript.configs.recommended,
    {
        ...vue.configs.flat.recommended,
        ...vue.configs['flat/strongly-recommended'],
        ...vue.configs['flat/essential'],
        languageOptions: {
            globals: {
                ...globals.browser,
            },
        },
        rules: {
            'vue/match-component-import-name': 'warn',
            'vue/match-component-file-name': [
                'error',
                {
                    extensions: ['vue'],
                    shouldMatchCase: true,
                },
            ],
            'vue/component-definition-name-casing': ['error', 'PascalCase'],
            'vue/block-tag-newline': [
                'warn',
                {
                    singleline: 'always',
                    multiline: 'always',
                    maxEmptyLines: 0,
                },
            ],
            'vue/html-self-closing': [
                'error',
                {
                    html: {
                        void: 'always',
                        normal: 'never',
                        component: 'always',
                    },
                    svg: 'always',
                    math: 'always',
                },
            ],
            'vue/require-default-prop': 'off',
        },
    },
    {
        plugins: {
            '@typescript-eslint': tseslint.plugin,
        },
        languageOptions: {
            parser: tseslint.parser,
            parserOptions: {
                project: true,
            },
        },
    },
    {
        ignores: ['vendor', 'node_modules', 'public', 'bootstrap/ssr', 'tailwind.config.js'],
    },
    prettier, // Turn off all rules that might conflict with Prettier
];
