FROM php:8.1-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && docker-php-ext-install zip pdo_mysql mysqli

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set working directory
WORKDIR /var/www/html

# Copy composer files first
COPY composer.json composer.lock ./

# Install dependencies with retry mechanism
RUN composer install --no-scripts --no-autoloader --no-interaction --prefer-dist || \
    (sleep 5 && composer install --no-scripts --no-autoloader --no-interaction --prefer-dist) || \
    (sleep 10 && composer install --no-scripts --no-autoloader --no-interaction --prefer-dist)

# Copy the rest of the application
COPY . .

# Generate autoload files
RUN composer dump-autoload --optimize

# Create necessary directories
RUN mkdir -p /var/www/html/storage /var/www/html/bootstrap/cache

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 777 /var/www/html/storage \
    && chmod -R 777 /var/www/html/bootstrap/cache

# Enable Apache modules
RUN a2enmod rewrite headers

# Copy Apache configuration
COPY apache.conf /etc/apache2/sites-available/000-default.conf

# Configure PHP
RUN echo "memory_limit = 1G" > /usr/local/etc/php/conf.d/custom.ini && \
    echo "max_execution_time = 300" >> /usr/local/etc/php/conf.d/custom.ini && \
    echo "upload_max_filesize = 64M" >> /usr/local/etc/php/conf.d/custom.ini && \
    echo "post_max_size = 64M" >> /usr/local/etc/php/conf.d/custom.ini && \
    echo "display_errors = On" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "error_reporting = E_ALL" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "log_errors = On" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "error_log = /proc/self/fd/2" >> /usr/local/etc/php/conf.d/custom.ini

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"] 