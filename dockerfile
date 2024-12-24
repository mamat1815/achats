FROM php:8.1-apache

# Install ekstensi yang dibutuhkan
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copy composer.json dan composer.lock
COPY composer.json /var/www/html/


# Install Composer dan dependensi
RUN apt-get update && apt-get install -y unzip \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && composer install --no-dev --optimize-autoloader

# Copy seluruh kode aplikasi
COPY . /var/www/html/

# Expose port 80 untuk akses web
EXPOSE 80
