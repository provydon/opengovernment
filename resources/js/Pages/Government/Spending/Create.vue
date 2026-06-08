<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import PublicLayout from '@/Layouts/PublicLayout.vue'

const form = useForm({
  title: '',
  category: '',
  description: '',
  amount_major: '',
  currency_code: 'NGN',
  vendor: '',
  spent_on: '',
  source_document_url: '',
  publish_now: true,
})
</script>

<template>
  <Head title="Publish spending" />
  <PublicLayout>
    <Link :href="route('government.dashboard')" class="text-sm text-slate-500 hover:text-emerald-700">← Dashboard</Link>

    <div class="mt-4 rounded-lg border border-slate-200 bg-white p-6">
      <h1 class="text-2xl font-semibold">Publish a spending record</h1>
      <p class="mt-1 text-sm text-slate-600">This record is permanent and visible to the public.</p>

      <form @submit.prevent="form.post(route('government.spending.store'))" class="mt-6 space-y-4">
        <div>
          <label class="text-sm font-medium">Title</label>
          <input v-model="form.title" type="text" maxlength="200" class="mt-1 w-full rounded border-slate-300 text-sm" />
          <p v-if="form.errors.title" class="mt-1 text-xs text-rose-600">{{ form.errors.title }}</p>
        </div>

        <div class="grid grid-cols-3 gap-4">
          <div>
            <label class="text-sm font-medium">Amount</label>
            <input v-model.number="form.amount_major" type="number" step="0.01" class="mt-1 w-full rounded border-slate-300 text-sm" />
            <p v-if="form.errors.amount_major" class="mt-1 text-xs text-rose-600">{{ form.errors.amount_major }}</p>
          </div>
          <div>
            <label class="text-sm font-medium">Currency</label>
            <select v-model="form.currency_code" class="mt-1 w-full rounded border-slate-300 text-sm">
              <option>NGN</option>
              <option>KES</option>
              <option>GHS</option>
              <option>USD</option>
            </select>
          </div>
          <div>
            <label class="text-sm font-medium">Category</label>
            <select v-model="form.category" class="mt-1 w-full rounded border-slate-300 text-sm">
              <option value="">—</option>
              <option value="infrastructure">Infrastructure</option>
              <option value="health">Health</option>
              <option value="education">Education</option>
              <option value="water">Water</option>
              <option value="security">Security</option>
              <option value="sanitation">Sanitation</option>
              <option value="other">Other</option>
            </select>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="text-sm font-medium">Vendor / contractor</label>
            <input v-model="form.vendor" type="text" class="mt-1 w-full rounded border-slate-300 text-sm" />
          </div>
          <div>
            <label class="text-sm font-medium">Spent on</label>
            <input v-model="form.spent_on" type="date" class="mt-1 w-full rounded border-slate-300 text-sm" />
            <p v-if="form.errors.spent_on" class="mt-1 text-xs text-rose-600">{{ form.errors.spent_on }}</p>
          </div>
        </div>

        <div>
          <label class="text-sm font-medium">Description</label>
          <textarea v-model="form.description" rows="6" class="mt-1 w-full rounded border-slate-300 text-sm" />
          <p v-if="form.errors.description" class="mt-1 text-xs text-rose-600">{{ form.errors.description }}</p>
        </div>

        <div>
          <label class="text-sm font-medium">Link to source document (optional)</label>
          <input v-model="form.source_document_url" type="url" placeholder="https://…" class="mt-1 w-full rounded border-slate-300 text-sm" />
        </div>

        <label class="flex items-center gap-2 text-sm">
          <input v-model="form.publish_now" type="checkbox" /> Publish immediately (uncheck to save as draft)
        </label>

        <div class="flex justify-end">
          <button :disabled="form.processing" class="rounded-md bg-emerald-700 px-4 py-2 text-sm text-white">Publish</button>
        </div>
      </form>
    </div>
  </PublicLayout>
</template>
