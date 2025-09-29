#!/usr/bin/env node
// Minimal CLI to drive Playwright for ad-hoc screenshots.
// Acts as a thin shim that an MCP tool can call.

import { chromium } from 'playwright';
import fs from 'node:fs';
import path from 'node:path';

const ART_DIR = path.resolve('mcp-artifacts');

function sanitizeBaseUrl(val) {
  if (!val) return 'http://127.0.0.1:8000';
  // Must start with http(s)://; otherwise ignore and fallback
  if (!/^https?:\/\//i.test(val)) return 'http://127.0.0.1:8000';
  // Guard against accidental path concatenation (e.g., drive letters appended)
  const m = val.match(/^(https?:\/\/[^\s]+?)([A-Za-z]:\\|[A-Za-z]:\/)?$/i);
  if (m) return m[1].replace(/\/$/, '');
  return val.replace(/\/$/, '');
}

function parseArgs(argv) {
  const args = { command: 'serve' };
  const rest = [];
  for (let i = 2; i < argv.length; i++) {
    const a = argv[i];
    if (a === 'shot') args.command = 'shot';
    else if (a === '--url') args.url = argv[++i];
    else if (a === '--device') args.device = argv[++i];
    else if (a === '--out') args.out = argv[++i];
    else if (a === '--name') args.name = argv[++i];
    else if (a === '--base') args.base = argv[++i];
    else rest.push(a);
  }
  args._ = rest;
  return args;
}

function normalizeRoute(input) {
  if (!input) return '/';
  if (/^https?:\/\//i.test(input)) return input; // full URL
  if (input.startsWith('/')) return input; // already a route
  // Git for Windows often rewrites '/path' -> 'C:/.../Git/path'
  const gitPrefix = input.match(/^[A-Za-z]:[\\/].*?[\\/]git[\\/](.*)$/i);
  if (gitPrefix && gitPrefix[1]) {
    return '/' + gitPrefix[1].replace(/^[\\/]+/, '').replace(/\\/g, '/');
  }
  // Fallback: ensure leading slash and POSIX separators
  return '/' + String(input).replace(/^\.+/, '').replace(/\\/g, '/');
}

async function ensureDir(dir) {
  await fs.promises.mkdir(dir, { recursive: true });
}

async function shot({ url = '/', device = 'both', out, name, base }) {
  const BASE = sanitizeBaseUrl(base || process.env.APP_URL || 'http://127.0.0.1:8000');
  const route = normalizeRoute(url);
  const fullUrl = /^https?:\/\//i.test(route) ? route : BASE + route;
  const targets = [];

  // Derive base name
  const baseName = (name || out || route.replace(/\/$/, '').split('/').filter(Boolean).pop() || 'home')
    .replace(/[^a-z0-9._-]/gi, '_');

  const variants = device === 'both' ? ['desktop', 'mobile'] : [device || 'desktop'];
  const browser = await chromium.launch();
  for (const variant of variants) {
    const viewport = variant === 'mobile' ? { width: 390, height: 844 } : { width: 1280, height: 800 };
    const filename = out && variants.length === 1
      ? out
      : `${baseName}.${variant}.png`;
    const target = path.join(ART_DIR, filename);
    await ensureDir(path.dirname(target));
    const ctx = await browser.newContext({ viewport, colorScheme: 'light', reducedMotion: 'reduce' });
    const page = await ctx.newPage();
    const resp = await page.goto(fullUrl, { waitUntil: 'networkidle' });
    const status = resp ? resp.status() : 0;
    if (status && status >= 400) {
      console.error(`[mcp:shot] Non-OK status ${status} for ${fullUrl}`);
    }
    console.log(`[mcp:shot] URL: ${fullUrl} device=${variant} status=${status}`);
    await page.screenshot({ path: target, fullPage: true });
    await ctx.close();
    targets.push(target);
  }
  await browser.close();
  return targets;
}

async function main() {
  const args = parseArgs(process.argv);
  if (args.command === 'shot') {
    const saved = await shot(args);
    for (const s of Array.isArray(saved) ? saved : [saved]) {
      console.log(s);
    }
    return;
  }
  console.log('Usage: node mcp/playwright-server.mjs shot --url /gallery [--device desktop|mobile|both] [--name gallery]');
}

main().catch((err) => {
  console.error(err);
  process.exit(1);
});
