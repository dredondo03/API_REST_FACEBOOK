# Facebook API · Taller Backend

API REST tipo Facebook construida con Laravel. Permite registrarse, iniciar sesión, crear publicaciones con varias imágenes, listarlas con paginación y búsqueda, comentarlas, darles "Me gusta" y eliminarlas. Incluye notificaciones por correo y documentación generada automáticamente.

| | |
|---|---|
| **Curso** | Arquitectura y Desarrollo Backend |
| **Institución** | Universidad Autónoma de Bucaramanga |
| **Docente** | Fabián Enrique Suárez Carvajal |
| **Estudiante** | _Tu nombre completo_ |

---

## Tecnologías

- PHP 8.2
- Laravel 12
- SQLite
- Laravel Sanctum (autenticación por tokens)
- Scribe (documentación de la API)
- Postman (pruebas)

---

## Instalación

1. Clonar el repositorio y entrar a la carpeta:

   ```bash
   git clone https://github.com/TU_USUARIO/TU_REPOSITORIO.git
   cd TU_REPOSITORIO
   ```

2. Instalar dependencias:

   ```bash
   composer install
   ```

3. Crear el archivo de entorno y la clave de la aplicación:

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. Revisar en `.env`:

   ```env
   APP_URL=http://127.0.0.1:8000
   DB_CONNECTION=sqlite
   MAIL_MAILER=log
   ```

5. Crear las tablas (si pregunta por crear `database.sqlite`, responder **yes**):

   ```bash
   php artisan migrate
   ```

6. Crear el enlace para ver las imágenes:

   ```bash
   php artisan storage:link
   ```

7. Generar la documentación y levantar el servidor:

   ```bash
   php artisan scribe:generate
   php artisan serve
   ```

La API queda disponible en `http://127.0.0.1:8000/api/v1` y la documentación en `http://127.0.0.1:8000/docs`.

---

## Modelo de datos

| Tabla | Descripción | Relaciones |
|---|---|---|
| `users` | Usuarios registrados | Un usuario tiene muchos posts |
| `posts` | Publicaciones (`title`, `content`, `likes_count`, `user_id`) | Pertenece a un usuario; tiene muchas imágenes y comentarios |
| `post_images` | Rutas de las imágenes de cada post | Pertenece a un post |
| `comments` | Comentarios (`author`, `content`) | Pertenece a un post |
| `likes` | Tabla intermedia `user_id` + `post_id` (única por pareja) | Relación muchos a muchos entre usuarios y posts |
| `personal_access_tokens` | Tokens de Sanctum | Pertenece a un usuario |

Al eliminar un post se eliminan en cascada sus imágenes, comentarios y likes, y también los archivos de imagen del almacenamiento.

---

## Autenticación

1. Registrarse en `POST /api/v1/register` o iniciar sesión en `POST /api/v1/login`.
2. La respuesta incluye un `token`.
3. Enviar ese token en las rutas protegidas con el header:

   ```
   Authorization: Bearer <token>
   ```

4. Todas las peticiones deben llevar también `Accept: application/json`.

---

## Endpoints

Todas las rutas usan el prefijo `/api/v1`.

| Método | Ruta | Token | Descripción |
|---|---|---|---|
| POST | `/register` | No | Registra un usuario y devuelve su token |
| POST | `/login` | No | Inicia sesión y devuelve un token |
| GET | `/me` | Sí | Datos del usuario autenticado |
| POST | `/logout` | Sí | Cierra sesión (invalida el token) |
| GET | `/posts` | No | Lista paginada de publicaciones. Acepta `search`, `page`, `per_page` |
| GET | `/posts/{id}` | No | Una publicación con imágenes, comentarios y autor |
| POST | `/posts` | Sí | Crea una publicación con imágenes (`multipart/form-data`) |
| DELETE | `/posts/{id}` | Sí | Elimina una publicación (solo su autor) |
| POST | `/posts/{id}/like` | Sí | Da o quita el "Me gusta" del usuario |
| GET | `/posts/{id}/comments` | No | Lista los comentarios de una publicación |
| POST | `/posts/{id}/comments` | Sí | Comenta y notifica por correo al autor del post |

### Ejemplos

**Registrar usuario** (`POST /api/v1/register`, JSON):

```json
{
  "name": "Ana",
  "email": "ana@test.com",
  "password": "12345678",
  "password_confirmation": "12345678"
}
```

**Crear publicación** (`POST /api/v1/posts`, form-data):

| Campo | Tipo | Valor |
|---|---|---|
| `title` | Text | Mi viaje |
| `content` | Text | Fotos de la playa |
| `images[]` | File | foto1.jpg |
| `images[]` | File | foto2.jpg |

**Listar con paginación y búsqueda:**

```
GET /api/v1/posts?search=viaje&page=1&per_page=5
```

**Comentar** (`POST /api/v1/posts/1/comments`, JSON):

```json
{
  "content": "¡Qué buenas fotos!"
}
```

---

## Códigos de respuesta

| Código | Significado | Ejemplo en esta API |
|---|---|---|
| 200 | OK | Listar posts, ver un post, dar like, login |
| 201 | Creado | Registrar usuario, crear post, comentar |
| 204 | Sin contenido | Eliminar post |
| 401 | No autenticado | Petición sin token o con token inválido |
| 403 | Prohibido | Intentar eliminar el post de otro usuario |
| 404 | No encontrado | Post que no existe |
| 405 | Método no permitido | Usar GET en `/logout` |
| 422 | Datos inválidos | Falta el título, email repetido, archivo que no es imagen |

---

## Retos implementados

| Reto | Cómo se implementó |
|---|---|
| Versionado | Todas las rutas bajo `Route::prefix('v1')` → `/api/v1/...` |
| Paginación | `paginate()` en el listado, con `page` y `per_page` (máximo 50) |
| Búsqueda por título | Parámetro `search` con `WHERE title LIKE %texto%`, compatible con la paginación |
| Autenticación con Sanctum | Registro, login, logout y rutas protegidas con `auth:sanctum` |
| Likes por usuario | Tabla `likes` con restricción única; cada usuario da o quita su like con `toggle()` |
| Notificaciones por email | `NewCommentNotification` avisa al autor del post cuando alguien más lo comenta |
| Documentación | Scribe genera la documentación en `/docs`, más OpenAPI y colección de Postman |

---

## Notificaciones por correo

Con `MAIL_MAILER=log`, los correos no se envían a internet: se escriben al final de `storage/logs/laravel.log`. Para recibirlos en una bandeja de pruebas se puede usar Mailtrap configurando `MAIL_MAILER=smtp` y sus credenciales en `.env`.

---

## Pruebas con Postman

La colección está en la raíz del proyecto: `facebook-api-postman-collection.json`.

1. En Postman: **Import** → seleccionar el archivo.
2. Revisar las variables de la colección:

   | Variable | Valor |
   |---|---|
   | `base_url` | `http://127.0.0.1:8000` |
   | `token` | Se llena solo al registrarse o iniciar sesión |
   | `post_id` | ID del post con el que se quiere probar |

3. Ejecutar en orden: **Autenticación** (registrar o iniciar sesión) → **Publicaciones** → **Comentarios**.

---

## Estructura principal

```
app/
├── Http/Controllers/
│   ├── AuthController.php
│   ├── PostController.php
│   └── CommentController.php
├── Models/
│   ├── User.php
│   ├── Post.php
│   ├── PostImage.php
│   └── Comment.php
└── Notifications/
    └── NewCommentNotification.php
database/migrations/        Tablas de la base de datos
routes/api.php              Rutas de la API (v1)
config/scribe.php           Configuración de la documentación
facebook-api-postman-collection.json
```

---

## Historial de desarrollo

El proyecto se construyó por etapas, cada una registrada con un commit descriptivo (`feat:`, `fix:`, `test:`, `docs:`). Se puede revisar con:

```bash
git log --oneline
```
