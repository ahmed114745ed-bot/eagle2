<template>
  <div class="style-preview-container">
    <!-- Real-time Preview Tabs -->
    <div class="flex space-x-2 mb-4 border-b border-gray-200">
      <button
        v-for="tab in tabs"
        :key="tab"
        @click="activeTab = tab"
        :class="[
          'px-4 py-2 font-medium transition-colors border-b-2',
          activeTab === tab
            ? 'border-blue-600 text-blue-600'
            : 'border-transparent text-gray-600 hover:text-gray-900'
        ]"
      >
        {{ tab }}
      </button>
    </div>

    <!-- Color Preview Tab -->
    <div v-if="activeTab === 'الألوان'" class="space-y-4">
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div
          v-for="(color, key) in colors"
          :key="key"
          class="space-y-2"
        >
          <div
            :style="{ backgroundColor: color }"
            class="w-full h-24 rounded-lg border-2 border-gray-200 cursor-pointer hover:border-blue-500 transition-all"
            @click="copyColor(color)"
            :title="color"
          />
          <div class="text-sm font-medium text-gray-700 capitalize">{{ key }}</div>
          <div class="text-xs text-gray-500 font-mono">{{ color }}</div>
        </div>
      </div>

      <!-- Color Harmony -->
      <div class="mt-6 pt-6 border-t border-gray-200">
        <h4 class="text-sm font-semibold text-gray-900 mb-3">🎨 توافق الألوان</h4>
        <div class="space-y-3">
          <div class="flex items-center space-x-2">
            <div class="text-sm font-medium text-gray-600 w-24">أساسي:</div>
            <div class="flex space-x-2">
              <div
                v-for="color in colorHarmony.primary"
                :key="color"
                :style="{ backgroundColor: color }"
                class="w-12 h-12 rounded border border-gray-300"
                :title="color"
              />
            </div>
          </div>
          <div class="flex items-center space-x-2">
            <div class="text-sm font-medium text-gray-600 w-24">ثانوي:</div>
            <div class="flex space-x-2">
              <div
                v-for="color in colorHarmony.secondary"
                :key="color"
                :style="{ backgroundColor: color }"
                class="w-12 h-12 rounded border border-gray-300"
                :title="color"
              />
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Shape & Border Preview Tab -->
    <div v-if="activeTab === 'الأشكال'" class="space-y-4">
      <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
        <div class="flex flex-col items-center space-y-2">
          <div
            class="w-20 h-20 bg-blue-500"
            :style="{ borderRadius: '0px' }"
          />
          <span class="text-xs text-gray-600">مربع</span>
        </div>
        <div class="flex flex-col items-center space-y-2">
          <div
            class="w-20 h-20 bg-blue-500"
            :style="{ borderRadius: '8px' }"
          />
          <span class="text-xs text-gray-600">مشطوف</span>
        </div>
        <div class="flex flex-col items-center space-y-2">
          <div
            class="w-20 h-20 bg-blue-500"
            :style="{ borderRadius: '50%' }"
          />
          <span class="text-xs text-gray-600">دائري</span>
        </div>
        <div class="flex flex-col items-center space-y-2">
          <div
            class="w-20 h-20 bg-blue-500 border-2 border-gray-800"
            :style="{ borderRadius: '8px' }}
          />
          <span class="text-xs text-gray-600">مع حد</span>
        </div>
        <div class="flex flex-col items-center space-y-2">
          <div
            class="w-20 h-20 bg-blue-500"
            :style="{ borderRadius: '8px', boxShadow: '0 4px 6px rgba(0,0,0,0.1)' }}
          />
          <span class="text-xs text-gray-600">مع ظل</span>
        </div>
        <div class="flex flex-col items-center space-y-2">
          <div
            class="w-20 h-20 bg-gradient-to-br from-blue-500 to-purple-600"
            :style="{ borderRadius: '8px' }}
          />
          <span class="text-xs text-gray-600">Gradient</span>
        </div>
      </div>
    </div>

    <!-- Typography Preview Tab -->
    <div v-if="activeTab === 'الخطوط'" class="space-y-4">
      <div class="space-y-3">
        <div class="p-3 bg-gray-50 rounded-lg">
          <div style="font-size: 28px; font-weight: bold;">المسمى الرئيسي</div>
          <div class="text-xs text-gray-500">Heading 1 - 28px / Bold</div>
        </div>
        <div class="p-3 bg-gray-50 rounded-lg">
          <div style="font-size: 24px; font-weight: 600;">العنوان الفرعي</div>
          <div class="text-xs text-gray-500">Heading 2 - 24px / Semibold</div>
        </div>
        <div class="p-3 bg-gray-50 rounded-lg">
          <div style="font-size: 18px; font-weight: 500;">عنوان النص</div>
          <div class="text-xs text-gray-500">Heading 3 - 18px / Medium</div>
        </div>
        <div class="p-3 bg-gray-50 rounded-lg">
          <div style="font-size: 16px; font-weight: 400; line-height: 1.6;">
            هذا هو نص الفقرة العادي. يمكنك أن ترى كيف يبدو النص العادي مع الخط المختار والحجم المناسب.
          </div>
          <div class="text-xs text-gray-500 mt-2">Body - 16px / Regular</div>
        </div>
        <div class="p-3 bg-gray-50 rounded-lg">
          <div style="font-size: 14px; font-weight: 400; color: #666;">
            هذا هو النص الصغير للتعليقات والتفاصيل الإضافية.
          </div>
          <div class="text-xs text-gray-500 mt-2">Small - 14px / Regular</div>
        </div>
      </div>
    </div>

    <!-- Shadow & Effects Tab -->
    <div v-if="activeTab === 'التأثيرات'" class="space-y-4">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="flex flex-col items-center space-y-2">
          <div
            class="w-32 h-32 bg-white rounded-lg"
            style="box-shadow: none;"
          />
          <span class="text-xs text-gray-600">بدون ظل</span>
        </div>
        <div class="flex flex-col items-center space-y-2">
          <div
            class="w-32 h-32 bg-white rounded-lg"
            style="box-shadow: 0 1px 2px rgba(0,0,0,0.05);"
          />
          <span class="text-xs text-gray-600">ظل صغير</span>
        </div>
        <div class="flex flex-col items-center space-y-2">
          <div
            class="w-32 h-32 bg-white rounded-lg"
            style="box-shadow: 0 4px 6px rgba(0,0,0,0.1);"
          />
          <span class="text-xs text-gray-600">ظل متوسط</span>
        </div>
        <div class="flex flex-col items-center space-y-2">
          <div
            class="w-32 h-32 bg-white rounded-lg"
            style="box-shadow: 0 10px 25px rgba(0,0,0,0.15);"
          />
          <span class="text-xs text-gray-600">ظل كبير</span>
        </div>
        <div class="flex flex-col items-center space-y-2">
          <div
            class="w-32 h-32 bg-blue-500 rounded-lg"
            style="opacity: 0.5;"
          />
          <span class="text-xs text-gray-600">50% شفافية</span>
        </div>
        <div class="flex flex-col items-center space-y-2">
          <div
            class="w-32 h-32 bg-blue-500 rounded-lg transition-transform hover:scale-110"
          />
          <span class="text-xs text-gray-600">Hover Scale</span>
        </div>
      </div>
    </div>

    <!-- Spacing Preview Tab -->
    <div v-if="activeTab === 'التباعد'" class="space-y-4">
      <div class="space-y-3">
        <div class="p-3 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
          <div class="text-xs font-medium text-gray-600 mb-2">Padding</div>
          <div class="bg-white p-4 rounded border-2 border-blue-400">
            <div class="bg-blue-100 text-center py-2 text-xs">محتوى</div>
          </div>
        </div>

        <div class="p-3 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
          <div class="text-xs font-medium text-gray-600 mb-2">Margin</div>
          <div class="border-2 border-red-400 rounded inline-block">
            <div class="bg-red-100 p-4 rounded text-center py-2 text-xs">محتوى</div>
          </div>
        </div>

        <div class="grid grid-cols-4 gap-2">
          <div class="text-center">
            <div class="bg-gray-300 h-12 mb-1 rounded" />
            <div class="text-xs text-gray-600">XS (4px)</div>
          </div>
          <div class="text-center">
            <div class="bg-gray-300 h-16 mb-1 rounded" />
            <div class="text-xs text-gray-600">SM (8px)</div>
          </div>
          <div class="text-center">
            <div class="bg-gray-300 h-20 mb-1 rounded" />
            <div class="text-xs text-gray-600">MD (16px)</div>
          </div>
          <div class="text-center">
            <div class="bg-gray-300 h-32 mb-1 rounded" />
            <div class="text-xs text-gray-600">LG (32px)</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Responsive Preview Tab -->
    <div v-if="activeTab === 'الاستجابة'" class="space-y-4">
      <div class="space-y-4">
        <div class="border rounded-lg overflow-hidden">
          <div class="bg-gray-100 p-1 text-xs text-gray-600 font-medium">📱 Mobile (375px)</div>
          <div class="bg-white p-4" style="width: 375px; max-width: 100%; margin: auto;">
            <div class="space-y-2">
              <div class="h-8 bg-blue-500 rounded" />
              <div class="h-4 bg-gray-300 rounded" />
              <div class="h-4 bg-gray-300 rounded w-5/6" />
            </div>
          </div>
        </div>

        <div class="border rounded-lg overflow-hidden">
          <div class="bg-gray-100 p-1 text-xs text-gray-600 font-medium">📱 Tablet (768px)</div>
          <div class="bg-white p-4" style="width: 768px; max-width: 100%; margin: auto;">
            <div class="grid grid-cols-2 gap-4">
              <div class="space-y-2">
                <div class="h-8 bg-blue-500 rounded" />
                <div class="h-4 bg-gray-300 rounded" />
              </div>
              <div class="space-y-2">
                <div class="h-8 bg-blue-500 rounded" />
                <div class="h-4 bg-gray-300 rounded" />
              </div>
            </div>
          </div>
        </div>

        <div class="border rounded-lg overflow-hidden">
          <div class="bg-gray-100 p-1 text-xs text-gray-600 font-medium">💻 Desktop (1024px)</div>
          <div class="bg-white p-4" style="width: 1024px; max-width: 100%; margin: auto;">
            <div class="grid grid-cols-3 gap-4">
              <div class="space-y-2">
                <div class="h-8 bg-blue-500 rounded" />
                <div class="h-4 bg-gray-300 rounded" />
              </div>
              <div class="space-y-2">
                <div class="h-8 bg-blue-500 rounded" />
                <div class="h-4 bg-gray-300 rounded" />
              </div>
              <div class="space-y-2">
                <div class="h-8 bg-blue-500 rounded" />
                <div class="h-4 bg-gray-300 rounded" />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'StylePreviewComponent',
  props: {
    colorConfig: {
      type: Object,
      default: () => ({
        primary: '#3b82f6',
        secondary: '#10b981',
        accent: '#f59e0b',
        text: '#111827',
        background: '#ffffff',
      }),
    },
  },
  data() {
    return {
      activeTab: 'الألوان',
      tabs: ['الألوان', 'الأشكال', 'الخطوط', 'التأثيرات', 'التباعد', 'الاستجابة'],
      colorHarmony: {
        primary: ['#3b82f6', '#2563eb', '#1d4ed8'],
        secondary: ['#10b981', '#059669', '#047857'],
      },
    };
  },
  computed: {
    colors() {
      return this.colorConfig;
    },
  },
  methods: {
    copyColor(color) {
      navigator.clipboard.writeText(color);
      alert(`تم نسخ: ${color}`);
    },
  },
};
</script>

<style scoped>
.style-preview-container {
  padding: 1rem;
  background: white;
  border-radius: 0.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.flex {
  display: flex;
}

button {
  transition: all 0.3s ease;
}
</style>
