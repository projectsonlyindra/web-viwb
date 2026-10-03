FROM php:8.3-fpm-bookworm

LABEL maintainer="VIWB"
LABEL description="All-in-one: Nginx + PHP-FPM + MariaDB + Node.js"

ENV TZ=Asia/Jakarta
ENV DEBIAN_FRONTEND=noninteractive

# ── Install system packages ──────────────────────────────────────────
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        # PHP build deps
        libfreetype6-dev libjpeg62-turbo-dev libpng-dev libwebp-dev \
        libzip-dev libicu-dev unzip git curl ca-certificates gnupg \
        # Nginx
        nginx \
        # MariaDB
        mariadb-server mariadb-client \
        # Supervisor
        supervisor \
        # Sync
        rsync \
        # Misc
        procps \
    # ── PHP extensions ──
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath pdo_mysql gd intl opcache zip \
    # ── Cleanup apt ──
    && rm -rf /var/lib/apt/lists/*

# ── Install Node.js 22 ──────────────────────────────────────────────
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y --no-install-recommends nodejs \
    && rm -rf /var/lib/apt/lists/*

# ── Install Composer ────────────────────────────────────────────────
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ── vboxsf: allow www-data to read VirtualBox shared folders ─────────
RUN groupadd -g 986 vboxsf 2>/dev/null; usermod -aG vboxsf www-data

# ── Copy config files ──────────────────────────────────────────────
COPY docker/php.ini      /usr/local/etc/php/conf.d/99-custom.ini
COPY docker/nginx.conf   /etc/nginx/sites-available/default
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/start.sh     /start.sh
RUN chmod +x /start.sh

# ── MariaDB: listen on localhost TCP for Laravel ────────────────────
RUN mkdir -p /run/mysqld && chown mysql:mysql /run/mysqld \
    && sed -i 's/^bind-address\s*=.*/bind-address = 127.0.0.1/' /etc/mysql/mariadb.conf.d/50-server.cnf

WORKDIR /var/www/html

RUN rm -rf /var/lib/mysql/*

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=45s --retries=3 \
    CMD curl -f http://127.0.0.1/login >/dev/null 2>&1 || exit 1

CMD ["/start.sh"]
