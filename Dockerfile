FROM php:8.2-apache

# 必要なツールをインストール
RUN apt-get update && apt-get install -y \
    curl \
    unzip \
    git

# Composerをインストール
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Apacheのドキュメントルートを設定
ENV APACHE_DOCUMENT_ROOT /var/www/html/public

# Apache設定の修正
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 必要なPHP拡張モジュールをインストール
RUN docker-php-ext-install pdo_mysql

# Apacheの再起動
RUN a2enmod rewrite
