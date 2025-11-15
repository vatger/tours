<script setup lang="ts">
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import {
    FormField,
    FormItem,
    FormLabel,
    FormDescription,
    FormMessage,
    FormControl,
} from '@/components/ui/form';
import { BugIcon } from 'lucide-vue-next';
import { Calendar } from '@/components/ui/calendar';
import { useForm } from '@inertiajs/vue3';
import { check } from '@/routes/legs';
import { Button } from '@/components/ui/button';

const props = defineProps<{
    legId: number
}>()

// Inertia form object
const form = useForm({
    leg: props.legId,
    date: undefined
});

</script>

<template>
    <Dialog>
        <DialogTrigger>
            <BugIcon></BugIcon>
        </DialogTrigger>
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Missing Leg?</DialogTitle>
                <DialogDescription>
                    <form @submit.prevent="form.post(check().url)">
                        <FormField name="date">
                            <FormItem>
                                <FormLabel>Select Date</FormLabel>
                                <FormControl>
                                    <div class="flex justify-center">
                                        <Calendar v-model="form.date" />
                                    </div>
                                </FormControl>
                                <FormDescription>
                                    If you are missing a leg please select the date when you have completed the Leg.
                                </FormDescription>
                                <FormMessage />
                            </FormItem>
                        </FormField>
                        <FormField name="submit">
                            <FormItem >
                                <FormControl>
                                    <Button class="mt-5" type="submit">Submit</Button>
                                </FormControl>
                            </FormItem>
                        </FormField>
                    </form>
                </DialogDescription>
            </DialogHeader>
        </DialogContent>
    </Dialog>
</template>
