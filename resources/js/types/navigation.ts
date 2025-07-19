// types/navigation.ts
import { Component, DefineComponent } from 'vue';

/**
 * Representa os eventos emitidos pelo componente NavigationGuard
 */
export type NavigationGuardEmits = {
    'navigation-attempt': (url: string) => void;
    confirm: () => void;
    cancel: () => void;
};

/**
 * Propriedades do componente NavigationGuard
 */
export interface NavigationGuardProps {
    formIsDirty: boolean;
    confirmationText?: string;
}

/**
 * Tipo completo do componente NavigationGuard
 */
export type NavigationGuard = DefineComponent<NavigationGuardProps, {}, {}, {}, {}, {}, {}, NavigationGuardEmits>;

/**
 * Item de navegação para breadcrumbs
 */
export interface BreadcrumbItem {
    title: string;
    href: string;
    icon?: Component;
    disabled?: boolean;
}
