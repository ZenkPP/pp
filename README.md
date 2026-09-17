## Запуск

Нужен Docker с плагином Compose.

```bash
cp .env.example .env
docker compose up -d --build
docker compose exec php composer install
docker compose exec php php yii migrate --interactive=0
```
При необходимости поменяйте `COOKIE_VALIDATION_KEY` в .env

Приложение будет доступно по адресу [http://localhost:8080](http://localhost:8080). Порт, язык и параметры MySQL задаются в `.env`.

## Выбор языка
Язык прописывается в переменной `APP_LANGUAGE` файла `.env`

## Полезные команды

```bash
# Остановить контейнеры
docker compose down
```
