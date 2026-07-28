# Railway deployment files

The repository is ready to use one MySQL service and three Railway services:

| Railway service | Source | Command |
| --- | --- | --- |
| GeoPort App | This GitHub repository | Default Railpack Laravel command |
| GeoPort Scheduler | This GitHub repository | `sh railway/run-cron.sh` |
| GeoPort Worker | This GitHub repository | `sh railway/run-worker.sh` |
| MySQL | Railway MySQL template | No application command |

## App Service settings

Set the App Service **Pre-Deploy Command** to:

```sh
sh railway/init-app.sh
```

Set its **Healthcheck Path** to `/up`. Laravel already exposes that endpoint.

Paste the variables listed in `railway/railway.env.example` into Railway's
Variables editor. Generate `APP_KEY` locally with:

```sh
php artisan key:generate --show
```

Use Railway's MySQL variable references exactly as shown in the example file.

## Initial database data

For an empty Railway database, deploy the App Service first, then run once:

```sh
php artisan db:seed --force
```

Do not put `db:seed` in the pre-deploy script; it must not run on every
deployment. Before seeding production, set `ADMIN_EMAIL` and a strong unique
`ADMIN_PASSWORD` in Railway Variables.

If the live site must preserve the current XAMPP data, import a MySQL dump into
the Railway MySQL service instead of running the initial seed.
