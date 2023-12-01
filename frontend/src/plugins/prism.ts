import Prism from 'prismjs'
import loadLanguages from 'prismjs/components/index'

import 'prismjs/components/prism-bash'
import 'prismjs/components/prism-docker'
import 'prismjs/components/prism-php'
import 'prismjs/components/prism-php-extras'
import 'prismjs/components/prism-ini'
import 'prismjs/components/prism-java'
import 'prismjs/components/prism-json'
import 'prismjs/components/prism-markdown'
import 'prismjs/components/prism-nginx'
import 'prismjs/components/prism-python'
import 'prismjs/components/prism-regex'
import 'prismjs/components/prism-ruby'
import 'prismjs/components/prism-rust'
import 'prismjs/components/prism-sass'
import 'prismjs/components/prism-scss'
import 'prismjs/components/prism-sql'
import 'prismjs/components/prism-plsql'
import 'prismjs/components/prism-typescript'
import 'prismjs/components/prism-yaml'
import 'prismjs/components/prism-markup-templating'

if (process.server) {
  loadLanguages([
    'bash',
    'docker',
    'php',
    'php-extras',
    'ini',
    'java',
    'json',
    'markdown',
    'nginx',
    'python',
    'regex',
    'ruby',
    'rust',
    'sass',
    'scss',
    'sql',
    'plsql',
    'typescript',
    'yaml',
  ])
}

export default defineNuxtPlugin(() => {
  // eslint-disable-next-line import/no-named-as-default-member
  Prism.manual = true

  return {
    provide: {
      prism(code: string, language: string = 'text') {
        // eslint-disable-next-line import/no-named-as-default-member
        return language in Prism.languages ? Prism.highlight(code, Prism.languages[language], language) : code
      },
      // eslint-disable-next-line import/no-named-as-default-member
      prismLanguages: Prism.languages,
    },
  }
})
