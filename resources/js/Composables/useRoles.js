/**
 * Composable reutilizable para la gestión de roles en el frontend.
 *
 * Centraliza las etiquetas y colores de los roles para evitar
 * código duplicado en los componentes Vue.
 *
 * Uso:
 *   import { useRoles } from '@/Composables/useRoles.js'
 *   const { etiquetaRol, colorRol, etiquetas } = useRoles()
 */
export function useRoles() {
    /** Mapa de nombre interno → etiqueta legible en castellano */
    const etiquetaRol = {
        admin:   'Administrador',
        gestor:  'Gestor',
        cliente: 'Cliente',
    };

    /** Mapa de nombre interno → clases CSS del badge */
    const colorRol = {
        admin:   'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300 border border-purple-200 dark:border-purple-800/50',
        gestor:  'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 border border-blue-200 dark:border-blue-800/50',
        cliente: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/50',
    };

    /**
     * Devuelve la etiqueta legible de un rol.
     * Si el rol no existe en el mapa, devuelve el nombre tal cual.
     */
    const etiqueta = (rol) => etiquetaRol[rol] ?? rol;

    /**
     * Devuelve las clases CSS para el badge de un rol.
     * Si el rol no existe, devuelve clases por defecto.
     */
    const color = (rol) => colorRol[rol] ?? 'bg-gray-100 text-gray-800';

    /**
     * Lista de roles disponibles como array de objetos {value, label}
     * Útil para poblar selectores en formularios.
     */
    const listaRoles = Object.entries(etiquetaRol).map(([value, label]) => ({
        value,
        label,
    }));

    return {
        etiquetaRol,
        colorRol,
        etiqueta,
        color,
        listaRoles,
    };
}
