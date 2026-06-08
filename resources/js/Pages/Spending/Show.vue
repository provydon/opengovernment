<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import PublicLayout from '@/Layouts/PublicLayout.vue'

const props = defineProps({ record: Object })
const user = computed(() => usePage().props.auth?.user ?? null)

const comment = useForm({ body: '' })

function fmt(amount_minor, currency_code) {
  return new Intl.NumberFormat(undefined, { style: 'currency', currency: currency_code || 'NGN' }).format((amount_minor ?? 0) / 100)
}

function submitComment() {
  comment.post(route('spending.comments.store', props.record.slug), {
    preserveScroll: true,
    onSuccess: () => comment.reset(),
  })
}
</script>

<template>
  <Head :title="record.title" />
  <PublicLayout>
    <Link :href="route('spending.index')" class="text-sm text-slate-500 hover:text-emerald-700">← All spending</Link>

    <article class="mt-4 rounded-lg border border-slate-200 bg-white p-6">
      <header class="flex items-start justify-between gap-6">
        <div>
          <h1 class="text-2xl font-semibold">{{ record.title }}</h1>
          <p class="mt-1 text-xs uppercase tracking-wide text-slate-500">
            {{ record.local_government?.name }} · {{ record.category || 'uncategorised' }}
          </p>
        </div>
        <div class="text-right">
          <div class="text-2xl font-semibold tabular-nums">{{ fmt(record.amount_minor, record.currency_code) }}</div>
          <div class="text-xs text-slate-500">Spent {{ record.spent_on }}</div>
        </div>
      </header>

      <dl class="mt-6 grid grid-cols-2 gap-4 text-sm sm:grid-cols-4">
        <div><dt class="text-slate-500">Vendor</dt><dd>{{ record.vendor || '—' }}</dd></div>
        <div><dt class="text-slate-500">Published by</dt><dd>{{ record.publisher?.name }}</dd></div>
        <div><dt class="text-slate-500">Title</dt><dd>{{ record.publisher?.official_title || '—' }}</dd></div>
        <div>
          <dt class="text-slate-500">Source</dt>
          <dd>
            <a v-if="record.source_document_url" :href="record.source_document_url" class="text-emerald-700 hover:underline" target="_blank">View document</a>
            <span v-else>—</span>
          </dd>
        </div>
      </dl>

      <p class="mt-6 whitespace-pre-line text-slate-700">{{ record.description }}</p>
    </article>

    <section class="mt-8">
      <h2 class="text-lg font-semibold">Comments</h2>

      <form v-if="user" @submit.prevent="submitComment" class="mt-4 rounded-lg border border-slate-200 bg-white p-4">
        <textarea v-model="comment.body" rows="3" placeholder="What do you make of this?"
          class="w-full rounded border-slate-300 text-sm" />
        <div v-if="comment.errors.body" class="mt-1 text-xs text-rose-600">{{ comment.errors.body }}</div>
        <div class="mt-2 flex justify-end">
          <button class="rounded-md bg-emerald-700 px-3 py-1.5 text-sm text-white" :disabled="comment.processing">Post comment</button>
        </div>
      </form>
      <p v-else class="mt-4 rounded-md bg-amber-50 p-4 text-sm text-amber-800">
        <Link :href="route('login')" class="font-semibold underline">Sign in</Link>
        and verify your identity to post a comment.
      </p>

      <ul class="mt-6 space-y-4">
        <li v-for="c in record.comments" :key="c.id" class="rounded-lg border border-slate-200 bg-white p-4">
          <div class="text-xs text-slate-500">{{ c.user?.name }} · {{ new Date(c.created_at).toLocaleString() }}</div>
          <p class="mt-2 whitespace-pre-line text-sm text-slate-700">{{ c.body }}</p>
        </li>
        <li v-if="!record.comments?.length" class="text-sm text-slate-500">Be the first to comment.</li>
      </ul>
    </section>
  </PublicLayout>
</template>
