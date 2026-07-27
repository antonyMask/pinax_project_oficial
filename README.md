# Pinax — Sistema Contable

Pinax es un sistema contable académico desarrollado con una arquitectura separada en frontend, API y base de datos.

## Arquitectura

```text
Navegador
    ↓
Frontend Laravel + AdminLTE
    ↓ Peticiones HTTP
API Node.js + Express
    ↓
MySQL
```

El frontend Laravel no se conecta directamente a MySQL. Toda consulta, autenticación y modificación de datos debe realizarse mediante la API.

## Estructura del repositorio

```text
pinax_project/
├── pinax-api/        API desarrollada con Node.js y Express
├── pinax-frontend/   Frontend Laravel con AdminLTE
└── README.md
```
## Estrategia de ramas

| Rama | Propósito | Estado |
|---|---|---|
| `main` | Versión oficial, estable e integrada del sistema Pinax. Todo cambio validado debe llegar aquí. | Activa |
| `integration/asientos-clean` | Respaldo de la integración limpia del módulo de Asientos Contables, ya validada e integrada en `main`. | Respaldo temporal |
| `agent/reproducible-team-setup` | Rama histórica usada para preparar una instalación reproducible del proyecto. Contiene una implementación anterior de Asientos; no debe usarse como base de trabajo. | Histórica / no usar para nuevas funciones |

## Requisitos

Cada integrante debe tener instalado:

- Git
- PHP 8.2 o superior
- Composer 2
- Node.js y npm
- MySQL o MariaDB
- XAMPP puede utilizarse para PHP y MySQL

Comprobar las instalaciones:

```powershell
php -v
composer --version
node --version
npm --version
git --version
```

## 1. Clonar el repositorio

```powershell
git clone https://github.com/antonyMask/pinax_project.git
cd pinax_project
```

## 2. Preparar la base de datos

Iniciar MySQL desde XAMPP o el servidor MySQL utilizado localmente.

Crear la base de datos:

Iniciar MySQL desde XAMPP o el servidor utilizado localmente.

Desde MySQL Workbench, ejecutar los scripts en el siguiente orden:

1. `database/01_sistema_contable_pinax.sql`
2. `database/02_procedimientos_almacenados.sql`

El primer script crea la base de datos, construye las 22 tablas,
establece sus relaciones e inserta los datos de prueba.

El segundo script crea los 15 procedimientos almacenados utilizados
por la API.

> **Advertencia:** El primer script utiliza `DROP TABLE IF EXISTS` y
> reconstruye las tablas. Debe ejecutarse en una instalación nueva o
> cuando se quiera restaurar completamente la base incluida en el
> repositorio.

Cada instalación local utiliza su propia base de datos. Los registros
creados posteriormente en una computadora no aparecen automáticamente
en las demás.

## 3. Instalar la API

Entrar a la carpeta:

```powershell
cd pinax-api
```

Instalar las dependencias:

```powershell
npm install
```

Crear el archivo de configuración local:

```powershell
Copy-Item .env.example .env
```

Generar un secreto JWT:

```powershell
node -e "console.log(require('crypto').randomBytes(64).toString('hex'))"
```

Copiar el resultado en `JWT_SECRET` dentro de `pinax-api/.env`.

También se deben ajustar las credenciales locales de MySQL:

```dotenv
PORT=3000

DB_HOST=127.0.0.1
DB_PORT=3306
DB_USER=root
DB_PASSWORD=
DB_NAME=sistema_contable_pinax

JWT_SECRET=SECRETO_GENERADO_LOCALMENTE
JWT_EXPIRES_IN=2h
JWT_ISSUER=pinax-api
JWT_AUDIENCE=pinax-frontend

BCRYPT_ROUNDS=12
```

Levantar la API:

```powershell
npm run dev
```

La terminal debe mostrar:

```text
Conexion exitosa a MySql
Servidor Pinax ejecutandose en el puerto 3000
```

Comprobar en el navegador:

```text
http://127.0.0.1:3000/
```

## 4. Instalar el frontend Laravel

Abrir otra terminal y entrar al frontend:

```powershell
cd pinax-frontend
```

Ejecutar el instalador únicamente durante la primera instalación:

```powershell
composer run setup
```

Este comando:

- Instala las dependencias de Composer.
- Crea `.env` desde `.env.example`.
- Genera `APP_KEY`.
- Publica los recursos de AdminLTE.
- Instala las dependencias de npm.
- Compila los recursos de Vite.

El frontend no necesita ejecutar migraciones porque no utiliza una base de datos propia.

Verificar AdminLTE:

```powershell
php artisan adminlte:status
```

Los recursos `assets` y `translations` deben aparecer como `Installed`.

Levantar Laravel:

```powershell
php artisan serve
```

Abrir:

```text
http://127.0.0.1:8000/login
```

## 5. Orden para iniciar Pinax

Se deben mantener dos terminales abiertas.

### Terminal 1 — API

```powershell
cd pinax-api
npm run dev
```

### Terminal 2 — Frontend

```powershell
cd pinax-frontend
php artisan serve
```

Primero debe iniciar MySQL, después la API y finalmente Laravel.

## Solución de problemas

### No aparecen todos los estilos o iconos

```powershell
cd pinax-frontend
php artisan adminlte:install --only=assets --only=translations
php artisan optimize:clear
php artisan adminlte:status
```

Después recargar el navegador con `Ctrl + F5`.

### Laravel no se conecta con la API

Comprobar que `pinax-frontend/.env` contenga:

```dotenv
PINAX_API_URL=http://127.0.0.1:3000/api
PINAX_API_TIMEOUT=10
```

Después:

```powershell
php artisan optimize:clear
```

### La API devuelve error al iniciar sesión

Comprobar:

- Que MySQL esté iniciado.
- Que la API muestre una conexión exitosa.
- Que `JWT_SECRET` tenga al menos 64 caracteres.
- Que el usuario exista en la base local.
- Que `IND_USR` tenga el valor `1`.
- Que la contraseña almacenada sea un hash válido.

## Seguridad

Nunca se deben subir al repositorio:

- Archivos `.env`.
- Contraseñas reales.
- Secretos JWT.
- Credenciales de bases de datos.
- Carpetas `node_modules`.
- Carpetas `vendor`.
- Registros de ejecución o archivos temporales.

Los archivos `.env.example` sí deben subirse porque solamente contienen plantillas sin secretos.

## Tecnologías

- Laravel 12
- AdminLTE 3 mediante Composer
- Node.js
- Express
- MySQL/MariaDB
- JWT
- Blade
- Bootstrap
