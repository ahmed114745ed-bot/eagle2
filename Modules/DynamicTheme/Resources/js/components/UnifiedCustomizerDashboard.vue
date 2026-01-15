<template>
  <div class="unified-customizer-dashboard">
    <!-- Header -->
    <div class="dashboard-header bg-gradient-to-r from-indigo-600 to-purple-600 text-white p-6 rounded-lg mb-6">
      <h2 class="text-2xl font-bold mb-2">Widget & Children Customizer</h2>
      <p class="text-indigo-100">Complete visual editor for widgets and their child elements</p>
    </div>

    <!-- Tab Navigation -->
    <div class="tabs flex gap-2 mb-6 bg-gray-100 p-2 rounded-lg">
      <button
        v-for="tab in tabs"
        :key="tab"
        @click="activeTab = tab"
        :class="[
          'px-4 py-2 rounded-lg font-medium transition-all',
          activeTab === tab
            ? 'bg-white text-purple-600 shadow-md'
            : 'text-gray-600 hover:text-gray-800'
        ]"
      >
        {{ tab }}
      </button>
    </div>

    <!-- Action Buttons -->
    <div class="actions flex gap-2 mb-6 flex-wrap">
      <button
        @click="saveAll"
        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center gap-2"
      >
        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
          <path d="M5.5 13a3.5 3.5 0 01-.369-6.98 4 4 0 117.753-1.3A4.5 4.5 0 1113.5 13H11V9.413l1.293 1.293a1 1 0 001.414-1.414l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13H5.5z"/>
        </svg>
        Save All
      </button>
      <button
        @click="exportConfiguration"
        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2"
      >
        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/>
        </svg>
        Export
      </button>
      <button
        @click="showImportDialog = true"
        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2"
      >
        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 9.414V3a1 1 0 112 0v6.414l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/>
        </svg>
        Import
      </button>
      <button
        @click="showCloneDialog = true"
        class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 flex items-center gap-2"
      >
        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
          <path d="M8 3a1 1 0 011 1v2h2V4a1 1 0 011-1h2a1 1 0 011 1v2h2V4a1 1 0 011-1h-1V2a1 1 0 10-2 0v1H8V2a1 1 0 10-2 0v1H3a1 1 0 00-1 1v2h2V4a1 1 0 011-1h2z"/>
          <path fill-rule="evenodd" d="M3 8h14v7a2 2 0 01-2 2H5a2 2 0 01-2-2V8zm2 3a1 1 0 100 2h1a1 1 0 100-2H5zm5-1a1 1 0 011 1v1h1a1 1 0 110 2h-1v1a1 1 0 11-2 0v-1h-1a1 1 0 110-2h1v-1a1 1 0 011-1z" clip-rule="evenodd"/>
        </svg>
        Clone
      </button>
    </div>

    <!-- Tabs Content -->
    <div class="tab-content">
      <!-- Widget Tab -->
      <div v-show="activeTab === 'Widget'" class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <div class="lg:col-span-2">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Widget Customizer</h3>
            <ColorPickerComponent
              v-model="widgetData.colors"
              @update="updateWidget"
            />
            <VisualBuilderComponent
              v-model="widgetData.style"
              class="mt-4"
              @update="updateWidget"
            />
          </div>
          <div>
            <h3 class="text-lg font-bold text-gray-800 mb-4">Preview</h3>
            <StylePreviewComponent
              :style="widgetData.style"
              :colors="widgetData.colors"
            />
          </div>
        </div>
      </div>

      <!-- Children Tab -->
      <div v-show="activeTab === 'Children'" class="space-y-6">
        <ChildCustomizerComponent
          :config-widget-override-id="widgetOverrideId"
          @updated="refreshData"
          @deleted="refreshData"
        />
      </div>

      <!-- Color Presets Tab -->
      <div v-show="activeTab === 'Color Presets'" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div
            v-for="preset in colorPresets"
            :key="preset.id"
            class="preset-card p-4 bg-white rounded-lg border-2 border-gray-200 hover:border-purple-400 cursor-pointer transition-colors"
            @click="applyPreset(preset)"
          >
            <h4 class="font-semibold text-gray-800 mb-2">{{ preset.name }}</h4>
            <div class="flex gap-2 mb-2">
              <div
                v-for="(color, key) in parsePresetColors(preset.colors)"
                :key="key"
                class="w-8 h-8 rounded border border-gray-300"
                :style="{ backgroundColor: color }"
                :title="key"
              />
            </div>
            <button
              @click.stop="deletePreset(preset.id)"
              class="text-xs text-red-600 hover:text-red-800"
            >
              Delete
            </button>
          </div>
          <div
            @click="showNewPresetDialog = true"
            class="preset-card p-4 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300 cursor-pointer hover:border-purple-400 transition-colors flex items-center justify-center min-h-32"
          >
            <div class="text-center">
              <svg class="w-8 h-8 text-gray-400 mx-auto mb-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM11 9a1 1 0 11-2 0 1 1 0 012 0zm1 4a1 1 0 100-2 1 1 0 000 2zm3-1a1 1 0 11-2 0 1 1 0 012 0z" clip-rule="evenodd"/>
              </svg>
              <p class="text-gray-600 font-medium">Add Preset</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Design Templates Tab -->
      <div v-show="activeTab === 'Design Templates'" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div
            v-for="template in designTemplates"
            :key="template.id"
            class="template-card p-4 bg-white rounded-lg border-2 border-gray-200 hover:border-purple-400 cursor-pointer transition-colors"
          >
            <h4 class="font-semibold text-gray-800 mb-2">{{ template.name }}</h4>
            <p class="text-sm text-gray-600 mb-3">{{ template.description }}</p>
            <div class="flex gap-2">
              <button
                @click="useTemplate(template)"
                class="text-xs bg-purple-600 text-white px-3 py-1 rounded hover:bg-purple-700"
              >
                Use
              </button>
              <button
                @click="deleteTemplate(template.id)"
                class="text-xs bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700"
              >
                Delete
              </button>
            </div>
          </div>
          <div
            @click="showNewTemplateDialog = true"
            class="template-card p-4 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300 cursor-pointer hover:border-purple-400 transition-colors flex items-center justify-center min-h-32"
          >
            <div class="text-center">
              <svg class="w-8 h-8 text-gray-400 mx-auto mb-2" fill="currentColor" viewBox="0 0 20 20">
                <path d="M5.5 13a3.5 3.5 0 01-.369-6.98 4 4 0 117.753-1.3A4.5 4.5 0 1113.5 13H11V9.413l1.293 1.293a1 1 0 001.414-1.414l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13H5.5z"/>
              </svg>
              <p class="text-gray-600 font-medium">Save Template</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Code Tab -->
      <div v-show="activeTab === 'Code'" class="space-y-6">
        <div class="bg-gray-800 text-gray-100 p-4 rounded-lg font-mono text-sm overflow-x-auto">
          <pre>{{ compiledCSS }}</pre>
        </div>
        <button
          @click="copyCSS"
          class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700"
        >
          Copy CSS
        </button>
      </div>
    </div>

    <!-- New Preset Dialog -->
    <div v-if="showNewPresetDialog" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h4 class="text-lg font-bold text-gray-800 mb-4">Create Color Preset</h4>
        <input
          v-model="newPreset.name"
          type="text"
          placeholder="Preset name"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg mb-4 focus:ring-2 focus:ring-purple-500"
        />
        <div class="flex gap-3">
          <button
            @click="createPreset"
            class="flex-1 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700"
          >
            Create
          </button>
          <button
            @click="showNewPresetDialog = false"
            class="flex-1 px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400"
          >
            Cancel
          </button>
        </div>
      </div>
    </div>

    <!-- Import Dialog -->
    <div v-if="showImportDialog" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h4 class="text-lg font-bold text-gray-800 mb-4">Import Configuration</h4>
        <textarea
          v-model="importData"
          placeholder="Paste JSON configuration"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg mb-4 focus:ring-2 focus:ring-purple-500"
          rows="6"
        />
        <div class="flex gap-3">
          <button
            @click="importConfiguration"
            class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
          >
            Import
          </button>
          <button
            @click="showImportDialog = false"
            class="flex-1 px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400"
          >
            Cancel
          </button>
        </div>
      </div>
    </div>

    <!-- Clone Dialog -->
    <div v-if="showCloneDialog" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h4 class="text-lg font-bold text-gray-800 mb-4">Clone Configuration</h4>
        <label class="block text-sm font-medium text-gray-700 mb-2">Target Widget Override ID</label>
        <input
          v-model.number="cloneTargetId"
          type="number"
          placeholder="Enter target widget override ID"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg mb-4 focus:ring-2 focus:ring-purple-500"
        />
        <div class="flex gap-3">
          <button
            @click="cloneConfiguration"
            class="flex-1 px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700"
          >
            Clone
          </button>
          <button
            @click="showCloneDialog = false"
            class="flex-1 px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400"
          >
            Cancel
          </button>
        </div>
      </div>
    </div>

    <!-- Toast Notification -->
    <div v-if="toast.message" :class="['fixed bottom-4 right-4 p-4 rounded-lg text-white', toast.type === 'success' ? 'bg-green-600' : 'bg-red-600']">
      {{ toast.message }}
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import ColorPickerComponent from './ColorPickerComponent.vue';
import VisualBuilderComponent from './VisualBuilderComponent.vue';
import StylePreviewComponent from './StylePreviewComponent.vue';
import ChildCustomizerComponent from './ChildCustomizerComponent.vue';

const props = defineProps({
  configId: {
    type: Number,
    required: true
  },
  widgetOverrideId: {
    type: Number,
    required: true
  }
});

const tabs = ['Widget', 'Children', 'Color Presets', 'Design Templates', 'Code'];
const activeTab = ref('Widget');

const widgetData = ref({
  colors: {},
  style: {}
});

const colorPresets = ref([]);
const designTemplates = ref([]);
const compiledCSS = ref('');

const showNewPresetDialog = ref(false);
const showImportDialog = ref(false);
const showCloneDialog = ref(false);

const newPreset = ref({ name: '' });
const importData = ref('');
const cloneTargetId = ref(null);

const toast = ref({ message: '', type: 'success' });

onMounted(() => {
  loadCompleteData();
});

const loadCompleteData = async () => {
  try {
    const response = await axios.get(
      `/api/configurations/${props.configId}/widgets/${props.widgetOverrideId}/complete`
    );
    const data = response.data.data;
    
    compiledCSS.value = data.compiled_css;
    colorPresets.value = data.color_presets;
    designTemplates.value = data.design_templates;
  } catch (error) {
    showToast('Failed to load data', 'error');
    console.error(error);
  }
};

const saveAll = async () => {
  try {
    await axios.post(
      `/api/configurations/${props.configId}/widgets/${props.widgetOverrideId}/complete`,
      {
        widget: widgetData.value,
        children: [],
        color_presets: colorPresets.value
      }
    );
    showToast('Configuration saved successfully', 'success');
  } catch (error) {
    showToast('Failed to save configuration', 'error');
    console.error(error);
  }
};

const exportConfiguration = async () => {
  try {
    const response = await axios.get(
      `/api/configurations/${props.configId}/widgets/${props.widgetOverrideId}/complete/export`
    );
    const dataStr = JSON.stringify(response.data.data, null, 2);
    const dataBlob = new Blob([dataStr], { type: 'application/json' });
    const url = URL.createObjectURL(dataBlob);
    const link = document.createElement('a');
    link.href = url;
    link.download = response.data.filename;
    link.click();
    showToast('Configuration exported', 'success');
  } catch (error) {
    showToast('Failed to export', 'error');
  }
};

const importConfiguration = async () => {
  try {
    const config = JSON.parse(importData.value);
    await axios.post(
      `/api/configurations/${props.configId}/complete/import`,
      { configuration: config }
    );
    showImportDialog.value = false;
    importData.value = '';
    loadCompleteData();
    showToast('Configuration imported successfully', 'success');
  } catch (error) {
    showToast('Failed to import configuration', 'error');
  }
};

const cloneConfiguration = async () => {
  if (!cloneTargetId.value) {
    showToast('Please enter target widget override ID', 'error');
    return;
  }
  try {
    await axios.post(
      `/api/configurations/${props.configId}/widgets/${props.widgetOverrideId}/complete/clone`,
      { target_widget_override_id: cloneTargetId.value }
    );
    showCloneDialog.value = false;
    cloneTargetId.value = null;
    showToast('Configuration cloned successfully', 'success');
  } catch (error) {
    showToast('Failed to clone configuration', 'error');
  }
};

const applyPreset = (preset) => {
  const colors = parsePresetColors(preset.colors);
  widgetData.value.colors = colors;
  updateWidget();
};

const parsePresetColors = (colors) => {
  if (typeof colors === 'string') {
    try {
      return JSON.parse(colors);
    } catch {
      return {};
    }
  }
  return colors || {};
};

const createPreset = async () => {
  try {
    const response = await axios.post('/api/color-presets', {
      configuration_id: props.configId,
      name: newPreset.value.name,
      colors: JSON.stringify(widgetData.value.colors)
    });
    colorPresets.value.push(response.data.preset);
    showNewPresetDialog.value = false;
    newPreset.value = { name: '' };
    showToast('Preset created successfully', 'success');
  } catch (error) {
    showToast('Failed to create preset', 'error');
  }
};

const deletePreset = async (id) => {
  if (!confirm('Delete this preset?')) return;
  try {
    await axios.delete(`/api/color-presets/${id}`);
    colorPresets.value = colorPresets.value.filter(p => p.id !== id);
    showToast('Preset deleted', 'success');
  } catch (error) {
    showToast('Failed to delete preset', 'error');
  }
};

const useTemplate = async (template) => {
  try {
    const config = JSON.parse(template.design_config);
    widgetData.value = config;
    updateWidget();
    showToast('Template applied', 'success');
  } catch (error) {
    showToast('Failed to apply template', 'error');
  }
};

const deleteTemplate = async (id) => {
  if (!confirm('Delete this template?')) return;
  try {
    await axios.delete(`/api/design-templates/${id}`);
    designTemplates.value = designTemplates.value.filter(t => t.id !== id);
    showToast('Template deleted', 'success');
  } catch (error) {
    showToast('Failed to delete template', 'error');
  }
};

const updateWidget = () => {
  // Trigger save in parent or emit event
};

const refreshData = () => {
  loadCompleteData();
};

const copyCSS = () => {
  navigator.clipboard.writeText(compiledCSS.value);
  showToast('CSS copied to clipboard', 'success');
};

const showToast = (message, type = 'success') => {
  toast.value = { message, type };
  setTimeout(() => {
    toast.value = { message: '', type: 'success' };
  }, 3000);
};
</script>

<style scoped>
.unified-customizer-dashboard {
  padding: 1rem;
}

.preset-card,
.template-card {
  min-height: 140px;
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.tab-content > div {
  animation: slideIn 0.3s ease;
}
</style>
