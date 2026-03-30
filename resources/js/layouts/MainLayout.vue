<script setup>
import { useRoute } from 'vue-router';
import { computed } from 'vue';

const route = useRoute();
const pageTitle = computed(() => route.meta.title || 'Barber Shop');

const navigation = [
  { name: 'لوحة التحكم', to: '/', icon: 'home' },
  { name: 'المواعيد', to: '/appointments', icon: 'calendar' },
  { name: 'الفواتير', to: '/invoices', icon: 'document' },
];
</script>

<template>
  <div class="min-h-screen bg-gray-50 flex" dir="rtl">
    <!-- Sidebar -->
    <aside class="w-64 bg-white border-l border-gray-200 flex-shrink-0 fixed h-full right-0 z-10">
      <div class="h-16 flex items-center justify-center border-b border-gray-100">
        <h1 class="text-xl font-bold text-gray-800 tracking-wide">Barber Shop</h1>
      </div>
      
      <nav class="p-4 space-y-1">
        <router-link 
          v-for="item in navigation" 
          :key="item.to" 
          :to="item.to"
          class="flex items-center px-4 py-3 text-gray-600 rounded-lg transition-colors group hover:bg-blue-50 hover:text-blue-600"
          active-class="bg-blue-50 text-blue-600 font-medium"
        >
          <span class="ml-3">
            <!-- Simple SVG Icons based on name -->
            <svg v-if="item.icon === 'home'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            <svg v-else-if="item.icon === 'calendar'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <svg v-else-if="item.icon === 'document'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </span>
          {{ item.name }}
        </router-link>
      </nav>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 mr-64 flex flex-col min-h-screen transition-all duration-300">
      <!-- Topbar -->
      <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-8 sticky top-0 z-10">
        <h2 class="text-lg font-semibold text-gray-800">{{ pageTitle }}</h2>
        
        <div class="flex items-center space-x-4 space-x-reverse">
          <div class="flex items-center space-x-2 space-x-reverse cursor-pointer hover:bg-gray-50 p-2 rounded-md transition-colors">
            <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-sm">
              A
            </div>
            <span class="text-sm font-medium text-gray-700">Admin User</span>
          </div>
        </div>
      </header>

      <!-- Page Content -->
      <div class="p-8">
        <slot />
      </div>
    </main>
  </div>
</template>
