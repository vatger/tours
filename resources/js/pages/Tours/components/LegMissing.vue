<script setup lang="ts">
import { ref, watch } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { check } from '@/routes/legs';
import { BugIcon } from 'lucide-vue-next';
import { Dialog, DialogTrigger, DialogContent, DialogHeader, DialogTitle, DialogDescription } from '@/components/ui/dialog';
import { FormField, FormItem, FormLabel, FormDescription, FormMessage, FormControl } from '@/components/ui/form';
import { Calendar } from '@/components/ui/calendar';
import { Button } from '@/components/ui/button';
import type { CheckResult } from '@/types/LegMissing';

const props = defineProps<{ legId: number }>();

const form = useForm({
    leg: props.legId,
    date: undefined,
});

const page = usePage();
const open = ref(false);

const checkResult = ref<CheckResult | null>(null);

watch(
    () => page.flash.checkResult,
    (val) => {
        if (val) {
            checkResult.value = val as CheckResult;
            open.value = true;
        }
    },
    { immediate: true }
);

const submit = () => {
    form.post(check().url, { preserveState: true });
};
</script>



<template>
    <Dialog v-model:open="open">
        <DialogTrigger>
            <BugIcon />
        </DialogTrigger>

        <DialogContent class="max-w-3xl">
            <DialogHeader>
                <DialogTitle>Missing Leg?</DialogTitle>
                <DialogDescription>
                    <form @submit.prevent="submit">
                        <FormField name="date">
                            <FormItem>
                                <FormLabel>Select Date</FormLabel>
                                <FormControl>
                                    <div class="flex justify-center">
                                        <Calendar v-model="form.date" />
                                    </div>
                                </FormControl>
                                <FormDescription>
                                    Select the date when you completed this leg.
                                </FormDescription>
                                <FormMessage />
                            </FormItem>
                        </FormField>

                        <Button class="mt-5" type="submit" :disabled="form.processing">
                            Submit
                        </Button>
                    </form>

                    <!-- Flash-based alerts -->
                    <div v-if="checkResult?.status === 'error'" class="mt-4 rounded-md border border-red-300 bg-red-50 p-4 text-red-800">
                        <div class="font-semibold">Error</div>
                        <p class="mt-1 text-sm">{{ checkResult.message }}</p>
                    </div>

                    <div v-else-if="checkResult?.status === 'no_flights_found'" class="mt-4 rounded-md border border-yellow-300 bg-yellow-50 p-4 text-yellow-800">
                        <div class="font-semibold">No Flights Found</div>
                        <p class="mt-1 text-sm">
                            We could not find any flights matching this leg for the selected date.
                        </p>
                    </div>

                    <div v-if="checkResult?.status === 'found'" class="mt-6 space-y-4 text-sm">
                        <div>
                            <h3 class="font-semibold">Matched Flights</h3>
                            <pre class="bg-muted p-2 rounded max-h-48 overflow-auto">
{{ checkResult.filtered_flights }}
                        </pre>
                        </div>

                        <div>
                            <h3 class="font-semibold">All Flights</h3>
                            <pre class="bg-muted p-2 rounded max-h-48 overflow-auto">
{{ checkResult.all_flights }}
                        </pre>
                        </div>

                        <div v-if="checkResult.selected_flight">
                            <h3 class="font-semibold">Selected Flight</h3>
                            <pre class="bg-muted p-2 rounded">
{{ checkResult.selected_flight }}
                        </pre>
                        </div>

                        <div>
                            <span class="font-semibold">Completed at:</span>
                            {{ checkResult.completed_at }}
                        </div>
                    </div>
                </DialogDescription>
            </DialogHeader>
        </DialogContent>
    </Dialog>

</template>
