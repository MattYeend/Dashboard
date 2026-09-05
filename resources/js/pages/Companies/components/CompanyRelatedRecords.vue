<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { show as contactShow, index as contactsIndex } from '@/routes/contacts';
import { show as dealShow, index as dealsIndex } from '@/routes/deals';
import { show as orderShow, index as ordersIndex } from '@/routes/orders';
import type { Contact, Order, Deal } from '@/types';

interface Props {
    companyId: number;
    contacts: Contact[];
    orders: Order[];
    deals: Deal[];
}

defineProps<Props>();
</script>

<template>
    <div class="rounded-lg border p-4">
        <section>
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-semibold text-gray-300">Contacts</h2>
                <Link
                    :href="
                        contactsIndex.url({
                            query: {
                                contactable_type: 'company',
                                contactable_id: companyId,
                            },
                        })
                    "
                    class="text-xs text-gray-400 hover:text-gray-300"
                >
                    View all contacts
                </Link>
            </div>
            <ul class="mt-2 divide-y divide-gray-500">
                <li
                    v-for="contact in contacts"
                    :key="contact.id"
                    class="py-2 text-sm"
                >
                    <Link
                        :href="contactShow.url(contact.id)"
                        class="text-gray-300 hover:underline"
                    >
                        {{
                            contact.email ??
                            contact.phone ??
                            `Contact #${contact.id}`
                        }}
                    </Link>
                </li>
                <li v-if="!contacts.length" class="py-2 text-xs text-gray-400">
                    No related contacts.
                </li>
            </ul>
        </section>

        <section>
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-semibold text-gray-300">Orders</h2>
                <Link
                    :href="
                        ordersIndex.url({
                            query: {
                                orderable_type: 'company',
                                orderable_id: companyId,
                            },
                        })
                    "
                    class="text-xs text-gray-400 hover:text-gray-300"
                >
                    View all orders
                </Link>
            </div>
            <ul class="mt-2 divide-y divide-gray-500">
                <li
                    v-for="order in orders"
                    :key="order.id"
                    class="py-2 text-sm"
                >
                    <Link
                        :href="orderShow.url(order.id)"
                        class="text-gray-300 hover:underline"
                    >
                        {{ order.order_number }}: {{ order.title }}
                    </Link>
                </li>
                <li v-if="!orders.length" class="py-2 text-xs text-gray-400">
                    No related orders.
                </li>
            </ul>
        </section>

        <section>
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-semibold text-gray-300">Deals</h2>
                <Link
                    :href="dealsIndex.url({ query: { company_id: companyId } })"
                    class="text-xs text-gray-400 hover:text-gray-300"
                >
                    View all deals
                </Link>
            </div>
            <ul class="mt-2 divide-y divide-gray-500">
                <li v-for="deal in deals" :key="deal.id" class="py-2 text-sm">
                    <Link
                        :href="dealShow.url(deal.id)"
                        class="text-gray-300 hover:underline"
                    >
                        {{ deal.title }}
                    </Link>
                </li>
                <li v-if="!deals.length" class="py-2 text-xs text-gray-400">
                    No related deals.
                </li>
            </ul>
        </section>
    </div>
</template>
