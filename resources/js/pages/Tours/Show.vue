<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { tours } from '@/routes';
import { type BreadcrumbItem, Tour } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
    Circle,
    CircleCheckBig,
    CirclePause,
} from 'lucide-vue-next';

// Components
import TourHeader from './components/TourHeader.vue';
import TourDetails from './components/TourDetails.vue';
import TourLegs from './components/TourLegs.vue';

const page = usePage();

// Get all routes (for sidebar)
const allTours = computed(() => page.props.tours_list as Array<Tour>);
// Current selected route
const currentTour = computed(() => page.props.current_tour as Tour);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Tours', href: tours().url },
    { title: currentTour?.value?.name, href: '#' },
];

const sidebarItems = computed(() =>
    allTours.value.map((tour: Tour) => ({
        title: tour.name,
        href: tours({ id: tour.id }).url,
        icon:
            tour.status != null
                ? tour.status.completed
                    ? CircleCheckBig
                    : CirclePause
                : Circle,
    })),
);
</script>

<template>
    <Head :title="currentTour?.name ?? ' '" />

    <AppLayout :breadcrumbs="breadcrumbs" :sidebarItems="sidebarItems">
        <div v-if="currentTour" class="flex flex-col gap-6 p-4">
            <TourHeader
                :tour="currentTour"
                :signedUp="currentTour.status != null"
            />
            <TourDetails
                :aircraft="currentTour.aircraft"
                :flightRules="currentTour.flight_rules"
                :requireOrder="currentTour.require_order"
            />
            <TourLegs :legs="currentTour.legs" />
        </div>
    </AppLayout>
</template>
