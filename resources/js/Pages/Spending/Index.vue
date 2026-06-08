<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { reactive, watch } from 'vue'
import PublicLayout from '@/Layouts/PublicLayout.vue'

const props = defineProps({
  records: Object,
  filters: Object,
  lgas: Array,
})

const form = reactive({
  q: props.filters?.q ?? '',
  lga: props.filters?.lga ?? '',
})

let timer
watch(form, () => {
  clearTimeout(timer)
  timer = setTimeout(() => {
    router.get(route('spending.index'), form, { preserveState: true, preserveScroll: true, replace: true })
  }, 250)
})

function fmt(amount_minor, currency_code) {
  const major = (amount_minor ?? 0) / 100
  return new Intl.NumberFormat(undefined, { style: 'currency', currency: currency_code || 'NGN' }).format(major)
}
</script>

<template>
  <Head title="Public spending" />
  <PublicLayout>
    <div class="mb-6 flex items-end justify-between gap-4">
      <div>
        <h1 class="text-2xl font-semibold">Published spending</h1>
        <p class="text-sm text-slate-600">Money local governments say they've spent — and on what.</p>
      </div>
      <div class="flex gap-2">
        <input v-model="form.q" type="search" placeholder="Search title, vendor, category…"
          class="w-64 rounded-md border-slate-300 text-sm" />
        <select v-model="form.lga" class="rounded-md border-slate-300 text-sm">
          <option value="">All local governments</option>
          <option v-for="l in lgas" :key="l.id" :value="l.id">{{ l.name }}</option>
        </select>
      </div>
    </div>

    <ul class="divide-y divide-slate-200 rounded-lg border border-slate-200 bg-white">
      <li v-for="r in records.data" :key="r.id" class="px-5 py-4">
        <Link :href="route('spending.show', r.slug)" class="flex items-start justify-between gap-6">
          <div>
            <h2 class="font-medium text-slate-900">{{ r.title }}</h2>
            <p class="mt-1 text-xs uppercase tracking-wide text-slate-500">
              {{ r.local_government?.name }} · {{ r.category || 'uncategorised' }}
              <span v-if="r.vendor"> · paid to {{ r.vendor }}</span>
            </p>
          </div>
          <div class="text-right">
            <div class="font-semibold tabular-nums">{{ fmt(r.amount_minor, r.currency_code) }}</div>
            <div class="text-xs text-slate-500">{{ r.spent_on }}</div>
          </div>
        </Link>
      </li>
      <li v-if="!records.data.length" class="p-10 text-center text-sm text-slate-500">
        No records match.
      </li>
    </ul>

    <div class="mt-4 flex justify-center gap-2 text-sm">
      <Link v-for="link in records.links" :key="link.label" v-html="link.label" :href="link.url ?? ''"
        class="rounded px-3 py-1.5"
        :class="link.active ? 'bg-emerald-700 text-white' : 'text-slate-600 hover:bg-slate-100'" />
    </div>
  </PublicLayout>
</template>
