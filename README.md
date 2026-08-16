# Job Seekers Guide

A structured-learning platform for people trying to get their first job in
software. The thinking behind it — the problem, the personas, the curriculum,
and the product flow — lives in [`abstract/`](./abstract/).

- **Hosting:** Hostinger VPS
- **Runtime:** WordPress running in Docker containers
- **LMS:** Guide LMS (`wp-content/plugins/guide-lms`) — custom, no Tutor, no WooCommerce
- **Theme:** Guide WP Theme (`wp-content/themes/guide-wp-theme`) — classic PHP theme on Bulma

## Stack

| Layer      | Choice                          |
|------------|----------------------------------|
| Server     | Hostinger VPS                   |
| Container  | Docker + Docker Compose         |
| CMS        | WordPress                       |
| LMS Plugin | Guide LMS (custom)              |
| Theme      | Guide WP Theme (custom, Bulma)  |
| Database   | MySQL / MariaDB (containerized) |

## Repository Layout

```
.
├── docker/            # Docker Compose files, Dockerfiles, env templates
├── wp-content/
│   ├── themes/         # guide-wp-theme
│   ├── plugins/        # guide-lms
│   └── uploads/        # (gitignored) media, synced separately
├── scripts/            # Deploy / backup / sync scripts
├── abstract/           # Project abstract, personas, flows, curriculum, seed data
├── TODO.md
└── README.md
```

## Local Development

1. Copy `docker/.env.example` to `docker/.env` and fill in values.
2. `docker compose -f docker/docker-compose.yml up -d`
3. Visit `http://localhost:8080` to complete the WordPress install.

## Deployment (Hostinger VPS)

See [TODO.md](./TODO.md) for the rollout plan.

## Front-end build

The compiled stylesheet is committed, so production needs no Node toolchain.
To change styles:

```
cd wp-content/themes/guide-wp-theme
npm install
npm run build     # theme + scoped admin console + wp-admin skin
npm run watch     # theme only, on change
```

Design tokens live in `src/scss/_tokens.scss`; the rationale for each is in the
file and in [`abstract/04-vision-and-principles.md`](./abstract/04-vision-and-principles.md).

## License

Private project — all rights reserved unless stated otherwise.
