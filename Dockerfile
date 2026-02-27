# Dockerfile for the MMM web service (local development).
# Extends the official PHP 8.2 + Apache image and pre-installs the
# mysqli extension so it is available without runtime installation.
FROM php:8.2-apache

# Install the mysqli extension (required by dblib.php / DB::connect())
RUN docker-php-ext-install mysqli

# Enable Apache mod_rewrite for .htaccess rules
RUN a2enmod rewrite

# Set the document root to the multimadness.de directory.
# In docker-compose the directory is mounted at /var/www/html.
ENV APACHE_DOCUMENT_ROOT /var/www/html

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
        /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' \
        /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
