<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog';
import { FormControl, FormDescription, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { check } from '@/routes/legs';
import { useForm } from '@inertiajs/vue3';
import { BugIcon } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps<{ legId: number }>();

const form = useForm({
  leg: props.legId,
  date: undefined,
});

const open = ref(false);

const firstError = computed(() => {
  const errors = form.errors;
  if (!errors || Object.keys(errors).length === 0) return null;
  return Object.values(errors)[0];
});

const submit = () => {
  form.post(check().url, {
    preserveState: true,
    onSuccess: () => {
      open.value = false;
    },
    onError: () => {
      open.value = true;
    },
  });
};
</script>

<template>
  <Dialog v-model:open="open">
    <DialogTrigger as-child>
      <button class="text-muted-foreground hover:text-primary">
        <BugIcon class="h-4 w-4" />
      </button>
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
                <FormDescription>Select the date when you completed this leg.</FormDescription>
                <FormMessage />
              </FormItem>
            </FormField>

            <Button class="mt-5" type="submit" :disabled="form.processing">Submit</Button>

            <div v-if="firstError" class="mt-4 rounded-md border border-red-300 bg-red-50 p-4 text-red-800">
              <div class="font-semibold">Error</div>
              <p class="mt-1 text-sm">{{ firstError }}</p>
            </div>
          </form>
        </DialogDescription>
      </DialogHeader>
    </DialogContent>
  </Dialog>
</template>
