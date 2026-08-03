# Guía de Instalación y Despliegue en Servidor

Esta guía detalla los pasos completos para instalar, configurar y desplegar la aplicación **CMS Base de Usuarios** en entornos de desarrollo local o servidores de producción.

---

## 1. Requisitos del Sistema

- **PHP**: v8.4+ con extensiones `pdo_mysql`, `mbstring`, `openssl`, `bcmath`, `curl`, `redis`, `gd`, `zip`.
- **Composer**: v2.6+
- **Node.js**: v18+ o v20+ con `npm`
- **Base de Datos**: MySQL 8.0+, MariaDB 10.5+ o SQLite
- **Servidor Redis**: v6.x+ corriendo en `127.0.0.1:6379`
- **Servidor Web**: Nginx (Proxy Inverso) + Apache (con módulo `mod_rewrite` activo)

---

## 2. Proceso Paso a Paso

### 2.1 Clonación e Instalación de Dependencias PHP
```bash
# Clonar el repositorio
git clone https://github.com/francishift/Base-Usuarios.git
cd Base-Usuarios

# Instalar dependencias de Composer optimizadas para producción
composer install --no-interaction --prefer-dist --optimize-autoloader
```

### 2.2 Configuración del Archivo de Entorno (`.env`)
```bash
# Copiar el archivo de plantilla
cp .env.example .env

# Generar la clave de encriptación de la aplicación
php artisan key:generate
```

Editar `.env` verificando los siguientes parámetros de base de datos y Redis:
```env
APP_NAME="CMS SSR FRANCIS"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://ssr.francisvalenzuela.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tu_base_de_datos
DB_USERNAME=tu_usuario
DB_PASSWORD="tu_password"

SESSION_DRIVER=redis
CACHE_STORE=redis
CACHE_PREFIX=cms_ssr_francis_

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_DB=4
REDIS_CACHE_DB=5
REDIS_PREFIX=cms_ssr_francis_
```

### 2.3 Migraciones y Sembrado de Datos
```bash
# Ejecutar migraciones de base de datos
php artisan migrate --force

# Sembrar los roles iniciales y el usuario administrador
php artisan db:seed --class=RolesSeeder
```

### 2.4 Compilación de Assets Frontend
```bash
# Instalar paquetes de npm
npm install

# Compilar los bundles estáticos de producción con Vite
npm run build
```

### 2.5 Limpieza y Optimización de Caché
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 3. Credenciales por Defecto

Tras ejecutar `RolesSeeder`, el acceso inicial al panel de administración es:
- **URL**: `https://ssr.francisvalenzuela.com/login`
- **Email**: `admin@example.com`
- **Contraseña**: `Admin1234!`
- **Rol**: `admin`
