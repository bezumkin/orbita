export default defineEventHandler((event) => {
  setHeader(event, 'content-type', 'application/text')
  setHeader(event, 'cache-control', 'max-age=3600')
  const {SITE_URL} = useRuntimeConfig().public

  return String(`
User-agent: *

Disallow: /admin
Disallow: /user
Disallow: /search
Disallow: /? 

Sitemap: ${SITE_URL}sitemap.xml
`).trim()
})
