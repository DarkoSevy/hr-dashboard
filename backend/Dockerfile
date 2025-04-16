FROM php:8.1-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    unzip \
    git \
    curl \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql mysqli zip

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Install Composer using the official installer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set working directory
WORKDIR /var/www/html

# Copy composer files
COPY composer.json ./

# Install dependencies with retry mechanism
RUN composer install --no-scripts --no-autoloader --no-interaction --prefer-dist || \
    (sleep 5 && composer install --no-scripts --no-autoloader --no-interaction --prefer-dist) || \
    (sleep 10 && composer install --no-scripts --no-autoloader --no-interaction --prefer-dist)

# Copy application files
COPY . .

# Generate autoload files
RUN composer dump-autoload --optimize

# Configure Apache
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf
RUN a2enmod rewrite headers

# Create Apache configuration
COPY apache.conf /etc/apache2/sites-available/000-default.conf

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Expose port
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"] 