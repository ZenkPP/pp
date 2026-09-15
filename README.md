## Запуск

Нужен Docker с плагином Compose.

```bash
cp .env.example .env
docker compose up -d --build
docker compose exec php composer install
docker compose exec php php yii migrate --interactive=0
```

Приложение будет доступно по адресу [http://localhost:8080](http://localhost:8080). Порт, язык и параметры MySQL задаются в `.env`.

## Полезные команды

```bash
# Остановить контейнеры
docker compose down
```
