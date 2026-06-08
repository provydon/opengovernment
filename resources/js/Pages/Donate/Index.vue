<script setup>
import { Head, useForm, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import PublicLayout from '@/Layouts/PublicLayout.vue'

defineProps({ recent: Array })

const flash = computed(() => usePage().props.flash?.flash ?? null)
const user = computed(() => usePage().props.auth?.user ?? null)

const form = useForm({
  amount_major: 5000,
  currency_code: 'NGN',
  donor_name: user.value?.name ?? '',
  donor_email: user.value?.email ?? '',
  message: '',
  display_publicly: true,
})

function fmt(amount_minor, currency_code) {
  return new Intl.NumberFormat(undefined, { style: 'currency', currency: currency_code || 'NGN' }).format((amount_minor ?? 0) / 100)
}
</script>

<template>
  <Head title="Donate" />
  <PublicLayout>
    <div class="grid gap-8 md:grid-cols-2">
      <div>
        <h1 class="text-2xl font-semibold">Keep OpenGovernment running</h1>
        <p class="mt-2 text-sm text-slate-600">
          Servers, identity verification calls, and AI usage all cost money. We don't take ads or sell data.
          This whole platform runs on donations from people who want their government held accountable.
        </p>
        <div v-if="flash" class="mt-4 rounded-md bg-emerald-50 p-3 text-sm text-emerald-800">{{ flash }}</div>

        <form @submit.prevent="form.post(route('donate.initiate'))" class="mt-6 space-y-3 rounded-lg border border-slate-200 bg-white p-5">
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="text-sm font-medium">Amount</label>
              <input v-model.number="form.amount_major" type="number" min="100" class="mt-1 w-full rounded border-slate-300 text-sm" />
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
          </div>
          <div>
            <label class="text-sm font-medium">Your name (shown publicly unless you opt out)</label>
            <input v-model="form.donor_name" type="text" class="mt-1 w-full rounded border-slate-300 text-sm" />
          </div>
          <div>
            <label class="text-sm font-medium">Email (for receipt)</label>
            <input v-model="form.donor_email" type="email" required class="mt-1 w-full rounded border-slate-300 text-sm" />
          </div>
          <div>
            <label class="text-sm font-medium">Message (optional)</label>
            <textarea v-model="form.message" rows="2" class="mt-1 w-full rounded border-slate-300 text-sm" />
          </div>
          <label class="flex items-center gap-2 text-sm">
            <input v-model="form.display_publicly" type="checkbox" />
            Show my name and message on the public donor list
          </label>
          <button class="rounded-md bg-emerald-700 px-4 py-2 text-sm text-white" :disabled="form.processing">Donate</button>
        </form>
      </div>

      <div>
        <h2 class="text-lg font-semibold">Recent donors</h2>
        <ul class="mt-3 space-y-3">
          <li v-for="(d, i) in recent" :key="i" class="rounded-lg border border-slate-200 bg-white p-4 text-sm">
            <div class="flex items-baseline justify-between">
              <span class="font-medium">{{ d.donor_name || 'Anonymous' }}</span>
              <span class="tabular-nums text-slate-600">{{ fmt(d.amount_minor, d.currency_code) }}</span>
            </div>
            <p v-if="d.message" class="mt-1 text-slate-600">"{{ d.message }}"</p>
          </li>
          <li v-if="!recent.length" class="text-sm text-slate-500">Be the first.</li>
        </ul>
      </div>
    </div>
  </PublicLayout>
</template>
