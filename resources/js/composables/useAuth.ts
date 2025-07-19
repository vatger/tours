// resources/js/composables/useAuth.ts
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

type AuthProps = {
    permissions: string[];
    roles: string[];
};

export function useAuth() {
    const page = usePage<{ auth: AuthProps }>();

    // Computed para garantir reatividade e fallback
    const permissions = computed(() => page.props.auth?.permissions ?? []);
    const roles = computed(() => page.props.auth?.roles ?? []);

    // Checa se o usuário tem uma permissão específica
    function hasPermission(permission: string): boolean {
        return permissions.value.includes(permission);
    }

    // Checa se o usuário tem todas as permissões passadas
    function hasPermissions(...perms: string[]): boolean {
        return perms.every((perm) => permissions.value.includes(perm));
    }

    // Checa se o usuário tem ao menos uma das permissões passadas
    function hasSomePermissions(...perms: string[]): boolean {
        return perms.some((perm) => permissions.value.includes(perm));
    }

    // Checa se o usuário tem um papel específico
    function hasRole(role: string): boolean {
        return roles.value.includes(role);
    }

    // Checa se o usuário tem todos os papéis passados
    function hasRoles(...rs: string[]): boolean {
        return rs.every((r) => roles.value.includes(r));
    }
    // Checa se o usuário tem ao menos um dos papéis passados
    function hasSomeRoles(...rs: string[]): boolean {
        return rs.some((r) => roles.value.includes(r));
    }

    return {
        permissions,
        roles,
        hasPermission,
        hasPermissions,
        hasSomePermissions,
        hasRole,
        hasRoles,
        hasSomeRoles,
    };
}
