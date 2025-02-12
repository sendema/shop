
## Shop с интеграцией PayPal
### Описание проекта
Простой интернет-магазин с функционалом:
- Оформление заказа
- Интеграция с PayPal
- Отправка email-уведомлений
- Просмотр истории заказов


## Технологический стек
- Laravel
- MySQL
- Nginx
- PayPal API
- Docker

## Установка и запуск
### Клонирование репозитория
```bash
git clone https://github.com/sendema/shop.git
cd shop
```
### Настройка .env
```bash
cp .env.example .env
# Заполнить PayPal, SMTP и другие credentials
```
## Запуск через Docker
```bash
docker-compose up -d
docker-compose exec app composer install
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan migrate
```
## Функционал
- Создание заказа
- Оплата через PayPal
- Email-уведомления
- История заказов
## Тестирование
```bash
docker-compose exec app php artisan test
```
