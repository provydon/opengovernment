<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { reactive, watch } from 'vue'
import PublicLayout from '@/Layouts/PublicLayout.vue'

const props = defineProps({
  issues: Object,
  filters: Object,
  lgas: Array,
})

const form = reactive({
  lga: props.filters?.lga ?? '',
  status: props.filters?.status ?? '',
})

watch(form, () => {
  router.get(route('issues.index'), form, { preserveState: true, preserveScroll: true, replace: true })
})
</script>

<template>
  <Head title="Issues" />
  <PublicLayout>
    <div class="mb-6 flex items-end justify-between gap-4">
      <div>
        <h1 class="text-2xl font-semibold">What people want fixed</h1>
        <p class="text-sm text-slate-600">Ranked by how many residents are asking for the same thing.</p>
      </div>
      <div class="flex gap-2">
        <select v-model="form.lga" class="rounded-md border-slate-300 text-sm">
          <option value="">All local governments</option>
          <option v-for="l in lgas" :key="l.id" :value="l.id">{{ l.name }}</option>
        </select>
        <select v-model="form.status" class="rounded-md border-slate-300 text-sm">
          <option value="">All statuses</option>
          <option value="open">Open</option>
          <option value="acknowledged">Acknowledged</option>
          <option value="in_progress">In progress</option>
          <option value="resolved">Resolved</option>
        </select>
        <Link :href="route('issues.create')" class="rounded-md bg-emerald-700 px-3 py-1.5 text-sm text-white">Post issue</Link>
      </div>
    </div>

    <ul class="divide-y divide-slate-200 rounded-lg border border-slate-200 bg-white">
      <li v-for="i in issues.data" :key="i.id" class="flex gap-5 px-5 py-4">
        <div class="w-16 shrink-0 text-center">
          <div class="text-2xl font-semibold tabular-nums text-emerald-700">{{ i.score }}</div>
          <div class="text-xs uppercase text-slate-500">votes</div>
        </div>
        <div class="flex-1">
          <Link :href="route('issues.show', i.slug)" class="font-medium hover:text-emerald-700">{{ i.title }}</Link>
          <div class="mt-1 text-xs uppercase tracking-wide text-slate-500">
            {{ i.local_government?.name }} · {{ i.category || 'general' }} · {{ i.status.replace('_', ' ') }}
          </div>
          <p class="mt-2 line-clamp-2 text-sm text-slate-600">{{ i.body }}</p>
        </div>
      </li>
      <li v-if="!issues.data.length" class="p-10 text-center text-sm text-slate-500">
        Nothing to show yet. <Link :href="route('issues.create')" class="text-emerald-700 underline">Post the first issue.</Link>
      </li>
    </ul>

    <div class="mt-4 flex justify-center gap-2 text-sm">
      <Link v-for="link in issues.links" :key="link.label" v-html="link.label" :href="link.url ?? ''"
        class="rounded px-3 py-1.5"
        :class="link.active ? 'bg-emerald-700 text-white' : 'text-slate-600 hover:bg-slate-100'" />
    </div>
  </PublicLayout>
</template>
