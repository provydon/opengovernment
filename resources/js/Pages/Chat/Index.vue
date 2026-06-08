<script setup>
import { Head, usePage } from '@inertiajs/vue3'
import { computed, nextTick, ref } from 'vue'
import PublicLayout from '@/Layouts/PublicLayout.vue'

const user = computed(() => usePage().props.auth?.user ?? null)

const messages = ref([
  {
    role: 'assistant',
    content: user.value
      ? `Hi ${user.value.name}. Tell me what's broken in your area, or ask me what your local government is spending money on.`
      : `Hi — tell me what's broken in your area, or ask me what your local government is spending money on. To file an issue you'll need to sign in and verify your identity, but you can still browse and ask questions as a guest.`,
  },
])
const input = ref('')
const sending = ref(false)
const scrollContainer = ref(null)

async function send() {
  const message = input.value.trim()
  if (!message || sending.value) return

  messages.value.push({ role: 'user', content: message })
  input.value = ''
  sending.value = true
  await scroll()

  try {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content
    const res = await fetch(route('chat.send'), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrf,
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      credentials: 'same-origin',
      body: JSON.stringify({
        message,
        history: messages.value.slice(0, -1).filter(m => m.role !== 'system'),
      }),
    })
    const data = await res.json()
    messages.value.push({ role: 'assistant', content: data.reply ?? '...' })
  } catch (e) {
    messages.value.push({ role: 'assistant', content: 'Network error talking to the assistant.' })
  } finally {
    sending.value = false
    await scroll()
  }
}

async function scroll() {
  await nextTick()
  const el = scrollContainer.value
  if (el) el.scrollTop = el.scrollHeight
}
</script>

<template>
  <Head title="Ask" />
  <PublicLayout>
    <div class="mx-auto max-w-3xl">
      <h1 class="text-2xl font-semibold">Ask</h1>
      <p class="mt-1 text-sm text-slate-600">
        Describe a problem in your area, or ask about spending. I'll check if anyone's
        already raised the same thing before filing a new one.
      </p>

      <div ref="scrollContainer" class="mt-6 h-[500px] overflow-y-auto rounded-lg border border-slate-200 bg-white p-4">
        <div v-for="(m, i) in messages" :key="i"
          class="mb-3 flex"
          :class="m.role === 'user' ? 'justify-end' : 'justify-start'">
          <div
            class="max-w-[80%] whitespace-pre-line rounded-2xl px-4 py-2 text-sm"
            :class="m.role === 'user' ? 'bg-emerald-700 text-white' : 'bg-slate-100 text-slate-800'">
            {{ m.content }}
          </div>
        </div>
        <div v-if="sending" class="text-xs text-slate-400">…</div>
      </div>

      <form @submit.prevent="send" class="mt-4 flex gap-2">
        <input v-model="input" :disabled="sending" type="text" autofocus
          placeholder="e.g. potholes are getting worse on Awolowo Way in Ikeja"
          class="flex-1 rounded-md border-slate-300 text-sm" />
        <button :disabled="sending" class="rounded-md bg-emerald-700 px-4 py-2 text-sm text-white">Send</button>
      </form>

      <p class="mt-3 text-xs text-slate-500">
        The assistant uses tools (search_similar_issues, create_issue, upvote_issue, search_spending_records). These tools are also exposed over MCP at <code>/mcp</code> for use from Claude Desktop / Cursor / scripts.
      </p>
    </div>
  </PublicLayout>
</template>
