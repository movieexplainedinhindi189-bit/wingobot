FROM php:8.1-cli
RUN apt-get update && apt-get install -y libcurl4-openssl-dev && docker-php-ext-install curl
COPY . /usr/src/myapp
WORKDIR /usr/src/myapp
EXPOSE 10000
CMD [ "php", "-S", "0.0.0.0:10000", "index.php" ]
