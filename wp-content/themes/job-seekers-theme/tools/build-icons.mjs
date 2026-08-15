/**
 * Bakes the Phosphor icons this product uses into inc/icons.php.
 *
 * Why bake rather than link a CDN or ship the whole set: the site is a PWA
 * that has to work offline, a strict icon font is a render-blocking request,
 * and 1,500 unused SVGs is not something to put in a theme. This copies only
 * the icons the templates actually reference, as inline markup, so they cost
 * one PHP array lookup and no network at all.
 *
 * Run: npm run icons   (then npm run build)
 *
 * Phosphor Icons is MIT licensed — see LICENSE note in the generated file.
 */

import { readFileSync, writeFileSync, mkdirSync } from "node:fs";
import { dirname, resolve } from "node:path";
import { fileURLToPath } from "node:url";

const here = dirname(fileURLToPath(import.meta.url));
const themeDir = resolve(here, "..");
const srcDir = resolve(themeDir, "node_modules/@phosphor-icons/core/assets");
const outFile = resolve(themeDir, "inc/icons.php");

/** name -> weights to bake. "regular" for outlines, "fill" for active/selected states. */
const ICONS = {
	// Navigation & chrome
	house: ["regular", "fill"],
	"magnifying-glass": ["regular"],
	list: ["regular"],
	x: ["regular"],
	"caret-right": ["regular"],
	"caret-left": ["regular"],
	"caret-down": ["regular"],
	"caret-up": ["regular"],
	"arrow-right": ["regular"],
	"arrow-left": ["regular"],
	"dots-three-vertical": ["regular"],
	moon: ["regular", "fill"],
	sun: ["regular", "fill"],
	gear: ["regular", "fill"],
	bell: ["regular"],
	"globe": ["regular"],
	"share-network": ["regular"],
	funnel: ["regular"],

	// Learning
	"graduation-cap": ["regular", "fill"],
	path: ["regular", "fill"],
	"book-open": ["regular", "fill"],
	play: ["regular", "fill"],
	"play-circle": ["regular", "fill"],
	check: ["regular"],
	"check-circle": ["regular", "fill"],
	circle: ["regular"],
	stack: ["regular", "fill"],
	"list-checks": ["regular"],
	article: ["regular", "fill"],
	question: ["regular"],
	certificate: ["regular", "fill"],
	trophy: ["regular", "fill"],
	target: ["regular"],
	clock: ["regular"],
	"film-strip": ["regular"],
	medal: ["regular", "fill"],
	"rocket-launch": ["regular"],
	compass: ["regular"],
	sparkle: ["regular", "fill"],
	lightning: ["regular", "fill"],
	star: ["regular", "fill"],
	"bookmark-simple": ["regular"],

	// Access & identity
	lock: ["regular", "fill"],
	"lock-simple": ["regular"],
	briefcase: ["regular", "fill"],
	user: ["regular", "fill"],
	users: ["regular", "fill"],
	"user-circle": ["regular", "fill"],
	"sign-in": ["regular"],
	"sign-out": ["regular"],
	"google-logo": ["regular"],
	"shield-check": ["regular"],
	"credit-card": ["regular", "fill"],
	"envelope-simple": ["regular"],

	// Console / authoring
	"chart-line": ["regular"],
	plus: ["regular"],
	trash: ["regular"],
	"pencil-simple": ["regular"],
	"note-pencil": ["regular"],
	"floppy-disk": ["regular"],
	eye: ["regular"],
	folder: ["regular"],
	image: ["regular"],
	link: ["regular"],
	"link-simple": ["regular"],
	code: ["regular"],
	quotes: ["regular"],
	"text-b": ["regular"],
	"text-italic": ["regular"],
	"list-bullets": ["regular"],
	"list-numbers": ["regular"],
	"text-h-two": ["regular"],
	"text-h-three": ["regular"],
	"arrows-out-simple": ["regular"],
	calendar: ["regular"],
	"map-pin": ["regular"],
};

/** Pull the drawable markup out of a Phosphor SVG file. */
function extractBody(svg) {
	const inner = svg.replace(/^[\s\S]*?<svg[^>]*>/, "").replace(/<\/svg>\s*$/, "");
	return inner
		.replace(/<rect\s+width="256"\s+height="256"\s+fill="none"\s*\/>/g, "") // Phosphor's transparent bounding rect
		.replace(/\s+/g, " ")
		.trim();
}

const entries = [];
let count = 0;

for (const [name, weights] of Object.entries(ICONS)) {
	for (const weight of weights) {
		const suffix = weight === "regular" ? "" : `-${weight}`;
		const file = resolve(srcDir, weight, `${name}${suffix}.svg`);

		let raw;
		try {
			raw = readFileSync(file, "utf8");
		} catch {
			console.error(`! missing icon: ${weight}/${name}${suffix}.svg`);
			process.exitCode = 1;
			continue;
		}

		const key = weight === "regular" ? name : `${name}-${weight}`;
		entries.push(`\t\t'${key}' => '${extractBody(raw).replace(/'/g, "\\'")}',`);
		count++;
	}
}

const php = `<?php
/**
 * Phosphor icon paths, baked from @phosphor-icons/core.
 *
 * GENERATED FILE — do not edit by hand. Add the icon to tools/build-icons.mjs
 * and run \`npm run icons\`.
 *
 * Phosphor Icons © Phosphor Icons, MIT licensed.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Raw icon bodies keyed by name. Fill variants are suffixed "-fill".
 *
 * @return array<string, string>
 */
function jsl_icon_paths() {
	return array(
${entries.join("\n")}
	);
}
`;

mkdirSync(dirname(outFile), { recursive: true });
writeFileSync(outFile, php, "utf8");
console.log(`Baked ${count} icons -> inc/icons.php`);
