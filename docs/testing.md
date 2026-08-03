# Suite de Pruebas Automatizadas y Cobertura de Testing

## 1. Descripción de la Suite de Pruebas

El proyecto **CMS SSR FRANCIS** mantiene una suite automatizada de pruebas unitarias y de integración desarrollada en PHPUnit / Laravel Testing. 

Todas las pruebas están diseñadas para ejecutarse tanto en entornos locales como en pipelines de integración continua (CI/CD), asegurando la calidad del código, la estabilidad de los contratos API y la seguridad en el control de acceso.

---

## 2. Comandos de Ejecución

### Ejecución estándar vía Artisan:
```bash
php artisan test
```

### Ejecución directa vía PHPUnit:
```bash
vendor/bin/phpunit
```

---

## 3. Cobertura y Métricas de Calidad

- **Pruebas Totales**: 55
- **Pruebas Pasadas**: 55 (100% Éxito)
- **Aserciones Ejecutadas**: 168
- **Tiempo de Ejecución medio**: ~1.9 segundos

---

## 4. Estructura y Cobertura por Módulo

### 4.1 Autenticación (`Tests\Feature\Auth\AuthenticationTest`)
- Acceso a pantalla de Login.
- Autenticación correcta con credenciales válidas.
- Rechazo de inicio de sesión con contraseña incorrecta.
- Cierre de sesión (`Logout`) e invalidación del token de sesión.
- **Middleware `VerificarUsuarioActivo`**: Verifica que usuarios con `active = false` sean expulsados automáticamente al intentar acceder a cualquier ruta protegida.

### 4.2 Gestión de Usuarios (`Tests\Feature\Controllers\UserControllerTest` & `Tests\Unit\Services\UserServiceTest`)
- **Index**: Renderiza el listado paginado para administradores. Rechaza el acceso a usuarios no administradores (403 Forbidden).
- **Create & Store**: Valida campos obligatorios y formato de email. Permite contraseña personalizada o genera temporal, asigna el rol y envía notificación de bienvenida por email.
- **Edit & Update**: Actualiza datos de usuario, encripta nueva contraseña opcionalmente si es proporcionada y sincroniza roles mediante `syncRoles`.
- **Toggle Active**: Cambia el estado activo/inactivo. Verifica que un administrador NO pueda desactivar su propia cuenta (`RuntimeException`).
- **Destroy (Soft Delete)**: Realiza borrado suave enviando al usuario a la papelera. Verifica que un administrador NO pueda enviar a la papelera su propia cuenta (`RuntimeException`).
- **Trashed (Papelera)**: Renderiza el listado de usuarios eliminados suavemente (`esPapelera = true`).
- **Restore (Restaurar)**: Restaura un usuario previamente en la papelera devolviéndole su acceso.
- **ForceDelete (Borrado Definitivo)**: Elimina de forma permanente al usuario de la base de datos de manera irreversible.

### 4.3 Verificación de Email y Contraseñas
- `EmailVerificationTest`: Valida generación y consumo de rutas firmadas temporales para verificación de correo.
- `PasswordUpdateTest`: Actualización segura de contraseña con verificación de hash actual.
- `PasswordConfirmationTest`: Confirmación de contraseña previa a acciones sensibles.
- `ProfileTest`: Modificación de datos del perfil propio y eliminación de cuenta de usuario con aserción de soft delete.
