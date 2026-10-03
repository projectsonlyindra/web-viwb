#!/bin/bash
set -e

echo "═══════════════════════════════════════════════"
echo " VIWB Web - Starting all services"
echo "═══════════════════════════════════════════════"

# ── Ensure /run/php and /run/mysqld exist ────────────────────────────
mkdir -p /run/php /run/mysqld && chown mysql:mysql /run/mysqld

# ── Sync app code from host bind-mount → local volume ───────────────
# Host source is at /host-src (read-only bind mount, e.g. VirtualBox).
# /var/www/html is a Docker volume (fast ext4).
# Only sync app code (~3 MB); vendor/node_modules are installed locally.
APP_DIR=/var/www/html

if [ -f /host-src/artisan ]; then
    echo "▸ Syncing app code from host..."
    mkdir -p "$APP_DIR"
    rsync -a \
        --exclude='vendor/' \
        --exclude='node_modules/' \
        --exclude='.git/' \
        --exclude='bootstrap/cache/' \
        --exclude='storage/' \
        --exclude='docker/' \
        --exclude='docs/' \
        --exclude='Dockerfile' \
        --exclude='docker-compose.yml' \
        --exclude='.dockerignore' \
        --exclude='.agents/' \
        --exclude='.claude/' \
        /host-src/ "$APP_DIR/"
fi

# ── Ensure directories exist ────────────────────────────────────────
mkdir -p "$APP_DIR/bootstrap/cache"
mkdir -p "$APP_DIR/storage/logs" "$APP_DIR/storage/framework/cache" "$APP_DIR/storage/framework/sessions" "$APP_DIR/storage/framework/views" "$APP_DIR/storage/app/public" "$APP_DIR/storage/app" 2>/dev/null || true

# ── Fix permissions ─────────────────────────────────────────────────
chmod -R a+rX "$APP_DIR" 2>/dev/null || true
chown -R www-data:www-data "$APP_DIR" 2>/dev/null || true
chmod -R 775 "$APP_DIR/storage" "$APP_DIR/bootstrap/cache" 2>/dev/null || true

# ── Install Composer dependencies (if not present) ─────────────────
if [ ! -d "$APP_DIR/vendor" ]; then
    echo "▸ Installing Composer dependencies …"
    cd "$APP_DIR"
    if [ "${APP_ENV:-production}" = "production" ]; then
        composer install --optimize-autoloader --no-interaction --no-dev 2>&1
    else
        composer install --optimize-autoloader --no-interaction 2>&1
    fi
fi

# ── Install npm dependencies & build assets (if needed) ─────────────
if [ ! -d "$APP_DIR/node_modules" ] && [ -f "$APP_DIR/package.json" ]; then
    echo "▸ Installing npm dependencies & building assets …"
    cd "$APP_DIR"
    npm ci 2>&1 || npm install 2>&1
    npm run build 2>&1 || true
fi

# ── Initialise MariaDB data directory on first run ──────────────────
if [ ! -f "/var/lib/mysql/.viwb-initialized" ]; then
    echo "▸ Initialising MariaDB data directory …"
    mariadb-install-db --user=mysql --datadir=/var/lib/mysql > /dev/null 2>&1

    echo "▸ Starting temporary MariaDB …"
    mysqld --user=mysql --datadir=/var/lib/mysql &
    MYSQL_PID=$!

    # Wait until MariaDB is ready
    for i in $(seq 1 30); do
        if mysqladmin ping -h 127.0.0.1 --silent 2>/dev/null; then
            break
        fi
        sleep 1
    done

    echo "▸ Creating database & user …"
    mariadb -u root <<-EOSQL
        CREATE DATABASE IF NOT EXISTS \`${MYSQL_DATABASE}\`;
        CREATE USER IF NOT EXISTS '${MYSQL_USER}'@'%' IDENTIFIED BY '${MYSQL_PASSWORD}';
        GRANT ALL PRIVILEGES ON \`${MYSQL_DATABASE}\`.* TO '${MYSQL_USER}'@'%';
        CREATE USER IF NOT EXISTS '${MYSQL_USER}'@'localhost' IDENTIFIED BY '${MYSQL_PASSWORD}';
        GRANT ALL PRIVILEGES ON \`${MYSQL_DATABASE}\`.* TO '${MYSQL_USER}'@'localhost';
        ALTER USER 'root'@'localhost' IDENTIFIED BY '${MYSQL_ROOT_PASSWORD:?MYSQL_ROOT_PASSWORD belum diset}';
        FLUSH PRIVILEGES;
EOSQL

    echo "▸ Running Laravel migrations …"
    cd "$APP_DIR"
    php artisan key:generate --force 2>/dev/null || true
    php artisan migrate --force 2>/dev/null || true
    php artisan db:seed --force 2>/dev/null || true
    php artisan config:cache 2>/dev/null || true
    php artisan route:cache 2>/dev/null || true
    php artisan view:cache 2>/dev/null || true

    echo "▸ Stopping temporary MariaDB …"
    mariadb-admin -u root -p"${MYSQL_ROOT_PASSWORD:?MYSQL_ROOT_PASSWORD belum diset}" shutdown 2>/dev/null || mariadb-admin -u root shutdown 2>/dev/null
    wait $MYSQL_PID 2>/dev/null || true

    touch /var/lib/mysql/.viwb-initialized
fi

# ── Fix permissions (after migrations may have written files) ────────
chmod -R a+rX "$APP_DIR" 2>/dev/null || true
chown -R www-data:www-data "$APP_DIR" 2>/dev/null || true
chmod -R 775 "$APP_DIR/storage" "$APP_DIR/bootstrap/cache" 2>/dev/null || true

echo "═══════════════════════════════════════════════"
echo " All services ready - launching supervisord"
echo "═══════════════════════════════════════════════"

exec supervisord -n -c /etc/supervisor/conf.d/supervisord.conf
