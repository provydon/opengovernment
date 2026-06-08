<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import PublicLayout from '@/Layouts/PublicLayout.vue'

defineProps({ official: Object, lga: Object, topIssues: Array, recentSpend: Array })

const flash = computed(() => usePage().props.flash?.flash ?? null)

function fmt(amount_minor, currency_code) {
  return new Intl.NumberFormat(undefined, { style: 'currency', currency: currency_code || 'NGN' }).format((amount_minor ?? 0) / 100)
}
</script>

<template>
  <Head title="Government dashboard" />
  <PublicLayout>
    <header class="flex items-end justify-between">
      <div>
        <h1 class="text-2xl font-semibold">{{ lga.name }} — government dashboard</h1>
        <p class="text-sm text-slate-600">{{ official.official_title }} · {{ official.name }}</p>
      </div>
      <Link :href="route('government.spending.create')" class="rounded-md bg-emerald-700 px-3 py-2 text-sm text-white">Publish spending record</Link>
    </header>

    <div v-if="flash" class="mt-4 rounded-md bg-emerald-50 p-3 text-sm text-emerald-800">{{ flash }}</div>

    <section class="mt-8 grid gap-6 md:grid-cols-2">
      <div>
        <h2 class="text-lg font-semibold">What residents are asking for</h2>
        <p class="text-xs text-slate-500">Top 20 open issues, ranked by net score.</p>
        <ul class="mt-3 divide-y divide-slate-200 rounded-lg border border-slate-200 bg-white">
          <li v-for="i in topIssues" :key="i.id" class="flex items-center gap-4 px-4 py-3">
            <div class="w-12 text-center">
              <div class="text-lg font-semibold tabular-nums text-emerald-700">{{ i.score }}</div>
              <div class="text-[10px] uppercase text-slate-500">votes</div>
            </div>
            <Link :href="route('issues.show', i.slug)" class="flex-1 text-sm hover:text-emerald-700">{{ i.title }}</Link>
            <span class="text-xs uppercase text-slate-500">{{ i.status.replace('_', ' ') }}</span>
          </li>
          <li v-if="!topIssues.length" class="p-4 text-sm text-slate-500">No open issues.</li>
        </ul>
      </div>

      <div>
        <h2 class="text-lg font-semibold">Recently published</h2>
        <ul class="mt-3 divide-y divide-slate-200 rounded-lg border border-slate-200 bg-white">
          <li v-for="r in recentSpend" :key="r.id" class="px-4 py-3 text-sm">
            <div class="flex justify-between">
              <Link :href="route('spending.show', r.slug)" class="hover:text-emerald-700">{{ r.title }}</Link>
              <span class="tabular-nums">{{ fmt(r.amount_minor, r.currency_code) }}</span>
            </div>
            <div class="text-xs text-slate-500">Spent {{ r.spent_on }} · Published {{ r.published_at ? 'live' : 'draft' }}</div>
          </li>
          <li v-if="!recentSpend.length" class="p-4 text-sm text-slate-500">Nothing published yet.</li>
        </ul>
      </div>
    </section>
  </PublicLayout>
</template>
