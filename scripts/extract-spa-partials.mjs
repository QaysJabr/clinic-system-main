/**
 * Split x-app-layout views into partials/content for SPA AJAX responses.
 * Run: node scripts/extract-spa-partials.mjs
 */
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const root = path.join(__dirname, '..');

/** @type {{ dir: string, file: string, include: string }[]} */
const tasks = [
    { dir: 'doctors', file: 'index.blade.php', include: 'doctors.partials.content' },
    { dir: 'appointments', file: 'index.blade.php', include: 'appointments.partials.content' },
    { dir: 'visits', file: 'index.blade.php', include: 'visits.partials.content' },
    { dir: 'staff', file: 'index.blade.php', include: 'staff.partials.content' },
    { dir: 'expenses', file: 'index.blade.php', include: 'expenses.partials.content' },
    { dir: 'expense-categories', file: 'index.blade.php', include: 'expense-categories.partials.content' },
    { dir: 'staff-compensation-profiles', file: 'index.blade.php', include: 'staff-compensation-profiles.partials.content' },
    { dir: 'staff-payments', file: 'index.blade.php', include: 'staff-payments.partials.content' },
    { dir: 'doctor-earnings', file: 'index.blade.php', include: 'doctor-earnings.partials.content' },
    { dir: 'reports', file: 'index.blade.php', include: 'reports.partials.content' },
    { dir: 'users', file: 'index.blade.php', include: 'users.partials.content' },
    { dir: 'audit-logs', file: 'index.blade.php', include: 'audit-logs.partials.content' },
    { dir: 'backups', file: 'index.blade.php', include: 'backups.partials.content' },
    { dir: 'services', file: 'index.blade.php', include: 'services.partials.content' },
    { dir: 'settings', file: 'edit.blade.php', include: 'settings.partials.content' },
    { dir: 'dashboards', file: 'reception.blade.php', include: 'dashboards.partials.reception' },
    { dir: 'dashboards', file: 'clinical.blade.php', include: 'dashboards.partials.clinical' },
];

const layoutRe = /^([\s\S]*?)<x-app-layout>\s*([\s\S]*?)\s*<\/x-app-layout>\s*$/;

for (const t of tasks) {
    const filePath = path.join(root, 'resources/views', t.dir, t.file);
    const text = fs.readFileSync(filePath, 'utf8');
    const m = text.match(layoutRe);
    if (!m) {
        console.error('No x-app-layout match:', filePath);
        process.exitCode = 1;
        continue;
    }
    const before = m[1].replace(/\s+$/, '');
    const inner = m[2].trim();

    const partialName = t.include.split('.').pop();
    const partialDir = path.join(root, 'resources/views', t.dir, 'partials');
    fs.mkdirSync(partialDir, { recursive: true });
    const partialPath = path.join(partialDir, `${partialName}.blade.php`);

    const pageTitleBlock =
        `@if(! empty($pageTitle ?? null))\n` +
        `    <span class="sr-only" data-spa-page-title>{{ $pageTitle }}</span>\n` +
        `@endif\n`;

    const partialBody = (before ? `${before}\n\n` : '') + pageTitleBlock + inner + '\n';
    fs.writeFileSync(partialPath, partialBody);

    const newIndex = `<x-app-layout>\n    @include('${t.include}')\n</x-app-layout>\n`;
    fs.writeFileSync(filePath, newIndex);
    console.log('extracted', t.include);
}

console.log('done');
