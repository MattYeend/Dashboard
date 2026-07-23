<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { dashboard, login } from '@/routes';
import { register } from '@/routes';

const modules = [
    { code: '01', route: 'users.index', label: 'People', detail: 'Team members, roles and access' },
    { code: '02', route: 'companies.index', label: 'Companies', detail: 'Organisations you work with' },
    { code: '03', route: 'contacts.index', label: 'Contacts', detail: 'Phone, address and email records' },
    { code: '04', route: 'tasks.index', label: 'Tasks', detail: 'Assigned work with due dates' },
    { code: '05', route: 'deals.index', label: 'Deals', detail: 'Pipeline stages and value' },
];

const auditFeed = [
    'Company · Acme Ltd · updated by Priya',
    'Task · Renew contract · assigned to Sam',
    'Contact · J. Whitfield · created by Priya',
    'User · restored by Matt',
    'Deal · Q3 renewal · moved to Negotiation',
    'Company · Acme Ltd · updated by Priya',
    'Task · Renew contract · assigned to Sam',
    'Contact · J. Whitfield · created by Priya',
];
</script>

<template>
    <Head title="Welcome">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous" />
        <link
            href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&display=swap"
            rel="stylesheet"
        />
    </Head>

    <div class="min-h-screen p-6 text-[#1b1b18] lg:p-10 dark:text-[#EDEDEC]">
        <header class="mx-auto mb-10 flex w-full max-w-5xl items-center justify-between text-sm lg:mb-16">
            <span class="font-['Space_Grotesk'] text-base font-semibold tracking-tight">
                Dashboard
            </span>
            <nav class="flex items-center gap-4">
                <Link
                    v-if="$page.props.auth.user"
                    :href="dashboard()"
                    class="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal hover:border-[#1915014a] dark:border-[#3E3E3A] dark:hover:border-[#62605b]"
                >
                    Go to dashboard
                </Link>
                <template v-else>
                    <Link
                        :href="login()"
                        class="inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal hover:border-[#19140035] dark:hover:border-[#3E3E3A]"
                    >
                        Log in
                    </Link>
                    <Link
                        :href="register()"
                        class="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal hover:border-[#1915014a] dark:border-[#3E3E3A] dark:hover:border-[#62605b]"
                    >
                        Request access
                    </Link>
                </template>
            </nav>
        </header>

        <main class="mx-auto flex w-full max-w-5xl flex-col gap-16">
            <!-- Hero -->
            <section class="grid gap-10 lg:grid-cols-[1.1fr_0.9fr] lg:items-center">
                <div>
                    <p class="mb-4 font-mono text-xs tracking-widest text-[#706f6c] uppercase dark:text-[#A1A09A]">
                        Service-oriented CRM
                    </p>
                    <h1 class="font-['Space_Grotesk'] text-4xl leading-tight font-semibold tracking-tight lg:text-5xl">
                        Every record has a story.
                        <span class="block text-emerald-600 dark:text-emerald-400">Every change has an author.</span>
                    </h1>
                    <p class="mt-5 max-w-md text-[15px] leading-relaxed text-[#706f6c] dark:text-[#A1A09A]">
                        People, companies, contacts, tasks and deals, tracked with a full audit trail: who
                        created it, who last touched it, and who restored it if it was ever deleted.
                    </p>
                    <div class="mt-8 flex gap-3">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="dashboard()"
                            class="inline-block rounded-sm border border-[#1b1b18] px-5 py-2 text-sm leading-normal font-medium hover:border-black dark:border-[#eeeeec] dark:hover:border-white"
                        >
                            Go to dashboard
                        </Link>
                        <template v-else>
                            <Link
                                :href="login()"
                                class="inline-block rounded-sm border border-[#1b1b18] px-5 py-2 text-sm leading-normal font-medium hover:border-black dark:border-[#eeeeec] dark:hover:border-white"
                            >
                                Sign in
                            </Link>
                            <Link
                                :href="register()"
                                class="inline-block rounded-sm border border-[#19140035] px-5 py-2 text-sm leading-normal hover:border-[#1915014a] dark:border-[#3E3E3A] dark:hover:border-[#62605b]"
                            >
                                Request access
                            </Link>
                        </template>
                    </div>
                </div>

                <!-- Signature element: live audit ticker -->
                <div
                    class="flex h-64 flex-col overflow-hidden rounded-lg border border-[#19140035] font-mono text-[13px] dark:border-[#3E3E3A]"
                >
                    <div class="flex shrink-0 items-center justify-between border-b border-[#19140035] px-4 py-2 dark:border-[#3E3E3A]">
                        <span class="flex items-center gap-2 text-[#706f6c] dark:text-[#A1A09A]">
                            <svg width="6" height="6" viewBox="0 0 6 6" class="text-emerald-500">
                                <circle cx="3" cy="3" r="3" fill="currentColor" />
                            </svg>
                            audit_log
                        </span>
                        <span class="text-[#706f6c] dark:text-[#A1A09A]">live</span>
                    </div>
                    <div class="relative flex-1 overflow-hidden">
                        <ul class="ticker space-y-3 px-4 py-3">
                            <li
                                v-for="(entry, i) in [...auditFeed, ...auditFeed]"
                                :key="i"
                                class="text-[#1b1b18] dark:text-[#EDEDEC]"
                            >
                                <span class="text-[#706f6c] dark:text-[#A1A09A]">&gt;</span> {{ entry }}
                            </li>
                        </ul>
                    </div>
                </div>
            </section>

            <!-- Module index -->
            <section>
                <p class="mb-4 font-mono text-xs tracking-widest text-[#706f6c] uppercase dark:text-[#A1A09A]">
                    What's inside
                </p>
                <ul class="divide-y divide-[#19140035] border-y border-[#19140035] dark:divide-[#3E3E3A] dark:border-[#3E3E3A]">
                    <li
                        v-for="module in modules"
                        :key="module.route"
                        class="flex items-center gap-6 py-4"
                    >
                        <span class="font-mono text-xs text-[#706f6c] dark:text-[#A1A09A]">{{ module.code }}</span>
                        <div class="flex-1">
                            <p class="font-['Space_Grotesk'] text-base font-medium">{{ module.label }}</p>
                            <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">{{ module.detail }}</p>
                        </div>
                        <span class="font-mono text-xs text-[#706f6c] dark:text-[#A1A09A]">{{ module.route }}</span>
                    </li>
                </ul>
            </section>
        </main>

        <footer class="mx-auto mt-16 w-full max-w-5xl border-t border-[#19140035] pt-6 text-xs text-[#706f6c] dark:border-[#3E3E3A] dark:text-[#A1A09A]">
            Built on Laravel and Vue.
        </footer>
    </div>
</template>

<style scoped>
.ticker {
    animation: ticker-scroll 12s linear infinite;
}

@keyframes ticker-scroll {
    0% {
        transform: translateY(0);
    }
    100% {
        transform: translateY(-50%);
    }
}

@media (prefers-reduced-motion: reduce) {
    .ticker {
        animation: none;
    }
}
</style>
