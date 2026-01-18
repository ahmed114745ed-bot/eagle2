<template>
  <div class="child-designer">
    <!-- Status Bar -->
    <div class="mb-4 p-3 bg-gradient-to-r from-blue-50 to-purple-50 border border-blue-200 rounded-lg text-sm flex justify-between items-center">
      <div class="flex items-center gap-4">
        <span class="font-bold text-blue-800">🎨 المصمم المرئي</span>
        <span class="px-2 py-1 bg-blue-100 rounded text-blue-700">{{ localChildren.length }} أطفال</span>
        <span v-if="selectedChild" class="px-2 py-1 bg-green-100 rounded text-green-700">
          الطفل #{{ selectedChildIndex + 1 }} - {{ (selectedChild.assets || []).length }} أصول
        </span>
        <span v-if="isSaving" class="px-2 py-1 bg-yellow-100 rounded text-yellow-700 animate-pulse">
          ⏳ جاري الحفظ...
        </span>
        <span v-if="saveSuccess" class="px-2 py-1 bg-green-100 rounded text-green-700">
          ✅ تم الحفظ
        </span>
      </div>
      <div class="flex items-center gap-2">
        <button 
          @click="savePositionsToServer" 
          :disabled="isSaving"
          class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 transition-all flex items-center gap-2"
        >
          <span v-if="isSaving">⏳</span>
          <span v-else>💾</span>
          حفظ المواقع
        </button>
      </div>
    </div>

    <!-- Header -->
    <div class="flex justify-between items-center mb-4 p-4 bg-white rounded-lg shadow">
      <div class="flex items-center gap-4">
        <h2 class="text-xl font-bold text-gray-800">🎨 مصمم الأطفال التفاعلي</h2>
        <span class="text-sm bg-blue-100 text-blue-700 px-3 py-1 rounded-full">
          {{ selectedChild ? 'طفل #' + (selectedChildIndex + 1) : 'اختر طفلاً' }}
        </span>
      </div>
      <div class="flex items-center gap-2">
        <button @click="zoomIn" class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300">🔍+</button>
        <span class="text-sm text-gray-600">{{ Math.round(zoom * 100) }}%</span>
        <button @click="zoomOut" class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300">🔍-</button>
        <button @click="resetZoom" class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300">↺</button>
        <button @click="toggleGrid" class="px-3 py-1 rounded" :class="showGrid ? 'bg-blue-500 text-white' : 'bg-gray-200'">⊞</button>
      </div>
    </div>

    <div class="flex gap-4">
      <!-- Left Panel - Children List -->
      <div class="w-56 bg-white rounded-lg shadow p-4">
        <h3 class="font-semibold text-gray-700 mb-3">👶 الأطفال</h3>
        <div class="space-y-2 max-h-[500px] overflow-y-auto">
          <div
            v-for="(child, index) in localChildren"
            :key="child.theme_child_id || index"
            @click="selectChild(index)"
            class="p-3 rounded-lg cursor-pointer transition-all border-2"
            :class="selectedChildIndex === index ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-blue-300'"
          >
            <div class="flex justify-between items-center">
              <span class="font-medium text-sm">🧩 #{{ index + 1 }}</span>
              <span class="text-xs text-gray-400">{{ (child.assets || []).length }} 🖼️</span>
            </div>
            <div class="text-xs text-gray-500 mt-1">
              {{ child.width || 300 }} × {{ child.height || 200 }}
            </div>
          </div>
        </div>
      </div>

      <!-- Center - Main Canvas Area -->
      <div class="flex-1 bg-white rounded-lg shadow overflow-hidden">
        <div 
          ref="canvasContainer"
          class="relative overflow-auto"
          :style="{ height: '650px', backgroundColor: '#f3f4f6' }"
          @mousedown="onCanvasMouseDown"
          @mousemove="onCanvasMouseMove"
          @mouseup="onCanvasMouseUp"
          @mouseleave="onCanvasMouseUp"
        >
          <!-- Grid Background -->
          <div 
            v-if="showGrid"
            class="absolute inset-0 pointer-events-none"
            :style="{
              backgroundImage: 'linear-gradient(to right, #d1d5db 1px, transparent 1px), linear-gradient(to bottom, #d1d5db 1px, transparent 1px)',
              backgroundSize: (20 * zoom) + 'px ' + (20 * zoom) + 'px',
            }"
          ></div>

          <!-- Child Container (The main drawing area for the selected child) -->
          <div
            v-if="selectedChild"
            ref="childContainer"
            class="absolute border-3 border-dashed cursor-move"
            :class="isDraggingChild ? 'border-blue-600' : 'border-blue-400'"
            :style="getChildStyle()"
            @mousedown.stop="startDragChild($event)"
          >
            <!-- Child Background -->
            <div class="absolute inset-0 bg-gradient-to-br from-blue-50 to-blue-100 opacity-50"></div>
            
            <!-- Child Label -->
            <div class="absolute -top-8 left-0 flex items-center gap-2">
              <span class="text-xs bg-blue-600 text-white px-2 py-1 rounded shadow">
                🧩 طفل #{{ selectedChildIndex + 1 }}
              </span>
              <span class="text-xs bg-gray-600 text-white px-2 py-1 rounded">
                {{ selectedChild.width || 300 }} × {{ selectedChild.height || 200 }}
              </span>
            </div>

            <!-- Resize Handles for Child (All corners and edges) -->
            <div class="absolute -right-2 -bottom-2 w-5 h-5 bg-blue-600 rounded-full cursor-se-resize shadow-lg border-2 border-white"
              @mousedown.stop="startResizeChild($event, 'se')"></div>
            <div class="absolute -left-2 -bottom-2 w-5 h-5 bg-blue-600 rounded-full cursor-sw-resize shadow-lg border-2 border-white"
              @mousedown.stop="startResizeChild($event, 'sw')"></div>
            <div class="absolute -right-2 -top-2 w-5 h-5 bg-blue-600 rounded-full cursor-ne-resize shadow-lg border-2 border-white"
              @mousedown.stop="startResizeChild($event, 'ne')"></div>
            <div class="absolute -left-2 -top-2 w-5 h-5 bg-blue-600 rounded-full cursor-nw-resize shadow-lg border-2 border-white"
              @mousedown.stop="startResizeChild($event, 'nw')"></div>

            <!-- Empty Assets Message -->
            <div v-if="!selectedChild.assets || selectedChild.assets.length === 0" 
              class="absolute inset-0 flex items-center justify-center text-gray-400">
              <div class="text-center">
                <span class="text-4xl">📦</span>
                <p class="mt-2 text-sm">لا توجد أصول في هذا الطفل</p>
                <p class="text-xs text-gray-300 mt-1">يتم جلب الأصول من الثيم المحدد</p>
              </div>
            </div>

            <!-- Assets inside Child (Draggable & Resizable) -->
            <div
              v-for="(asset, assetIndex) in selectedChild.assets || []"
              :key="asset.id || assetIndex"
              v-show="asset.is_visible !== false"
              class="absolute cursor-move transition-shadow"
              :class="[
                selectedAssetIndex === assetIndex ? 'ring-2 ring-orange-500 shadow-xl z-50' : 'hover:ring-2 hover:ring-orange-300',
                isDraggingAsset && selectedAssetIndex === assetIndex ? 'opacity-80' : ''
              ]"
              :style="getAssetStyle(asset)"
              @mousedown.stop="startDragAsset($event, assetIndex)"
            >
              <!-- Asset Visual -->
              <div class="w-full h-full rounded overflow-hidden border-2"
                :class="selectedAssetIndex === assetIndex ? 'border-orange-500' : 'border-gray-300'"
              >
                <!-- Asset Image Preview -->
                <img 
                  v-if="asset.file_url || asset.url" 
                  :src="asset.file_url || asset.url" 
                  class="w-full h-full object-contain bg-white"
                  draggable="false"
                />
                <!-- Placeholder if no image -->
                <div v-else class="w-full h-full flex items-center justify-center bg-gradient-to-br from-orange-100 to-orange-200">
                  <span class="text-xs text-orange-600 text-center font-medium p-1">
                    {{ asset.asset_key || asset.name || 'Asset' }}
                  </span>
                </div>
              </div>

              <!-- Asset Label (on hover or selected) -->
              <div 
                class="absolute -top-6 left-0 text-xs px-2 py-0.5 rounded shadow whitespace-nowrap transition-opacity"
                :class="selectedAssetIndex === assetIndex ? 'bg-orange-600 text-white opacity-100' : 'bg-gray-700 text-white opacity-0 group-hover:opacity-100'"
              >
                {{ asset.asset_key || 'Asset ' + (assetIndex + 1) }} ({{ asset.width || '?' }}×{{ asset.height || '?' }})
              </div>

              <!-- Resize Handle for Asset -->
              <div 
                class="absolute -right-1.5 -bottom-1.5 w-4 h-4 bg-orange-500 rounded-full cursor-se-resize shadow border border-white"
                @mousedown.stop="startResizeAsset($event, assetIndex)"
              ></div>
              
              <!-- Additional resize handles for selected asset -->
              <template v-if="selectedAssetIndex === assetIndex">
                <div class="absolute -left-1.5 -bottom-1.5 w-4 h-4 bg-orange-500 rounded-full cursor-sw-resize shadow border border-white"
                  @mousedown.stop="startResizeAsset($event, assetIndex, 'sw')"></div>
                <div class="absolute -right-1.5 -top-1.5 w-4 h-4 bg-orange-500 rounded-full cursor-ne-resize shadow border border-white"
                  @mousedown.stop="startResizeAsset($event, assetIndex, 'ne')"></div>
                <div class="absolute -left-1.5 -top-1.5 w-4 h-4 bg-orange-500 rounded-full cursor-nw-resize shadow border border-white"
                  @mousedown.stop="startResizeAsset($event, assetIndex, 'nw')"></div>
              </template>
            </div>
          </div>

          <!-- Empty State -->
          <div v-else class="absolute inset-0 flex items-center justify-center text-gray-400">
            <div class="text-center">
              <span class="text-7xl">👆</span>
              <p class="mt-4 text-xl font-medium">اختر طفلاً من القائمة</p>
              <p class="text-sm mt-2">للبدء في تصميم الأصول داخله</p>
            </div>
          </div>

          <!-- Coordinates Display (shows current position while dragging) -->
          <div v-if="isDragging || isResizing" 
            class="fixed bottom-4 left-1/2 transform -translate-x-1/2 bg-black bg-opacity-80 text-white px-4 py-2 rounded-lg text-sm shadow-lg z-50">
            <span v-if="isDragging">📍 X: {{ currentDragX }} | Y: {{ currentDragY }}</span>
            <span v-if="isResizing">📐 W: {{ currentResizeW }} | H: {{ currentResizeH }}</span>
          </div>
        </div>
      </div>

      <!-- Right Panel - Quick Info (Minimal) -->
      <div class="w-64 bg-white rounded-lg shadow p-4">
        <h3 class="font-semibold text-gray-700 mb-3">📋 معلومات سريعة</h3>
        
        <div v-if="selectedChild" class="space-y-4">
          <!-- Child Info -->
          <div class="bg-blue-50 rounded-lg p-3">
            <h4 class="text-sm font-semibold text-blue-700 mb-2">🧩 الطفل المحدد</h4>
            <div class="text-xs text-gray-600 space-y-1">
              <p><strong>الحجم:</strong> {{ selectedChild.width || 300 }} × {{ selectedChild.height || 200 }}</p>
              <p><strong>الموقع:</strong> X: {{ selectedChild.x || 0 }}, Y: {{ selectedChild.y || 0 }}</p>
              <p><strong>الأصول:</strong> {{ (selectedChild.assets || []).length }} عنصر</p>
            </div>
          </div>

          <!-- Selected Asset Info -->
          <div v-if="selectedAsset" class="bg-orange-50 rounded-lg p-3">
            <h4 class="text-sm font-semibold text-orange-700 mb-2">🖼️ Asset محدد</h4>
            <div class="text-xs text-gray-600 space-y-1">
              <p><strong>الاسم:</strong> {{ selectedAsset.asset_key || selectedAsset.name || 'غير معروف' }}</p>
              <p><strong>الحجم:</strong> {{ selectedAsset.width || '?' }} × {{ selectedAsset.height || '?' }}</p>
              <p><strong>الموقع:</strong> X: {{ selectedAsset.x || 0 }}, Y: {{ selectedAsset.y || 0 }}</p>
            </div>
            <div class="mt-2 flex gap-2">
              <button @click="toggleAssetVisibility" class="text-xs px-2 py-1 bg-orange-200 rounded hover:bg-orange-300">
                {{ selectedAsset.is_visible !== false ? '👁️ إخفاء' : '👁️‍🗨️ إظهار' }}
              </button>
              <button @click="bringAssetToFront" class="text-xs px-2 py-1 bg-orange-200 rounded hover:bg-orange-300">
                ⬆️ للأمام
              </button>
            </div>
          </div>

          <!-- Assets List -->
          <div class="border-t pt-3">
            <h4 class="text-sm font-semibold text-gray-700 mb-2">🖼️ الأصول</h4>
            <div class="space-y-1 max-h-[200px] overflow-y-auto">
              <div
                v-for="(asset, idx) in selectedChild.assets || []"
                :key="asset.id || idx"
                @click="selectAsset(idx)"
                class="p-2 rounded cursor-pointer text-xs flex justify-between items-center transition-all"
                :class="[
                  selectedAssetIndex === idx ? 'bg-orange-100 ring-1 ring-orange-400' : 'hover:bg-gray-100',
                  asset.is_visible === false ? 'opacity-50' : ''
                ]"
              >
                <span class="truncate flex-1">{{ asset.asset_key || asset.name || 'Asset ' + (idx + 1) }}</span>
                <span class="text-gray-400 ml-2">{{ asset.width || '?' }}×{{ asset.height || '?' }}</span>
              </div>
              <div v-if="!(selectedChild.assets || []).length" class="text-xs text-gray-400 text-center py-4">
                لا توجد أصول
              </div>
            </div>
          </div>
        </div>

        <div v-else class="text-center text-gray-400 py-8">
          <span class="text-4xl">📋</span>
          <p class="mt-2 text-sm">اختر طفلاً</p>
        </div>
      </div>
    </div>

    <!-- Bottom Toolbar -->
    <div class="mt-4 p-3 bg-gradient-to-r from-blue-50 to-purple-50 rounded-lg flex justify-between items-center">
      <div class="text-sm text-gray-600">
        💡 <strong>تلميحات:</strong> اسحب العناصر لتحريكها • استخدم المقابض الدائرية لتغيير الحجم • اضغط على Asset لتحديده
      </div>
      <div class="flex gap-2">
        <button @click="resetChildPosition" class="text-xs px-3 py-1 bg-gray-200 rounded hover:bg-gray-300">
          ↺ إعادة ضبط الموقع
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { designerApi } from '../services/api';

export default {
  name: 'ChildDesigner',
  props: {
    children: {
      type: Array,
      default: () => []
    },
    themes: {
      type: Array,
      default: () => []
    },
    selectedThemeId: {
      type: [Number, String],
      default: null
    }
  },
  emits: ['update:children'],
  data() {
    return {
      localChildren: [],
      selectedChildIndex: null,
      selectedAssetIndex: null,
      zoom: 1,
      showGrid: true,
      isDragging: false,
      isDraggingChild: false,
      isDraggingAsset: false,
      isResizing: false,
      isSaving: false,
      saveSuccess: false,
      dragType: null, // 'child' | 'asset'
      resizeType: null, // 'child' | 'asset'
      resizeDirection: 'se', // 'se' | 'sw' | 'ne' | 'nw'
      dragStartX: 0,
      dragStartY: 0,
      dragStartElementX: 0,
      dragStartElementY: 0,
      resizeStartWidth: 0,
      resizeStartHeight: 0,
      resizeStartX: 0,
      resizeStartY: 0,
      // For displaying current values while dragging
      currentDragX: 0,
      currentDragY: 0,
      currentResizeW: 0,
      currentResizeH: 0,
    };
  },
  computed: {
    // Get the selected theme from themes prop
    currentTheme() {
      if (!this.selectedThemeId || !this.themes || this.themes.length === 0) {
        return null;
      }
      return this.themes.find(t => parseInt(t.id) === parseInt(this.selectedThemeId));
    },
    selectedChild() {
      if (this.selectedChildIndex === null || !this.localChildren[this.selectedChildIndex]) {
        return null;
      }
      return this.localChildren[this.selectedChildIndex];
    },
    selectedAsset() {
      if (!this.selectedChild || this.selectedAssetIndex === null) {
        return null;
      }
      return (this.selectedChild.assets || [])[this.selectedAssetIndex] || null;
    }
  },
  watch: {
    children: {
      handler(newVal) {
        console.log('[ChildDesigner] children prop updated:', newVal?.length || 0);
        this.buildLocalChildren();
      },
      immediate: true,
      deep: true
    },
    themes: {
      handler(newVal) {
        console.log('[ChildDesigner] themes prop updated:', newVal?.length || 0, 'themes');
        this.buildLocalChildren();
      },
      immediate: true
    },
    selectedThemeId: {
      handler(newVal) {
        console.log('[ChildDesigner] selectedThemeId prop updated:', newVal);
        this.buildLocalChildren();
      },
      immediate: true
    },
    localChildren: {
      handler(newVal) {
        if (newVal && newVal.length > 0) {
          newVal.forEach((child, idx) => {
            console.log(`[ChildDesigner] LocalChild ${idx}:`, child.theme_child_id, 'assets:', child.assets?.length || 0);
          });
        }
      },
      deep: true
    }
  },
  mounted() {
    // Bind global mouse events for smoother dragging
    document.addEventListener('mousemove', this.handleGlobalMouseMove);
    document.addEventListener('mouseup', this.handleGlobalMouseUp);
  },
  beforeUnmount() {
    // Clean up global event listeners
    document.removeEventListener('mousemove', this.handleGlobalMouseMove);
    document.removeEventListener('mouseup', this.handleGlobalMouseUp);
  },
  methods: {
    handleGlobalMouseMove(e) {
      // Use global mousemove for smoother dragging
      this.onCanvasMouseMove(e);
    },
    handleGlobalMouseUp(e) {
      // Use global mouseup to ensure drag ends properly
      this.onCanvasMouseUp(e);
    },
    // Build localChildren from props with merged assets
    buildLocalChildren() {
      const baseChildren = this.children || [];
      const themeChildren = this.currentTheme?.children || [];
      
      console.log('🔄 [ChildDesigner] buildLocalChildren. Base:', baseChildren.length, 'Theme children:', themeChildren.length);
      
      this.localChildren = baseChildren.map(child => {
        const themeChild = themeChildren.find(tc => tc.id === child.theme_child_id);
        let assets = child.assets ? [...child.assets] : [];
        
        // If no assets in child but theme has them, use theme assets
        if (assets.length === 0 && themeChild && themeChild.assets && themeChild.assets.length > 0) {
          console.log('📦 [ChildDesigner] Merging assets from theme for child:', child.theme_child_id, 'found:', themeChild.assets.length);
          assets = themeChild.assets.map(asset => ({
            id: asset.id,
            asset_key: asset.asset_key || asset.name || asset.asset_label,
            name: asset.name || asset.asset_label || asset.asset_key,
            file_url: asset.file_url || asset.url || asset.default_url,
            url: asset.file_url || asset.url || asset.default_url,
            width: asset.width || 80,
            height: asset.height || 80,
            x: asset.x || 0,
            y: asset.y || 0,
            opacity: asset.opacity ?? 1,
            z_index: asset.z_index || 0,
            is_visible: asset.is_visible ?? true,
            scale: asset.scale ?? 1,
          }));
        }
        
        return {
          ...child,
          width: child.width || 300,
          height: child.height || 200,
          x: child.x ?? 50,
          y: child.y ?? 50,
          assets: assets
        };
      });
      
      console.log('✅ [ChildDesigner] localChildren built:', this.localChildren.length);
    },
    selectChild(index) {
      this.selectedChildIndex = index;
      this.selectedAssetIndex = null;
      const child = this.localChildren[index];
      console.log('👆 [ChildDesigner] selectChild:', index, child);
      console.log('👆 [ChildDesigner] child.assets:', child?.assets);
    },
    selectAsset(index) {
      this.selectedAssetIndex = index;
      // Ensure asset has default values
      const asset = this.selectedChild.assets[index];
      if (!asset.width) asset.width = 80;
      if (!asset.height) asset.height = 80;
      if (asset.x === undefined) asset.x = 10;
      if (asset.y === undefined) asset.y = 10;
      if (asset.opacity === undefined) asset.opacity = 1;
      if (asset.z_index === undefined) asset.z_index = 0;
      if (asset.is_visible === undefined) asset.is_visible = true;
    },
    getChildStyle() {
      if (!this.selectedChild) return {};
      return {
        left: (this.selectedChild.x || 50) * this.zoom + 'px',
        top: (this.selectedChild.y || 50) * this.zoom + 'px',
        width: (this.selectedChild.width || 300) * this.zoom + 'px',
        height: (this.selectedChild.height || 200) * this.zoom + 'px',
        cursor: this.isDraggingChild ? 'grabbing' : 'grab',
      };
    },
    getAssetStyle(asset) {
      return {
        left: (asset.x || 0) * this.zoom + 'px',
        top: (asset.y || 0) * this.zoom + 'px',
        width: (asset.width || 80) * this.zoom + 'px',
        height: (asset.height || 80) * this.zoom + 'px',
        opacity: asset.opacity ?? 1,
        zIndex: asset.z_index || 0,
      };
    },
    zoomIn() {
      this.zoom = Math.min(this.zoom + 0.1, 2);
    },
    zoomOut() {
      this.zoom = Math.max(this.zoom - 0.1, 0.3);
    },
    resetZoom() {
      this.zoom = 1;
    },
    toggleGrid() {
      this.showGrid = !this.showGrid;
    },
    toggleAssetVisibility() {
      if (this.selectedAsset && this.selectedChildIndex !== null) {
        this.selectedAsset.is_visible = this.selectedAsset.is_visible === false ? true : false;
        this.emitUpdatedChildren();
      }
    },
    bringAssetToFront() {
      if (this.selectedAsset && this.selectedChild) {
        // Find max z-index
        let maxZ = 0;
        (this.selectedChild.assets || []).forEach(a => {
          if ((a.z_index || 0) > maxZ) maxZ = a.z_index || 0;
        });
        this.selectedAsset.z_index = maxZ + 1;
        this.emitUpdatedChildren();
      }
    },
    resetChildPosition() {
      if (this.selectedChild && this.localChildren[this.selectedChildIndex]) {
        this.localChildren[this.selectedChildIndex].x = 50;
        this.localChildren[this.selectedChildIndex].y = 50;
        this.emitUpdatedChildren();
      }
    },
    // Save positions to server
    async savePositionsToServer() {
      this.isSaving = true;
      this.saveSuccess = false;
      
      try {
        // Prepare data for batch update
        const childrenData = [];
        const assetsData = [];
        
        this.localChildren.forEach(child => {
          // Only include children with valid theme_child_id
          if (child.theme_child_id) {
            childrenData.push({
              id: child.theme_child_id,
              width: child.width || 300,
              height: child.height || 200,
              x: child.x || 0,
              y: child.y || 0,
            });
          }
          
          // Include assets with valid ids
          (child.assets || []).forEach(asset => {
            if (asset.id) {
              assetsData.push({
                id: asset.id,
                width: asset.width || 80,
                height: asset.height || 80,
                x: asset.x || 0,
                y: asset.y || 0,
              });
            }
          });
        });
        
        console.log('💾 [ChildDesigner] Saving positions:', { children: childrenData, assets: assetsData });
        
        if (childrenData.length > 0 || assetsData.length > 0) {
          const response = await designerApi.batchUpdatePositions({
            children: childrenData,
            assets: assetsData,
          });
          console.log('✅ [ChildDesigner] Save response:', response.data);
        }
        
        this.saveSuccess = true;
        setTimeout(() => {
          this.saveSuccess = false;
        }, 3000);
        
      } catch (error) {
        console.error('❌ [ChildDesigner] Failed to save positions:', error);
        alert('فشل حفظ المواقع: ' + (error.response?.data?.message || error.message));
      } finally {
        this.isSaving = false;
      }
    },
    // Helper to emit updated children with merged assets
    emitUpdatedChildren() {
      // Emit localChildren which contains all the updates
      const updatedChildren = this.localChildren.map(child => ({
        ...child,
        assets: (child.assets || []).map(asset => ({ ...asset }))
      }));
      console.log('📤 [ChildDesigner] Emitting updated children:', updatedChildren);
      this.$emit('update:children', updatedChildren);
    },
    // Drag & Resize Logic
    startDragChild(e) {
      console.log('🧩 [ChildDesigner] startDragChild called');
      e.preventDefault(); // Prevent text selection
      this.isDragging = true;
      this.isDraggingChild = true;
      this.dragType = 'child';
      this.dragStartX = e.clientX;
      this.dragStartY = e.clientY;
      this.dragStartElementX = this.selectedChild.x || 50;
      this.dragStartElementY = this.selectedChild.y || 50;
      this.currentDragX = this.dragStartElementX;
      this.currentDragY = this.dragStartElementY;
      this.selectedAssetIndex = null;
    },
    startDragAsset(e, assetIndex) {
      console.log('🎯 [ChildDesigner] startDragAsset called:', assetIndex);
      e.preventDefault(); // Prevent text selection
      this.selectAsset(assetIndex);
      this.isDragging = true;
      this.isDraggingAsset = true;
      this.dragType = 'asset';
      this.dragStartX = e.clientX;
      this.dragStartY = e.clientY;
      const asset = this.selectedChild.assets[assetIndex];
      console.log('🎯 [ChildDesigner] Asset to drag:', asset);
      this.dragStartElementX = asset.x || 0;
      this.dragStartElementY = asset.y || 0;
      this.currentDragX = this.dragStartElementX;
      this.currentDragY = this.dragStartElementY;
      console.log('🎯 [ChildDesigner] Drag started at:', this.dragStartX, this.dragStartY, 'Element pos:', this.dragStartElementX, this.dragStartElementY);
    },
    startResizeChild(e, direction = 'se') {
      this.isResizing = true;
      this.resizeType = 'child';
      this.resizeDirection = direction;
      this.dragStartX = e.clientX;
      this.dragStartY = e.clientY;
      this.resizeStartWidth = this.selectedChild.width || 300;
      this.resizeStartHeight = this.selectedChild.height || 200;
      this.resizeStartX = this.selectedChild.x || 50;
      this.resizeStartY = this.selectedChild.y || 50;
      this.currentResizeW = this.resizeStartWidth;
      this.currentResizeH = this.resizeStartHeight;
    },
    startResizeAsset(e, assetIndex, direction = 'se') {
      console.log('📐 [ChildDesigner] startResizeAsset called:', assetIndex, direction);
      e.preventDefault(); // Prevent text selection
      this.selectAsset(assetIndex);
      this.isResizing = true;
      this.resizeType = 'asset';
      this.resizeDirection = direction;
      this.dragStartX = e.clientX;
      this.dragStartY = e.clientY;
      const asset = this.selectedChild.assets[assetIndex];
      this.resizeStartWidth = asset.width || 80;
      this.resizeStartHeight = asset.height || 80;
      this.resizeStartX = asset.x || 0;
      this.resizeStartY = asset.y || 0;
      this.currentResizeW = this.resizeStartWidth;
      this.currentResizeH = this.resizeStartHeight;
    },
    onCanvasMouseDown(e) {
      // Deselect asset if clicking on empty area
      if (e.target === this.$refs.canvasContainer) {
        this.selectedAssetIndex = null;
      }
    },
    onCanvasMouseMove(e) {
      if (this.isDragging) {
        e.preventDefault(); // Prevent text selection during drag
        const deltaX = (e.clientX - this.dragStartX) / this.zoom;
        const deltaY = (e.clientY - this.dragStartY) / this.zoom;

        if (this.dragType === 'child' && this.selectedChild) {
          const newX = Math.max(0, Math.round(this.dragStartElementX + deltaX));
          const newY = Math.max(0, Math.round(this.dragStartElementY + deltaY));
          this.selectedChild.x = newX;
          this.selectedChild.y = newY;
          this.currentDragX = newX;
          this.currentDragY = newY;
        } else if (this.dragType === 'asset' && this.selectedAsset) {
          const newX = Math.max(0, Math.round(this.dragStartElementX + deltaX));
          const newY = Math.max(0, Math.round(this.dragStartElementY + deltaY));
          console.log('🚀 [ChildDesigner] Moving asset to:', newX, newY);
          this.selectedAsset.x = newX;
          this.selectedAsset.y = newY;
          this.currentDragX = newX;
          this.currentDragY = newY;
        }
      }

      if (this.isResizing) {
        const deltaX = (e.clientX - this.dragStartX) / this.zoom;
        const deltaY = (e.clientY - this.dragStartY) / this.zoom;
        
        const target = this.resizeType === 'child' ? this.selectedChild : this.selectedAsset;
        const minSize = this.resizeType === 'child' ? 50 : 10;
        
        if (target) {
          let newWidth = this.resizeStartWidth;
          let newHeight = this.resizeStartHeight;
          let newX = this.resizeStartX;
          let newY = this.resizeStartY;
          
          // Handle different resize directions
          switch (this.resizeDirection) {
            case 'se': // Bottom-right
              newWidth = Math.max(minSize, Math.round(this.resizeStartWidth + deltaX));
              newHeight = Math.max(minSize, Math.round(this.resizeStartHeight + deltaY));
              break;
            case 'sw': // Bottom-left
              newWidth = Math.max(minSize, Math.round(this.resizeStartWidth - deltaX));
              newHeight = Math.max(minSize, Math.round(this.resizeStartHeight + deltaY));
              newX = Math.max(0, Math.round(this.resizeStartX + deltaX));
              break;
            case 'ne': // Top-right
              newWidth = Math.max(minSize, Math.round(this.resizeStartWidth + deltaX));
              newHeight = Math.max(minSize, Math.round(this.resizeStartHeight - deltaY));
              newY = Math.max(0, Math.round(this.resizeStartY + deltaY));
              break;
            case 'nw': // Top-left
              newWidth = Math.max(minSize, Math.round(this.resizeStartWidth - deltaX));
              newHeight = Math.max(minSize, Math.round(this.resizeStartHeight - deltaY));
              newX = Math.max(0, Math.round(this.resizeStartX + deltaX));
              newY = Math.max(0, Math.round(this.resizeStartY + deltaY));
              break;
          }
          
          target.width = newWidth;
          target.height = newHeight;
          if (this.resizeDirection !== 'se') {
            target.x = newX;
            target.y = newY;
          }
          
          this.currentResizeW = newWidth;
          this.currentResizeH = newHeight;
        }
      }
    },
    onCanvasMouseUp() {
      if (this.isDragging || this.isResizing) {
        console.log('✅ [ChildDesigner] Drag/Resize ended, emitting update');
        this.emitUpdatedChildren();
      }
      this.isDragging = false;
      this.isDraggingChild = false;
      this.isDraggingAsset = false;
      this.isResizing = false;
      this.dragType = null;
      this.resizeType = null;
    }
  }
};
</script>

<style scoped>
.child-designer {
  user-select: none;
}

.border-3 {
  border-width: 3px;
}
</style>
