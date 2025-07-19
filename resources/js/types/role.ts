// types/role.ts
import { DateWithFormats } from './base';

export interface Role {
    id: number;
    name: string;
    guard_name: string;
    // permissions?: Permission[]; // Descomente se for usar relacionamento com permissões
    created_at: DateWithFormats;
    updated_at: DateWithFormats;
}

// Interface para a resposta da API
export interface RoleResource {
    data: Role;
}

// Interface para o formulário de projeto
export interface RoleForm {
    id?: number;
    name: string;
}
