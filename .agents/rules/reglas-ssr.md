---
trigger: always_on
---

# Reglas Generales del Proyecto (CMS SSR)

- Comunícate siempre en castellano.
- Comentarios en el código siempre en castellano.
- Esto es una aplicación hecha en Laravel + VUE + Inertia con SSR, con idea de optimización crear un CMS orientado al SEO, tendra una parte backend privado y otra pública.
- Cualquier nuevo controlador, servicio o componente que haya que desarrollar, adoptar una arquitectura que permita miles de registros sin afectar al rendimiento, pensar en un sistema con miles de usuarios y peticiones.
- Cuando creamos un nuevo crud o función, usar siempre nuestros componentes o composables cuando sea necesario, si es necesario crear alguno nuevo reutilizable, preguntar antes.
- Evitar el código spaguetti al 100%.
- Antes de instalar librerias externas o dependencias, consultar para analizar las ventajs o inconvenintes.
- Evitar prioritariemnete n+1 en las queries.
- Usar siempre funciones y código profesional de Laravel, pensando en mantenibilidad y que sea cualquier funcion que se haga, intentar siempre hacerlo bajo los parametros profesionales de laravel.
- Priorizar la seguridad bajo las reglas especificas de laravel, validaciones de campos para evitar injection sql y xss en los formularios.
- Si creas archivos o scripts temporales para pruebas o depuración durante el desarrollo, asegúrate siempre de borrarlos al finalizar (manteniendo intacta la suite de pruebas unitarias e integración en tests/).
- Clean Code (Código Limpio): Exige que el código siga estándares de legibilidad, convenciones de nomenclatura claras y esté bien documentado.
- Principios SOLID: Son cinco principios de diseño que hacen que el software sea más fácil de mantener y expandir con el tiempo.
- Arquitectura Limpia (Clean Architecture) / Arquitectura Hexagonal: Separa la lógica del negocio de la interfaz y la base de datos. Si mañana cambias de servidor o de diseño, el núcleo de la app no se ve afectado.
- Alta cohesión y bajo acoplamiento: Significa que las diferentes partes de la aplicación son independientes entre sí. Si falla un módulo, no colapsa toda la app.
- Cobertura de pruebas (Testing): Pide que el código incluya Pruebas Unitarias (Unit Testing) y Pruebas de Integración automatizadas.
- Escalabilidad Horizontal y Vertical: La capacidad de la aplicación y la base de datos para crecer sin perder rendimiento a medida que aumentan los usuarios o el volumen de datos.
- Optimización de Base de Datos (Database Optimization): Exige un buen diseño relacional (o no relacional, según el caso), el uso correcto de Índices (Indexing) para que las búsquedas entre millones de registros sean instantáneas, y evitar cuellos de botella.
- Paginación y Lazy Loading (Carga diferida): Para que la aplicación no intente cargar 100,000 registros de golpe en la pantalla, sino solo los que el usuario está viendo en ese momento. Para datos pesados o secundarios (como estadísticas o listados de filtros) usar Inertia::lazy() o Inertia::defer() para no bloquear la carga inicial.
- API Resources (No pasar modelos Eloquent crudos): Prohibido enviar modelos Eloquent crudos al frontend a través de Inertia. Utilizar siempre API Resources de Laravel (o DTOs) para formatear, filtrar y securizar los datos enviados a los componentes Vue.
- Revisar en las subidas a Github que no se suben datos sensibles.