FROM php:8.2-apache

RUN a2enmod rewrite
RUN echo "ServerName localhost" > /etc/apache2/conf-available/servername.conf && a2enconf servername

# MySQL drivers
RUN docker-php-ext-install mysqli pdo pdo_mysql

WORKDIR /var/www/html
COPY . .
# Serve from /public
RUN sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf

# Bind Apache to Railway's PORT
CMD ["sh","-c","\
  sed -i 's/Listen 80/Listen ${PORT:-8080}/' /etc/apache2/ports.conf && \
  sed -i 's/<VirtualHost \\*:80>/<VirtualHost *:${PORT:-8080}>/' /etc/apache2/sites-available/000-default.conf && \
  apache2-foreground"]
