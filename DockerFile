FROM php:8.2-apache

# Optional but nice
RUN a2enmod rewrite
RUN echo "ServerName localhost" > /etc/apache2/conf-available/servername.conf && a2enconf servername

# MySQL drivers
RUN docker-php-ext-install mysqli pdo pdo_mysql

# App files and public web root
WORKDIR /var/www/html
COPY . .
RUN sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf

# 🔑 Listen on Railway's $PORT at runtime, then start Apache
CMD ["sh","-c","\
  sed -i 's/Listen 80/Listen ${PORT:-8080}/' /etc/apache2/ports.conf && \
  sed -i 's/<VirtualHost \\*:80>/<VirtualHost *:${PORT:-8080}>/' /etc/apache2/sites-available/000-default.conf && \
  apache2-foreground"]
