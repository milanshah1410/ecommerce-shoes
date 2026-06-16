<script setup lang="ts">
import { computed } from 'vue'
import VueApexCharts from 'vue3-apexcharts'
import type { ApexOptions } from 'apexcharts'

const props = defineProps<{
  statuses: Record<string, number>
}>()

const labels = computed(() => Object.keys(props.statuses).map((s) => s.charAt(0).toUpperCase() + s.slice(1)))
const series = computed(() => Object.values(props.statuses))

const chartOptions = computed<ApexOptions>(() => ({
  chart: { type: 'donut', fontFamily: 'inherit' },
  labels: labels.value,
  colors: ['#f59e0b', '#6366f1', '#8b5cf6', '#06b6d4', '#22c55e', '#ef4444', '#f97316'],
  legend: { position: 'bottom', fontSize: '12px', labels: { colors: '#6b7280' } },
  dataLabels: { enabled: false },
  plotOptions: { pie: { donut: { size: '65%' } } },
  stroke: { show: false },
  tooltip: { y: { formatter: (v: number) => `${v} orders` } },
}))
</script>

<template>
  <VueApexCharts type="donut" height="260" :options="chartOptions" :series="series" />
</template>
