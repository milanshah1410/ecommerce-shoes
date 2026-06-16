<script setup lang="ts">
import { computed } from 'vue'
import VueApexCharts from 'vue3-apexcharts'
import type { ApexOptions } from 'apexcharts'

const props = defineProps<{
  data: Array<{ month: string; revenue: number }>
}>()

const chartOptions = computed<ApexOptions>(() => ({
  chart: {
    type: 'area' as const,
    toolbar: { show: false },
    zoom: { enabled: false },
    fontFamily: 'inherit',
  },
  colors: ['#6366f1'],
  fill: {
    type: 'gradient',
    gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.0 },
  },
  dataLabels: { enabled: false },
  stroke: { curve: 'smooth', width: 2 },
  xaxis: {
    categories: props.data.map((d) => d.month),
    labels: { style: { colors: '#9ca3af', fontSize: '11px' } },
    axisBorder: { show: false },
    axisTicks: { show: false },
  },
  yaxis: {
    labels: {
      formatter: (v: number) => `₹${(v / 1000).toFixed(0)}k`,
      style: { colors: '#9ca3af', fontSize: '11px' },
    },
  },
  grid: {
    borderColor: '#e5e7eb',
    strokeDashArray: 4,
    yaxis: { lines: { show: true } },
    xaxis: { lines: { show: false } },
  },
  tooltip: {
    y: { formatter: (v: number) => `₹${v.toLocaleString()}` },
  },
}))

const series = computed(() => [
  { name: 'Revenue', data: props.data.map((d) => d.revenue) },
])
</script>

<template>
  <VueApexCharts type="area" height="280" :options="chartOptions" :series="series" />
</template>
