<template>
  <div class="child-customizer-component">
    <!-- Child List Header -->
    <div class="flex items-center justify-between mb-6 p-4 bg-gradient-to-r from-purple-50 to-blue-50 rounded-lg border border-purple-200">
      <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
        <svg class="w-5 h-5 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM7 9a1 1 0 100-2 1 1 0 000 2zm6 0a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
        </svg>
        Children Customizer
      </h3>
      <button
        @click="showAddForm = true"
        class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors flex items-center gap-2"
      >
        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 10l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
        </svg>
        Add Child
      </button>
    </div>

    <!-- Children List -->
    <div class="space-y-3 mb-6">
      <draggable
        v-model="childrenList"
        @change="onChildrenReorder"
        class="space-y-2"
        ghost-class="opacity-50"
      >
        <div
          v-for="(child, index) in childrenList"
          :key="child.id"
          class="child-item p-4 bg-white border-2 rounded-lg cursor-move hover:border-purple-400 transition-colors"
          :class="{ 'border-purple-500 bg-purple-50': selectedChildId === child.id, 'border-gray-200': selectedChildId !== child.id }"
        >
          <div class="flex items-center justify-between">
            <div
              @click="selectChild(child)"
              class="flex-1"
            >
              <h4 class="font-semibold text-gray-800">{{ child.name || `Child ${index + 1}` }}</h4>
              <p class="text-sm text-gray-600">{{ child.description || 'No description' }}</p>
            </div>
            <div class="flex items-center gap-2">
              <button
                @click="toggleChildVisibility(child)"
                class="p-2 rounded hover:bg-gray-100"
                :title="child.is_active ? 'Hide' : 'Show'"
              >
                <svg v-if="child.is_active" class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                  <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                </svg>
                <svg v-else class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-14-14zM10 5a5 5 0 013.707 8.707l-1.414-1.415A3 3 0 1010 5z" clip-rule="evenodd"/>
                </svg>
              </button>
              <button
                @click="editChild(child)"
                class="p-2 rounded hover:bg-blue-100"
              >
                <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                </svg>
              </button>
              <button
                @click="deleteChild(child.id)"
                class="p-2 rounded hover:bg-red-100"
              >
                <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5 0a1 1 0 012 0v6a1 1 0 11-2 0V8z" clip-rule="evenodd"/>
                </svg>
              </button>
            </div>
          </div>
        </div>
      </draggable>
    </div>

    <!-- Selected Child Editor -->
    <div v-if="selectedChildId" class="editor-section bg-white rounded-lg border border-gray-300 p-6">
      <h4 class="text-lg font-bold text-gray-800 mb-4">Edit Child: {{ selectedChild?.name || 'Unnamed' }}</h4>

      <!-- Shape Controls -->
      <div class="shape-controls mb-6">
        <h5 class="text-md font-semibold text-gray-700 mb-3">Shape</h5>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Type</label>
            <select
              v-model="selectedChild.shape_config.type"
              @change="updateChild"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
            >
              <option value="rectangle">Rectangle</option>
              <option value="circle">Circle</option>
              <option value="square">Square</option>
              <option value="rounded">Rounded</option>
              <option value="diamond">Diamond</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Border Radius</label>
            <input
              v-model.number="selectedChild.shape_config.borderRadius"
              @change="updateChild"
              type="range"
              min="0"
              max="50"
              class="w-full"
            />
            <span class="text-xs text-gray-500">{{ selectedChild.shape_config.borderRadius }}px</span>
          </div>
        </div>
      </div>

      <!-- Position Controls -->
      <div class="position-controls mb-6">
        <h5 class="text-md font-semibold text-gray-700 mb-3">Position & Size</h5>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Top</label>
            <input
              v-model.number="selectedChild.position_config.top"
              @change="updateChild"
              type="number"
              placeholder="0"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Left</label>
            <input
              v-model.number="selectedChild.position_config.left"
              @change="updateChild"
              type="number"
              placeholder="0"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Width</label>
            <input
              v-model.number="selectedChild.position_config.width"
              @change="updateChild"
              type="number"
              placeholder="100"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Height</label>
            <input
              v-model.number="selectedChild.position_config.height"
              @change="updateChild"
              type="number"
              placeholder="100"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg"
            />
          </div>
        </div>
      </div>

      <!-- Color Controls -->
      <div class="color-controls mb-6">
        <h5 class="text-md font-semibold text-gray-700 mb-3">Colors</h5>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Background</label>
            <input
              v-model="selectedChild.color_config.background"
              @change="updateChild"
              type="color"
              class="w-full h-10 cursor-pointer border border-gray-300 rounded-lg"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Border Color</label>
            <input
              v-model="selectedChild.color_config.border"
              @change="updateChild"
              type="color"
              class="w-full h-10 cursor-pointer border border-gray-300 rounded-lg"
            />
          </div>
        </div>
      </div>

      <!-- Border Controls -->
      <div class="border-controls mb-6">
        <h5 class="text-md font-semibold text-gray-700 mb-3">Border</h5>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Width</label>
            <input
              v-model.number="selectedChild.border_config.width"
              @change="updateChild"
              type="number"
              min="0"
              max="10"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Style</label>
            <select
              v-model="selectedChild.border_config.style"
              @change="updateChild"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg"
            >
              <option value="solid">Solid</option>
              <option value="dashed">Dashed</option>
              <option value="dotted">Dotted</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Animation Controls -->
      <div class="animation-controls mb-6">
        <h5 class="text-md font-semibold text-gray-700 mb-3">Animation</h5>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Type</label>
            <select
              v-model="selectedChild.animation_config.type"
              @change="updateChild"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg"
            >
              <option value="none">None</option>
              <option value="fade">Fade</option>
              <option value="slide">Slide</option>
              <option value="bounce">Bounce</option>
              <option value="pulse">Pulse</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Duration (ms)</label>
            <input
              v-model.number="selectedChild.animation_config.duration"
              @change="updateChild"
              type="number"
              min="100"
              step="100"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg"
            />
          </div>
        </div>
      </div>

      <!-- Preview -->
      <div class="preview-section bg-gray-50 p-4 rounded-lg">
        <h5 class="text-sm font-semibold text-gray-700 mb-3">Preview</h5>
        <div class="flex justify-center items-center w-full h-48 bg-white border-2 border-dashed border-gray-300 rounded-lg">
          <div
            class="preview-child"
            :style="getPreviewStyle(selectedChild)"
          />
        </div>
      </div>
    </div>

    <!-- Add Child Form Modal -->
    <div v-if="showAddForm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h4 class="text-lg font-bold text-gray-800 mb-4">Add New Child</h4>
        <div class="space-y-4">
          <input
            v-model="newChild.name"
            type="text"
            placeholder="Child name"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
          />
          <textarea
            v-model="newChild.description"
            placeholder="Description (optional)"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
            rows="3"
          />
          <div class="flex gap-3">
            <button
              @click="addNewChild"
              class="flex-1 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700"
            >
              Add
            </button>
            <button
              @click="showAddForm = false"
              class="flex-1 px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400"
            >
              Cancel
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import draggable from 'vuedraggable';
import axios from 'axios';

const props = defineProps({
  configWidgetOverrideId: {
    type: Number,
    required: true
  }
});

const emit = defineEmits(['updated', 'deleted']);

const childrenList = ref([]);
const selectedChildId = ref(null);
const showAddForm = ref(false);
const newChild = ref({
  name: '',
  description: ''
});

const selectedChild = computed(() => {
  return childrenList.value.find(c => c.id === selectedChildId.value);
});

onMounted(() => {
  loadChildren();
});

const loadChildren = async () => {
  try {
    const response = await axios.get(
      `/api/child-customizers/widget-override/${props.configWidgetOverrideId}`
    );
    childrenList.value = response.data.customizers;
  } catch (error) {
    console.error('Failed to load children:', error);
  }
};

const selectChild = (child) => {
  selectedChildId.value = child.id;
};

const addNewChild = async () => {
  try {
    const response = await axios.post('/api/child-customizers', {
      config_widget_override_id: props.configWidgetOverrideId,
      name: newChild.value.name,
      description: newChild.value.description,
      shape_config: {
        type: 'rectangle',
        borderRadius: 0
      },
      color_config: {
        background: '#ffffff',
        border: '#000000'
      },
      position_config: {
        top: 0,
        left: 0,
        width: 100,
        height: 100
      },
      border_config: {
        width: 1,
        style: 'solid'
      },
      animation_config: {
        type: 'none',
        duration: 300
      }
    });

    childrenList.value.push(response.data.customizer);
    showAddForm.value = false;
    newChild.value = { name: '', description: '' };
    emit('updated');
  } catch (error) {
    console.error('Failed to add child:', error);
  }
};

const updateChild = async () => {
  if (!selectedChild.value) return;

  try {
    await axios.put(`/api/child-customizers/${selectedChild.value.id}`, {
      shape_config: selectedChild.value.shape_config,
      color_config: selectedChild.value.color_config,
      position_config: selectedChild.value.position_config,
      border_config: selectedChild.value.border_config,
      animation_config: selectedChild.value.animation_config
    });
    emit('updated');
  } catch (error) {
    console.error('Failed to update child:', error);
  }
};

const deleteChild = async (id) => {
  if (!confirm('Are you sure?')) return;

  try {
    await axios.delete(`/api/child-customizers/${id}`);
    childrenList.value = childrenList.value.filter(c => c.id !== id);
    if (selectedChildId.value === id) selectedChildId.value = null;
    emit('deleted');
  } catch (error) {
    console.error('Failed to delete child:', error);
  }
};

const toggleChildVisibility = async (child) => {
  try {
    await axios.put(`/api/child-customizers/${child.id}`, {
      is_active: !child.is_active
    });
    child.is_active = !child.is_active;
    emit('updated');
  } catch (error) {
    console.error('Failed to toggle visibility:', error);
  }
};

const onChildrenReorder = async () => {
  try {
    const customizers = childrenList.value.map((c, idx) => ({
      id: c.id,
      order: idx,
      is_active: c.is_active
    }));
    await axios.post('/api/child-customizers/batch-update', { customizers });
    emit('updated');
  } catch (error) {
    console.error('Failed to reorder:', error);
  }
};

const getPreviewStyle = (child) => {
  if (!child) return {};
  return {
    position: 'absolute',
    top: `${child.position_config?.top || 0}px`,
    left: `${child.position_config?.left || 0}px`,
    width: `${child.position_config?.width || 100}px`,
    height: `${child.position_config?.height || 100}px`,
    backgroundColor: child.color_config?.background || '#ffffff',
    borderColor: child.color_config?.border || '#000000',
    borderWidth: `${child.border_config?.width || 1}px`,
    borderStyle: child.border_config?.style || 'solid',
    borderRadius: `${child.shape_config?.borderRadius || 0}px`,
    animation: child.animation_config?.type !== 'none'
      ? `${child.animation_config?.type} ${child.animation_config?.duration || 300}ms infinite`
      : 'none'
  };
};

defineExpose({
  loadChildren
});
</script>

<style scoped>
.child-customizer-component {
  padding: 1rem;
}

.child-item {
  transition: all 0.3s ease;
}

.child-item:hover {
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

@keyframes fade {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}

@keyframes slide {
  0%, 100% { transform: translateX(0); }
  50% { transform: translateX(10px); }
}

@keyframes bounce {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-10px); }
}

@keyframes pulse {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.1); }
}
</style>
