import { cp, mkdir, readdir, readFile, writeFile } from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const source = path.join(root, 'public');
const output = path.join(root, 'dist');
const original = 'https://dr-evald-demo.pages.dev';
const origin = new URL(process.env.SITE_URL || process.env.CF_PAGES_URL || original);
if (origin.protocol !== 'https:' || origin.pathname !== '/' || origin.search || origin.hash || origin.username || origin.password) {
  throw new Error('SITE_URL must be an HTTPS origin, e.g. https://dr-evald-demo.pages.dev');
}
await mkdir(output, { recursive: true });
await cp(source, output, { recursive: true });
async function rewrite(directory) {
  for (const item of await readdir(directory, { withFileTypes: true })) {
    const filename = path.join(directory, item.name);
    if (item.isDirectory()) await rewrite(filename);
    else if (/\.(html|css|js|json|txt)$/.test(item.name)) {
      const content = await readFile(filename, 'utf8');
      await writeFile(filename, content.replaceAll(original, origin.origin));
    }
  }
}
await rewrite(output);
console.log(`Built static demo in dist for ${origin.origin}`);
await import('./check.mjs');
