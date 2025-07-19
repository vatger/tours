import { router } from '@inertiajs/vue3';
import { ref } from 'vue';

export function useUsers() {
    const isFormOpen = ref(false);
    const isViewOpen = ref(false);
    const currentUser = ref<any>(null);
    const formAction = ref<'create' | 'edit'>('create');

    const openForm = (user = null) => {
        currentUser.value = user;
        formAction.value = user ? 'edit' : 'create';
        isFormOpen.value = true;
    };

    const openView = (user: any) => {
        currentUser.value = user;
        isViewOpen.value = true;
    };

    const deleteUser = (user: any) => {
        if (confirm('Tem certeza que deseja excluir este usuário?')) {
            router.delete(route('users.destroy', user.id));
        }
    };

    return { isFormOpen, isViewOpen, currentUser, formAction, openForm, openView, deleteUser };
}
