// Vite build pipeline for the Botble monorepo.
//
// This file is the only build *runner*. It discovers every
// `platform/<type>/<name>/vite.build.mjs` at startup, imports it for its
// default-exported spec, and builds them all in parallel. Plugins, packages,
// and themes are dynamic per project — each module ships its own descriptor
// so adding or removing a module is a no-touch operation on this file.
//
// CONTRIBUTORS: to change what a single module builds, edit THAT MODULE'S
// `vite.build.mjs`. This file only needs editing when adding a new kind of
// build primitive (e.g. a new `combine` variant).
//
// Descriptor shape (the default export of each module's vite.build.mjs):
//   {
//     js?:      ('name' | { src, out })[]    // JS entries
//     sass?:    { src, out, rtl? }[]         // SCSS entries (rtl = rtlcss sibling)
//     combine?: { srcs[], out }              // legacy file concatenation
//     vendor?:  { from, to }[]               // node_modules → dist copies
//     vue?:     boolean                      // enable @vitejs/plugin-vue + Vue global
//   }
//
// Output contract (must remain stable — file names and paths match the
// previous Laravel-Mix layout byte-for-byte):
//   public/<distSubpath>/js/<name>.js
//   public/<distSubpath>/css/<name>.css
//   (production) platform/<type>/<name>/public/js/<name>.js
//   (production) platform/<type>/<name>/public/css/<name>.css

import { build as viteBuild } from 'vite'
import vuePlugin from '@vitejs/plugin-vue'
import * as sass from 'sass'
import postcss from 'postcss'
import autoprefixer from 'autoprefixer'
import cssnano from 'cssnano'
import rtlcss from 'rtlcss'
import { glob } from 'node:fs/promises'
import { dirname, resolve, join, relative } from 'node:path'
import { fileURLToPath, pathToFileURL } from 'node:url'
import { mkdir, writeFile, copyFile, readFile } from 'node:fs/promises'

const REPO_ROOT = dirname(fileURLToPath(import.meta.url))
const IS_PROD = process.env.NODE_ENV === 'production'

// Deprecations come from legacy upstream SCSS (Bootstrap 4/5 + vendor
// stylesheets). Upstream fixes are out of scope for this build config.
const SASS_SILENCED = ['import', 'global-builtin', 'color-functions', 'if-function', 'legacy-js-api']

// ============================================================================
// Main — discover modules, build in parallel, report failures.
// ============================================================================
async function main() {
    const packages = await discoverPackages()
    const label = IS_PROD ? 'production' : 'development'
    console.log(`[vite-build] ${label} build across ${packages.length} packages`)
    const startedAt = Date.now()

    const results = await Promise.allSettled(packages.map(buildPackage))
    const failed = results
        .map((r, i) => (r.status === 'rejected' ? { dir: packages[i].dir, error: r.reason } : null))
        .filter(Boolean)

    const ms = Date.now() - startedAt
    if (failed.length) {
        console.error(`\n[vite-build] ${failed.length} package(s) failed:\n`)
        for (const { dir, error } of failed) {
            console.error(`  x ${dir}`)
            console.error(`    ${error?.stack || error?.message || error}`)
        }
        process.exit(1)
    }
    console.log(`[vite-build] all ${packages.length} packages built in ${ms}ms`)
}

// ============================================================================
// Discovery — glob every per-module descriptor, import it for its default
// export, and enrich with the `dir` and derived dist path.
// ============================================================================
async function discoverPackages() {
    const found = []
    for await (const file of glob('platform/*/*/vite.build.mjs', { cwd: REPO_ROOT })) {
        // Normalize to POSIX separators so distSubpathFor()'s regex works on
        // Windows (where glob/dirname yield backslashes).
        const dir = dirname(file).replace(/\\/g, '/')
        const moduleUrl = pathToFileURL(resolve(REPO_ROOT, file)).href
        const spec = (await import(moduleUrl)).default
        if (!spec || typeof spec !== 'object') {
            throw new Error(`[vite-build] ${file} must \`export default\` a spec object`)
        }
        found.push({ ...spec, dir })
    }
    found.sort((a, b) => a.dir.localeCompare(b.dir))
    return found
}

// Map `platform/<type>/<name>` → dist subpath under `public/`.
// Themes land in `themes/<name>`; everything else goes to the
// `vendor/core/<type>/<name>` tree the Botble CMS expects.
function distSubpathFor(dir) {
    const m = dir.match(/^platform\/(core|packages|plugins|themes)\/(.+)$/)
    if (!m) throw new Error(`[vite-build] unexpected package dir: ${dir}`)
    const [, type, name] = m
    return type === 'themes' ? `themes/${name}` : `vendor/core/${type}/${name}`
}

// ============================================================================
// Per-package driver
// ============================================================================
async function buildPackage(spec) {
    const source = resolve(REPO_ROOT, spec.dir)
    const dist = resolve(REPO_ROOT, 'public', distSubpathFor(spec.dir))
    const pluginPublic = resolve(source, 'public')
    const displayName = spec.dir.replace(/^platform\//, '')
    const ctx = { source, dist, pluginPublic, displayName }

    const startedAt = Date.now()

    await copyVendor(spec.vendor, ctx)
    await Promise.all([
        buildAllJs(spec, ctx),
        buildAllSass(spec, ctx),
        buildCombine(spec, ctx),
    ])
    if (IS_PROD) await copyToPluginPublic(spec, ctx)

    console.log(`[${displayName}] ${IS_PROD ? 'production' : 'development'} build in ${Date.now() - startedAt}ms`)
}

// ============================================================================
// JS builds — one Vite lib/IIFE invocation per entry so Rollup never shares
// chunks between entries. Each entry becomes a standalone self-executing
// script loadable by a plain <script> tag in a Blade template.
// ============================================================================
async function buildAllJs(spec, ctx) {
    if (!spec.js?.length) return
    const entries = spec.js.map((item) => {
        if (typeof item === 'string') {
            return {
                name: item,
                src: join(ctx.source, 'resources/js', `${item}.js`),
                out: `${item}.js`,
            }
        }
        // Explicit form for nested outputs (e.g. 'dashboard/script.js').
        return {
            name: item.out.replace(/\.js$/, '').replace(/\//g, '-'),
            src: join(ctx.source, item.src),
            out: item.out,
        }
    })
    await Promise.all(entries.map((e) => buildJsEntry(e, spec, ctx)))
}

async function buildJsEntry(entry, spec, ctx) {
    const outDir = resolve(ctx.dist, 'js', dirname(entry.out))
    const fileName = entry.out.split('/').pop()

    await viteBuild({
        configFile: false,
        logLevel: 'warn',
        // Vite's default publicDir is <root>/public — our outDir lives inside
        // that tree, so mirroring publicDir would recurse infinitely.
        publicDir: false,
        plugins: [
            rewriteTopLevelRequirePlugin,
            ...(spec.vue ? [vuePlugin(), inlineSfcCssPlugin] : []),
        ],
        // When Vue is enabled, teach Rollup to resolve `.vue` extension
        // implicitly — legacy code does `import X from './Component'` without
        // the extension, relying on webpack's resolve.extensions including .vue.
        resolve: spec.vue
            ? { extensions: ['.vue', '.mjs', '.js', '.mts', '.ts', '.jsx', '.tsx', '.json'] }
            : undefined,
        build: {
            emptyOutDir: false,
            outDir,
            manifest: false,
            minify: IS_PROD ? 'esbuild' : false,
            sourcemap: !IS_PROD,
            target: 'es2017',
            // Allow bare `require()` in ESM source. Legacy Botble code uses
            // it in plain .js entries (e.g. core/base/app.js does
            // `window._ = require('lodash')`) and also inside Vue SFC
            // <script> blocks (e.g. ecommerce DiscountComponent.vue does
            // `const moment = require('moment')`). Leaving `include` unset
            // means the plugin processes every module — required because
            // @vitejs/plugin-vue emits SFC scripts as virtual module IDs
            // (`path.vue?vue&type=script...`) that don't match simple path
            // regexes reliably.
            commonjsOptions: {
                transformMixedEsModules: true,
            },
            lib: {
                entry: entry.src,
                formats: ['iife'],
                // Rollup's IIFE format requires a non-empty global name even
                // when nothing reads it.
                name: `__botble_${entry.name.replace(/[^a-zA-Z0-9]+/g, '_')}`,
                fileName: () => fileName,
                // Vite 6+ always computes a cssFileName in lib mode even when
                // no CSS is produced; omitting this crashes vite:css-post.
                cssFileName: fileName.replace(/\.js$/, ''),
            },
            rollupOptions: {
                // Silence Rolldown's advisory PLUGIN_TIMINGS perf hints — the
                // flagged plugins are either internal (vite:resolve-builtin)
                // or known-acceptable cost (botble:rewrite-top-level-require,
                // vite:vue). Build is healthy; warnings are noise.
                onwarn(warning, warn) {
                    if (warning.code === 'PLUGIN_TIMINGS') return
                    warn(warning)
                },
                // Externals:
                // - `vue`: global injected by the copied vue.global lib
                // - `jquery`: bare `$` that already exists on the page
                // - Absolute URLs under `/vendor/` or `/themes/`: Laravel-served
                //   static assets referenced from SFC templates like
                //   `<img src="/vendor/core/plugins/ecommerce/images/foo.svg">`.
                //   Rollup would otherwise try to resolve them as JS imports.
                external: (id) => {
                    if (id === 'jquery') return true
                    if (spec.vue && id === 'vue') return true
                    if (/^\/(?:vendor|themes|storage)\//.test(id)) return true
                    return false
                },
                output: {
                    extend: true,
                    // Globals resolution: map known externals to their window
                    // names, and emit the runtime URL verbatim for externalised
                    // asset paths (so `<img src="...">` keeps working).
                    globals: (id) => {
                        if (id === 'vue') return 'Vue'
                        if (id === 'jquery') return '$'
                        if (/^\/(?:vendor|themes|storage)\//.test(id)) return JSON.stringify(id)
                        return ''
                    },
                },
            },
        },
    })
}

// Rollup plugin: rewrite `const X = require('Y')` at the top level of legacy
// Botble source files (including Vue SFC <script> blocks) to
// `import X from 'Y'`. webpack/mix used Babel to transparently convert
// CommonJS interop, but Rollup's ESM pipeline leaves these calls in place,
// so they throw "require is not defined" at runtime in the browser.
//
// Runs with `enforce: 'pre'` so the rewrite happens before @vitejs/plugin-vue
// compiles the SFC. The regex only matches statements at column 0 (optionally
// preceded by whitespace on that line), which is where legacy top-level
// require() calls always live.
const rewriteTopLevelRequirePlugin = {
    name: 'botble:rewrite-top-level-require',
    enforce: 'pre',
    transform(code, id) {
        // Only touch first-party source under platform/ (or standalone plugin
        // repos whose source sits at the project root). Never rewrite
        // node_modules — dependencies ship legitimate CJS we must not touch.
        if (id.includes('/node_modules/')) return null
        if (!/\.(?:js|vue|mjs|ts)(?:\?|$)/.test(id)) return null
        if (!/require\s*\(/.test(code)) return null

        let out = code

        // Both patterns REQUIRE column-zero (no leading whitespace) to ensure
        // we only rewrite requires at the module top level. A require() inside
        // a try/catch, function, or any nested block would be at a non-zero
        // indent in every hand-written source — rewriting it to `import` would
        // produce a syntax error because ESM imports must sit at module top.

        // Pattern 1: `const/let/var X = require('Y')` → `import X from 'Y'`
        out = out.replace(
            /^(?:const|let|var)\s+(\w+)\s*=\s*require\s*\(\s*(['"][^'"]+['"])\s*\)\s*;?$/gm,
            (_m, name, path) => `import ${name} from ${path}`,
        )

        // Pattern 2: bare `require('Y')` (side-effect import) → `import 'Y'`.
        // Covers top-level jQuery plugin loads like `require('./form')`.
        out = out.replace(
            /^require\s*\(\s*(['"][^'"]+['"])\s*\)\s*;?$/gm,
            (_m, path) => `import ${path}`,
        )

        return out === code ? null : { code: out, map: null }
    },
}

// Rollup plugin: merge any extracted CSS (typically from Vue SFC <style>
// blocks) into the JS bundle as a runtime style injection. Matches mix's
// behaviour of inlining SFC styles into the per-entry JS so Blade templates
// don't need to load an extra <link> per component.
const inlineSfcCssPlugin = {
    name: 'botble:inline-sfc-css',
    enforce: 'post',
    generateBundle(_options, bundle) {
        const cssAssets = []
        const jsChunks = []
        for (const [name, item] of Object.entries(bundle)) {
            if (item.type === 'asset' && name.endsWith('.css')) cssAssets.push({ name, item })
            else if (item.type === 'chunk' && name.endsWith('.js')) jsChunks.push(item)
        }
        if (!cssAssets.length || !jsChunks.length) return
        const css = cssAssets.map((c) => c.item.source).join('\n')
        const injector =
            `(function(){if(typeof document==='undefined')return;` +
            `var s=document.createElement('style');` +
            `s.textContent=${JSON.stringify(css)};` +
            `document.head.appendChild(s);})();`
        jsChunks[0].code = injector + jsChunks[0].code
        // Drop the CSS asset so Vite doesn't write it to disk.
        for (const c of cssAssets) delete bundle[c.name]
    },
}

// ============================================================================
// Sass builds — compile via the modern Sass JS API, run through postcss
// (autoprefixer + cssnano in prod), optionally rtlcss a sibling.
// ============================================================================
async function buildAllSass(spec, ctx) {
    if (!spec.sass?.length) return
    await Promise.all(spec.sass.map((entry) => buildSassEntry(entry, ctx)))
}

async function buildSassEntry(entry, ctx) {
    const srcPath = resolve(ctx.source, entry.src)
    const outPath = resolve(ctx.dist, 'css', entry.out)
    const outDir = dirname(outPath)

    const result = sass.compile(srcPath, {
        style: 'expanded', // cssnano handles minification below
        sourceMap: !IS_PROD,
        loadPaths: [resolve(REPO_ROOT, 'node_modules'), resolve(ctx.source, 'resources/sass')],
        silenceDeprecations: SASS_SILENCED,
    })

    const plugins = [autoprefixer]
    if (IS_PROD) plugins.push(cssnano({ preset: 'default' }))
    const processed = await postcss(plugins).process(result.css, { from: undefined, to: outPath })

    await mkdir(outDir, { recursive: true })
    await writeFile(outPath, processed.css)

    if (entry.rtl) {
        await writeFile(resolve(outDir, entry.rtl), rtlcss.process(processed.css))
    }
}

// ============================================================================
// File concatenation — legacy jQuery plugins that mutate globals and can't
// be imported as ES modules. Equivalent to Laravel Mix's `mix.combine()`.
// ============================================================================
async function buildCombine(spec, ctx) {
    if (!spec.combine) return
    const { srcs, out } = spec.combine
    const outPath = resolve(ctx.dist, out)

    const contents = await Promise.all(srcs.map((s) => readFile(resolve(ctx.source, s), 'utf8')))
    let combined = contents.join('\n')

    if (IS_PROD) {
        const esbuild = await import('esbuild')
        combined = (await esbuild.transform(combined, { minify: true, loader: 'js' })).code
    }

    await mkdir(dirname(outPath), { recursive: true })
    await writeFile(outPath, combined)
}

// ============================================================================
// Vendor file copies (node_modules → dist)
// ============================================================================
async function copyVendor(vendor, ctx) {
    if (!vendor?.length) return
    const tasks = vendor.map(({ from, to }) => ({
        from: resolve(REPO_ROOT, 'node_modules', from),
        to: resolve(ctx.dist, to),
    }))
    await ensureDirsAndCopy(tasks)
}

// ============================================================================
// Plugin-public mirroring (production only) — ships compiled assets inside
// each module's own public/ folder. Required by Envato packaging contract.
// ============================================================================
async function copyToPluginPublic(spec, ctx) {
    const tasks = []

    for (const item of spec.js || []) {
        const out = typeof item === 'string' ? `${item}.js` : item.out
        tasks.push({
            from: resolve(ctx.dist, 'js', out),
            to: resolve(ctx.pluginPublic, 'js', out),
        })
    }
    for (const entry of spec.sass || []) {
        tasks.push({
            from: resolve(ctx.dist, 'css', entry.out),
            to: resolve(ctx.pluginPublic, 'css', entry.out),
        })
        if (entry.rtl) {
            const rtlRel = join(dirname(entry.out), entry.rtl)
            tasks.push({
                from: resolve(ctx.dist, 'css', rtlRel),
                to: resolve(ctx.pluginPublic, 'css', rtlRel),
            })
        }
    }
    if (spec.combine) {
        tasks.push({
            from: resolve(ctx.dist, spec.combine.out),
            to: resolve(ctx.pluginPublic, spec.combine.out),
        })
    }
    for (const v of spec.vendor || []) {
        tasks.push({
            from: resolve(ctx.dist, v.to),
            to: resolve(ctx.pluginPublic, v.to),
        })
    }

    await ensureDirsAndCopy(tasks)
}

async function ensureDirsAndCopy(tasks) {
    const dirs = new Set(tasks.map((t) => dirname(t.to)))
    await Promise.all([...dirs].map((d) => mkdir(d, { recursive: true })))
    await Promise.all(tasks.map(({ from, to }) => copyFile(from, to)))
}

main().catch((err) => {
    console.error('[vite-build] orchestrator crashed:', err)
    process.exit(1)
})
