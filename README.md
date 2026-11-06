# Egoz

Tienda online de pulseras artesanales y artículos de espiritualidad, con pago con tarjeta a través de Stripe. Los clientes navegan por categorías, añaden productos al carrito, pagan en línea y dejan su reseña. La dueña gestiona productos, pedidos, mensajes y reseñas desde un panel propio. La tienda está en español y alemán.

![Página de inicio](docs/capturas/inicio.png)

## Funcionalidades

**Tienda**
- Catálogo con filtro por categoría y orden, ficha de producto y productos destacados.
- Carrito y pago con tarjeta con Stripe (Payment Intents), confirmado por webhook.
- Página de gracias y correos de confirmación al cliente y a la tienda, con PHPMailer.
- Reseñas de clientes, página "Sobre mí", contacto y botón de WhatsApp.
- Dos idiomas: español y alemán.
- Instalable como aplicación en el móvil (PWA, con manifest y service worker).

**Seguridad**
- Protección CSRF en los formularios, reCAPTCHA y límite de intentos en el acceso al panel.

**Panel de administración** (`/admin`)
- Resumen de ventas y notificaciones de pedidos nuevos.
- Productos (alta, edición e imágenes) y categorías.
- Pedidos con su detalle y estado, mensajes de contacto y moderación de reseñas.
- Traducción automática de los textos de cada producto al alemán con la API de DeepL.

## Tecnologías

| | |
|---|---|
| Backend | PHP 8 sin framework, PDO, Composer |
| Frontend | HTML, CSS, JavaScript, Bootstrap 5, Bootstrap Icons |
| Base de datos | MySQL |
| Servicios | Stripe, PHPMailer (SMTP), reCAPTCHA, API de DeepL |

## Capturas

| Móvil | Tienda | Sobre mí |
|---|---|---|
| ![Inicio en el móvil](docs/capturas/inicio-movil.png) | ![Tienda](docs/capturas/tienda.png) | ![Sobre mí](docs/capturas/sobre-mi.png) |

## Instalación local

1. Clonar el repositorio en la carpeta del servidor web (Laragon, XAMPP…) e instalar las dependencias:
   ```bash
   git clone https://github.com/MarcoAfHz/egoz.git
   cd egoz
   composer install
   ```
2. Crear una base de datos MySQL e importar `egoz.sql`.
3. Copiar `config.example.php` a `config.php` y rellenar la base de datos, las claves de Stripe (las de prueba sirven), el correo SMTP, reCAPTCHA y DeepL.
4. Abrir la web y entrar al panel en `/admin`.
