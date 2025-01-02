<script setup lang="ts">
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { router } from '@inertiajs/vue3'

// Components
import InputError from '@/components/InputError.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import SettingsHeading from '@/components/settings/Heading.vue'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
  DialogFooter,
  DialogClose,
} from '@/components/ui/dialog'

const passwordInput = ref<HTMLInputElement | null>(null)
const form = useForm({
  password: '',
})

const deleteUser = (e: Event) => {
  e.preventDefault()

  form.delete(route('profile.destroy'), {
    preserveScroll: true,
    onSuccess: () => closeModal(),
    onError: () => passwordInput.value?.focus(),
    onFinish: () => form.reset(),
  })
}

const closeModal = () => {
  form.clearErrors()
  form.reset()
}
</script>

<template>
  <div>
    <SettingsHeading
      title="Delete Account"
      description="Delete your account and all of its resources"
    />
    <Dialog>
      <DialogTrigger as-child>
        <Button variant="destructive">Delete Account</Button>
      </DialogTrigger>
      <DialogContent>
        <form class="space-y-6" @submit="deleteUser">
          <DialogHeader class="space-y-3">
            <DialogTitle>Are you sure you want to delete your account?</DialogTitle>
            <DialogDescription>
              Once your account is deleted, all of its resources and data will also be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.
            </DialogDescription>
          </DialogHeader>
          <div class="grid gap-2">
            <Label for="password" class="sr-only">Password</Label>

            <Input
              id="password"
              type="password"
              name="password"
              ref="passwordInput"
              v-model="form.password"
              placeholder="Password"
            />

            <InputError :message="form.errors.password" />
          </div>
          
          <DialogFooter>
            <DialogClose as-child>
              <Button variant="secondary" @click="closeModal">
                Cancel
              </Button>
            </DialogClose>

            <Button variant="destructive" :disabled="form.processing">
              <button type="submit">
                Delete Account
              </button>
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
  </div>
</template>
