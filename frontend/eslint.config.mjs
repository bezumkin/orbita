// @ts-check
import withNuxt from './.nuxt/eslint.config.mjs'

export default withNuxt({
  rules: {
    'no-console': 0,
    'no-new': 0,
    'no-empty': 0,
    'vue/no-v-html': 0,
    'vue/multi-word-component-names': 0,
    'vue/no-v-text-v-html-on-component': 0,
    'vue/component-name-in-template-casing': ['error', 'PascalCase', {registeredComponentsOnly: false}],
    '@typescript-eslint/no-this-alias': 0,
    '@typescript-eslint/no-unused-vars': 0,
    '@typescript-eslint/ban-ts-comment': 0,
    '@typescript-eslint/no-explicit-any': 0,
    '@typescript-eslint/no-dynamic-delete': 0,
    '@stylistic/object-curly-spacing': ['error', 'never'],
    'brace-style': ['error', '1tbs'],
    'vue/object-curly-spacing': 0,
    'vue/singleline-html-element-content-newline': 1,
    'vue/max-attributes-per-line': ['error', {
      singleline: 100,
      multiline: 1,
    }],
  },
})
