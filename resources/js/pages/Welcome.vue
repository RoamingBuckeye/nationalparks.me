<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/atoms/AppLogoIcon.vue';
import { dashboard, login, register } from '@/routes';
</script>

<template>
    <Head title="Track every U.S. National Park you visit">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>

    <div class="landing">
        <header class="landing-header">
            <nav class="landing-nav">
                <span class="landing-brand">
                    <span class="landing-brand-chip">
                        <AppLogoIcon class="landing-brand-icon" />
                    </span>
                    NationalParks.me
                </span>

                <div class="landing-nav-actions">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="dashboard()"
                        class="landing-btn landing-btn--primary"
                    >
                        Go to dashboard
                    </Link>
                    <template v-else>
                        <Link
                            :href="login()"
                            class="landing-btn landing-btn--ghost"
                        >
                            Log in
                        </Link>
                        <Link
                            :href="register()"
                            class="landing-btn landing-btn--primary"
                        >
                            Get started
                        </Link>
                    </template>
                </div>
            </nav>
        </header>

        <main class="landing-hero">
            <span class="landing-pill"> All 63 U.S. National Parks </span>

            <h1 class="landing-title">
                Every park. One map.
                <span class="landing-title-accent">Your story.</span>
            </h1>

            <p class="landing-lead">
                Check in to the parks you've explored, tick off the trails,
                peaks, and viewpoints you've reached, and keep a journal of
                every trip — on the web and on your phone.
            </p>

            <div class="landing-cta">
                <Link
                    v-if="$page.props.auth.user"
                    :href="dashboard()"
                    class="landing-btn-lg landing-btn-lg--primary"
                >
                    Go to your dashboard
                </Link>
                <template v-else>
                    <Link
                        :href="register()"
                        class="landing-btn-lg landing-btn-lg--primary"
                    >
                        Start your map
                    </Link>
                    <Link
                        :href="login()"
                        class="landing-btn-lg landing-btn-lg--outline"
                    >
                        Log in
                    </Link>
                </template>
            </div>

            <dl class="landing-stats">
                <div class="landing-stat">
                    <dt class="landing-stat-value">63</dt>
                    <dd class="landing-stat-label">National Parks</dd>
                </div>
                <div class="landing-stat">
                    <dt class="landing-stat-value">7,000+</dt>
                    <dd class="landing-stat-label">Points of interest</dd>
                </div>
                <div class="landing-stat">
                    <dt class="landing-stat-value">Web + app</dt>
                    <dd class="landing-stat-label">iOS &amp; Android</dd>
                </div>
            </dl>
        </main>

        <footer class="landing-footer">nationalparks.me</footer>
    </div>
</template>
