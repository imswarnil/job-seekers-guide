# TODO — Job Seekers Guide (WordPress + Tutor LMS on Hostinger VPS)

## 1. VPS Provisioning
- [ ] Confirm Hostinger VPS plan (RAM/CPU/disk) is enough for WordPress + MySQL + Docker
- [ ] SSH into VPS, create a non-root sudo user, disable root SSH login
- [ ] Set up UFW firewall (allow 22, 80, 443 only)
- [ ] Install Docker Engine + Docker Compose plugin
- [ ] Point domain DNS (A record) to VPS IP

## 2. Dockerize WordPress
- [ ] Write `docker/docker-compose.yml` with services: `wordpress`, `db` (MySQL/MariaDB), `phpmyadmin` (optional, dev only)
- [ ] Create `docker/.env.example` (DB name/user/password, WP table prefix, site URL)
- [ ] Mount volumes for `wp-content` and DB data so they persist and are version-controlled/backed up
- [ ] Add `nginx` or Caddy reverse proxy container for TLS termination
- [ ] Set up Let's Encrypt / Certbot (or Caddy auto-HTTPS) for SSL

## 3. WordPress Base Setup
- [ ] Run first-time WP install via container, set site title/admin user
- [ ] Install & configure essential plugins: SEO (Rank Math/Yoast), caching, security (Wordfence or similar), backups
- [ ] Configure `wp-config.php` for Docker env vars, disable file editing in dashboard
- [ ] Set permalinks, timezone, and general settings

## 4. Tutor LMS
- [ ] Install Tutor LMS plugin
- [ ] Configure course, lesson, quiz, and certificate settings
- [ ] Set up instructor roles/permissions
- [ ] Configure payment gateway (if selling courses) — Tutor LMS Pro add-ons as needed
- [ ] Set up email notifications (SMTP plugin, e.g. WP Mail SMTP, so course emails don't land in spam)

## 5. Tutor Starter Theme
- [ ] Install Tutor Starter theme (or its Pro version if licensed)
- [ ] Create a child theme for customizations (never edit the parent theme directly)
- [ ] Customize branding: logo, colors, typography to match Job Seekers Guide identity
- [ ] Customize homepage, course archive, and single course page templates
- [ ] Build out core pages: About, Contact, Blog, Pricing/Courses

## 6. Content & Structure
- [ ] Plan site information architecture (courses, categories, blog, resources)
- [ ] Draft initial course(s) content outline
- [ ] Write starter blog posts / job-seeking resources

## 7. Git & GitHub Sync
- [x] `git init` local repo
- [x] Add `README.md`
- [x] Add `TODO.md`
- [ ] Add `.gitignore` (exclude `wp-content/uploads`, `.env`, DB volumes, node_modules, etc.)
- [ ] Create GitHub repo `job-seekers-guide` (public) and push initial commit
- [ ] Decide sync strategy for existing VPS content → repo (e.g. `rsync` VPS `wp-content` down, or start fresh and deploy up)
- [ ] Set up a deploy script (`scripts/deploy.sh`) to pull latest and restart containers on the VPS

## 8. Backups & Monitoring
- [ ] Automate DB + `wp-content` backups (cron + offsite storage, e.g. S3/Backblaze)
- [ ] Set up uptime monitoring (UptimeRobot or similar)
- [ ] Set up basic log rotation for Docker containers

## 9. Launch
- [ ] Test full user flow: registration → enroll in course → complete lesson → get certificate
- [ ] Cross-browser/device QA
- [ ] Go live, submit sitemap to Google Search Console
