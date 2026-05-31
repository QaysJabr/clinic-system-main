/**
 * Extract every top-level x-app-layout view into {dir}/partials/{stem}.blade.php
 * Skips vendor, components, any path under partials, and files that are already layout+include only.
 */
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const viewsRoot = path.join(__dirname, '..', 'resources', 'views');

const layoutRe = /^([\s\S]*?)<x-app-layout>\s*([\s\S]*?)\s*<\/x-app-layout>\s*$/;

function walk(dir, out = []) {
    if (!fs.existsSync(dir)) return out;
    for (const name of fs.readdirSync(dir)) {
        const p = path.join(dir, name);
        const rel = path.relative(viewsRoot, p).split(path.sep).join('/');
        if (name === 'vendor' || name === 'components') continue;
        if (rel.includes('/partials/')) continue;
        const st = fs.statSync(p);
        if (st.isDirectory()) walk(p, out);
        else if (name.endsWith('.blade.php')) out.push(p);
    }
    return out;
}

function isAlreadyWrapperOnly(text) {
    const t = text.trim();
    return /^\s*<x-app-layout>\s*@include\(\s*['"][^'"]+['"]\s*\)\s*<\/x-app-layout>\s*$/s.test(t);
}

function main() {
    const files = walk(viewsRoot);
    let n = 0;
    for (const filePath of files) {
        const text = fs.readFileSync(filePath, 'utf8');
        if (!text.includes('<x-app-layout>')) continue;
        if (isAlreadyWrapperOnly(text)) continue;

        const m = text.match(layoutRe);
        if (!m) {
            console.warn('skip (no single layout wrap):', path.relative(viewsRoot, filePath));
            continue;
        }

        const rel = path.relative(viewsRoot, filePath).split(path.sep).join('/');
        const dir = path.dirname(rel);
        const base = path.basename(rel, '.blade.php');
        const partialDir = path.join(viewsRoot, dir, 'partials');
        fs.mkdirSync(partialDir, { recursive: true });
        const partialFile = path.join(partialDir, `${base}.blade.php`);
        const includeName = (dir ? dir.split('/').join('.') + '.' : '') + `partials.${base}`;

        const before = m[1].replace(/\s+$/, '');
        const inner = m[2].trim();
        const pageTitleBlock =
            `@if(! empty($pageTitle ?? null))\n` +
            `    <span class="sr-only" data-spa-page-title>{{ $pageTitle }}</span>\n` +
            `@endif\n`;
        const partialBody = (before ? `${before}\n\n` : '') + pageTitleBlock + inner + '\n';
        fs.writeFileSync(partialFile, partialBody);

        const newMain = `<x-app-layout>\n    @include('${includeName}')\n</x-app-layout>\n`;
        fs.writeFileSync(filePath, newMain);
        console.log('OK', includeName);
        n++;
    }
    console.log('total', n);
}

main();
