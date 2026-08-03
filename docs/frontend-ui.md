# Guía de Diseño y UI Frontend

## 1. Sistema de Diseño e Identidad Visual

La interfaz de la aplicación ha sido diseñada para ofrecer una experiencia estética premium, moderna e intuitiva:

- **Paleta Primaria**: Indigo de Tailwind CSS (`#6366f1` / `indigo-500` como tono principal, graduado desde `indigo-50` hasta `indigo-950`).
- **Paleta Neutral y Superficies**: Slate de Tailwind CSS (sustituye los grises fríos por un tono pizarra moderno en fondos, tarjetas y bordes).
- **Componentes UI**: PrimeVue v4 personalizado con el tema `Aura`. Todos los componentes de PrimeVue (`DataTable`, `Dialog`, `Button`, `InputText`, `Paginator`) adoptan automáticamente los colores semánticos del sistema.

---

## 2. Implementación de Modo Claro y Modo Oscuro

El sistema admite cambio dinámico de tema entre **Modo Claro** y **Modo Oscuro**:

- **Selector Global**: Botón toggle con icono de Sol (`pi-sun`) / Luna (`pi-moon`) ubicado en la barra superior (`Topbar`) y en el layout de invitados (`GuestLayout.vue`).
- **Persistencia**: La selección se guarda en `localStorage.getItem('modo-oscuro')`.
- **Inversión de Logotipo**: El componente `ApplicationLogo.vue` incorpora la clase `dark:brightness-0 dark:invert`. En modo oscuro, el logotipo se transforma automáticamente a blanco puro mediante filtros CSS nativos, garantizando máximo contraste sobre fondos oscuros.
- **Componentes de Formulario Adaptados**:
  - `TextInput.vue`: `bg-white dark:bg-slate-900 border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white`.
  - `PasswordInput.vue`: Incorpora toggle reactivo para mostrar/ocultar contraseña con icono `pi-eye` / `pi-eye-slash` e integración de colores para modo claro/oscuro.
  - `InputLabel.vue`: `text-slate-700 dark:text-slate-200`.
  - `Checkbox.vue`: `border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-indigo-600`.

---

## 3. Layouts de la Aplicación

### 3.1 `AuthenticatedLayout.vue` (Panel Privado)
- **Sidebar Lateral**: Fondo `slate-900` con menú de navegación, perfil de usuario activo y botón de cierre de sesión. En móviles, se transforma en un cajón deslizante con fondo translúcido `backdrop-blur-sm`.
- **Header Topbar**: Fondo `white` en claro y `slate-800` en oscuro, con título dinámico de sección y botón toggle de tema.
- **Notificaciones Globales**: Integra `Toast` de PrimeVue posicionado en `top-right` conectado con las variables flash de Inertia (`$page.props.flash.success` / `$page.props.flash.error`).
- **Diálogos de Confirmación Globales**: Configurado con `ConfirmDialog` de PrimeVue para solicitar confirmación antes de acciones destructivas (ej. eliminar usuario).

### 3.2 `GuestLayout.vue` (Páginas Públicas y Auth)
- **Contenedor**: Fondo `bg-slate-100 dark:bg-slate-950` centrado vertical y horizontalmente.
- **Tarjeta Principal**: `bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xl rounded-2xl`.
- **Toggle de Tema**: Ubicado de forma flotante en la esquina superior derecha (`absolute top-4 right-4`).
