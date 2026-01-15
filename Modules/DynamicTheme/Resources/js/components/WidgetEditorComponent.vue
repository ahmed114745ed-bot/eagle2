<template>
  <div class="widget-editor-container">
    <!-- Header -->
    <div class="bg-white shadow-md rounded-lg p-4 mb-4">
      <div class="flex justify-between items-center">
        <div>
          <h2 class="text-2xl font-bold text-gray-900">
            ✏️ محرر الويدجت المتقدم
          </h2>
          <p class="text-sm text-gray-600 mt-1">
            رسم وتخصيص شكل وألوان الويدجت بسهولة
          </p>
        </div>
        <div class="flex space-x-2">
          <button
            @click="activeView = 'editor'"
            :class="[
              'px-4 py-2 rounded-lg font-medium transition-colors',
              activeView === 'editor'
                ? 'bg-blue-600 text-white'
                : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
            ]"
          >
            🎨 المحرر
          </button>
          <button
            @click="activeView = 'preview'"
            :class="[
              'px-4 py-2 rounded-lg font-medium transition-colors',
              activeView === 'preview'
                ? 'bg-blue-600 text-white'
                : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
            ]"
          >
            👁️ المعاينة
          </button>
          <button
            @click="activeView = 'code'"
            :class="[
              'px-4 py-2 rounded-lg font-medium transition-colors',
              activeView === 'code'
                ? 'bg-blue-600 text-white'
                : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
            ]"
          >
            💻 الكود
          </button>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
      <!-- Sidebar - Controls -->
      <div class="lg:col-span-1">
        <!-- Tabs -->
        <div class="bg-white rounded-lg shadow mb-4">
          <div class="flex border-b border-gray-200">
            <button
              v-for="tab in sidebarTabs"
              :key="tab"
              @click="activeSidebarTab = tab"
              :class="[
                'flex-1 px-4 py-2 text-sm font-medium transition-colors',
                activeSidebarTab === tab
                  ? 'border-b-2 border-blue-600 text-blue-600'
                  : 'text-gray-600 hover:text-gray-900'
              ]"
            >
              {{ tab }}
            </button>
          </div>

          <!-- Color Tab -->
          <div v-if="activeSidebarTab === 'الألوان'" class="p-4">
            <ColorPickerComponent
              v-model="designConfig.color_config"
              :presets="colorPresets"
              @apply="applyColors"
              @save-preset="saveColorPreset"
            />
          </div>

          <!-- Shape Tab -->
          <div v-if="activeSidebarTab === 'الشكل'" class="p-4">
            <VisualBuilderComponent
              v-model="designConfig"
              @apply="applyDesign"
              @save-template="saveDesignTemplate"
            />
          </div>

          <!-- Presets Tab -->
          <div v-if="activeSidebarTab === 'القوالب'" class="p-4 space-y-3">
            <div class="space-y-2">
              <label class="block text-sm font-medium text-gray-700">
                القوالب المحفوظة
              </label>
              <div v-if="designTemplates.length === 0" class="text-sm text-gray-500 text-center py-4">
                لا توجد قوالب محفوظة
              </div>
              <div v-else class="space-y-2 max-h-64 overflow-y-auto">
                <button
                  v-for="template in designTemplates"
                  :key="template.id"
                  @click="loadTemplate(template.id)"
                  class="w-full p-2 text-left bg-gray-50 hover:bg-gray-100 rounded border border-gray-200 transition-colors"
                >
                  <div class="font-medium text-sm">{{ template.name }}</div>
                  <div class="text-xs text-gray-500">{{ template.description }}</div>
                </button>
              </div>
            </div>

            <!-- Import/Export -->
            <div class="pt-3 border-t border-gray-200 space-y-2">
              <button
                @click="exportDesign"
                class="w-full px-3 py-2 bg-green-600 text-white text-sm rounded hover:bg-green-700"
              >
                📥 تصدير التصميم
              </button>
              <button
                @click="importDesign"
                class="w-full px-3 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700"
              >
                📤 استيراد التصميم
              </button>
            </div>
          </div>
        </div>

        <!-- Save Button -->
        <button
          @click="saveCustomizer"
          class="w-full px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold transition-colors"
        >
          💾 حفظ التغييرات
        </button>
      </div>

      <!-- Child Customizer Section -->
      <div class="lg:col-span-3 mt-6">
        <ChildCustomizerComponent
          :config-widget-override-id="configWidgetOverrideId"
          @updated="onChildUpdated"
          @deleted="onChildDeleted"
        />
      </div>
    </div>

      <!-- Main Content Area -->
      <div class="lg:col-span-2">
        <!-- Editor View -->
        <div v-if="activeView === 'editor'" class="bg-white rounded-lg shadow p-4">
          <VisualBuilderComponent
            v-model="designConfig"
            @apply="applyDesign"
            @save-template="saveDesignTemplate"
          />
        </div>

        <!-- Preview View -->
        <div v-if="activeView === 'preview'" class="space-y-4">
          <div class="bg-white rounded-lg shadow p-4">
            <StylePreviewComponent
              :color-config="designConfig.color_config || {}"
            />
          </div>
        </div>

        <!-- Code View -->
        <div v-if="activeView === 'code'" class="bg-white rounded-lg shadow p-4">
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                CSS المُنتج
              </label>
              <div class="bg-gray-900 text-gray-100 p-4 rounded font-mono text-sm max-h-96 overflow-auto">
                <pre>{{ generatedCSS }}</pre>
              </div>
              <button
                @click="copyCSS"
                class="mt-2 px-3 py-1 bg-gray-600 text-white rounded text-sm hover:bg-gray-700"
              >
                📋 نسخ
              </button>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                CSS Variables
              </label>
              <div class="bg-gray-900 text-gray-100 p-4 rounded font-mono text-sm max-h-96 overflow-auto">
                <pre>{{ cssVariables }}</pre>
              </div>
              <button
                @click="copyVariables"
                class="mt-2 px-3 py-1 bg-gray-600 text-white rounded text-sm hover:bg-gray-700"
              >
                📋 نسخ
              </button>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                JSON Config
              </label>
              <div class="bg-gray-900 text-gray-100 p-4 rounded font-mono text-sm max-h-96 overflow-auto">
                <pre>{{ JSON.stringify(designConfig, null, 2) }}</pre>
              </div>
              <button
                @click="copyJSON"
                class="mt-2 px-3 py-1 bg-gray-600 text-white rounded text-sm hover:bg-gray-700"
              >
                📋 نسخ
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Notifications -->
    <div class="fixed top-4 right-4 space-y-2 z-50">
      <div
        v-for="notification in notifications"
        :key="notification.id"
        :class="[
          'px-4 py-3 rounded-lg text-white font-medium shadow-lg animation-fade-in-out',
          notification.type === 'success' ? 'bg-green-600' :
          notification.type === 'error' ? 'bg-red-600' :
          'bg-blue-600'
        ]"
      >
        {{ notification.message }}
      </div>
    </div>
  </div>
</template>

<script>
import ColorPickerComponent from './ColorPickerComponent.vue';
import VisualBuilderComponent from './VisualBuilderComponent.vue';
import StylePreviewComponent from './StylePreviewComponent.vue';
import ChildCustomizerComponent from './ChildCustomizerComponent.vue';

export default {
  name: 'WidgetEditorComponent',
  components: {
    ColorPickerComponent,
    VisualBuilderComponent,
    StylePreviewComponent,
    ChildCustomizerComponent,
  },
  props: {
    configWidgetOverrideId: {
      type: Number,
      required: true,
    },
  },
  emits: ['save', 'error'],
  data() {
    return {
      activeView: 'editor',
      activeSidebarTab: 'الألوان',
      sidebarTabs: ['الألوان', 'الشكل', 'القوالب'],
      designConfig: {
        shape_config: { borderRadius: 8 },
        color_config: {
          primary: '#3b82f6',
          secondary: '#10b981',
          accent: '#f59e0b',
          text: '#111827',
        },
        border_config: { width: 1, color: '#e5e7eb', style: 'solid' },
        shadow_config: { blur: 4, spread: 0, offsetX: 0, offsetY: 2, color: '#000000', opacity: 10 },
        typography_config: { fontSize: 16, fontWeight: '400', color: '#111827' },
        layout_config: { padding: 16, margin: 0, minHeight: 80 },
        effects_config: { opacity: 100, scale: 1, rotation: 0 },
      },
      colorPresets: [],
      designTemplates: [],
      notifications: [],
      notificationId: 0,
    };
  },
  computed: {
    generatedCSS() {
      if (!this.designConfig) return '';
      
      let css = '';
      
      if (this.designConfig.shape_config?.borderRadius !== undefined) {
        css += `border-radius: ${this.designConfig.shape_config.borderRadius}px;\n`;
      }
      
      if (this.designConfig.border_config) {
        const bc = this.designConfig.border_config;
        css += `border: ${bc.width}px ${bc.style} ${bc.color};\n`;
      }
      
      if (this.designConfig.shadow_config) {
        const sc = this.designConfig.shadow_config;
        css += `box-shadow: ${sc.offsetX}px ${sc.offsetY}px ${sc.blur}px ${sc.spread}px rgba(0, 0, 0, ${sc.opacity / 100});\n`;
      }
      
      if (this.designConfig.typography_config) {
        const tc = this.designConfig.typography_config;
        css += `font-size: ${tc.fontSize}px;\n`;
        css += `font-weight: ${tc.fontWeight};\n`;
        css += `color: ${tc.color};\n`;
      }
      
      if (this.designConfig.layout_config) {
        const lc = this.designConfig.layout_config;
        css += `padding: ${lc.padding}px;\n`;
        css += `margin: ${lc.margin}px;\n`;
        css += `min-height: ${lc.minHeight}px;\n`;
      }
      
      if (this.designConfig.effects_config) {
        const ec = this.designConfig.effects_config;
        css += `opacity: ${ec.opacity / 100};\n`;
        css += `transform: scale(${ec.scale}) rotate(${ec.rotation}deg);\n`;
      }
      
      return css;
    },
    cssVariables() {
      let vars = ':root {\n';
      
      if (this.designConfig.color_config) {
        for (const [key, value] of Object.entries(this.designConfig.color_config)) {
          vars += `  --color-${key}: ${value};\n`;
        }
      }
      
      vars += '}\n';
      return vars;
    },
  },
  mounted() {
    this.loadPresets();
    this.loadTemplates();
  },
  methods: {
    loadPresets() {
      axios.get(`/api/color-presets/configuration/${this.configWidgetOverrideId}`)
        .then(response => {
          this.colorPresets = response.data.presets || [];
        })
        .catch(error => {
          this.showNotification('فشل تحميل الحفظ المسبق', 'error');
        });
    },
    loadTemplates() {
      axios.get(`/api/design-templates/configuration/${this.configWidgetOverrideId}`)
        .then(response => {
          this.designTemplates = response.data.templates || [];
        })
        .catch(error => {
          this.showNotification('فشل تحميل القوالب', 'error');
        });
    },
    loadTemplate(templateId) {
      axios.get(`/api/design-templates/${templateId}`)
        .then(response => {
          this.designConfig = response.data.template.design_config || this.designConfig;
          this.showNotification('تم تحميل القالب بنجاح', 'success');
        })
        .catch(error => {
          this.showNotification('فشل تحميل القالب', 'error');
        });
    },
    applyColors(colors) {
      this.designConfig.color_config = colors;
      this.showNotification('تم تطبيق الألوان', 'success');
    },
    applyDesign(design) {
      Object.assign(this.designConfig, design);
      this.showNotification('تم تطبيق التصميم', 'success');
    },
    saveCustomizer() {
      const data = {
        config_widget_override_id: this.configWidgetOverrideId,
        ...this.designConfig,
      };

      axios.post('/api/customizers', data)
        .then(response => {
          this.showNotification('تم حفظ التخصيص بنجاح', 'success');
          this.$emit('save', response.data.customizer);
        })
        .catch(error => {
          this.showNotification('فشل حفظ التخصيص', 'error');
        });
    },
    saveColorPreset(preset) {
      const data = {
        configuration_id: this.configWidgetOverrideId,
        name: preset.name,
        colors: preset.colors,
      };

      axios.post('/api/color-presets', data)
        .then(response => {
          this.colorPresets.push(response.data.preset);
          this.showNotification('تم حفظ الحفظ المسبق بنجاح', 'success');
        })
        .catch(error => {
          this.showNotification('فشل حفظ الحفظ المسبق', 'error');
        });
    },
    saveDesignTemplate(template) {
      const data = {
        configuration_id: this.configWidgetOverrideId,
        name: template.name,
        design_config: template.design_config,
      };

      axios.post('/api/design-templates', data)
        .then(response => {
          this.designTemplates.push(response.data.template);
          this.showNotification('تم حفظ القالب بنجاح', 'success');
        })
        .catch(error => {
          this.showNotification('فشل حفظ القالب', 'error');
        });
    },
    exportDesign() {
      const json = JSON.stringify(this.designConfig, null, 2);
      const blob = new Blob([json], { type: 'application/json' });
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = 'design-config.json';
      a.click();
      URL.revokeObjectURL(url);
    },
    importDesign() {
      const input = document.createElement('input');
      input.type = 'file';
      input.accept = '.json';
      input.onchange = (e) => {
        const file = e.target.files[0];
        const reader = new FileReader();
        reader.onload = (event) => {
          try {
            this.designConfig = JSON.parse(event.target.result);
            this.showNotification('تم استيراد التصميم بنجاح', 'success');
          } catch (error) {
            this.showNotification('خطأ في استيراد الملف', 'error');
          }
        };
        reader.readAsText(file);
      };
      input.click();
    },
    copyCSS() {
      navigator.clipboard.writeText(this.generatedCSS);
      this.showNotification('تم نسخ CSS', 'success');
    },
    copyVariables() {
      navigator.clipboard.writeText(this.cssVariables);
      this.showNotification('تم نسخ المتغيرات', 'success');
    },
    copyJSON() {
      navigator.clipboard.writeText(JSON.stringify(this.designConfig, null, 2));
      this.showNotification('تم نسخ JSON', 'success');
    },
    showNotification(message, type = 'info') {
      const id = this.notificationId++;
      this.notifications.push({ id, message, type });

      setTimeout(() => {
        this.notifications = this.notifications.filter(n => n.id !== id);
      }, 3000);
    },
    onChildUpdated() {
      this.showNotification('تم تحديث الطفل بنجاح', 'success');
      this.$emit('child-updated');
    },
    onChildDeleted() {
      this.showNotification('تم حذف الطفل بنجاح', 'success');
      this.$emit('child-deleted');
    },
  },
};
</script>

<style scoped>
.widget-editor-container {
  padding: 1rem;
}

@keyframes fadeInOut {
  0%, 100% { opacity: 0; transform: translateX(20px); }
  10%, 90% { opacity: 1; transform: translateX(0); }
}

.animation-fade-in-out {
  animation: fadeInOut 3s ease-in-out;
}
</style>
