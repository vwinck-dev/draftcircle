<script setup lang="ts">
import { Bar } from 'vue-chartjs';
import {
    BarElement,
    CategoryScale,
    Chart as ChartJS,
    Legend,
    LinearScale,
    Tooltip,
} from 'chart.js';
import { trans } from '@/i18n';
import { computed } from 'vue';
ChartJS.register(BarElement, CategoryScale, Legend, LinearScale, Tooltip);
const props = defineProps<{ values: Record<string, number> }>();
const data = computed(() => ({
    labels: Object.keys(props.values),
    datasets: [
        {
            label: trans('admin.metrics'),
            data: Object.values(props.values),
            backgroundColor: '#2563eb',
            borderRadius: 8,
        },
    ],
}));
const options = { responsive: true, maintainAspectRatio: false };
</script>
<template>
    <div class="panel h-80 p-5">
        <Bar :data="data" :options="options" />
    </div>
</template>
