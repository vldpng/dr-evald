import { readdir, readFile, stat } from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const dist = path.join(root, 'dist');
const routes = JSON.parse(await readFile(path.join(root, 'wordpress/migration/public-content.json'), 'utf8')).records;
const problems = [];
for (const record of routes) {
  const route = record.meta._dre_path[0];
  const file = path.join(dist, route, 'index.html');
  const content = await readFile(file, 'utf8');
  if ((content.match(/<h1\b/g) || []).length !== 1) problems.push(`${route}: H1 count`);
  if (!/<meta\b[^>]*name="robots"[^>]*content="noindex/.test(content)) problems.push(`${route}: indexing policy`);
  if (/localhost:10016|\/wp-admin\/admin-ajax.php/.test(content)) problems.push(`${route}: local endpoint`);
  for (const form of content.matchAll(/<form\b[^>]*>/g)) {
    if (!form[0].includes('data-demo-form="true"') || !form[0].includes('action="#"')) problems.push(`${route}: non-demo form`);
  }
  for (const match of content.matchAll(/\b(?:src|href)="(\/[^"#?]*)(?:[?#][^"]*)?"/g)) {
    const target = path.join(dist, decodeURIComponent(match[1]));
    try {
      const entry = await stat(target);
      if (entry.isDirectory()) await stat(path.join(target, 'index.html'));
    } catch { problems.push(`${route}: missing ${match[1]}`); }
  }
}
let files = 0;
async function walk(directory) {
  for (const item of await readdir(directory, { withFileTypes: true })) {
    const filename = path.join(directory, item.name);
    if (item.isDirectory()) await walk(filename);
    else {
      files++;
      if (/\.(php|sql|env|gz)$/.test(item.name)) problems.push(`Forbidden server file: ${filename}`);
      if ((await stat(filename)).size > 25 * 1024 * 1024) problems.push(`Pages asset too large: ${filename}`);
    }
  }
}
await walk(dist);
for (const filename of ['index.html', '404.html', '_headers', '_redirects', 'robots.txt', 'demo-runtime.js']) await stat(path.join(dist, filename));
if (problems.length) throw new Error(problems.join('\n'));
console.log(`Checks passed: ${routes.length} content pages, ${files} static files, internal links, demo forms, noindex.`);
