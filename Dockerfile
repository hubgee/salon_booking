# Use official PHP image
FROM php:8.2-cli

# Install mysqli and other extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Set working directory
WORKDIR /app

# Copy application files
COPY . /app/

# Expose the port Railway will use
EXPOSE ${PORT:-8080}

# Start PHP built-in server on Railway's PORT
CMD php -S 0.0.0.0:${PORT:-8080} -t /app
