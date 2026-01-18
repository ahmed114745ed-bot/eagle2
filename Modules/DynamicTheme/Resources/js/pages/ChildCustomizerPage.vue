<template>
  <div class="child-customizer-wrapper min-h-screen bg-gray-100 p-6">
    <div class="max-w-7xl mx-auto">
      <!-- Header -->
      <div class="mb-6 flex justify-between items-start">
        <div>
          <h1 class="text-4xl font-bold text-gray-900">🎨 نظام تخصيص العناصر الفرعية</h1>
          <p class="text-gray-600 mt-2">Child Customizer System with Drawing Tools</p>
        </div>
        <button
          @click="goBack"
          class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold"
        >
          ← العودة
        </button>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
        <p class="mt-4 text-gray-600">جاري التحميل...</p>
      </div>

      <!-- Main Component -->
      <ChildCustomizerComponentV2
        v-else
        :currentConfiguration="selectedConfiguration"
        @updated="handleChildUpdated"
        @deleted="handleChildDeleted"
        @created="handleChildCreated"
      />
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import ChildCustomizerComponentV2 from '../components/ChildCustomizerComponentV2.vue'

export default {
  name: 'ChildCustomizerPage',
  components: {
    ChildCustomizerComponentV2
  },
  data() {
    return {
      selectedConfiguration: null,
      loading: true,
      configId: null
    }
  },
  mounted() {
    this.initializePage()
  },
  methods: {
    async initializePage() {
      // Get config ID from URL params or use default
      const params = new URLSearchParams(window.location.search)
      this.configId = params.get('config_id') || 1
      
      await this.loadConfiguration()
    },
    async loadConfiguration() {
      try {
        const response = await fetch(`/api/configurations/${this.configId}`)
        const data = await response.json()
        this.selectedConfiguration = data.data
      } catch (error) {
        console.error('Error loading configuration:', error)
        alert('خطأ في تحميل الكونفيجريشن')
      } finally {
        this.loading = false
      }
    },
    handleChildUpdated() {
      console.log('Child customizer updated!')
    },
    handleChildDeleted() {
      console.log('Child customizer deleted!')
    },
    handleChildCreated() {
      console.log('Child customizer created!')
    },
    goBack() {
      window.history.back()
    }
  }
}
</script>

<style scoped lang="postcss">
.child-customizer-wrapper {
  direction: rtl;
}
</style>

<style scoped>
.child-customizer-wrapper {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}
</style>
