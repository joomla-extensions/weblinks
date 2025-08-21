# Start from the official PHP 8.2 image with Apache
FROM php:8.2-apache-bookworm

# Install system dependencies, Node.js, Composer, and Cypress dependencies
RUN apt-get update && apt-get install -y \
    # System tools and git
    git \
    zstd \
    wget \
    curl \
    sudo \
    # PHP extensions dependencies
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    # MySQL client AND server
    default-mysql-client \
    default-mysql-server \
    # Cypress dependencies
    xvfb \
    libgtk2.0-0 \
    libgtk-3-0 \
    libgbm-dev \
    libnotify-dev \
    libnss3 \
    libxss1 \
    libasound2 \
    libxtst6 \
    xauth \
    && \
    # Install the required PHP extensions for Zip and MySQL
    docker-php-ext-install zip mysqli && \
    # Install Node.js (LTS version)
    curl -fsSL https://deb.nodesource.com/setup_lts.x | bash - && \
    apt-get install -y nodejs && \
    # Install Composer globally
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
    # Install Xdebug
    pecl install xdebug && \
    # Enable Apache's rewrite module and set the ServerName to prevent warnings
    a2enmod rewrite && \
    echo "ServerName localhost" >> /etc/apache2/apache2.conf && \
    # Clean up apt cache to reduce image size
    apt-get clean && rm -rf /var/lib/apt/lists/* && \
    # Create a custom PHP configuration file to enable file uploads
    echo "upload_tmp_dir = /tmp" > /usr/local/etc/php/conf.d/custom-php.ini && \
    echo "post_max_size = 64M" >> /usr/local/etc/php/conf.d/custom-php.ini && \
    echo "upload_max_filesize = 64M" >> /usr/local/etc/php/conf.d/custom-php.ini
