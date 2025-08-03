FROM php:8.1-cli

# Устанавливаем системные пакеты и инструменты сборки
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
       build-essential \
       git zip unzip \
       libzip-dev zlib1g-dev \
    && rm -rf /var/lib/apt/lists/*

# Конфигурируем и устанавливаем расширения PHP: MySQL и ZIP
RUN docker-php-ext-configure mysqli --with-mysqli=mysqlnd \
    && docker-php-ext-install pdo pdo_mysql mysqli zip

# Устанавливаем Composer из официального образа
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Рабочая директория
WORKDIR /app

# Копируем файлы зависимостей Composer
COPY composer.json composer.lock ./

# Устанавливаем зависимости PHP через Composer
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Копируем остальные файлы приложения
COPY . /app

# Фиксируем порт для API
ENV PORT=80
EXPOSE 80

# Запуск встроенного PHP-сервера
CMD ["php", "-S", "0.0.0.0:80", "public/api.php"]
