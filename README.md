# Job Seekers Guide

A WordPress site (LMS-powered) helping job seekers, built on:

- **Hosting:** Hostinger VPS
- **Runtime:** WordPress running in Docker containers
- **LMS:** [Tutor LMS](https://tutorlms.com/) plugin
- **Theme:** [Tutor Starter](https://themeforest.net/item/tutor-starter/) (customized)

## Stack

| Layer      | Choice                          |
|------------|----------------------------------|
| Server     | Hostinger VPS                   |
| Container  | Docker + Docker Compose         |
| CMS        | WordPress                       |
| LMS Plugin | Tutor LMS                       |
| Theme      | Tutor Starter (customized)      |
| Database   | MySQL / MariaDB (containerized) |

## Repository Layout

```
.
├── docker/            # Docker Compose files, Dockerfiles, env templates
├── wp-content/
│   ├── themes/         # Tutor Starter (customized child theme)
│   ├── plugins/        # Custom / must-use plugins
│   └── uploads/        # (gitignored) media, synced separately
├── scripts/            # Deploy / backup / sync scripts
├── TODO.md
└── README.md
```

## Local Development

1. Copy `docker/.env.example` to `docker/.env` and fill in values.
2. `docker compose -f docker/docker-compose.yml up -d`
3. Visit `http://localhost:8080` to complete the WordPress install.

## Deployment (Hostinger VPS)

See [TODO.md](./TODO.md) for the step-by-step rollout plan, from provisioning the VPS through going live with Tutor LMS courses.

## License

Private project — all rights reserved unless stated otherwise.
