# GoStore

Tienda multiventa en PHP con base de datos MySQL, compatible con XAMPP.

## Inicio rápido

1. Copia la carpeta `GoStore` dentro de `C:\xampp\htdocs`.
2. Inicia Apache y MySQL desde el panel de XAMPP.
3. Abre `http://localhost/GoStore/`.
4. Al abrir la página por primera vez, la aplicación crea la base de datos `gostore`, sus tablas y el usuario administrador.

La conexión predeterminada usa MySQL en `127.0.0.1:3306`, usuario `root` y contraseña vacía. Para otra configuración, define `GOSTORE_DB_HOST`, `GOSTORE_DB_PORT`, `GOSTORE_DB_NAME`, `GOSTORE_DB_USER` y `GOSTORE_DB_PASS` en el entorno de PHP. Al iniciar, la aplicación también crea la cuenta única SuperAdmin, el registro de auditoría y los permisos configurables.

## Administrador inicial

- Usuario: `Admin`
- Contraseña: `63592922Deo`
- Teléfono de pagos y WhatsApp: `925869388`
- Correo: `deopamay@gmail.com`

Cambia la contraseña desde **Mi perfil** después del primer acceso. La cuenta se crea una sola vez; los siguientes accesos no restablecen su contraseña.

## SuperAdmin inicial

- Usuario: `GoStoreAd`
- Contraseña inicial: `925869388GoS`

Inicia sesión con estas credenciales y cambia la contraseña desde **Control SuperAdmin**. El nombre de acceso también se puede cambiar allí; el rol `superadmin` permanece protegido. La contraseña se guarda con `password_hash` y la inicialización no restablece las credenciales después del primer alta.

## Funciones

- Registro e inicio de sesión, edición de perfil y cambio de contraseña.
- Ajustes de usuario con cinco idiomas, tres monedas (PEN, USD y EUR) y cuatro estilos de color, guardados en la cuenta. Los precios se muestran convertidos con tasas fijas y se conservan internamente en soles peruanos.
- Guía de inicio automática al crear una cuenta, también disponible desde el menú para volver a consultarla.
- Catálogo con categorías y búsqueda; el carrusel automático (cada cuatro segundos) se muestra como ventana de presentación al entrar a la tienda.
- Comparación lado a lado de productos de la misma categoría y lista de favoritos persistente por cuenta.
- Código único por producto y por pedido; el historial muestra los códigos de los artículos para identificarlos en la boleta.
- Registro e inicio de sesión disponibles inmediatamente con usuario/correo y contraseña; se valida el formato del correo, sin exigir autenticación de Google ni confirmación por email.
- Consultas de productos enviadas por SMTP al correo configurado del administrador, con la identidad y el correo de la cuenta del cliente.
- Chat en línea entre clientes registrados y administración, con mensajes en tiempo real mediante actualización periódica, indicador de escritura, emojis y adjuntos de imagen/video de hasta 20 MB. Los archivos del chat solo se sirven a los participantes de la conversación. En una consulta de producto, el cliente puede elegir entre chat y WhatsApp personal del administrador.
- Publicación de productos con entre 2 y 4 imágenes y galería automática en la ficha del producto.
- Carrito, compra y medios de pago Yape, Plin, BBVA y contra entrega.
- Notificaciones persistentes: los clientes reciben avisos de productos nuevos y el administrador de pedidos confirmados.
- Panel de administrador con publicación, edición y eliminación de productos, imágenes y cuentas de clientes; al eliminar una cuenta también se borran sus pedidos, reseñas y notificaciones. Cada cliente también puede eliminar su propia cuenta desde **Mi perfil**, confirmando su contraseña. Las cuentas administradoras no se pueden eliminar desde estas opciones.
- Panel SuperAdmin con visión de todos los pedidos activos/retirados, contadores históricos de cuentas y pedidos eliminados o cancelados, auditoría con fecha y hora, restricciones de operaciones para clientes y administrador, cambio de credenciales protegido y limpieza exclusiva de archivos huérfanos de productos/carrusel/chat. El borrado de archivos se limita a nombres reconocidos y vuelve a comprobar referencias en la base de datos.
- La auditoría conserva inicios y cierres de sesión, registros y bajas de cuentas, creación de pedidos, publicación/edición/eliminación de productos, cambios de estado de pedidos, cancelaciones, retiros de vista y operaciones de chat sin almacenar el contenido de los mensajes.
- Seguimiento del pedido con línea de tiempo y estimado de entrega.
- Desde **Mis pedidos**, el cliente puede cancelar pedidos pendientes (se repone el stock) o eliminarlos permanentemente. Los pedidos aceptados/en camino solo se pueden cancelar contactando al administrador; los cancelados y entregados también se pueden eliminar del historial.

Las imágenes de productos se guardan en `uploads/`; los archivos del chat se guardan de forma privada en `uploads/chat-media/`. El servidor PHP debe tener habilitadas las extensiones PDO MySQL y Fileinfo, además de permisos de escritura en esas carpetas.

## Estructura MVC

- `index.php` es el punto de entrada y delega la respuesta al controlador.
- `app/Controllers/StoreController.php` valida el acceso a las páginas privadas y prepara los datos que necesita la vista.
- `app/Controllers/ChatController.php` sirve el chat autenticado, la presencia en línea y los archivos privados de las conversaciones.
- `app/Models/StoreModel.php` concentra las consultas para el catálogo, el carrito, los pedidos y el panel de administración.
- `app/Views/store.php` contiene la plantilla HTML; no ejecuta consultas a la base de datos.
- `app.php` conserva la inicialización de sesión/base de datos y el procesamiento existente de acciones POST.

Para agregar una página, prepara sus datos en el controlador mediante el modelo y presenta esos datos en la vista; evita consultar MySQL desde las plantillas.

El panel de administración separa el resumen, productos, carrusel y pedidos en pantallas independientes (`admin`, `admin-products`, `admin-carousel` y `admin-orders`). Cada pantalla permite exportar sus datos a CSV compatible con Excel (se abre con Excel) o PDF. Los pedidos retirados de la vista de administración se conservan en la base de datos y aparecen en la exportación con su fecha y hora de retiro. El servidor rechaza las acciones de carrito, checkout, reseñas y votos de las cuentas administradoras.

Las consultas de productos y los avisos de pedidos se envían por SMTP. El SMTP es independiente del registro e inicio de sesión: no se necesita configurar correo para crear una cuenta ni para entrar. Para habilitar el envío automático de consultas y avisos, configura `GOSTORE_SMTP_HOST`, `GOSTORE_SMTP_PORT`, `GOSTORE_SMTP_USERNAME`, `GOSTORE_SMTP_PASSWORD`, `GOSTORE_SMTP_ENCRYPTION` (`starttls` o `ssl`) y, opcionalmente, `GOSTORE_SMTP_FROM` en el entorno que ejecuta PHP/Apache o en el archivo local indicado abajo. Los mensajes se entregan al correo actual de la cuenta administradora, leído de la base de datos en cada envío, por lo que los cambios de correo del admin se aplican automáticamente. Para Gmail SMTP, usa una contraseña de aplicación; no guardes credenciales en el código fuente del proyecto. En consultas de producto, el botón de correo intenta el envío SMTP; si no está configurado, abre un borrador de Gmail dirigido al correo actual del administrador para que el usuario pulse enviar. El botón de WhatsApp abre el chat con el mensaje preparado aunque SMTP falle; el usuario debe pulsar Enviar en WhatsApp. El envío automático al correo del admin también se intenta, pero sin SMTP no se puede entregar. El botón con la G no inicia sesión ni autentica con Google. Las opciones de WhatsApp y Gmail de la boleta preparan el código de compra y los códigos de producto para compartir; Gmail abre un borrador.

### Configurar envío Gmail SMTP en Windows/XAMPP (opcional)

Este paso solo es necesario si quieres enviar consultas de productos y avisos de pedidos por correo. No es necesario para registrar usuarios o iniciar sesión.

1. En la cuenta Google que enviará los correos, activa la verificación en dos pasos y crea una **contraseña de aplicación**. No uses la contraseña normal de Gmail ni compartas la contraseña de aplicación.
2. Crea `C:\xampp\gostore-config.php` (fuera de `htdocs`) con este contenido y reemplaza los valores de ejemplo:

```php
<?php
return [
    'GOSTORE_SMTP_HOST' => 'smtp.gmail.com',
    'GOSTORE_SMTP_PORT' => '587',
    'GOSTORE_SMTP_USERNAME' => 'tu-cuenta@gmail.com',
    'GOSTORE_SMTP_PASSWORD' => 'tu contraseña de aplicación de Google',
    'GOSTORE_SMTP_ENCRYPTION' => 'starttls',
    'GOSTORE_SMTP_FROM' => 'tu-cuenta@gmail.com',
];
```

3. Guarda el archivo como `gostore-config.php` (no `gostore-config.php.txt`). No lo coloques en `htdocs` ni lo subas a un repositorio.
4. Reinicia Apache desde el panel de XAMPP para cargar la configuración.

Las variables definidas en Windows tienen prioridad sobre el archivo local. Si el SMTP no está configurado, el registro y el inicio de sesión siguen funcionando, pero los correos de consultas y pedidos no se enviarán.

El administrador puede abrir **Ver productos** para consultar el catálogo, sin acceder a fichas de compra ni controles para añadir productos al carrito. Al registrarse, el usuario inicia sesión y recibe una guía de uso; al confirmarla, no volverá a mostrarse en sus siguientes accesos.
