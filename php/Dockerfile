FROM php:8.2-apache

# Use the default production configuration
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Enable Apache mod_rewrite for router functionality
RUN a2enmod rewrite

# Install required packages
RUN apt-get update && apt-get install -y git unzip msmtp \
    && rm -rf /var/lib/apt/lists/*

# Configure msmtp for Mailpit
RUN printf "account default\nhost mailpit\nport 1025\nauto_from on\nmaildomain localhost\n" > /etc/msmtprc \
    && echo 'sendmail_path = "/usr/bin/msmtp -t"' > /usr/local/etc/php/conf.d/mail.ini

# Install mysqli, pdo_mysql and opcache
RUN docker-php-ext-install mysqli pdo_mysql opcache

# Configure opcache
RUN { \
        echo 'opcache.memory_consumption=128'; \
        echo 'opcache.interned_strings_buffer=8'; \
        echo 'opcache.max_accelerated_files=4000'; \
        echo 'opcache.revalidate_freq=2'; \
        echo 'opcache.fast_shutdown=1'; \
    } > /usr/local/etc/php/conf.d/docker-oc-opcache.ini

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Maak de sessions directory aan met juiste rechten voor www-data
RUN mkdir -p /var/www/sessions && chown www-data:www-data /var/www/sessions && chmod 700 /var/www/sessions
