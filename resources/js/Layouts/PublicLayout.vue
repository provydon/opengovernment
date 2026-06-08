<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const page = usePage()
const brand = computed(() => page.props.brand ?? { name: 'OpenGovernment', tagline: 'Public spending, public scrutiny.' })
const user = computed(() => page.props.auth?.user ?? null)
</script>

<template>
  <div class="min-h-screen bg-slate-50 text-slate-900">
    <header class="border-b border-slate-200 bg-white">
      <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
        <Link :href="route('home')" class="flex items-baseline gap-2">
          <span class="text-xl font-semibold tracking-tight">{{ brand.name }}</span>
          <span class="hidden text-xs text-slate-500 sm:inline">{{ brand.tagline }}</span>
        </Link>
        <nav class="flex items-center gap-5 text-sm">
          <Link :href="route('spending.index')" class="hover:text-emerald-700">Spending</Link>
          <Link :href="route('issues.index')" class="hover:text-emerald-700">Issues</Link>
          <Link :href="route('chat')" class="hover:text-emerald-700">Ask</Link>
          <Link :href="route('donate')" class="hover:text-emerald-700">Donate</Link>
          <template v-if="user">
            <Link :href="route('profile.edit')" class="text-slate-600">{{ user.name }}</Link>
            <Link :href="route('logout')" method="post" as="button" class="text-slate-500 hover:text-rose-600">Sign out</Link>
          </template>
          <template v-else>
            <Link :href="route('login')" class="text-slate-600">Citizen sign in</Link>
            <Link :href="route('government.login')" class="rounded-md bg-emerald-700 px-3 py-1.5 text-white hover:bg-emerald-800">Government</Link>
          </template>
        </nav>
      </div>
    </header>

    <main class="mx-auto max-w-6xl px-6 py-10">
      <slot />
    </main>

    <footer class="border-t border-slate-200 bg-white py-6 text-center text-xs text-slate-500">
      Open source · MIT · <a href="https://github.com/opengovernment/opengovernment" class="hover:text-emerald-700">github.com/opengovernment</a>
    </footer>
  </div>
</template>
