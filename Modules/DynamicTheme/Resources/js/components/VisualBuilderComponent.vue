<template>
  <div class="visual-builder-container">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
      <!-- Controls Panel -->
      <div class="lg:col-span-1 space-y-4">
        <!-- Shape Controls -->
        <div class="control-section bg-white rounded-lg shadow p-4">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">🎯 الشكل</h3>

          <!-- Border Radius -->
          <div class="control-group">
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Border Radius: {{ shapeConfig.borderRadius }}px
            </label>
            <input
              type="range"
              v-model.number="shapeConfig.borderRadius"
              min="0"
              max="50"
              class="w-full"
              @input="updatePreview"
            />
            <div class="flex space-x-1 mt-2">
              <button
                v-for="radius in [0, 8, 16, 24, 50]"
                :key="radius"
                @click="shapeConfig.borderRadius = radius; updatePreview()"
                :class="[
                  'flex-1 px-2 py-1 text-xs rounded',
                  shapeConfig.borderRadius === radius
                    ? 'bg-blue-600 text-white'
                    : 'bg-gray-200 text-gray-700'
                ]"
              >
                {{ radius }}
              </button>
            </div>
          </div>

          <!-- Border Width -->
          <div class="control-group mt-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Border Width: {{ borderConfig.width }}px
            </label>
            <input
              type="range"
              v-model.number="borderConfig.width"
              min="0"
              max="20"
              class="w-full"
              @input="updatePreview"
            />
          </div>

          <!-- Border Color -->
          <div class="control-group mt-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Border Color
            </label>
            <div class="flex items-center space-x-2">
              <input
                type="color"
                v-model="borderConfig.color"
                @input="updatePreview"
                class="w-12 h-10 rounded cursor-pointer"
              />
              <input
                type="text"
                v-model="borderConfig.color"
                class="flex-1 px-2 py-1 border border-gray-300 rounded text-sm font-mono"
                @input="updatePreview"
              />
            </div>
          </div>

          <!-- Border Style -->
          <div class="control-group mt-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Border Style
            </label>
            <select
              v-model="borderConfig.style"
              @change="updatePreview"
              class="w-full px-2 py-1 border border-gray-300 rounded text-sm"
            >
              <option value="solid">Solid</option>
              <option value="dashed">Dashed</option>
              <option value="dotted">Dotted</option>
              <option value="double">Double</option>
            </select>
          </div>
        </div>

        <!-- Shadow Controls -->
        <div class="control-section bg-white rounded-lg shadow p-4">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">💫 الظلال</h3>

          <!-- Shadow Blur -->
          <div class="control-group">
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Blur: {{ shadowConfig.blur }}px
            </label>
            <input
              type="range"
              v-model.number="shadowConfig.blur"
              min="0"
              max="30"
              class="w-full"
              @input="updatePreview"
            />
          </div>

          <!-- Shadow Spread -->
          <div class="control-group mt-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Spread: {{ shadowConfig.spread }}px
            </label>
            <input
              type="range"
              v-model.number="shadowConfig.spread"
              min="0"
              max="20"
              class="w-full"
              @input="updatePreview"
            />
          </div>

          <!-- Shadow Offset X -->
          <div class="control-group mt-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Offset X: {{ shadowConfig.offsetX }}px
            </label>
            <input
              type="range"
              v-model.number="shadowConfig.offsetX"
              min="-20"
              max="20"
              class="w-full"
              @input="updatePreview"
            />
          </div>

          <!-- Shadow Offset Y -->
          <div class="control-group mt-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Offset Y: {{ shadowConfig.offsetY }}px
            </label>
            <input
              type="range"
              v-model.number="shadowConfig.offsetY"
              min="-20"
              max="20"
              class="w-full"
              @input="updatePreview"
            />
          </div>

          <!-- Shadow Color -->
          <div class="control-group mt-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Shadow Color
            </label>
            <input
              type="color"
              v-model="shadowConfig.color"
              @input="updatePreview"
              class="w-full h-10 rounded cursor-pointer"
            />
          </div>

          <!-- Shadow Opacity -->
          <div class="control-group mt-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Opacity: {{ shadowConfig.opacity }}%
            </label>
            <input
              type="range"
              v-model.number="shadowConfig.opacity"
              min="0"
              max="100"
              class="w-full"
              @input="updatePreview"
            />
          </div>
        </div>

        <!-- Typography Controls -->
        <div class="control-section bg-white rounded-lg shadow p-4">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">📝 الخط</h3>

          <!-- Font Size -->
          <div class="control-group">
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Font Size: {{ typographyConfig.fontSize }}px
            </label>
            <input
              type="range"
              v-model.number="typographyConfig.fontSize"
              min="8"
              max="48"
              class="w-full"
              @input="updatePreview"
            />
          </div>

          <!-- Font Weight -->
          <div class="control-group mt-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Font Weight
            </label>
            <select
              v-model="typographyConfig.fontWeight"
              @change="updatePreview"
              class="w-full px-2 py-1 border border-gray-300 rounded text-sm"
            >
              <option value="400">Normal</option>
              <option value="500">Medium</option>
              <option value="600">Semibold</option>
              <option value="700">Bold</option>
              <option value="800">Extra Bold</option>
            </select>
          </div>

          <!-- Text Color -->
          <div class="control-group mt-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Text Color
            </label>
            <input
              type="color"
              v-model="typographyConfig.color"
              @input="updatePreview"
              class="w-full h-10 rounded cursor-pointer"
            />
          </div>
        </div>

        <!-- Layout Controls -->
        <div class="control-section bg-white rounded-lg shadow p-4">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">📏 التخطيط</h3>

          <!-- Padding -->
          <div class="control-group">
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Padding: {{ layoutConfig.padding }}px
            </label>
            <input
              type="range"
              v-model.number="layoutConfig.padding"
              min="0"
              max="40"
              class="w-full"
              @input="updatePreview"
            />
          </div>

          <!-- Margin -->
          <div class="control-group mt-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Margin: {{ layoutConfig.margin }}px
            </label>
            <input
              type="range"
              v-model.number="layoutConfig.margin"
              min="0"
              max="40"
              class="w-full"
              @input="updatePreview"
            />
          </div>

          <!-- Min Height -->
          <div class="control-group mt-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Min Height: {{ layoutConfig.minHeight }}px
            </label>
            <input
              type="range"
              v-model.number="layoutConfig.minHeight"
              min="20"
              max="300"
              class="w-full"
              @input="updatePreview"
            />
          </div>
        </div>

        <!-- Effects Controls -->
        <div class="control-section bg-white rounded-lg shadow p-4">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">✨ التأثيرات</h3>

          <!-- Opacity -->
          <div class="control-group">
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Opacity: {{ effectsConfig.opacity }}%
            </label>
            <input
              type="range"
              v-model.number="effectsConfig.opacity"
              min="0"
              max="100"
              class="w-full"
              @input="updatePreview"
            />
          </div>

          <!-- Scale -->
          <div class="control-group mt-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Scale: {{ (effectsConfig.scale * 100).toFixed(0) }}%
            </label>
            <input
              type="range"
              v-model.number="effectsConfig.scale"
              min="0.5"
              max="2"
              step="0.1"
              class="w-full"
              @input="updatePreview"
            />
          </div>

          <!-- Rotation -->
          <div class="control-group mt-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Rotation: {{ effectsConfig.rotation }}°
            </label>
            <input
              type="range"
              v-model.number="effectsConfig.rotation"
              min="0"
              max="360"
              class="w-full"
              @input="updatePreview"
            />
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons space-y-2">
          <button
            @click="applyDesign"
            class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium"
          >
            ✅ تطبيق التصميم
          </button>
          <button
            @click="resetDesign"
            class="w-full px-4 py-2 bg-gray-400 text-white rounded-lg hover:bg-gray-500 font-medium"
          >
            🔄 إعادة تعيين
          </button>
          <button
            @click="saveAsTemplate"
            class="w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium"
          >
            💾 حفظ كقالب
          </button>
        </div>
      </div>

      <!-- Preview Panel -->
      <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow p-4">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">👁️ معاينة</h3>

          <!-- Preview Background Options -->
          <div class="mb-4 pb-4 border-b border-gray-200">
            <label class="block text-sm font-medium text-gray-700 mb-2">
              خلفية المعاينة
            </label>
            <div class="flex space-x-2">
              <button
                @click="previewBg = '#ffffff'"
                :class="[
                  'px-3 py-1 rounded text-sm',
                  previewBg === '#ffffff'
                    ? 'bg-gray-900 text-white'
                    : 'bg-gray-200 text-gray-700'
                ]"
              >
                أبيض
              </button>
              <button
                @click="previewBg = '#f3f4f6'"
                :class="[
                  'px-3 py-1 rounded text-sm',
                  previewBg === '#f3f4f6'
                    ? 'bg-gray-900 text-white'
                    : 'bg-gray-200 text-gray-700'
                ]"
              >
                رمادي فاتح
              </button>
              <button
                @click="previewBg = '#1f2937'"
                :class="[
                  'px-3 py-1 rounded text-sm',
                  previewBg === '#1f2937'
                    ? 'bg-gray-900 text-white'
                    : 'bg-gray-200 text-gray-700'
                ]"
              >
                مظلم
              </button>
            </div>
          </div>

          <!-- Preview Canvas -->
          <div
            :style="{ backgroundColor: previewBg, padding: '2rem' }"
            class="min-h-96 rounded-lg flex items-center justify-center overflow-auto"
          >
            <div
              :style="getPreviewStyle()"
              class="transition-all duration-300"
            >
              <div class="text-center">
                <div class="text-2xl font-bold mb-2">🎨</div>
                <div class="text-sm font-medium">معاينة الويدجت</div>
              </div>
            </div>
          </div>

          <!-- Code Preview -->
          <div class="mt-4 pt-4 border-t border-gray-200">
            <label class="block text-sm font-medium text-gray-700 mb-2">
              📋 CSS المُنتج
            </label>
            <div class="bg-gray-900 text-gray-100 p-3 rounded text-xs font-mono overflow-x-auto max-h-48 overflow-y-auto">
              <pre>{{ generatedCSS }}</pre>
            </div>
            <button
              @click="copyCSS"
              class="mt-2 px-3 py-1 bg-gray-600 text-white rounded text-sm hover:bg-gray-700"
            >
              📋 نسخ
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'VisualBuilderComponent',
  props: {
    modelValue: {
      type: Object,
      default: () => ({}),
    },
  },
  emits: ['update:modelValue', 'apply', 'save-template'],
  data() {
    return {
      shapeConfig: {
        borderRadius: 8,
      },
      borderConfig: {
        width: 1,
        color: '#e5e7eb',
        style: 'solid',
      },
      shadowConfig: {
        blur: 4,
        spread: 0,
        offsetX: 0,
        offsetY: 2,
        color: '#000000',
        opacity: 10,
      },
      typographyConfig: {
        fontSize: 16,
        fontWeight: '400',
        color: '#111827',
      },
      layoutConfig: {
        padding: 16,
        margin: 0,
        minHeight: 80,
      },
      effectsConfig: {
        opacity: 100,
        scale: 1,
        rotation: 0,
      },
      previewBg: '#ffffff',
    };
  },
  computed: {
    generatedCSS() {
      let css = '';
      css += `border-radius: ${this.shapeConfig.borderRadius}px;\n`;
      css += `border: ${this.borderConfig.width}px ${this.borderConfig.style} ${this.borderConfig.color};\n`;
      css += `box-shadow: ${this.shadowConfig.offsetX}px ${this.shadowConfig.offsetY}px ${this.shadowConfig.blur}px ${this.shadowConfig.spread}px rgba(0, 0, 0, ${this.shadowConfig.opacity / 100});\n`;
      css += `font-size: ${this.typographyConfig.fontSize}px;\n`;
      css += `font-weight: ${this.typographyConfig.fontWeight};\n`;
      css += `color: ${this.typographyConfig.color};\n`;
      css += `padding: ${this.layoutConfig.padding}px;\n`;
      css += `margin: ${this.layoutConfig.margin}px;\n`;
      css += `min-height: ${this.layoutConfig.minHeight}px;\n`;
      css += `opacity: ${this.effectsConfig.opacity / 100};\n`;
      css += `transform: scale(${this.effectsConfig.scale}) rotate(${this.effectsConfig.rotation}deg);\n`;
      return css;
    },
  },
  methods: {
    getPreviewStyle() {
      return {
        borderRadius: `${this.shapeConfig.borderRadius}px`,
        border: `${this.borderConfig.width}px ${this.borderConfig.style} ${this.borderConfig.color}`,
        boxShadow: `${this.shadowConfig.offsetX}px ${this.shadowConfig.offsetY}px ${this.shadowConfig.blur}px ${this.shadowConfig.spread}px rgba(0, 0, 0, ${this.shadowConfig.opacity / 100})`,
        fontSize: `${this.typographyConfig.fontSize}px`,
        fontWeight: this.typographyConfig.fontWeight,
        color: this.typographyConfig.color,
        padding: `${this.layoutConfig.padding}px`,
        margin: `${this.layoutConfig.margin}px`,
        minHeight: `${this.layoutConfig.minHeight}px`,
        opacity: this.effectsConfig.opacity / 100,
        transform: `scale(${this.effectsConfig.scale}) rotate(${this.effectsConfig.rotation}deg)`,
        backgroundColor: '#ffffff',
        display: 'inline-block',
      };
    },
    updatePreview() {
      this.$emit('update:modelValue', this.getDesignConfig());
    },
    getDesignConfig() {
      return {
        shape_config: this.shapeConfig,
        border_config: this.borderConfig,
        shadow_config: this.shadowConfig,
        typography_config: this.typographyConfig,
        layout_config: this.layoutConfig,
        effects_config: this.effectsConfig,
      };
    },
    applyDesign() {
      this.$emit('apply', this.getDesignConfig());
    },
    resetDesign() {
      this.shapeConfig = { borderRadius: 8 };
      this.borderConfig = { width: 1, color: '#e5e7eb', style: 'solid' };
      this.shadowConfig = { blur: 4, spread: 0, offsetX: 0, offsetY: 2, color: '#000000', opacity: 10 };
      this.typographyConfig = { fontSize: 16, fontWeight: '400', color: '#111827' };
      this.layoutConfig = { padding: 16, margin: 0, minHeight: 80 };
      this.effectsConfig = { opacity: 100, scale: 1, rotation: 0 };
      this.updatePreview();
    },
    saveAsTemplate() {
      const name = prompt('أدخل اسم القالب:');
      if (name) {
        this.$emit('save-template', {
          name,
          design_config: this.getDesignConfig(),
        });
      }
    },
    copyCSS() {
      navigator.clipboard.writeText(this.generatedCSS);
      alert('تم النسخ!');
    },
  },
};
</script>

<style scoped>
.visual-builder-container {
  padding: 1rem;
}

.control-section {
  background: white;
  border-radius: 0.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.control-group {
  margin-bottom: 1rem;
}

input[type='range'] {
  cursor: pointer;
}

input[type='color'] {
  cursor: pointer;
}
</style>
