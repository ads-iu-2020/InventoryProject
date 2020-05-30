FROM php:7.3
RUN apt-get update -y && apt-get install -y openssl zip unzip git
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN docker-php-ext-install pdo mbstring
WORKDIR /app
COPY . /app
RUN composer install
RUN php artisan migrate
RUN php artisan db:seed

CMD php -S 0.0.0.0:8181 -t public/
EXPOSE 8181
