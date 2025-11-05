# PeopleApp-REST

Proyecto REST simple para manejar usuarios y contactos.

Este repo contiene una pequeña API en PHP que expone endpoints para registro/login de usuarios y CRUD de contactos.

Requisitos
- Docker 20+ y Docker Compose
- Postman (para probar los endpoints)

Pasos para levantar el proyecto (Docker Compose)

1. Abrir una terminal en la raíz del proyecto:

```bash
cd </ruta>
```

2. Levantar los contenedores (construye la imagen web y crea la DB si no existe):

```bash
docker compose up --build
```

3. Acceder a la aplicación:

- URL base web: http://localhost:8080

Credenciales de la base de datos (usadas internamente por la app)
- Host: peopleapp_db (nombre del servicio en docker-compose)
- Base de datos: people
- Usuario: upeople
- Contraseña: 1234

Notas sobre la inicialización de la BD
- El archivo `create_db.sql` se monta en `/docker-entrypoint-initdb.d/` del contenedor MySQL, por lo que se ejecutará la primera vez que se cree la base de datos. Si ya existe un volumen `db_data`, el script no se volverá a ejecutar; borrar el volumen forzará la re-inicialización:

```bash
docker compose down
docker volume rm peopleapp-rest_db_data || true
docker compose up --build
```

Probar los endpoints con Postman

1. Importa `postman_collection.json` desde la raíz del repo en Postman.

2. Asegúrate de que la variable `baseUrl` (si la colección la usa) esté configurada a:

```
http://localhost:8080
```

3. Endpoints de ejemplo (según rutas del proyecto):
- Registro de usuario: POST http://localhost:8080/usuarios/registro (ver `01_usuarios_registro.txt` para ejemplo)
- Login: POST http://localhost:8080/usuarios/login (ver `02_usuarios_login.txt`)
- Obtener contactos: GET http://localhost:8080/contactos (ver `03_contactos_GET.txt`)
- Crear contacto: POST http://localhost:8080/contactos (ver `04_contactos_POST.txt`)
- Actualizar contacto: PUT http://localhost:8080/contactos/{id} (ver `05_contactos_PUT.txt`)
- Eliminar contacto: DELETE http://localhost:8080/contactos/{id} (ver `06_contactos_DELETE.txt`)

4. Si alguna petición necesita cabeceras (Content-Type: application/json, Authorization, etc.), revisa los archivos `.txt` en la raíz para ejemplos de body y cabeceras.

Problemas comunes
- Si el contenedor MySQL no arranca, revisa los logs con:

```bash
docker compose logs db
```

- Si la API devuelve errores 500, revisa los logs web:

```bash
docker compose logs web
```