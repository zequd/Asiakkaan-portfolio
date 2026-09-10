# Asiakkaan portfolio

Portfolio site. Plain PHP, plain CSS and JS, no build step and no npm.

## Running it

Needs PHP 8 (tested on 8.3). From the project root:

```
php -S localhost:8000 -t public public/index.php
```

Open http://localhost:8000

For the contact form, copy `.env.example` to `.env` and fill in the bot
token and chat id. Without them the page still works, but the form returns
an error. To test sending on its own: `php tools/test_telegram.php`.

## Layout

```
config/          settings, route table, site copy
src/Core/        router and template rendering
src/Controllers/ route handlers
views/           templates: layout, page, sections
public/          document root, this is what the web server points at
  assets/css/    styles, one file per section
  assets/js/     scripts
  assets/lib/    third party libraries (Lenis)
```

Text is edited in `config/content.php`, the templates stay untouched.

## The form

Posts to `POST /contact`. Fields: `name`, `contact`, `message`, plus a
hidden `website` field used as a bot trap.

With an `X-Requested-With: XMLHttpRequest` header the answer comes back as
JSON, otherwise it redirects to `/?sent=1` or `/?sent=0`. That way the form
also works with JavaScript turned off.

## Query flags

| URL | Effect |
| --- | --- |
| `?motion=off` | turns animation off, same as the system setting |
| `?motion=on` | forces it on |
| `?lite` | uses the static background instead of WebGL |
| `?heavy` | forces the WebGL background even with software rendering |
