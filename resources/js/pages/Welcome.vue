<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { dashboard, login, register } from '@/routes';
</script>

<template>
    <Head title="Track every U.S. National Park you visit">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>

    <div
        class="flex min-h-screen flex-col bg-[#FDFDFC] text-[#1b1b18] dark:bg-[#0a0a0a] dark:text-[#EDEDEC]"
    >
        <header class="mx-auto w-full max-w-5xl p-6 lg:p-8">
            <nav class="flex items-center justify-between gap-4">
                <span class="flex items-center gap-2 font-semibold">
                    <span
                        class="flex size-8 items-center justify-center rounded-md bg-brand-700 text-white"
                    >
                        <AppLogoIcon class="size-5" />
                    </span>
                    NationalParks.me
                </span>

                <div class="flex items-center gap-3 text-sm">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="dashboard()"
                        class="rounded-md bg-brand-700 px-4 py-1.5 font-medium text-white transition-colors hover:bg-brand-800"
                    >
                        Go to dashboard
                    </Link>
                    <template v-else>
                        <Link
                            :href="login()"
                            class="rounded-md px-4 py-1.5 font-medium text-[#1b1b18] transition-colors hover:bg-black/5 dark:text-[#EDEDEC] dark:hover:bg-white/10"
                        >
                            Log in
                        </Link>
                        <Link
                            :href="register()"
                            class="rounded-md bg-brand-700 px-4 py-1.5 font-medium text-white transition-colors hover:bg-brand-800"
                        >
                            Get started
                        </Link>
                    </template>
                </div>
            </nav>
        </header>

        <main
            class="mx-auto flex w-full max-w-5xl flex-1 flex-col items-center justify-center gap-8 px-6 py-16 text-center"
        >
            <span
                class="inline-flex items-center gap-2 rounded-full border border-brand-700/20 bg-brand-700/10 px-3 py-1 text-xs font-medium text-brand-800 dark:text-brand-300"
            >
                All 63 U.S. National Parks
            </span>

            <h1 class="max-w-3xl text-4xl font-bold tracking-tight sm:text-6xl">
                Every park. One map.
                <span class="text-brand-700 dark:text-brand-400"
                    >Your story.</span
                >
            </h1>

            <p class="max-w-2xl text-lg text-[#706f6c] dark:text-[#A1A09A]">
                Check in to the parks you've explored, tick off the trails,
                peaks, and viewpoints you've reached, and keep a journal of
                every trip — on the web and on your phone.
            </p>

            <div class="flex flex-col gap-3 sm:flex-row">
                <Link
                    v-if="$page.props.auth.user"
                    :href="dashboard()"
                    class="rounded-lg bg-brand-700 px-6 py-3 font-medium text-white transition-colors hover:bg-brand-800"
                >
                    Go to your dashboard
                </Link>
                <template v-else>
                    <Link
                        :href="register()"
                        class="rounded-lg bg-brand-700 px-6 py-3 font-medium text-white transition-colors hover:bg-brand-800"
                    >
                        Start your map
                    </Link>
                    <Link
                        :href="login()"
                        class="rounded-lg border border-[#19140035] px-6 py-3 font-medium transition-colors hover:border-[#1915014a] dark:border-[#3E3E3A] dark:hover:border-[#62605b]"
                    >
                        Log in
                    </Link>
                </template>
            </div>

            <dl
                class="mt-8 grid w-full max-w-2xl grid-cols-3 gap-4 text-center"
            >
                <div
                    class="rounded-xl border border-black/5 bg-white p-4 dark:border-white/10 dark:bg-[#161615]"
                >
                    <dt
                        class="text-2xl font-bold text-brand-700 dark:text-brand-400"
                    >
                        63
                    </dt>
                    <dd class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                        National Parks
                    </dd>
                </div>
                <div
                    class="rounded-xl border border-black/5 bg-white p-4 dark:border-white/10 dark:bg-[#161615]"
                >
                    <dt
                        class="text-2xl font-bold text-brand-700 dark:text-brand-400"
                    >
                        7,000+
                    </dt>
                    <dd class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                        Points of interest
                    </dd>
                </div>
                <div
                    class="rounded-xl border border-black/5 bg-white p-4 dark:border-white/10 dark:bg-[#161615]"
                >
                    <dt
                        class="text-2xl font-bold text-brand-700 dark:text-brand-400"
                    >
                        Web + app
                    </dt>
                    <dd class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                        iOS &amp; Android
                    </dd>
                </div>
            </dl>
        </main>

        <footer
            class="mx-auto w-full max-w-5xl p-6 text-center text-sm text-[#706f6c] lg:p-8 dark:text-[#A1A09A]"
        >
            nationalparks.me
        </footer>
    </div>
</template>
