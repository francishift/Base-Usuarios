# Arquitectura del Sistema

## 1. Patrones de Diseño y Principios SOLID

La aplicación **CMS Base de Usuarios** está diseñada siguiendo una arquitectura limpia (Clean Architecture), altamente cohesiva y de bajo acoplamiento:

### 1.1 Single Responsibility Principle (SRP)
- **Controllers Delgados (`Thin Controllers`)**: Los controladores en `app/Http/Controllers/` se limitan a orquestar las peticiones HTTP y retornar respuestas Inertia. Ninguna lógica de persistencia ni reglas de negocio reside en los controladores.
- **Servicios Dedicados (`Services`)**: La lógica de negocio pesada y compleja reside en clases de servicio dedicadas dentro de `app/Services/` (ej. `UserService`).
- **Form Requests (`Http/Requests`)**: Las validaciones de datos entrantes están 100% aisladas en clases `StoreUserRequest`, `UpdateUserRequest`, `LoginRequest`, etc.
- **Transformación de Datos (`API Resources / DTOs`)**: Las respuestas hacia el frontend se transforman a través de `UserResource`, garantizando que nunca se expongan modelos Eloquent crudos ni columnas sensibles (`password`, `remember_token`).

### 1.2 Open/Closed Principle (OCP)
- **Sistema de Roles Extensible**: La gestión de roles mediante `spatie/laravel-permission` y el composable `useRoles.js` permite añadir nuevos roles o permisos sin alterar el código existente de los controladores ni de las vistas.

### 1.3 Dependency Inversion Principle (DIP)
- Inyección de dependencias en constructores de controladores y servicios (ej. `public function __construct(protected readonly UserService $userService)`).

---

## 2. Aislamiento de Almacenamiento y Servidores (Redis & DB)

### 2.1 Aislamiento de Redis
En servidores compartidos (ej. Plesk / cPanel), donde múltiples aplicaciones hacen uso del mismo servidor de Redis en `127.0.0.1:6379`, se ha implementado un esquema de aislamiento riguroso en `.env` y `config/database.php`:
- **Instancia Base de Datos Principal**: `REDIS_DB=4`
- **Instancia Base de Datos de Caché**: `REDIS_CACHE_DB=5`
- **Prefijo de Claves Global (`REDIS_PREFIX`)**: `cms_ssr_francis_`
- **Prefijo de Caché (`CACHE_PREFIX`)**: `cms_ssr_francis_`

Esto evita la contaminación cruzada de claves de caché, sesiones y colas entre distintos sitios web alojados en el mismo servidor.

### 2.2 Optimización de Base de Datos MySQL
- **Eager Loading Anti N+1**: Las consultas de listados cargan explícitamente sus relaciones necesarias (ej. `User::with('roles')`).
- **Índices de Alto Rendimiento**: Migración `2026_07_13_120054_add_indexes_to_users_table.php` que añade índices simples y compuestos en los campos `email` y `active` para realizar búsquedas instantáneas entre miles de registros.
- **Paginación Server-Side**: Uso obligatorio de `paginate(20)` para evitar desbordamientos de memoria al cargar grandes volúmenes de datos.

---

## 3. Infraestructura y Seguridad en Producción

### 3.1 Proxy Inverso (Nginx + Apache en Plesk)
En entornos donde Nginx gestiona las conexiones SSL (puerto 443) y proxifica internamente hacia Apache/PHP en HTTP, se han aplicado los siguientes mecanismos de seguridad:
- **HTTPS Forzado**: En `AppServiceProvider::boot()`, se evalúa el entorno y la URL de aplicación para forzar el esquema `https://` vía `URL::forceScheme('https')`. Esto garantiza que los enlaces, archivos estáticos de Vite y payloads de Inertia se sirvan con protocolo seguro `https://`, eliminando errores de contenido mixto (Mixed Content).
- **Manejo de Handler de Errores para Plesk**: En `AppServiceProvider::register()`, se intercepta el error handler para suprimir advertencias de `open_basedir` generadas por herramientas del entorno sin afectar al resto de excepciones de Laravel.

---

## 4. Arquitectura de Sincronización de Tema (Modo Claro / Oscuro)

Para evitar desincronizaciones entre Tailwind CSS y PrimeVue:
- **Tailwind CSS v4**: En `resources/css/app.css`, se define la variante personalizada `@custom-variant dark (&:where(.dark, .dark *));`. Esto vincula todas las utilidades `dark:` de Tailwind directamente con la presencia de la clase `.dark` en la etiqueta `<html>`.
- **PrimeVue 4**: Configurado con `darkModeSelector: '.dark'`.
- **Script de Inicialización Síncrona**: En `<head>` de `resources/views/app.blade.php`, se ejecuta un script IIFE en tiempo de renderizado que lee `localStorage.getItem('modo-oscuro')` e inyecta la clase `.dark` antes de que el navegador dibuje el DOM, eliminando por completo cualquier parpadeo visual (FOUC).
