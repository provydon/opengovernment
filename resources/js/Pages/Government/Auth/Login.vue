<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import PublicLayout from '@/Layouts/PublicLayout.vue'

const form = useForm({ email: '', password: '', remember: false })

function submit() {
  form.post(route('government.login'), { onFinish: () => form.reset('password') })
}
</script>

<template>
  <Head title="Government sign in" />
  <PublicLayout>
    <div class="mx-auto max-w-md rounded-lg border border-slate-200 bg-white p-6">
      <h1 class="text-xl font-semibold">Government official sign in</h1>
      <p class="mt-1 text-sm text-slate-600">For approved budget / finance officers publishing spending records.</p>

      <form @submit.prevent="submit" class="mt-5 space-y-4">
        <div>
          <label class="text-sm font-medium">Work email</label>
          <input v-model="form.email" type="email" required class="mt-1 w-full rounded border-slate-300 text-sm" />
          <p v-if="form.errors.email" class="mt-1 text-xs text-rose-600">{{ form.errors.email }}</p>
        </div>
        <div>
          <label class="text-sm font-medium">Password</label>
          <input v-model="form.password" type="password" required class="mt-1 w-full rounded border-slate-300 text-sm" />
        </div>
        <label class="flex items-center gap-2 text-sm">
          <input v-model="form.remember" type="checkbox" /> Remember me on this device
        </label>
        <button :disabled="form.processing" class="w-full rounded-md bg-emerald-700 py-2 text-sm text-white">Sign in</button>
      </form>

      <p class="mt-4 text-xs text-slate-500">
        New here? Government accounts are created by platform admins after verifying your role offline.
        Contact <a href="mailto:hello@opengovernment.org" class="text-emerald-700">hello@opengovernment.org</a>.
      </p>
    </div>
  </PublicLayout>
</template>
