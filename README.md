# Wireframe Cingulate — Drupal 11 Project

A Drupal 11 site built with a custom theme (`cingulate`) and custom modules, managed via DDEV for local development.

- **Local URL:** https://wireframe-cingulate.ddev.site
- **PHP:** 8.4
- **Drupal:** 11
- **Repo:** https://github.com/rajeshsaini7931/wireframe-cingulate

---

## Prerequisites

Install these on the new machine before starting:

| Tool | Install |
|---|---|
| [Docker Desktop](https://www.docker.com/products/docker-desktop/) | Required by DDEV |
| [DDEV](https://ddev.readthedocs.io/en/stable/users/install/) | Local dev environment |
| [Composer](https://getcomposer.org/download/) | PHP dependency manager |
| [Git](https://git-scm.com/) | Version control |

---

## Local Setup (New Laptop / New Developer)

### 1. Clone the repository

```bash
git clone https://github.com/rajeshsaini7931/wireframe-cingulate.git
cd wireframe-cingulate
```

### 2. Start DDEV

```bash
ddev start
```

This provisions the web server, database, and PHP containers using the `.ddev/config.yaml` already in the repo.

### 3. Install PHP dependencies

```bash
ddev composer install
```

Restores everything that is gitignored: `vendor/`, `web/core/`, `web/modules/contrib/`, `web/themes/contrib/`.

### 4. Import the database

The database dump (`.sql.gz`) is **not** in the repo (gitignored for security). Obtain it from the team, then:

```bash
ddev import-db --file=project-db-2026-05-27-18-13.sql.gz
```

### 5. Import configuration

```bash
ddev exec drush cim -y
```

Applies the 403 YAML config files from `config/sync/` on top of the imported database, ensuring the site config matches the codebase.

### 6. Clear cache

```bash
ddev exec drush cr
```

### 7. Open the site

```bash
ddev launch
```

The site opens at **https://wireframe-cingulate.ddev.site**

---

## Daily Development Workflow

```bash
# Start environment
ddev start

# Stop environment
ddev stop

# SSH into the container
ddev ssh

# Run Drush commands
ddev exec drush <command>

# After making config changes in the UI — export to YAML
ddev exec drush cex -y

# After pulling new code with config changes — import to DB
ddev exec drush cim -y

# Clear all caches
ddev exec drush cr
```

---

## Project Structure

```
├── config/sync/          # Drupal configuration YAML (403 files, tracked in git)
├── setup-scripts/        # One-time Drush PHP scripts for content/config setup
├── docs/                 # Project documentation
├── web/
│   ├── modules/custom/   # Custom Drupal modules
│   └── themes/custom/    # Custom theme (cingulate)
├── composer.json         # PHP dependencies
├── composer.lock         # Locked dependency versions
└── .ddev/config.yaml     # DDEV environment config
```

**Not tracked in git** (must be obtained separately or regenerated):
- `vendor/` — restored by `composer install`
- `web/core/` — restored by `composer install`
- `web/modules/contrib/` — restored by `composer install`
- `web/themes/contrib/` — restored by `composer install`
- `web/sites/default/files/` — user-uploaded files
- `*.sql.gz` — database dumps

---

## Troubleshooting

**DDEV won't start**
```bash
docker ps        # ensure Docker Desktop is running
ddev poweroff    # stop all DDEV projects
ddev start       # retry
```

**`ddev composer install` fails**
```bash
ddev exec composer install --no-interaction -v
```

**Config import errors**
```bash
ddev exec drush cst   # review differences before importing
ddev exec drush cim -y
```

**`drush cim` reports UUID mismatch**
The imported database must match the site UUID in config. Fix with:
```bash
ddev exec drush config:set system.site uuid $(grep uuid config/sync/system.site.yml | awk '{print $2}') -y
ddev exec drush cim -y
```
