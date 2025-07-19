import { router } from '@inertiajs/vue3';
import { ref } from 'vue';

export function useObjects() {
    const isFormOpen = ref(false);
    const isViewOpen = ref(false);
    const currentObject = ref<any>(null);
    const formAction = ref<'create' | 'edit'>('create');

    const openForm = (object = null) => {
        currentObject.value = object;
        formAction.value = object ? 'edit' : 'create';
        isFormOpen.value = true;
    };

    const openView = (object: any) => {
        currentObject.value = object;
        isViewOpen.value = true;
    };

    const deleteObject = (object: any) => {
        if (confirm('Tem certeza que deseja excluir?')) {
            router.delete(route('objects.destroy', object.id));
        }
    };

    return { isFormOpen, isViewOpen, currentObject, formAction, openForm, openView, deleteObject };
}
