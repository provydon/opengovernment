<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import PublicLayout from '@/Layouts/PublicLayout.vue'

const props = defineProps({ issue: Object, userVote: Number })
const user = computed(() => usePage().props.auth?.user ?? null)

function vote(value) {
  router.post(route('issues.vote', props.issue.slug), { value }, { preserveScroll: true })
}
</script>

<template>
  <Head :title="issue.title" />
  <PublicLayout>
    <Link :href="route('issues.index')" class="text-sm text-slate-500 hover:text-emerald-700">← All issues</Link>

    <article class="mt-4 flex gap-6 rounded-lg border border-slate-200 bg-white p-6">
      <div class="flex w-16 shrink-0 flex-col items-center gap-1">
        <button @click="vote(1)" :disabled="!user"
          class="rounded p-2 text-2xl hover:bg-slate-100"
          :class="userVote === 1 ? 'text-emerald-700' : 'text-slate-400'">▲</button>
        <div class="text-xl font-semibold tabular-nums">{{ issue.score }}</div>
        <button @click="vote(-1)" :disabled="!user"
          class="rounded p-2 text-2xl hover:bg-slate-100"
          :class="userVote === -1 ? 'text-rose-600' : 'text-slate-400'">▼</button>
      </div>

      <div class="flex-1">
        <h1 class="text-2xl font-semibold">{{ issue.title }}</h1>
        <p class="mt-1 text-xs uppercase tracking-wide text-slate-500">
          {{ issue.local_government?.name }} · {{ issue.category || 'general' }} · {{ issue.status.replace('_', ' ') }} · posted by {{ issue.user?.name }}
        </p>
        <p class="mt-4 whitespace-pre-line text-slate-700">{{ issue.body }}</p>
        <p v-if="!user" class="mt-4 rounded-md bg-amber-50 p-3 text-sm text-amber-800">
          <Link :href="route('login')" class="font-semibold underline">Sign in</Link>
          to add your vote.
        </p>
      </div>
    </article>
  </PublicLayout>
</template>
