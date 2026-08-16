/**
 * Scopes the compiled admin stylesheet under `.guide-admin`.
 *
 * Bulma's minireset and generic layers style bare `html`, `body`, `a`,
 * `button`, `input`, and `table`. Loading that unscoped into wp-admin would
 * restyle the admin menu, the admin bar, every core settings screen, and every
 * other plugin's pages — so the console gets real Bulma, but only inside its
 * own wrapper.
 *
 * Only used by `npm run build:admin`; the front-end build needs no PostCSS.
 */

const prefixer = require("postcss-prefix-selector");

module.exports = {
  plugins: [
    prefixer({
      prefix: ".guide-admin",

      transform(prefix, selector, prefixedSelector) {
        // Custom-property declarations and theme switches have to keep working
        // at the document root — they are inherited, not matched, and Bulma's
        // dark theme is toggled on <html>. Prefixing these would break theming
        // entirely while looking like it had worked.
        if (
          selector === ":root" ||
          selector.startsWith(":root") ||
          selector.startsWith("[data-theme") ||
          selector.startsWith(".theme-light") ||
          selector.startsWith(".theme-dark")
        ) {
          return selector;
        }

        // `html` and `body` cannot live inside the wrapper. Their declarations
        // are dropped rather than rewritten: whatever Bulma wants to do to the
        // document is WordPress's business in admin, not ours.
        if (selector === "html" || selector === "body") {
          return ".guide-admin";
        }

        // Keyframe steps (`from`, `to`, `0%`) must never be prefixed.
        if (/^(from|to|\d+%)$/.test(selector.trim())) {
          return selector;
        }

        return prefixedSelector;
      },
    }),
  ],
};
