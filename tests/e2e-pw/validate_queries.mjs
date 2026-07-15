import fs from 'fs';
import path from 'path';
import 'dotenv/config';

const ROOT = 'tests/graphQL/graphql/Queries';
const BASE = process.env.BAGISTO_URL || 'http://127.0.0.1:8000';
const ADMIN_TOKEN = process.env.ADMIN_INTEGRATION_TOKEN;
const SHOP_KEY = process.env.STOREFRONT_ACCESS_KEY;

function walk(d) {
  return fs.readdirSync(d, { withFileTypes: true }).flatMap(e => {
    const p = path.join(d, e.name);
    return e.isDirectory() ? walk(p) : (p.endsWith('.ts') ? [p] : []);
  });
}

// pull `export const NAME = ` + backtick template literal
function extract(src) {
  const out = [];
  const re = /export\s+const\s+([A-Z0-9_]+)\s*=\s*`([\s\S]*?)`;/g;
  let m;
  while ((m = re.exec(src))) out.push({ name: m[1], query: m[2] });
  return out;
}

const SCHEMA_ERR = /Cannot query field|Unknown argument|Unknown type|Unknown field|is not defined by type|Field ".*" argument/;

let bad = 0;
for (const file of walk(ROOT)) {
  const isAdmin = file.includes('/admin/');
  const headers = { 'Content-Type': 'application/json' };
  if (isAdmin) headers['Authorization'] = `Bearer ${ADMIN_TOKEN}`;
  else headers['X-STOREFRONT-KEY'] = SHOP_KEY;
  const url = `${BASE}${isAdmin ? '/api/admin/graphql' : '/api/graphql'}`;

  for (const { name, query } of extract(fs.readFileSync(file, 'utf8'))) {
    let body;
    try {
      const r = await fetch(url, { method: 'POST', headers, body: JSON.stringify({ query, variables: {} }) });
      body = await r.json();
    } catch (e) { console.log(`ERR  ${file} :: ${name} :: ${e.message}`); continue; }

    const errs = (body.errors || []).map(e => e.message).filter(m => SCHEMA_ERR.test(m));
    if (errs.length) {
      bad++;
      console.log(`\nSTALE ${file}\n  ${name}`);
      [...new Set(errs)].forEach(m => console.log(`    - ${m}`));
    }
  }
}
console.log(`\n=== query definitions with schema errors: ${bad}`);
