<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import PublicLayout from '@/Layouts/PublicLayout.vue'

const props = defineProps({ lgas: Array })

const form = useForm({
  title: '',
  body: '',
  category: '',
  local_government_id: '',
})

function submit() {
  form.post(route('issues.store'))
}
</script>

<template>
  <Head title="Post an issue" />
  <PublicLayout>
    <Link :href="route('issues.index')" class="text-sm text-slate-500 hover:text-emerald-700">← All issues</Link>

    <div class="mt-4 rounded-lg border border-slate-200 bg-white p-6">
      <h1 class="text-2xl font-semibold">Post an issue</h1>
      <p class="mt-1 text-sm text-slate-600">
        Tip: not sure if someone already raised this? Try the <Link :href="route('chat')" class="text-emerald-700 underline">assistant</Link> — it'll search before you post.
      </p>

      <form @submit.prevent="submit" class="mt-6 space-y-4">
        <div>
          <label class="text-sm font-medium">Headline</label>
          <input v-model="form.title" type="text" maxlength="200" class="mt-1 w-full rounded border-slate-300 text-sm" />
          <p v-if="form.errors.title" class="mt-1 text-xs text-rose-600">{{ form.errors.title }}</p>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="text-sm font-medium">Local government</label>
            <select v-model="form.local_government_id" class="mt-1 w-full rounded border-slate-300 text-sm">
              <option value="">Choose…</option>
              <option v-for="l in lgas" :key="l.id" :value="l.id">{{ l.name }}</option>
            </select>
            <p v-if="form.errors.local_government_id" class="mt-1 text-xs text-rose-600">{{ form.errors.local_government_id }}</p>
          </div>
          <div>
            <label class="text-sm font-medium">Category</label>
            <select v-model="form.category" class="mt-1 w-full rounded border-slate-300 text-sm">
              <option value="">—</option>
              <option value="roads">Roads</option>
              <option value="water">Water</option>
              <option value="electricity">Electricity</option>
              <option value="security">Security</option>
              <option value="health">Health</option>
              <option value="education">Education</option>
              <option value="sanitation">Sanitation</option>
              <option value="other">Other</option>
            </select>
          </div>
        </div>

        <div>
          <label class="text-sm font-medium">Describe the problem</label>
          <textarea v-model="form.body" rows="6" class="mt-1 w-full rounded border-slate-300 text-sm" />
          <p v-if="form.errors.body" class="mt-1 text-xs text-rose-600">{{ form.errors.body }}</p>
        </div>

        <div class="flex justify-end">
          <button :disabled="form.processing" class="rounded-md bg-emerald-700 px-4 py-2 text-sm text-white">Post issue</button>
        </div>
      </form>
    </div>
  </PublicLayout>
</template>
