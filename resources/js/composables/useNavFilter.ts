import { useAuth } from '@/composables/useAuth';
import type { NavItem } from '@/types';

export function useNavFilter() {
    const { hasPermission } = useAuth();

    function filterNavItems(items: NavItem[]): NavItem[] {
        return items
            .filter((item) => !item.permission || hasPermission(item.permission))
            .map((item) => ({
                ...item,
                children: item.children ? filterNavItems(item.children) : undefined,
            }));
    }

    return { filterNavItems };
}
