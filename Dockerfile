FROM php:8.1-cli

# Install PDO MySQL extension
RUN docker-php-ext-install pdo_mysql

WORKDIR /var/www/html

COPY . .

EXPOSE 10000

CMD ["php", "-S", "0.0.0.0:10000", "-t", "public"]
