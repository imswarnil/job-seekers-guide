# Guide — documentation

Everything needed to run, extend, and understand the platform.

| Document | For |
|---|---|
| [help-centre.md](help-centre.md) | Running the site day to day — the operator's manual |
| [learner-help.md](learner-help.md) | What learners are told, source for the public help page |
| [architecture.md](architecture.md) | How the plugin and theme fit together |
| [CHANGELOG.md](CHANGELOG.md) | What changed, when, and why |
| [ROADMAP.md](ROADMAP.md) | What is coming, and what is deliberately not |

The *product* thinking — the problem, the personas, the curriculum — lives in
[`../abstract/`](../abstract/), not here. This folder is about the software.

## Quick reference

```
wp-content/plugins/guide-lms     Guide LMS — all behaviour and data
wp-content/themes/guide-wp-theme Guide WP Theme — all presentation
abstract/                        Product thinking (why, who, what to teach)
docs/                            This folder (how to run and extend it)
```

Build the CSS:

```
cd wp-content/themes/guide-wp-theme
npm install
npm run build
```
