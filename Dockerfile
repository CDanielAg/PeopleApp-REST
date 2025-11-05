FROM php:8.1-apache

# Instala dependencias y extensiones necesarias
RUN apt-get update && apt-get install -y \
    libzip-dev \
    libicu-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo pdo_mysql mysqli mbstring intl zip opcache \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

# Copia la aplicación
WORKDIR /var/www/html
COPY . /var/www/html/
# Ajusta permisos (si es necesario)
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

EXPOSE 80
## Generar archivo de conexión `datos/login_mysql.php` en build (valores por defecto del repo)
RUN if [ -d /var/www/html/datos ]; then \
    printf '%s\n' '<?php' '/**' ' * Provee las constantes para conectarse a la base de datos' ' * Archivo generado en build' ' */' \
        'define("NOMBRE_HOST", "peopleapp_db");' \
        'define("BASE_DE_DATOS", "people");' \
        'define("USUARIO", "upeople");' \
        'define("CONTRASENA", "1234");' > /var/www/html/datos/login_mysql.php && \
    chown www-data:www-data /var/www/html/datos/login_mysql.php || true && \
    chmod 640 /var/www/html/datos/login_mysql.php || true; \
fi

CMD ["apache2-foreground"]
