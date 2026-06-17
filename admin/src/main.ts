import { createApp } from 'vue'
import { createPinia } from 'pinia'
import { VueQueryPlugin } from '@tanstack/vue-query'
import VueApexCharts from 'vue3-apexcharts'
import App from './App.vue'
import router from './router'
import './assets/main.css'

const app = createApp(App)

// Custom directive: v-click-outside — closes dropdowns when clicking outside
app.directive('click-outside', {
  mounted(el, binding) {
    el.__clickOutsideHandler__ = (e: MouseEvent) => {
      if (!el.contains(e.target as Node)) binding.value(e)
    }
    document.addEventListener('mousedown', el.__clickOutsideHandler__)
  },
  unmounted(el) {
    document.removeEventListener('mousedown', el.__clickOutsideHandler__)
  },
})

app.use(createPinia())
app.use(router)
app.use(VueQueryPlugin)
app.use(VueApexCharts)

app.mount('#app')
