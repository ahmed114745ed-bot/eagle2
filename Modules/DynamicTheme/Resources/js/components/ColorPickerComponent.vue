<template>
  <div class="color-picker-container">
    <!-- Color Picker Input -->
    <div class="space-y-4">
      <!-- Primary Color -->
      <div class="color-input-group">
        <label class="block text-sm font-medium text-gray-700 mb-2">
          🎨 اللون الأساسي
        </label>
        <div class="flex items-center space-x-3">
          <input
            type="color"
            v-model="localConfig.primary"
            @input="onColorChange('primary', $event)"
            class="w-16 h-10 rounded cursor-pointer border border-gray-300"
          />
          <input
            type="text"
            v-model="localConfig.primary"
            @input="onColorChange('primary', $event)"
            class="flex-1 px-3 py-2 border border-gray-300 rounded-lg font-mono text-sm"
            placeholder="#000000"
          />
          <button
            @click="copyToClipboard(localConfig.primary)"
            class="px-3 py-2 bg-gray-200 hover:bg-gray-300 rounded text-sm"
            title="نسخ"
          >
            📋
          </button>
        </div>
      </div>

      <!-- Secondary Color -->
      <div class="color-input-group">
        <label class="block text-sm font-medium text-gray-700 mb-2">
          🎨 اللون الثانوي
        </label>
        <div class="flex items-center space-x-3">
          <input
            type="color"
            v-model="localConfig.secondary"
            @input="onColorChange('secondary', $event)"
            class="w-16 h-10 rounded cursor-pointer border border-gray-300"
          />
          <input
            type="text"
            v-model="localConfig.secondary"
            @input="onColorChange('secondary', $event)"
            class="flex-1 px-3 py-2 border border-gray-300 rounded-lg font-mono text-sm"
            placeholder="#000000"
          />
          <button
            @click="copyToClipboard(localConfig.secondary)"
            class="px-3 py-2 bg-gray-200 hover:bg-gray-300 rounded text-sm"
            title="نسخ"
          >
            📋
          </button>
        </div>
      </div>

      <!-- Accent Color -->
      <div class="color-input-group">
        <label class="block text-sm font-medium text-gray-700 mb-2">
          🎨 لون التأكيد
        </label>
        <div class="flex items-center space-x-3">
          <input
            type="color"
            v-model="localConfig.accent"
            @input="onColorChange('accent', $event)"
            class="w-16 h-10 rounded cursor-pointer border border-gray-300"
          />
          <input
            type="text"
            v-model="localConfig.accent"
            @input="onColorChange('accent', $event)"
            class="flex-1 px-3 py-2 border border-gray-300 rounded-lg font-mono text-sm"
            placeholder="#000000"
          />
          <button
            @click="copyToClipboard(localConfig.accent)"
            class="px-3 py-2 bg-gray-200 hover:bg-gray-300 rounded text-sm"
            title="نسخ"
          >
            📋
          </button>
        </div>
      </div>

      <!-- Text Color -->
      <div class="color-input-group">
        <label class="block text-sm font-medium text-gray-700 mb-2">
          🎨 لون النص
        </label>
        <div class="flex items-center space-x-3">
          <input
            type="color"
            v-model="localConfig.text"
            @input="onColorChange('text', $event)"
            class="w-16 h-10 rounded cursor-pointer border border-gray-300"
          />
          <input
            type="text"
            v-model="localConfig.text"
            @input="onColorChange('text', $event)"
            class="flex-1 px-3 py-2 border border-gray-300 rounded-lg font-mono text-sm"
            placeholder="#000000"
          />
          <button
            @click="copyToClipboard(localConfig.text)"
            class="px-3 py-2 bg-gray-200 hover:bg-gray-300 rounded text-sm"
            title="نسخ"
          >
            📋
          </button>
        </div>
      </div>

      <!-- Color Format Selection -->
      <div class="color-format-group mt-4 pt-4 border-t border-gray-200">
        <label class="block text-sm font-medium text-gray-700 mb-2">
          📋 صيغة الألوان
        </label>
        <div class="flex space-x-2">
          <button
            v-for="format in ['hex', 'rgb', 'hsl']"
            :key="format"
            @click="changeColorFormat(format)"
            :class="[
              'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
              colorFormat === format
                ? 'bg-blue-600 text-white'
                : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
            ]"
          >
            {{ format.toUpperCase() }}
          </button>
        </div>
      </div>

      <!-- Color Presets -->
      <div class="color-presets mt-4 pt-4 border-t border-gray-200">
        <label class="block text-sm font-medium text-gray-700 mb-2">
          💾 الحفظ المسبق
        </label>
        <div class="space-y-2">
          <div class="flex space-x-2">
            <input
              v-model="presetName"
              type="text"
              placeholder="اسم الحفظ المسبق"
              class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm"
            />
            <button
              @click="savePreset"
              class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm hover:bg-green-700"
            >
              حفظ
            </button>
          </div>
          <div v-if="presets.length > 0" class="grid grid-cols-2 gap-2">
            <button
              v-for="preset in presets"
              :key="preset.id"
              @click="applyPreset(preset)"
              class="p-2 bg-gray-50 border border-gray-200 rounded text-sm hover:bg-gray-100 text-left"
            >
              <div class="font-medium">{{ preset.name }}</div>
              <div class="flex space-x-1 mt-1">
                <div
                  v-for="(color, key) in preset.colors"
                  :key="key"
                  :style="{ backgroundColor: color }"
                  class="w-4 h-4 rounded border border-gray-300"
                  :title="key"
                />
              </div>
            </button>
          </div>
        </div>
      </div>

      <!-- Color Picker Grid (Quick Colors) -->
      <div class="quick-colors mt-4 pt-4 border-t border-gray-200">
        <label class="block text-sm font-medium text-gray-700 mb-3">
          🎯 الألوان السريعة
        </label>
        <div class="grid grid-cols-6 gap-2">
          <button
            v-for="color in quickColors"
            :key="color"
            @click="setColor('primary', color)"
            :style="{ backgroundColor: color }"
            class="w-8 h-8 rounded border-2 border-gray-300 hover:border-blue-500 cursor-pointer transition-all"
            :title="color"
          />
        </div>
      </div>

      <!-- Preview Box -->
      <div class="preview-section mt-4 pt-4 border-t border-gray-200">
        <label class="block text-sm font-medium text-gray-700 mb-2">
          👁️ معاينة
        </label>
        <div
          :style="{
            backgroundColor: localConfig.primary,
            color: localConfig.text,
          }"
          class="p-4 rounded-lg text-center font-medium"
        >
          معاينة النص
        </div>
        <div class="grid grid-cols-3 gap-2 mt-3">
          <div
            :style="{ backgroundColor: localConfig.primary }"
            class="h-12 rounded-lg border-2 border-gray-300 flex items-center justify-center text-xs font-medium text-white"
          >
            أساسي
          </div>
          <div
            :style="{ backgroundColor: localConfig.secondary }"
            class="h-12 rounded-lg border-2 border-gray-300 flex items-center justify-center text-xs font-medium text-white"
          >
            ثانوي
          </div>
          <div
            :style="{ backgroundColor: localConfig.accent }"
            class="h-12 rounded-lg border-2 border-gray-300 flex items-center justify-center text-xs font-medium text-white"
          >
            تأكيد
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="action-buttons mt-6 flex space-x-2">
        <button
          @click="applyColors"
          class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium"
        >
          ✅ تطبيق الألوان
        </button>
        <button
          @click="resetColors"
          class="flex-1 px-4 py-2 bg-gray-400 text-white rounded-lg hover:bg-gray-500 font-medium"
        >
          🔄 إعادة تعيين
        </button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ColorPickerComponent',
  props: {
    modelValue: {
      type: Object,
      default: () => ({
        primary: '#3b82f6',
        secondary: '#10b981',
        accent: '#f59e0b',
        text: '#111827',
      }),
    },
    presets: {
      type: Array,
      default: () => [],
    },
  },
  emits: ['update:modelValue', 'apply', 'save-preset'],
  data() {
    return {
      localConfig: { ...this.modelValue },
      colorFormat: 'hex',
      presetName: '',
      quickColors: [
        '#ef4444', '#f97316', '#eab308', '#84cc16', '#22c55e',
        '#10b981', '#06b6d4', '#3b82f6', '#6366f1', '#8b5cf6',
        '#d946ef', '#ec4899', '#000000', '#ffffff', '#6b7280',
      ],
    };
  },
  watch: {
    modelValue(newVal) {
      this.localConfig = { ...newVal };
    },
  },
  methods: {
    onColorChange(key, event) {
      this.localConfig[key] = event.target.value;
      this.$emit('update:modelValue', { ...this.localConfig });
    },
    setColor(key, color) {
      this.localConfig[key] = color;
      this.$emit('update:modelValue', { ...this.localConfig });
    },
    changeColorFormat(format) {
      this.colorFormat = format;
      // يمكن إضافة تحويل الألوان بين الصيغ هنا
    },
    applyColors() {
      this.$emit('apply', this.localConfig);
    },
    resetColors() {
      this.localConfig = { ...this.modelValue };
    },
    copyToClipboard(text) {
      navigator.clipboard.writeText(text);
      this.$emit('update:modelValue', { ...this.localConfig });
    },
    savePreset() {
      if (this.presetName.trim()) {
        this.$emit('save-preset', {
          name: this.presetName,
          colors: { ...this.localConfig },
        });
        this.presetName = '';
      }
    },
    applyPreset(preset) {
      this.localConfig = { ...preset.colors };
      this.$emit('update:modelValue', { ...this.localConfig });
    },
  },
};
</script>

<style scoped>
.color-picker-container {
  padding: 1rem;
  background: white;
  border-radius: 0.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.color-input-group {
  padding: 0.5rem 0;
}

input[type='color'] {
  cursor: pointer;
}

input[type='color']::-webkit-color-swatch-wrapper {
  padding: 2px;
}

input[type='color']::-webkit-color-swatch {
  border: none;
  border-radius: 0.375rem;
}
</style>
