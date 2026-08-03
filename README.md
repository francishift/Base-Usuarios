# CMS Base de Usuarios - Sistema de Administración

[![Laravel Version](https://img.shields.io/badge/Laravel-13.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP Version](https://img.shields.io/badge/PHP-8.4-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![Vue.js Version](https://img.shields.io/badge/Vue.js-3.x-4FC08D?style=for-the-badge&logo=vuedotjs&logoColor=white)](https://vuejs.org)
[![Inertia.js](https://img.shields.io/badge/Inertia.js-v2-9553E9?style=for-the-badge&logo=inertia&logoColor=white)](https://inertiajs.com)
[![PrimeVue](https://img.shields.io/badge/PrimeVue-4.x-41B883?style=for-the-badge&logo=prime&logoColor=white)](https://primevue.org)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-v4.0-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white)](https://tailwindcss.com)
[![Tests Status](https://img.shields.io/badge/Tests-54%2F54%20PASSED-brightgreen?style=for-the-badge&logo=php)](docs/testing.md)

**CMS Base de Usuarios** es un sistema de administración empresarial optimizado para el rendimiento, desarrollado sobre la arquitectura **Laravel 13 + Inertia.js (Vue 3) + PrimeVue 4 + Tailwind CSS v4**. Está diseñado bajo estándares de alta disponibilidad, Clean Architecture, SOLID y soporte para miles de usuarios y registros sin degradación de rendimiento.

---

## 🚀 Características Principales

### 🔐 1. Gestión Profesional de Usuarios & RBAC (Spatie)
- **Roles preconfigurados**: `admin` (Administrador), `gestor` (Gestor) y `cliente` (Cliente).
- **Inertia API Resources**: Ningún modelo Eloquent crudo expuesto al frontend.
- **Validación de Seguridad**: Reglas globales de contraseñas (mínimo 8 caracteres, mayúsculas, minúsculas, números, símbolos y verificación de contraseñas no comprometidas).

### 🗑️ 2. Soft Delete & Papelera Integrada
- Eliminación suave (`softDeletes`) de usuarios.
- **Pestaña de Papelera con badge en vivo**: Permite ver, restaurar usuarios o eliminarlos definitivamente de forma permanente.

### 🔑 3. Generador de Contraseñas Seguras
- Formulario de alta con botón para **generar contraseñas aleatorias seguras** (Fisher-Yates) que cumplen los requisitos del sistema.
- **Visualización y Copiado**: Previsualización y botón de copia rápida al portapapeles.
- Respaldo en backend si se deja en blanco para generar clave temporal y enviar por email (`BienvenidaUsuario`).

### 🎨 4. Sistema de Diseño (Emerald & Dark Zinc)
- **Paleta tailoreada**: Fondo negro mate/zinc (`#09090b`) con acentos verde esmeralda (`#10b981`).
- **Sincronización Modo Claro / Oscuro**: Script en `<head>` que elimina por completo el parpadeo de pantalla (FOUC) y sincroniza PrimeVue con Tailwind v4.
- **Fuente Tipográfica 100% Local**: `Hanken Grotesk` empaquetada vía npm (cero peticiones externas o CDNs, cumplimiento RGPD).

---

## 🛠️ Stack Tecnológico

- **PHP**: 8.4+
- **Framework Backend**: Laravel 13.x
- **Frontend Core**: Vue 3 (Composition API, `<script setup>`)
- **Adaptador SPA**: Inertia.js (v2)
- **UI Components**: PrimeVue 4 (Tema Aura personalizado)
- **Estilos CSS**: Tailwind CSS v4 (`@theme`)
- **Base de datos**: MySQL / PostgreSQL / SQLite
- **Almacenamiento en Caché / Sesión / Colas**: Redis (Aislamiento de DBs para servidores compartidos)
- **Iconos**: PrimeIcons & Lucide Icons

---

## 📥 Guía de Instalación Rápida

### 1. Clonar el repositorio:
```bash
git clone https://github.com/francishift/Base-Usuarios.git
cd Base-Usuarios
```

### 2. Instalar dependencias de PHP y Node:
```bash
composer install
npm install
```

### 3. Configurar el archivo de entorno:
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Configurar Base de Datos e Iniciar Seeders:
```bash
php artisan migrate --seed
```

> **Credenciales de acceso por defecto (Seeder):**
> - **Email**: `admin@example.com`
> - **Contraseña**: `Admin1234!`

### 5. Compilar recursos con Vite:
```bash
# Entorno de desarrollo
npm run dev

# Compilar para producción
npm run build
```

---

## 🧪 Pruebas Automatizadas (Testing)

El proyecto cuenta con una suite completa de pruebas unitarias y de integración en PHPUnit:

```bash
php artisan test
```

- **Cobertura**: 54 Pruebas (100% OK, 0 fallos).
- **Aserciones**: 167 aserciones comprobando autenticación, roles, middleware de usuarios activos, Soft Deletes, restauración y contraseñas.

---

## 📚 Documentación Técnica Detallada

La carpeta [`docs/`](docs/) incluye manuales técnicos sobre cada módulo:

- [📄 Stack Tecnológico](docs/stack.md)
- [📄 Módulo de Usuarios & Rutas API](docs/modulo-usuarios.md)
- [📄 Arquitectura Clean & SOLID](docs/arquitectura.md)
- [📄 Frontend & Sistema de Diseño UI](docs/frontend-ui.md)
- [📄 Guía de Instalación](docs/instalacion.md)
- [📄 Suite de Pruebas (Testing)](docs/testing.md)
- [📄 Reglas de Desarrollo](docs/reglas-desarrollo.md)

---

## 📄 Licencia

Este proyecto es software de código abierto bajo la licencia [MIT](LICENSE).
