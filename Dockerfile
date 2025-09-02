FROM php:8.3-fpm

# Install system dependencies needed by the MongoDB extension and Composer
RUN apt-get update && apt-get install -y \
    libssl-dev \
    pkg-config \
    libzip-dev \
    unzip \
    git \
    # Clean up APT cache to keep the image size down
    && rm -rf /var/lib/apt/lists/*


# Install the MySQL PDO extension
RUN docker-php-ext-install pdo_mysql

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set the working directory inside the container
WORKDIR /var/www/html

# Copy your application's source code into the container
COPY . /var/www/html

# Run composer install to get your PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Set ownership to the www-data user/group (common for Nginx/PHP-FPM)
RUN chown -R www-data:www-data /var/www/html

# Expose port 9000 for PHP-FPM
EXPOSE 9000

# The command to run the container (this is usually handled by docker-compose)
CMD ["php-fpm"]
