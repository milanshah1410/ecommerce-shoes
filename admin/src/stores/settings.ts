import { defineStore } from 'pinia'
import { ref } from 'vue'
import { settingsApi } from '@/api/settings'
import type { AppSettings } from '@/types'

export const useSettingsStore = defineStore('settings', () => {
  const settings = ref<AppSettings | null>(null)
  const sidebarOpen = ref(true)
  const darkMode = ref(localStorage.getItem('admin_dark') === 'true')

  async function fetchSettings() {
    try {
      const { data } = await settingsApi.get()
      settings.value = data
    } catch {}
  }

  function toggleSidebar() {
    sidebarOpen.value = !sidebarOpen.value
  }

  function toggleDarkMode() {
    darkMode.value = !darkMode.value
    localStorage.setItem('admin_dark', String(darkMode.value))
    document.documentElement.classList.toggle('dark', darkMode.value)
  }

  function initDarkMode() {
    document.documentElement.classList.toggle('dark', darkMode.value)
  }

  return { settings, sidebarOpen, darkMode, fetchSettings, toggleSidebar, toggleDarkMode, initDarkMode }
})
