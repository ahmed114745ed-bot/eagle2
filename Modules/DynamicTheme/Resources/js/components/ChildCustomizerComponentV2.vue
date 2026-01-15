<template>
  <div class="child-customizer-wrapper">
    <!-- Configuration Info Header -->
    <div class="config-header bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 rounded-r">
      <div class="grid grid-cols-2 gap-4">
        <div>
          <span class="text-sm text-gray-600">📍 Configuration الحالية:</span>
          <p class="text-lg font-bold text-blue-700">{{ currentConfiguration?.name || 'No Config' }}</p>
        </div>
        <div>
          <span class="text-sm text-gray-600">👥 عدد الأطفال:</span>
          <p class="text-lg font-bold text-blue-700">{{ configChildren.length }}</p>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Left: Children List -->
      <div class="children-panel">
        <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
          <span>👶 العناصر الفرعية</span>
          <button
            @click="showAddChildForm = true"
            class="ml-auto px-3 py-1 bg-purple-600 text-white text-sm rounded hover:bg-purple-700"
          >
            ➕ جديد
          </button>
        </h3>

        <div class="space-y-2 max-h-96 overflow-y-auto">
          <div
            v-for="child in configChildren"
            :key="child.id"
            @click="selectChild(child)"
            class="child-item p-3 bg-white border-2 rounded cursor-pointer hover:border-purple-400 transition-all"
            :class="{ 'border-purple-500 bg-purple-50': selectedChild?.id === child.id, 'border-gray-200': selectedChild?.id !== child.id }"
          >
            <div class="flex justify-between items-start">
              <div>
                <h4 class="font-semibold text-gray-800">{{ child.name || 'بدون اسم' }}</h4>
                <p class="text-xs text-gray-500">ID: {{ child.id }}</p>
              </div>
              <button
                @click.stop="deleteChild(child)"
                class="text-red-600 hover:text-red-700 p-1"
              >
                🗑️
              </button>
            </div>
          </div>

          <div v-if="configChildren.length === 0" class="text-center text-gray-500 py-8">
            <p>لا توجد عناصر فرعية</p>
            <p class="text-sm">اضغط على "جديد" لإضافة عنصر</p>
          </div>
        </div>
      </div>

      <!-- Right: Editor or Drawing Canvas -->
      <div class="editor-panel">
        <div v-if="!showDrawing" class="space-y-4">
          <h3 class="text-xl font-bold text-gray-800 flex items-center gap-2">
            <span>✏️ تحرير {{ selectedChild?.name || 'العنصر' }}</span>
          </h3>

          <div v-if="selectedChild" class="space-y-4 max-h-96 overflow-y-auto">
            <!-- Name -->
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-1">الاسم:</label>
              <input
                v-model="selectedChild.name"
                @change="updateChild"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
                placeholder="اسم العنصر"
              />
            </div>

            <!-- Color Preset -->
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">اللون:</label>
              <div class="grid grid-cols-5 gap-2">
                <button
                  v-for="color in colorPresets"
                  :key="color"
                  @click="selectedChild.color_config = { primary: color }; updateChild()"
                  class="w-full h-10 rounded border-2 transition-all hover:scale-110"
                  :style="{ backgroundColor: color, borderColor: selectedChild.color_config?.primary === color ? '#000' : '#ccc' }"
                />
              </div>
              <input
                v-model="selectedChild.color_config.primary"
                @change="updateChild"
                type="color"
                class="w-full h-10 mt-2 rounded cursor-pointer border border-gray-300"
              />
            </div>

            <!-- Shape -->
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-1">الشكل:</label>
              <select
                v-model="selectedChild.shape_config.type"
                @change="updateChild"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
              >
                <option value="rectangle">🟦 مستطيل</option>
                <option value="circle">⭕ دائرة</option>
                <option value="square">◼️ مربع</option>
              </select>
            </div>

            <!-- Border -->
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-1">الحد:</label>
              <input
                v-model.number="selectedChild.border_config.width"
                @change="updateChild"
                type="number"
                min="0"
                max="10"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                placeholder="عرض الحد"
              />
            </div>

            <!-- Drawing Button -->
            <button
              @click="showDrawing = true"
              class="w-full px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center justify-center gap-2 font-semibold"
            >
              🎨 افتح أداة الرسم
            </button>
          </div>

          <div v-else class="text-center text-gray-500 py-8">
            <p>اختر عنصراً من القائمة</p>
          </div>
        </div>

        <!-- Drawing Canvas -->
        <div v-else class="drawing-section">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-bold text-gray-800">🎨 أداة الرسم</h3>
            <button
              @click="showDrawing = false"
              class="px-3 py-1 bg-gray-400 text-white text-sm rounded hover:bg-gray-500"
            >
              ❌ إغلاق
            </button>
          </div>

          <DrawingCanvas
            v-if="selectedChild"
            :key="`drawing-${selectedChild.id}`"
            :childCustomizerId="selectedChild.id"
            :initialDrawingData="selectedChild.drawing_data"
            @save-drawing="saveDrawing"
          />
        </div>
      </div>
    </div>

    <!-- Add Child Modal -->
    <div v-if="showAddChildForm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-xl font-bold text-gray-800 mb-4">إضافة عنصر فرعي جديد</h3>

        <div class="space-y-4">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">الاسم:</label>
            <input
              v-model="newChild.name"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
              placeholder="أدخل اسم العنصر"
            />
          </div>

          <div class="flex gap-2">
            <button
              @click="createChild"
              class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold"
            >
              ✅ إضافة
            </button>
            <button
              @click="showAddChildForm = false"
              class="flex-1 px-4 py-2 bg-gray-400 text-white rounded-lg hover:bg-gray-500 font-semibold"
            >
              ❌ إلغاء
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import DrawingCanvas from './DrawingCanvas.vue';

export default {
  name: 'ChildCustomizerComponent',
  components: {
    DrawingCanvas
  },
  props: {
    currentConfiguration: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      configChildren: [],
      selectedChild: null,
      showAddChildForm: false,
      showDrawing: false,
      newChild: {
        name: ''
      },
      colorPresets: [
        '#FF6B6B',
        '#4ECDC4',
        '#45B7D1',
        '#FFA502',
        '#34495E',
        '#2ECC71',
        '#9B59B6',
        '#E74C3C'
      ]
    };
  },
  watch: {
    currentConfiguration(newConfig) {
      if (newConfig?.id) {
        this.loadChildrenForConfig(newConfig.id);
      }
    }
  },
  mounted() {
    if (this.currentConfiguration?.id) {
      this.loadChildrenForConfig(this.currentConfiguration.id);
    }
  },
  methods: {
    async loadChildrenForConfig(configId) {
      try {
        const response = await fetch(`/api/child-customizers?config_id=${configId}`);
        const data = await response.json();
        this.configChildren = data.data || [];
        this.selectedChild = null;
      } catch (error) {
        console.error('Error loading children:', error);
      }
    },
    selectChild(child) {
      this.selectedChild = JSON.parse(JSON.stringify(child));
      this.showDrawing = false;
    },
    async createChild() {
      try {
        const response = await fetch('/api/child-customizers', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            config_widget_override_id: this.currentConfiguration.id,
            name: this.newChild.name || 'New Child',
            shape_config: { type: 'rectangle' },
            color_config: { primary: '#4ECDC4' },
            border_config: { width: 1, style: 'solid', color: '#000000' },
            is_active: true
          })
        });

        const data = await response.json();
        if (data.data) {
          this.configChildren.push(data.data);
          this.newChild.name = '';
          this.showAddChildForm = false;
        }
      } catch (error) {
        console.error('Error creating child:', error);
      }
    },
    async updateChild() {
      try {
        await fetch(`/api/child-customizers/${this.selectedChild.id}`, {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(this.selectedChild)
        });
      } catch (error) {
        console.error('Error updating child:', error);
      }
    },
    async deleteChild(child) {
      if (confirm('هل أنت متأكد من حذف هذا العنصر؟')) {
        try {
          await fetch(`/api/child-customizers/${child.id}`, {
            method: 'DELETE'
          });
          this.configChildren = this.configChildren.filter(c => c.id !== child.id);
          if (this.selectedChild?.id === child.id) {
            this.selectedChild = null;
          }
        } catch (error) {
          console.error('Error deleting child:', error);
        }
      }
    },
    async saveDrawing({ drawingData, drawingJson }) {
      try {
        await fetch(`/api/child-customizers/${this.selectedChild.id}`, {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            ...this.selectedChild,
            drawing_data: drawingData,
            drawing_metadata: drawingJson
          })
        });

        this.selectedChild.drawing_data = drawingData;
        this.selectedChild.drawing_metadata = drawingJson;
        alert('✅ تم حفظ الرسم بنجاح!');
        this.showDrawing = false;
      } catch (error) {
        console.error('Error saving drawing:', error);
        alert('❌ خطأ في حفظ الرسم');
      }
    }
  }
};
</script>

<style scoped lang="postcss">
.child-customizer-wrapper {
  @apply p-6 bg-gray-50 rounded-lg;
}

.config-header {
  @apply border-l-4 border-blue-500;
}

.children-panel,
.editor-panel {
  @apply bg-white rounded-lg shadow-md p-4;
}

.child-item {
  @apply transition-all duration-200;
}

.drawing-section {
  @apply bg-white rounded-lg p-4 shadow-md;
}
</style>
