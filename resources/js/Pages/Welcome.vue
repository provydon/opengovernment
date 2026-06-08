<script setup>
import { Head, Link } from '@inertiajs/vue3'
import PublicLayout from '@/Layouts/PublicLayout.vue'

defineProps({
  canLogin: Boolean,
  canRegister: Boolean,
  laravelVersion: String,
  phpVersion: String,
  brand: Object,
})
</script>

<template>
  <Head :title="brand?.name ?? 'OpenGovernment'" />
  <PublicLayout>
    <section class="grid gap-12 md:grid-cols-2 md:items-center">
      <div>
        <h1 class="text-4xl font-semibold tracking-tight text-slate-900 sm:text-5xl">
          What your government spends.<br />
          What your neighbours want fixed.
        </h1>
        <p class="mt-5 max-w-xl text-lg text-slate-600">
          {{ brand?.name }} is an open source platform that puts local government spending
          in public view, lets citizens flag problems, and ranks them by how many
          people are asking for the same thing.
        </p>
        <div class="mt-8 flex flex-wrap gap-3">
          <Link :href="route('spending.index')" class="rounded-md bg-emerald-700 px-4 py-2 text-white shadow hover:bg-emerald-800">
            Browse spending
          </Link>
          <Link :href="route('issues.index')" class="rounded-md border border-slate-300 px-4 py-2 hover:bg-slate-100">
            See what people want fixed
          </Link>
          <Link :href="route('chat')" class="rounded-md border border-emerald-700 px-4 py-2 text-emerald-700 hover:bg-emerald-50">
            Just describe a problem →
          </Link>
        </div>
      </div>

      <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">How it works</h2>
        <ol class="mt-4 space-y-4 text-sm text-slate-700">
          <li><span class="font-semibold">Governments publish.</span> Verified officials post spending records — what, who got paid, when.</li>
          <li><span class="font-semibold">Citizens read & comment.</span> Anyone can read; verified residents can comment.</li>
          <li><span class="font-semibold">Citizens raise issues.</span> Tell the assistant what's broken in your area. It checks if someone already raised it; if so, your voice joins theirs instead of starting another thread.</li>
          <li><span class="font-semibold">Top issues rise.</span> Local officials see the ranked list of what their residents are collectively asking for.</li>
        </ol>
      </div>
    </section>
  </PublicLayout>
</template>
