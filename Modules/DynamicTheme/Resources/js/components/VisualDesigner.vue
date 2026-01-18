<template>
  <div v-if="isOpen" class="visual-designer-overlay" @click.self="close">
    <div class="visual-designer-container">
      <!-- Header -->
      <div class="designer-header">
        <div class="flex items-center gap-4">
          <div class="header-logo">
            <span class="logo-icon">🎨</span>
          </div>
          <div>
            <h1 class="text-xl font-bold text-white">المصمم المرئي المتقدم</h1>
            <div class="flex items-center gap-2 mt-1">
              <span class="header-badge blue">{{ widgetName }}</span>
              <span v-if="selectedTheme" class="header-badge purple">{{ selectedTheme.theme_name }}</span>
            </div>
          </div>
        </div>
        
        <!-- Toolbar -->
        <div class="header-toolbar">
          <div class="toolbar-group">
            <button @click="undo" :disabled="!canUndo" class="toolbar-btn" title="تراجع (Ctrl+Z)">
              <span>↩️</span>
            </button>
            <button @click="redo" :disabled="!canRedo" class="toolbar-btn" title="إعادة (Ctrl+Y)">
              <span>↪️</span>
            </button>
          </div>
          
          <div class="toolbar-divider"></div>
          
          <div class="toolbar-group">
            <button @click="duplicateSelected" :disabled="!selectedElement" class="toolbar-btn" title="نسخ (Ctrl+D)">
              <span>📋</span>
            </button>
            <button @click="deleteSelected" :disabled="!selectedElement" class="toolbar-btn" title="حذف (Delete)">
              <span>🗑️</span>
            </button>
          </div>
          
          <div class="toolbar-divider"></div>
          
          <div class="toolbar-group">
            <button @click="alignSelected('left')" :disabled="!selectedElement" class="toolbar-btn" title="محاذاة لليسار">
              <span>⬅️</span>
            </button>
            <button @click="alignSelected('center-h')" :disabled="!selectedElement" class="toolbar-btn" title="توسيط أفقي">
              <span>↔️</span>
            </button>
            <button @click="alignSelected('right')" :disabled="!selectedElement" class="toolbar-btn" title="محاذاة لليمين">
              <span>➡️</span>
            </button>
            <button @click="alignSelected('top')" :disabled="!selectedElement" class="toolbar-btn" title="محاذاة للأعلى">
              <span>⬆️</span>
            </button>
            <button @click="alignSelected('center-v')" :disabled="!selectedElement" class="toolbar-btn" title="توسيط عمودي">
              <span>↕️</span>
            </button>
            <button @click="alignSelected('bottom')" :disabled="!selectedElement" class="toolbar-btn" title="محاذاة للأسفل">
              <span>⬇️</span>
            </button>
          </div>
          
          <div class="toolbar-divider"></div>
          
          <div class="toolbar-group">
            <button @click="bringToFront" :disabled="!selectedElement" class="toolbar-btn" title="للمقدمة">
              <span>🔝</span>
            </button>
            <button @click="sendToBack" :disabled="!selectedElement" class="toolbar-btn" title="للخلف">
              <span>🔙</span>
            </button>
          </div>
        </div>
        
        <div class="flex items-center gap-3">
          <button @click="saveDesign" :disabled="isSaving" class="save-btn">
            <span v-if="isSaving" class="animate-spin">⏳</span>
            <span v-else>💾</span>
            {{ isSaving ? 'جاري الحفظ...' : 'حفظ التصميم' }}
          </button>
          <button @click="close" class="close-btn">✕</button>
        </div>
      </div>

      <div class="designer-body">
        <!-- Left Panel - Theme Children & Assets Library -->
        <div class="left-panel">
          <!-- Theme Selector -->
          <div class="panel-section">
            <h3 class="section-title">🎭 الثيمات</h3>
            <select v-model="selectedThemeId" @change="onThemeChange" class="theme-select">
              <option v-for="theme in themes" :key="theme.id" :value="theme.id">
                {{ theme.theme_name }}
              </option>
            </select>
          </div>

          <!-- Children List -->
          <div class="panel-section flex-1">
            <h3 class="section-title">👶 الأطفال (اسحب للموبايل)</h3>
            <div class="children-list">
              <div
                v-for="child in themeChildren"
                :key="child.id"
                class="child-item"
                :class="{ 'active': selectedChildId === child.id, 'placed': isChildPlaced(child.id) }"
                draggable="true"
                @dragstart="onChildDragStart($event, child)"
                @click="selectChild(child)"
              >
                <div class="child-icon">🧩</div>
                <div class="child-info">
                  <span class="child-name">{{ child.label || child.child_key }}</span>
                  <span class="child-type">{{ child.child_type }}</span>
                </div>
                <span class="child-assets-count">{{ (child.assets || []).length }} 🖼️</span>
              </div>
              <div v-if="!themeChildren.length" class="empty-message">
                لا يوجد أطفال في هذا الثيم
              </div>
            </div>
          </div>

          <!-- Assets Library (for selected child) -->
          <div v-if="selectedChild" class="panel-section">
            <h3 class="section-title">🖼️ أصول "{{ selectedChild.label || selectedChild.child_key }}"</h3>
            <div class="assets-library">
              <div
                v-for="asset in selectedChild.assets || []"
                :key="asset.id"
                class="asset-item"
                :class="{ 'active': selectedAssetId === asset.id }"
                draggable="true"
                @dragstart="onAssetDragStart($event, asset)"
                @click="selectAsset(asset)"
              >
                <div class="asset-preview">
                  <img v-if="asset.file_url || asset.url" :src="asset.file_url || asset.url" />
                  <span v-else class="asset-placeholder">📦</span>
                </div>
                <span class="asset-name">{{ asset.name || asset.asset_key }}</span>
              </div>
              <div v-if="!(selectedChild.assets || []).length" class="empty-message">
                لا يوجد أصول
              </div>
            </div>
          </div>
        </div>

        <!-- Center - Mobile Preview -->
        <div class="center-panel">
          <div class="mobile-frame">
            <div class="mobile-notch"></div>
            <div class="mobile-status-bar">
              <span>9:41</span>
              <div class="status-icons">
                <span>📶</span>
                <span>🔋</span>
              </div>
            </div>
            <div 
              ref="mobileScreen"
              class="mobile-screen"
              @dragover.prevent="onDragOver"
              @drop="onDrop"
              @click="onScreenClick"
            >
              <!-- Grid -->
              <div v-if="showGrid" class="mobile-grid"></div>
              
              <!-- Placed Children -->
              <div
                v-for="child in placedChildren"
                :key="child.id"
                class="placed-child"
                :class="{ 'selected': selectedChildId === child.theme_child_id }"
                :style="getChildStyle(child)"
                @mousedown.stop="startDragPlacedChild($event, child)"
                @click.stop="selectPlacedChild(child)"
              >
                <div class="child-label">{{ child.name }}</div>
                
                <!-- Assets inside child -->
                <div
                  v-for="asset in child.assets || []"
                  :key="asset.id"
                  class="placed-asset"
                  :class="{ 'selected': selectedAssetId === asset.id }"
                  :style="getAssetStyle(asset)"
                  @mousedown.stop="startDragPlacedAsset($event, child, asset)"
                  @click.stop="selectPlacedAsset(asset)"
                >
                  <img v-if="asset.file_url || asset.url" :src="asset.file_url || asset.url" draggable="false" />
                  <span v-else class="asset-placeholder-small">{{ asset.name }}</span>
                  
                  <!-- Resize handles -->
                  <div v-if="selectedAssetId === asset.id" class="resize-handles">
                    <div class="resize-handle se" @mousedown.stop="startResize($event, 'asset', asset, 'se')"></div>
                    <div class="resize-handle sw" @mousedown.stop="startResize($event, 'asset', asset, 'sw')"></div>
                    <div class="resize-handle ne" @mousedown.stop="startResize($event, 'asset', asset, 'ne')"></div>
                    <div class="resize-handle nw" @mousedown.stop="startResize($event, 'asset', asset, 'nw')"></div>
                  </div>
                </div>
                
                <!-- Child resize handles -->
                <div v-if="selectedChildId === child.theme_child_id" class="resize-handles">
                  <div class="resize-handle se" @mousedown.stop="startResize($event, 'child', child, 'se')"></div>
                  <div class="resize-handle sw" @mousedown.stop="startResize($event, 'child', child, 'sw')"></div>
                  <div class="resize-handle ne" @mousedown.stop="startResize($event, 'child', child, 'ne')"></div>
                  <div class="resize-handle nw" @mousedown.stop="startResize($event, 'child', child, 'nw')"></div>
                </div>
              </div>
              
              <!-- Drop indicator -->
              <div v-if="isDraggingOver" class="drop-indicator">
                أفلت هنا
              </div>
            </div>
            <div class="mobile-home-bar"></div>
          </div>
          
          <!-- Zoom & Grid Controls -->
          <div class="preview-controls">
            <button @click="zoomOut" class="control-btn">➖</button>
            <span class="zoom-level">{{ Math.round(zoom * 100) }}%</span>
            <button @click="zoomIn" class="control-btn">➕</button>
            <button @click="toggleGrid" class="control-btn" :class="{ 'active': showGrid }">⊞</button>
            <button @click="resetView" class="control-btn">↺</button>
          </div>
        </div>

        <!-- Right Panel - Properties -->
        <div class="right-panel">
          <!-- Selected Element Properties -->
          <div v-if="selectedElement" class="panel-section properties-section">
            <h3 class="section-title">⚙️ الخصائص</h3>
            <div class="properties-form scrollable">
              <div class="property-group">
                <label>الاسم</label>
                <input type="text" v-model="selectedElement.name" readonly class="property-input readonly" />
              </div>
              
              <div class="property-row">
                <div class="property-group half">
                  <label>العرض (W)</label>
                  <input type="number" v-model.number="selectedElement.width" @change="onPropertyChange" class="property-input" />
                </div>
                <div class="property-group half">
                  <label>الارتفاع (H)</label>
                  <input type="number" v-model.number="selectedElement.height" @change="onPropertyChange" class="property-input" />
                </div>
              </div>
              
              <div class="property-row">
                <div class="property-group half">
                  <label>X</label>
                  <input type="number" v-model.number="selectedElement.x" @change="onPropertyChange" class="property-input" />
                </div>
                <div class="property-group half">
                  <label>Y</label>
                  <input type="number" v-model.number="selectedElement.y" @change="onPropertyChange" class="property-input" />
                </div>
              </div>
              
              <!-- Rotation & Scale -->
              <div class="property-row">
                <div class="property-group half">
                  <label>الدوران (°)</label>
                  <input type="number" v-model.number="selectedElement.rotation" min="-360" max="360" @change="onPropertyChange" class="property-input" />
                </div>
                <div class="property-group half">
                  <label>التكبير</label>
                  <input type="number" v-model.number="selectedElement.scale" min="0.1" max="5" step="0.1" @change="onPropertyChange" class="property-input" />
                </div>
              </div>
              
              <!-- Opacity Slider -->
              <div class="property-group">
                <label>الشفافية: {{ (selectedElement.opacity || 1).toFixed(1) }}</label>
                <div class="slider-container">
                  <input type="range" min="0" max="1" step="0.1" v-model.number="selectedElement.opacity" @input="onPropertyChange" class="slider-input" />
                </div>
              </div>
              
              <!-- Z-Index -->
              <div class="property-group">
                <label>الترتيب (Z-Index)</label>
                <input type="number" v-model.number="selectedElement.z_index" @change="onPropertyChange" class="property-input" />
              </div>
              
              <!-- Background Color (for children) -->
              <div v-if="selectedElementType === 'child'" class="property-group">
                <label>لون الخلفية</label>
                <div class="color-picker-row">
                  <input type="color" v-model="selectedElement.background_color" @input="onPropertyChange" class="color-picker" />
                  <input type="text" v-model="selectedElement.background_color" @change="onPropertyChange" class="property-input flex-1" placeholder="transparent" />
                </div>
              </div>
              
              <!-- Border -->
              <div class="property-group">
                <label>الحدود</label>
                <div class="border-controls">
                  <input type="number" v-model.number="selectedElement.border_width" min="0" max="20" @change="onPropertyChange" class="property-input small" placeholder="0" title="العرض" />
                  <select v-model="selectedElement.border_style" @change="onPropertyChange" class="property-input">
                    <option value="solid">صلب</option>
                    <option value="dashed">متقطع</option>
                    <option value="dotted">منقط</option>
                    <option value="double">مزدوج</option>
                    <option value="groove">محفور</option>
                    <option value="ridge">بارز</option>
                    <option value="none">بدون</option>
                  </select>
                  <input type="color" v-model="selectedElement.border_color" @input="onPropertyChange" class="color-picker" title="اللون" />
                </div>
              </div>
              
              <!-- Border Radius - 4 Corners -->
              <div class="property-group">
                <label>🔘 حواف دائرية (الزوايا الأربع)</label>
                <div class="corners-grid">
                  <div class="corner-input">
                    <span class="corner-label">↖</span>
                    <input type="number" v-model.number="selectedElement.border_radius_tl" min="0" max="200" @change="onPropertyChange" class="property-input" placeholder="0" title="أعلى يسار" />
                  </div>
                  <div class="corner-input">
                    <span class="corner-label">↗</span>
                    <input type="number" v-model.number="selectedElement.border_radius_tr" min="0" max="200" @change="onPropertyChange" class="property-input" placeholder="0" title="أعلى يمين" />
                  </div>
                  <div class="corner-input">
                    <span class="corner-label">↙</span>
                    <input type="number" v-model.number="selectedElement.border_radius_bl" min="0" max="200" @change="onPropertyChange" class="property-input" placeholder="0" title="أسفل يسار" />
                  </div>
                  <div class="corner-input">
                    <span class="corner-label">↘</span>
                    <input type="number" v-model.number="selectedElement.border_radius_br" min="0" max="200" @change="onPropertyChange" class="property-input" placeholder="0" title="أسفل يمين" />
                  </div>
                </div>
                <div class="corner-sync">
                  <button @click="syncCorners" class="sync-btn" title="تطبيق نفس القيمة على جميع الزوايا">🔗 توحيد الزوايا</button>
                  <input type="number" v-model.number="cornerSyncValue" min="0" max="200" class="property-input small" placeholder="قيمة" />
                </div>
              </div>
              
              <!-- Visibility -->
              <div class="property-group">
                <label class="checkbox-label">
                  <input type="checkbox" v-model="selectedElement.is_visible" @change="onPropertyChange" />
                  مرئي
                </label>
              </div>
              
              <!-- Quick Actions -->
              <div class="quick-actions">
                <button @click="resetElementTransform" class="quick-action-btn">↺ إعادة تعيين</button>
                <button @click="fitToScreen" class="quick-action-btn">📐 ملء الشاشة</button>
              </div>
            </div>
          </div>
          
          <div v-else class="panel-section">
            <div class="empty-properties">
              <span class="empty-icon">👆</span>
              <p>اختر عنصراً لعرض خصائصه</p>
            </div>
          </div>

          <!-- Layer List -->
          <div class="panel-section flex-1">
            <h3 class="section-title">📚 الطبقات</h3>
            <div class="layers-list">
              <div
                v-for="child in placedChildren"
                :key="child.id"
                class="layer-group"
              >
                <div 
                  class="layer-item parent"
                  :class="{ 'selected': selectedChildId === child.theme_child_id }"
                  @click="selectPlacedChild(child)"
                >
                  <span class="layer-icon">🧩</span>
                  <span class="layer-name">{{ child.name }}</span>
                  <button @click.stop="removeChild(child)" class="layer-remove">🗑️</button>
                </div>
                <div 
                  v-for="asset in child.assets || []"
                  :key="asset.id"
                  class="layer-item child-layer"
                  :class="{ 'selected': selectedAssetId === asset.id }"
                  @click="selectPlacedAsset(asset)"
                >
                  <span class="layer-icon">🖼️</span>
                  <span class="layer-name">{{ asset.name }}</span>
                  <button @click.stop="removeAsset(child, asset)" class="layer-remove">🗑️</button>
                </div>
              </div>
              <div v-if="!placedChildren.length" class="empty-message">
                اسحب الأطفال إلى الشاشة
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="designer-footer">
        <div class="footer-info">
          <span v-if="selectedElement">
            {{ selectedElementType === 'child' ? '🧩 طفل' : '🖼️ أصل' }}: {{ selectedElement.name }}
            | الموقع: ({{ selectedElement.x }}, {{ selectedElement.y }})
            | الحجم: {{ selectedElement.width }} × {{ selectedElement.height }}
          </span>
          <span v-else>💡 اسحب الأطفال من القائمة اليسرى إلى شاشة الموبايل</span>
        </div>
        <div class="footer-actions">
          <span class="unsaved-indicator" v-if="hasUnsavedChanges">● تغييرات غير محفوظة</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { designerApi } from '../services/api';

export default {
  name: 'VisualDesigner',
  props: {
    widget: {
      type: Object,
      default: null
    },
    themes: {
      type: Array,
      default: () => []
    },
    configurationId: {
      type: [Number, String],
      default: null
    }
  },
  emits: ['close', 'save'],
  data() {
    return {
      isOpen: false,
      selectedThemeId: null,
      selectedChildId: null,
      selectedAssetId: null,
      selectedElementType: null, // 'child' | 'asset'
      placedChildren: [],
      zoom: 1,
      showGrid: true,
      isSaving: false,
      hasUnsavedChanges: false,
      isDraggingOver: false,
      
      // History for Undo/Redo
      history: [],
      historyIndex: -1,
      maxHistory: 50,
      
      // Clipboard
      clipboard: null,
      
      // Drag state
      isDragging: false,
      dragType: null, // 'new-child' | 'new-asset' | 'placed-child' | 'placed-asset'
      dragData: null,
      dragStartX: 0,
      dragStartY: 0,
      dragStartElementX: 0,
      dragStartElementY: 0,
      
      // Resize state
      isResizing: false,
      resizeType: null,
      resizeTarget: null,
      resizeDirection: null,
      resizeStartWidth: 0,
      resizeStartHeight: 0,
      resizeStartX: 0,
      resizeStartY: 0,
      
      // Mobile screen dimensions (iPhone-like)
      screenWidth: 375,
      screenHeight: 667,
      
      // Corner sync value
      cornerSyncValue: 0,
      
      // Auto save timeout
      autoSaveTimeout: null,
    };
  },
  computed: {
    widgetName() {
      return this.widget?.display_name || this.widget?.widget_key || 'Widget';
    },
    selectedTheme() {
      return this.themes.find(t => t.id === this.selectedThemeId);
    },
    themeChildren() {
      return this.selectedTheme?.children || [];
    },
    selectedChild() {
      return this.themeChildren.find(c => c.id === this.selectedChildId);
    },
    selectedElement() {
      if (this.selectedElementType === 'child') {
        return this.placedChildren.find(c => c.theme_child_id === this.selectedChildId);
      } else if (this.selectedElementType === 'asset') {
        for (const child of this.placedChildren) {
          const asset = (child.assets || []).find(a => a.id === this.selectedAssetId);
          if (asset) return asset;
        }
      }
      return null;
    },
    canUndo() {
      return this.historyIndex > 0;
    },
    canRedo() {
      return this.historyIndex < this.history.length - 1;
    }
  },
  watch: {
    widget: {
      handler(newVal) {
        if (newVal) {
          this.initializeFromWidget();
        }
      },
      immediate: true
    },
    themes: {
      handler() {
        if (!this.selectedThemeId && this.themes.length > 0) {
          this.selectedThemeId = this.themes[0].id;
        }
      },
      immediate: true
    }
  },
  mounted() {
    document.addEventListener('mousemove', this.onMouseMove);
    document.addEventListener('mouseup', this.onMouseUp);
    document.addEventListener('keydown', this.onKeyDown);
  },
  beforeUnmount() {
    document.removeEventListener('mousemove', this.onMouseMove);
    document.removeEventListener('mouseup', this.onMouseUp);
    document.removeEventListener('keydown', this.onKeyDown);
  },
  methods: {
    open() {
      this.isOpen = true;
      this.initializeFromWidget();
    },
    close() {
      if (this.hasUnsavedChanges) {
        if (!confirm('هناك تغييرات غير محفوظة. هل تريد الإغلاق؟')) {
          return;
        }
      }
      this.isOpen = false;
      this.$emit('close');
    },
    initializeFromWidget() {
      if (!this.widget) return;
      
      // Set initial theme
      const themeId = this.widget.settings?.theme_id || this.widget.selected_theme_id || this.widget.widget_theme_id;
      if (themeId) {
        this.selectedThemeId = parseInt(themeId);
      } else if (this.themes.length > 0) {
        this.selectedThemeId = this.themes[0].id;
      }
      
      // Load placed children from widget settings
      this.placedChildren = (this.widget.settings?.children || []).map(child => ({
        ...child,
        name: child.name || child.child_key || `طفل ${child.theme_child_id}`,
        width: child.width || 300,
        height: child.height || 200,
        x: child.x ?? 10,
        y: child.y ?? 10,
        is_visible: child.is_visible ?? true,
        rotation: child.rotation ?? 0,
        scale: child.scale ?? 1,
        background_color: child.background_color || 'transparent',
        border_width: child.border_width ?? 2,
        border_style: child.border_style || 'dashed',
        border_color: child.border_color || '#6366f1',
        border_radius_tl: child.border_radius_tl ?? child.border_radius ?? 8,
        border_radius_tr: child.border_radius_tr ?? child.border_radius ?? 8,
        border_radius_bl: child.border_radius_bl ?? child.border_radius ?? 8,
        border_radius_br: child.border_radius_br ?? child.border_radius ?? 8,
        assets: (child.assets || []).map(asset => ({
          ...asset,
          name: asset.name || asset.asset_key,
          width: asset.width || 80,
          height: asset.height || 80,
          x: asset.x ?? 0,
          y: asset.y ?? 0,
          opacity: asset.opacity ?? 1,
          z_index: asset.z_index ?? 0,
          rotation: asset.rotation ?? 0,
          scale: asset.scale ?? 1,
          border_width: asset.border_width ?? 0,
          border_style: asset.border_style || 'solid',
          border_color: asset.border_color || 'transparent',
          border_radius_tl: asset.border_radius_tl ?? asset.border_radius ?? 0,
          border_radius_tr: asset.border_radius_tr ?? asset.border_radius ?? 0,
          border_radius_bl: asset.border_radius_bl ?? asset.border_radius ?? 0,
          border_radius_br: asset.border_radius_br ?? asset.border_radius ?? 0,
          is_visible: asset.is_visible ?? true,
        }))
      }));
      
      // Save initial state to history
      this.saveToHistory();
    },
    onThemeChange() {
      // Clear selections when theme changes
      this.selectedChildId = null;
      this.selectedAssetId = null;
      this.selectedElementType = null;
    },
    isChildPlaced(childId) {
      return this.placedChildren.some(c => c.theme_child_id === childId);
    },
    selectChild(child) {
      this.selectedChildId = child.id;
      this.selectedAssetId = null;
    },
    selectAsset(asset) {
      this.selectedAssetId = asset.id;
    },
    selectPlacedChild(child) {
      this.selectedChildId = child.theme_child_id;
      this.selectedAssetId = null;
      this.selectedElementType = 'child';
    },
    selectPlacedAsset(asset) {
      this.selectedAssetId = asset.id;
      this.selectedElementType = 'asset';
    },
    
    // Drag & Drop handlers
    onChildDragStart(e, child) {
      e.dataTransfer.setData('type', 'child');
      e.dataTransfer.setData('childId', child.id.toString());
      e.dataTransfer.effectAllowed = 'copy';
    },
    onAssetDragStart(e, asset) {
      if (!this.selectedChildId) {
        e.preventDefault();
        alert('يرجى اختيار طفل أولاً');
        return;
      }
      e.dataTransfer.setData('type', 'asset');
      e.dataTransfer.setData('assetId', asset.id.toString());
      e.dataTransfer.effectAllowed = 'copy';
    },
    onDragOver(e) {
      this.isDraggingOver = true;
    },
    onDrop(e) {
      this.isDraggingOver = false;
      const type = e.dataTransfer.getData('type');
      const rect = this.$refs.mobileScreen.getBoundingClientRect();
      const x = Math.round((e.clientX - rect.left) / this.zoom);
      const y = Math.round((e.clientY - rect.top) / this.zoom);
      
      if (type === 'child') {
        const childId = parseInt(e.dataTransfer.getData('childId'));
        this.placeChild(childId, x, y);
      } else if (type === 'asset') {
        const assetId = parseInt(e.dataTransfer.getData('assetId'));
        this.placeAsset(assetId, x, y);
      }
    },
    placeChild(childId, x, y) {
      // Check if already placed
      if (this.isChildPlaced(childId)) {
        return;
      }
      
      const themeChild = this.themeChildren.find(c => c.id === childId);
      if (!themeChild) return;
      
      const newChild = {
        theme_child_id: themeChild.id,
        name: themeChild.label || themeChild.child_key || `طفل ${themeChild.id}`,
        child_key: themeChild.child_key,
        width: themeChild.width || 200,
        height: themeChild.height || 150,
        x: x,
        y: y,
        is_visible: true,
        rotation: 0,
        scale: 1,
        opacity: 1,
        z_index: this.placedChildren.length,
        background_color: 'transparent',
        border_width: 2,
        border_style: 'dashed',
        border_color: '#6366f1',
        border_radius_tl: 8,
        border_radius_tr: 8,
        border_radius_bl: 8,
        border_radius_br: 8,
        assets: []
      };
      
      this.placedChildren.push(newChild);
      this.selectPlacedChild(newChild);
      this.hasUnsavedChanges = true;
      this.saveToHistory();
    },
    placeAsset(assetId, x, y) {
      // Find the selected placed child
      const placedChild = this.placedChildren.find(c => c.theme_child_id === this.selectedChildId);
      if (!placedChild) {
        alert('يرجى اختيار طفل موضوع في الشاشة أولاً');
        return;
      }
      
      // Find the asset in theme
      const themeChild = this.themeChildren.find(c => c.id === this.selectedChildId);
      const themeAsset = (themeChild?.assets || []).find(a => a.id === assetId);
      if (!themeAsset) return;
      
      // Check if already placed
      if ((placedChild.assets || []).some(a => a.id === assetId)) {
        return;
      }
      
      // Calculate position relative to child
      const childX = placedChild.x;
      const childY = placedChild.y;
      const relativeX = Math.max(0, x - childX);
      const relativeY = Math.max(0, y - childY);
      
      const newAsset = {
        id: themeAsset.id,
        asset_key: themeAsset.asset_key,
        name: themeAsset.name || themeAsset.asset_key,
        file_url: themeAsset.file_url || themeAsset.url,
        url: themeAsset.file_url || themeAsset.url,
        width: themeAsset.width || 80,
        height: themeAsset.height || 80,
        x: relativeX,
        y: relativeY,
        opacity: 1,
        z_index: (placedChild.assets || []).length,
        rotation: 0,
        scale: 1,
        border_width: 0,
        border_style: 'solid',
        border_color: 'transparent',
        border_radius_tl: 0,
        border_radius_tr: 0,
        border_radius_bl: 0,
        border_radius_br: 0,
        is_visible: true,
      };
      
      if (!placedChild.assets) placedChild.assets = [];
      placedChild.assets.push(newAsset);
      this.selectPlacedAsset(newAsset);
      this.hasUnsavedChanges = true;
      this.saveToHistory();
    },
    
    // Drag placed elements
    startDragPlacedChild(e, child) {
      this.isDragging = true;
      this.dragType = 'placed-child';
      this.dragData = child;
      this.dragStartX = e.clientX;
      this.dragStartY = e.clientY;
      this.dragStartElementX = child.x;
      this.dragStartElementY = child.y;
      this.selectPlacedChild(child);
    },
    startDragPlacedAsset(e, child, asset) {
      this.isDragging = true;
      this.dragType = 'placed-asset';
      this.dragData = { child, asset };
      this.dragStartX = e.clientX;
      this.dragStartY = e.clientY;
      this.dragStartElementX = asset.x;
      this.dragStartElementY = asset.y;
      this.selectPlacedAsset(asset);
    },
    
    // Resize
    startResize(e, type, target, direction) {
      e.preventDefault();
      this.isResizing = true;
      this.resizeType = type;
      this.resizeTarget = target;
      this.resizeDirection = direction;
      this.dragStartX = e.clientX;
      this.dragStartY = e.clientY;
      this.resizeStartWidth = target.width;
      this.resizeStartHeight = target.height;
      this.resizeStartX = target.x;
      this.resizeStartY = target.y;
    },
    
    onMouseMove(e) {
      if (this.isDragging) {
        const deltaX = (e.clientX - this.dragStartX) / this.zoom;
        const deltaY = (e.clientY - this.dragStartY) / this.zoom;
        
        if (this.dragType === 'placed-child') {
          this.dragData.x = Math.max(0, Math.round(this.dragStartElementX + deltaX));
          this.dragData.y = Math.max(0, Math.round(this.dragStartElementY + deltaY));
          this.hasUnsavedChanges = true;
        } else if (this.dragType === 'placed-asset') {
          this.dragData.asset.x = Math.max(0, Math.round(this.dragStartElementX + deltaX));
          this.dragData.asset.y = Math.max(0, Math.round(this.dragStartElementY + deltaY));
          this.hasUnsavedChanges = true;
        }
      }
      
      if (this.isResizing) {
        const deltaX = (e.clientX - this.dragStartX) / this.zoom;
        const deltaY = (e.clientY - this.dragStartY) / this.zoom;
        
        let newWidth = this.resizeStartWidth;
        let newHeight = this.resizeStartHeight;
        let newX = this.resizeStartX;
        let newY = this.resizeStartY;
        
        switch (this.resizeDirection) {
          case 'se':
            newWidth = Math.max(20, Math.round(this.resizeStartWidth + deltaX));
            newHeight = Math.max(20, Math.round(this.resizeStartHeight + deltaY));
            break;
          case 'sw':
            newWidth = Math.max(20, Math.round(this.resizeStartWidth - deltaX));
            newHeight = Math.max(20, Math.round(this.resizeStartHeight + deltaY));
            newX = Math.max(0, Math.round(this.resizeStartX + deltaX));
            break;
          case 'ne':
            newWidth = Math.max(20, Math.round(this.resizeStartWidth + deltaX));
            newHeight = Math.max(20, Math.round(this.resizeStartHeight - deltaY));
            newY = Math.max(0, Math.round(this.resizeStartY + deltaY));
            break;
          case 'nw':
            newWidth = Math.max(20, Math.round(this.resizeStartWidth - deltaX));
            newHeight = Math.max(20, Math.round(this.resizeStartHeight - deltaY));
            newX = Math.max(0, Math.round(this.resizeStartX + deltaX));
            newY = Math.max(0, Math.round(this.resizeStartY + deltaY));
            break;
        }
        
        this.resizeTarget.width = newWidth;
        this.resizeTarget.height = newHeight;
        if (this.resizeDirection !== 'se') {
          this.resizeTarget.x = newX;
          this.resizeTarget.y = newY;
        }
        this.hasUnsavedChanges = true;
      }
    },
    onMouseUp() {
      this.isDragging = false;
      this.isResizing = false;
      this.dragType = null;
      this.dragData = null;
      this.resizeTarget = null;
    },
    onKeyDown(e) {
      if (!this.isOpen) return;
      
      // Undo (Ctrl+Z)
      if (e.ctrlKey && e.key === 'z') {
        e.preventDefault();
        this.undo();
        return;
      }
      
      // Redo (Ctrl+Y or Ctrl+Shift+Z)
      if (e.ctrlKey && (e.key === 'y' || (e.shiftKey && e.key === 'z'))) {
        e.preventDefault();
        this.redo();
        return;
      }
      
      // Duplicate (Ctrl+D)
      if (e.ctrlKey && e.key === 'd') {
        e.preventDefault();
        this.duplicateSelected();
        return;
      }
      
      // Delete selected element
      if (e.key === 'Delete' && this.selectedElement) {
        this.deleteSelected();
      }
      
      // Arrow keys for moving (with shift for 10px steps)
      if (this.selectedElement && ['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'].includes(e.key)) {
        e.preventDefault();
        const step = e.shiftKey ? 10 : 1;
        
        switch (e.key) {
          case 'ArrowUp':
            this.selectedElement.y = Math.max(0, this.selectedElement.y - step);
            break;
          case 'ArrowDown':
            this.selectedElement.y += step;
            break;
          case 'ArrowLeft':
            this.selectedElement.x = Math.max(0, this.selectedElement.x - step);
            break;
          case 'ArrowRight':
            this.selectedElement.x += step;
            break;
        }
        
        this.hasUnsavedChanges = true;
        return;
      }
      
      // Escape to deselect
      if (e.key === 'Escape') {
        this.selectedChildId = null;
        this.selectedAssetId = null;
        this.selectedElementType = null;
      }
    },
    onScreenClick(e) {
      if (e.target === this.$refs.mobileScreen) {
        this.selectedChildId = null;
        this.selectedAssetId = null;
        this.selectedElementType = null;
      }
    },
    
    removeChild(child) {
      const index = this.placedChildren.findIndex(c => c.theme_child_id === child.theme_child_id);
      if (index !== -1) {
        this.placedChildren.splice(index, 1);
        this.selectedChildId = null;
        this.selectedElementType = null;
        this.hasUnsavedChanges = true;
        this.saveToHistory();
      }
    },
    removeAsset(child, asset) {
      const assetIndex = (child.assets || []).findIndex(a => a.id === asset.id);
      if (assetIndex !== -1) {
        child.assets.splice(assetIndex, 1);
        this.selectedAssetId = null;
        this.selectedElementType = null;
        this.hasUnsavedChanges = true;
        this.saveToHistory();
      }
    },
    
    onPropertyChange() {
      this.hasUnsavedChanges = true;
      this.saveToHistory();
      this.autoSave();
    },
    
    // Auto Save with debounce
    autoSave() {
      if (this.autoSaveTimeout) {
        clearTimeout(this.autoSaveTimeout);
      }
      this.autoSaveTimeout = setTimeout(() => {
        this.saveDesign();
      }, 1000); // Save after 1 second of inactivity
    },
    
    // Sync all corner radii
    syncCorners() {
      if (!this.selectedElement) return;
      
      const value = this.cornerSyncValue || 0;
      this.selectedElement.border_radius_tl = value;
      this.selectedElement.border_radius_tr = value;
      this.selectedElement.border_radius_bl = value;
      this.selectedElement.border_radius_br = value;
      this.hasUnsavedChanges = true;
      this.saveToHistory();
    },
    
    // History (Undo/Redo)
    saveToHistory() {
      // Remove any future states if we're in the middle of history
      if (this.historyIndex < this.history.length - 1) {
        this.history = this.history.slice(0, this.historyIndex + 1);
      }
      
      // Save current state
      const state = JSON.stringify(this.placedChildren);
      this.history.push(state);
      
      // Limit history size
      if (this.history.length > this.maxHistory) {
        this.history.shift();
      }
      
      this.historyIndex = this.history.length - 1;
    },
    undo() {
      if (this.canUndo) {
        this.historyIndex--;
        this.placedChildren = JSON.parse(this.history[this.historyIndex]);
        this.hasUnsavedChanges = true;
      }
    },
    redo() {
      if (this.canRedo) {
        this.historyIndex++;
        this.placedChildren = JSON.parse(this.history[this.historyIndex]);
        this.hasUnsavedChanges = true;
      }
    },
    
    // Duplicate
    duplicateSelected() {
      if (!this.selectedElement) return;
      
      if (this.selectedElementType === 'child') {
        const child = this.placedChildren.find(c => c.theme_child_id === this.selectedChildId);
        if (child) {
          const newChild = JSON.parse(JSON.stringify(child));
          newChild.x += 20;
          newChild.y += 20;
          newChild.theme_child_id = child.theme_child_id + '_copy_' + Date.now();
          this.placedChildren.push(newChild);
          this.hasUnsavedChanges = true;
          this.saveToHistory();
        }
      } else if (this.selectedElementType === 'asset') {
        for (const child of this.placedChildren) {
          const asset = (child.assets || []).find(a => a.id === this.selectedAssetId);
          if (asset) {
            const newAsset = JSON.parse(JSON.stringify(asset));
            newAsset.x += 10;
            newAsset.y += 10;
            newAsset.id = asset.id + '_copy_' + Date.now();
            child.assets.push(newAsset);
            this.hasUnsavedChanges = true;
            this.saveToHistory();
            break;
          }
        }
      }
    },
    deleteSelected() {
      if (!this.selectedElement) return;
      
      if (this.selectedElementType === 'child') {
        const child = this.placedChildren.find(c => c.theme_child_id === this.selectedChildId);
        if (child) this.removeChild(child);
      } else if (this.selectedElementType === 'asset') {
        for (const child of this.placedChildren) {
          const asset = (child.assets || []).find(a => a.id === this.selectedAssetId);
          if (asset) {
            this.removeAsset(child, asset);
            break;
          }
        }
      }
    },
    
    // Alignment
    alignSelected(alignment) {
      if (!this.selectedElement) return;
      
      switch (alignment) {
        case 'left':
          this.selectedElement.x = 0;
          break;
        case 'center-h':
          this.selectedElement.x = Math.round((this.screenWidth - this.selectedElement.width) / 2);
          break;
        case 'right':
          this.selectedElement.x = this.screenWidth - this.selectedElement.width;
          break;
        case 'top':
          this.selectedElement.y = 0;
          break;
        case 'center-v':
          this.selectedElement.y = Math.round((this.screenHeight - this.selectedElement.height) / 2);
          break;
        case 'bottom':
          this.selectedElement.y = this.screenHeight - this.selectedElement.height;
          break;
      }
      
      this.hasUnsavedChanges = true;
      this.saveToHistory();
    },
    
    // Z-Order
    bringToFront() {
      if (!this.selectedElement) return;
      
      let maxZ = 0;
      for (const child of this.placedChildren) {
        maxZ = Math.max(maxZ, child.z_index || 0);
        for (const asset of child.assets || []) {
          maxZ = Math.max(maxZ, asset.z_index || 0);
        }
      }
      
      this.selectedElement.z_index = maxZ + 1;
      this.hasUnsavedChanges = true;
      this.saveToHistory();
    },
    sendToBack() {
      if (!this.selectedElement) return;
      
      this.selectedElement.z_index = 0;
      
      // Shift all other elements up
      for (const child of this.placedChildren) {
        if (child !== this.selectedElement) {
          child.z_index = (child.z_index || 0) + 1;
        }
        for (const asset of child.assets || []) {
          if (asset !== this.selectedElement) {
            asset.z_index = (asset.z_index || 0) + 1;
          }
        }
      }
      
      this.hasUnsavedChanges = true;
      this.saveToHistory();
    },
    
    // Reset & Fit
    resetElementTransform() {
      if (!this.selectedElement) return;
      
      this.selectedElement.rotation = 0;
      this.selectedElement.scale = 1;
      this.selectedElement.opacity = 1;
      this.hasUnsavedChanges = true;
      this.saveToHistory();
    },
    fitToScreen() {
      if (!this.selectedElement) return;
      
      this.selectedElement.x = 0;
      this.selectedElement.y = 0;
      this.selectedElement.width = this.screenWidth;
      this.selectedElement.height = this.screenHeight;
      this.hasUnsavedChanges = true;
      this.saveToHistory();
    },
    
    // Styles
    getChildStyle(child) {
      const borderRadius = this.getBorderRadiusStyle(child);
      return {
        left: child.x * this.zoom + 'px',
        top: child.y * this.zoom + 'px',
        width: child.width * this.zoom + 'px',
        height: child.height * this.zoom + 'px',
        display: child.is_visible ? 'block' : 'none',
        transform: `rotate(${child.rotation || 0}deg) scale(${child.scale || 1})`,
        opacity: child.opacity ?? 1,
        backgroundColor: child.background_color || 'transparent',
        borderWidth: (child.border_width || 2) + 'px',
        borderStyle: child.border_style || 'dashed',
        borderColor: child.border_color || '#6366f1',
        borderRadius: borderRadius,
        zIndex: child.z_index || 0,
      };
    },
    getAssetStyle(asset) {
      const borderRadius = this.getBorderRadiusStyle(asset);
      return {
        left: asset.x * this.zoom + 'px',
        top: asset.y * this.zoom + 'px',
        width: asset.width * this.zoom + 'px',
        height: asset.height * this.zoom + 'px',
        opacity: asset.opacity,
        zIndex: asset.z_index,
        display: asset.is_visible ? 'block' : 'none',
        transform: `rotate(${asset.rotation || 0}deg) scale(${asset.scale || 1})`,
        borderRadius: borderRadius,
        borderWidth: (asset.border_width || 0) + 'px',
        borderStyle: asset.border_style || 'solid',
        borderColor: asset.border_color || 'transparent',
      };
    },
    
    // Helper for border radius
    getBorderRadiusStyle(element) {
      const tl = element.border_radius_tl ?? element.border_radius ?? 0;
      const tr = element.border_radius_tr ?? element.border_radius ?? 0;
      const br = element.border_radius_br ?? element.border_radius ?? 0;
      const bl = element.border_radius_bl ?? element.border_radius ?? 0;
      return `${tl}px ${tr}px ${br}px ${bl}px`;
    },
    
    // Zoom
    zoomIn() {
      this.zoom = Math.min(this.zoom + 0.1, 2);
    },
    zoomOut() {
      this.zoom = Math.max(this.zoom - 0.1, 0.5);
    },
    resetView() {
      this.zoom = 1;
    },
    toggleGrid() {
      this.showGrid = !this.showGrid;
    },
    
    // Save
    async saveDesign() {
      if (this.isSaving) return; // Prevent multiple saves
      this.isSaving = true;
      
      try {
        // Prepare data for saving with ALL properties
        const designData = {
          theme_id: this.selectedThemeId,
          children: this.placedChildren.map(child => ({
            theme_child_id: child.theme_child_id,
            name: child.name,
            child_key: child.child_key,
            width: child.width,
            height: child.height,
            x: child.x,
            y: child.y,
            is_visible: child.is_visible,
            rotation: child.rotation,
            scale: child.scale,
            opacity: child.opacity,
            z_index: child.z_index,
            background_color: child.background_color,
            border_width: child.border_width,
            border_style: child.border_style,
            border_color: child.border_color,
            border_radius_tl: child.border_radius_tl,
            border_radius_tr: child.border_radius_tr,
            border_radius_bl: child.border_radius_bl,
            border_radius_br: child.border_radius_br,
            assets: (child.assets || []).map(asset => ({
              id: asset.id,
              asset_key: asset.asset_key,
              name: asset.name,
              file_url: asset.file_url,
              url: asset.url,
              width: asset.width,
              height: asset.height,
              x: asset.x,
              y: asset.y,
              opacity: asset.opacity,
              z_index: asset.z_index,
              rotation: asset.rotation,
              scale: asset.scale,
              border_width: asset.border_width,
              border_style: asset.border_style,
              border_color: asset.border_color,
              border_radius_tl: asset.border_radius_tl,
              border_radius_tr: asset.border_radius_tr,
              border_radius_bl: asset.border_radius_bl,
              border_radius_br: asset.border_radius_br,
              is_visible: asset.is_visible,
            }))
          }))
        };
        
        // Emit to parent to save in configuration
        this.$emit('save', designData);
        
        // Also save ALL properties to database if we have real IDs
        const childrenToUpdate = [];
        const assetsToUpdate = [];
        
        for (const child of this.placedChildren) {
          if (child.theme_child_id) {
            childrenToUpdate.push({
              id: child.theme_child_id,
              width: child.width,
              height: child.height,
              x: child.x,
              y: child.y,
              rotation: child.rotation,
              scale: child.scale,
              opacity: child.opacity,
              z_index: child.z_index,
              background_color: child.background_color,
              border_width: child.border_width,
              border_style: child.border_style,
              border_color: child.border_color,
              border_radius_tl: child.border_radius_tl,
              border_radius_tr: child.border_radius_tr,
              border_radius_bl: child.border_radius_bl,
              border_radius_br: child.border_radius_br,
            });
          }
          for (const asset of child.assets || []) {
            if (asset.id) {
              assetsToUpdate.push({
                id: asset.id,
                width: asset.width,
                height: asset.height,
                x: asset.x,
                y: asset.y,
                opacity: asset.opacity,
                z_index: asset.z_index,
                rotation: asset.rotation,
                scale: asset.scale,
                border_width: asset.border_width,
                border_style: asset.border_style,
                border_color: asset.border_color,
                border_radius_tl: asset.border_radius_tl,
                border_radius_tr: asset.border_radius_tr,
                border_radius_bl: asset.border_radius_bl,
                border_radius_br: asset.border_radius_br,
              });
            }
          }
        }
        
        if (childrenToUpdate.length > 0 || assetsToUpdate.length > 0) {
          await designerApi.batchUpdatePositions({
            children: childrenToUpdate,
            assets: assetsToUpdate,
          });
        }
        
        this.hasUnsavedChanges = false;
      } catch (error) {
        console.error('Failed to save design:', error);
        alert('حدث خطأ أثناء الحفظ');
      } finally {
        this.isSaving = false;
      }
    }
  }
};
</script>

<style scoped>
.visual-designer-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.8);
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
}

.visual-designer-container {
  width: 95vw;
  height: 95vh;
  background: #1e1e2e;
  border-radius: 16px;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
}

/* Header */
.designer-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 24px;
  background: linear-gradient(135deg, #2d2d44 0%, #1e1e2e 100%);
  border-bottom: 1px solid #3d3d5c;
  gap: 16px;
}

.header-logo {
  width: 48px;
  height: 48px;
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
}

.logo-icon {
  font-size: 24px;
}

.header-badge {
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 500;
}

.header-badge.blue {
  background: rgba(59, 130, 246, 0.2);
  color: #60a5fa;
  border: 1px solid rgba(59, 130, 246, 0.3);
}

.header-badge.purple {
  background: rgba(139, 92, 246, 0.2);
  color: #a78bfa;
  border: 1px solid rgba(139, 92, 246, 0.3);
}

/* Toolbar */
.header-toolbar {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  background: rgba(0, 0, 0, 0.2);
  border-radius: 12px;
}

.toolbar-group {
  display: flex;
  gap: 4px;
}

.toolbar-divider {
  width: 1px;
  height: 24px;
  background: #3d3d5c;
  margin: 0 4px;
}

.toolbar-btn {
  width: 36px;
  height: 36px;
  background: #1e1e2e;
  border: 1px solid #3d3d5c;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 16px;
}

.toolbar-btn:hover:not(:disabled) {
  background: #3d3d5c;
  border-color: #6366f1;
}

.toolbar-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.save-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 20px;
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.save-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
}

.save-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

.close-btn {
  width: 40px;
  height: 40px;
  background: #ef4444;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 18px;
  cursor: pointer;
  transition: all 0.2s;
}

.close-btn:hover {
  background: #dc2626;
}

/* Body */
.designer-body {
  display: flex;
  flex: 1;
  overflow: hidden;
}

/* Panels */
.left-panel, .right-panel {
  width: 300px;
  background: #252536;
  display: flex;
  flex-direction: column;
  border-left: 1px solid #3d3d5c;
  border-right: 1px solid #3d3d5c;
  overflow: hidden;
}

.right-panel {
  overflow-y: auto;
}

.center-panel {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  background: #1a1a2e;
  padding: 20px;
  overflow: auto;
}

.panel-section {
  padding: 16px;
  border-bottom: 1px solid #3d3d5c;
}

.panel-section.properties-section {
  flex: 1;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.properties-form.scrollable {
  flex: 1;
  overflow-y: auto;
  overflow-x: hidden;
  padding-right: 8px;
  max-height: calc(100vh - 350px);
}

.properties-form.scrollable::-webkit-scrollbar {
  width: 6px;
}

.properties-form.scrollable::-webkit-scrollbar-track {
  background: #1e1e2e;
  border-radius: 3px;
}

.properties-form.scrollable::-webkit-scrollbar-thumb {
  background: #6366f1;
  border-radius: 3px;
}

.section-title {
  font-size: 14px;
  font-weight: 600;
  color: #a0a0b0;
  margin-bottom: 12px;
}

/* Theme Select */
.theme-select {
  width: 100%;
  padding: 10px 12px;
  background: #1e1e2e;
  border: 1px solid #3d3d5c;
  border-radius: 8px;
  color: white;
  font-size: 14px;
}

/* Children List */
.children-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
  max-height: 200px;
  overflow-y: auto;
}

.child-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px;
  background: #1e1e2e;
  border: 2px solid transparent;
  border-radius: 10px;
  cursor: grab;
  transition: all 0.2s;
}

.child-item:hover {
  border-color: #6366f1;
  background: #2d2d44;
}

.child-item.active {
  border-color: #6366f1;
  background: rgba(99, 102, 241, 0.2);
}

.child-item.placed {
  opacity: 0.5;
  cursor: not-allowed;
}

.child-icon {
  font-size: 24px;
}

.child-info {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.child-name {
  color: white;
  font-size: 13px;
  font-weight: 500;
}

.child-type {
  color: #6b7280;
  font-size: 11px;
}

.child-assets-count {
  font-size: 12px;
  color: #9ca3af;
}

/* Assets Library */
.assets-library {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 8px;
  max-height: 150px;
  overflow-y: auto;
}

.asset-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 8px;
  background: #1e1e2e;
  border: 2px solid transparent;
  border-radius: 8px;
  cursor: grab;
  transition: all 0.2s;
}

.asset-item:hover {
  border-color: #f59e0b;
}

.asset-item.active {
  border-color: #f59e0b;
  background: rgba(245, 158, 11, 0.2);
}

.asset-preview {
  width: 50px;
  height: 50px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #2d2d44;
  border-radius: 6px;
  overflow: hidden;
}

.asset-preview img {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
}

.asset-placeholder {
  font-size: 24px;
}

.asset-name {
  font-size: 10px;
  color: #9ca3af;
  margin-top: 4px;
  text-align: center;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  width: 100%;
}

/* Mobile Frame */
.mobile-frame {
  background: #000;
  border-radius: 40px;
  padding: 12px;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5), inset 0 0 0 3px #333;
}

.mobile-notch {
  width: 150px;
  height: 30px;
  background: #000;
  border-radius: 0 0 20px 20px;
  margin: 0 auto 0;
  position: relative;
  z-index: 10;
}

.mobile-status-bar {
  display: flex;
  justify-content: space-between;
  padding: 4px 20px;
  color: white;
  font-size: 12px;
  font-weight: 600;
}

.status-icons {
  display: flex;
  gap: 4px;
}

.mobile-screen {
  width: 375px;
  height: 667px;
  background: #f5f5f5;
  border-radius: 4px;
  position: relative;
  overflow: hidden;
  transform-origin: top center;
}

.mobile-grid {
  position: absolute;
  inset: 0;
  background-image: 
    linear-gradient(to right, rgba(0,0,0,0.05) 1px, transparent 1px),
    linear-gradient(to bottom, rgba(0,0,0,0.05) 1px, transparent 1px);
  background-size: 20px 20px;
  pointer-events: none;
}

.mobile-home-bar {
  width: 134px;
  height: 5px;
  background: #fff;
  border-radius: 3px;
  margin: 8px auto 0;
}

/* Placed Elements */
.placed-child {
  position: absolute;
  background: rgba(99, 102, 241, 0.1);
  border: 2px dashed #6366f1;
  border-radius: 8px;
  cursor: move;
  transition: box-shadow 0.2s;
}

.placed-child.selected {
  border-style: solid;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.3);
}

.placed-child .child-label {
  position: absolute;
  top: -24px;
  left: 0;
  background: #6366f1;
  color: white;
  font-size: 11px;
  padding: 2px 8px;
  border-radius: 4px;
  white-space: nowrap;
}

.placed-asset {
  position: absolute;
  border: 1px solid transparent;
  border-radius: 4px;
  cursor: move;
  overflow: hidden;
  transition: box-shadow 0.2s;
}

.placed-asset.selected {
  border-color: #f59e0b;
  box-shadow: 0 0 0 2px rgba(245, 158, 11, 0.3);
}

.placed-asset img {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

.asset-placeholder-small {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  height: 100%;
  background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
  color: white;
  font-size: 10px;
  text-align: center;
  padding: 4px;
}

/* Resize Handles */
.resize-handles {
  position: absolute;
  inset: 0;
  pointer-events: none;
}

.resize-handle {
  position: absolute;
  width: 12px;
  height: 12px;
  background: white;
  border: 2px solid #6366f1;
  border-radius: 50%;
  pointer-events: all;
}

.resize-handle.se { bottom: -6px; right: -6px; cursor: se-resize; }
.resize-handle.sw { bottom: -6px; left: -6px; cursor: sw-resize; }
.resize-handle.ne { top: -6px; right: -6px; cursor: ne-resize; }
.resize-handle.nw { top: -6px; left: -6px; cursor: nw-resize; }

/* Drop Indicator */
.drop-indicator {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(99, 102, 241, 0.2);
  border: 3px dashed #6366f1;
  color: #6366f1;
  font-size: 18px;
  font-weight: 600;
}

/* Preview Controls */
.preview-controls {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-top: 16px;
  padding: 8px 16px;
  background: #252536;
  border-radius: 12px;
}

.control-btn {
  width: 36px;
  height: 36px;
  background: #1e1e2e;
  border: 1px solid #3d3d5c;
  border-radius: 8px;
  color: white;
  cursor: pointer;
  transition: all 0.2s;
}

.control-btn:hover {
  background: #3d3d5c;
}

.control-btn.active {
  background: #6366f1;
  border-color: #6366f1;
}

.zoom-level {
  color: #9ca3af;
  font-size: 14px;
  min-width: 50px;
  text-align: center;
}

/* Properties Panel */
.properties-form {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.property-group {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.property-group label {
  font-size: 12px;
  color: #9ca3af;
}

.property-input {
  padding: 8px 12px;
  background: #1e1e2e;
  border: 1px solid #3d3d5c;
  border-radius: 6px;
  color: white;
  font-size: 14px;
}

.property-input.readonly {
  opacity: 0.7;
}

.property-input:focus {
  outline: none;
  border-color: #6366f1;
}

.property-row {
  display: flex;
  gap: 8px;
}

.property-group.half {
  flex: 1;
}

.range-value {
  font-size: 12px;
  color: #6366f1;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
}

/* Color Picker */
.color-picker-row {
  display: flex;
  gap: 8px;
  align-items: center;
}

.color-picker {
  width: 40px;
  height: 36px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  background: none;
}

/* Border Controls */
.border-controls {
  display: flex;
  gap: 6px;
  align-items: center;
}

.property-input.small {
  width: 60px;
}

/* Corners Grid */
.corners-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 8px;
  margin-bottom: 8px;
}

.corner-input {
  display: flex;
  align-items: center;
  gap: 6px;
}

.corner-label {
  font-size: 16px;
  width: 24px;
  text-align: center;
}

.corner-input .property-input {
  flex: 1;
  padding: 6px 8px;
  font-size: 13px;
}

.corner-sync {
  display: flex;
  gap: 8px;
  align-items: center;
  margin-top: 4px;
  padding-top: 8px;
  border-top: 1px dashed #3d3d5c;
}

.sync-btn {
  flex: 1;
  padding: 6px 10px;
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  border: none;
  border-radius: 6px;
  color: white;
  font-size: 11px;
  cursor: pointer;
  transition: all 0.2s;
}

.sync-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(99, 102, 241, 0.4);
}

/* Slider */
.slider-container {
  display: flex;
  align-items: center;
  gap: 8px;
}

.slider-input {
  flex: 1;
  height: 6px;
  border-radius: 3px;
  appearance: none;
  background: linear-gradient(to right, #3d3d5c, #6366f1);
}

.slider-input::-webkit-slider-thumb {
  appearance: none;
  width: 16px;
  height: 16px;
  border-radius: 50%;
  background: #6366f1;
  cursor: pointer;
  border: 2px solid white;
}

/* Quick Actions */
.quick-actions {
  display: flex;
  gap: 8px;
  margin-top: 8px;
  padding-top: 12px;
  border-top: 1px solid #3d3d5c;
}

.quick-action-btn {
  flex: 1;
  padding: 8px 12px;
  background: #1e1e2e;
  border: 1px solid #3d3d5c;
  border-radius: 6px;
  color: #9ca3af;
  font-size: 12px;
  cursor: pointer;
  transition: all 0.2s;
}

.quick-action-btn:hover {
  background: #3d3d5c;
  color: white;
}

.empty-properties {
  text-align: center;
  padding: 30px;
  color: #6b7280;
}

.empty-icon {
  font-size: 40px;
  display: block;
  margin-bottom: 10px;
}

/* Layers */
.layers-list {
  display: flex;
  flex-direction: column;
  gap: 4px;
  max-height: 300px;
  overflow-y: auto;
}

.layer-group {
  display: flex;
  flex-direction: column;
}

.layer-item {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 12px;
  background: #1e1e2e;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s;
}

.layer-item:hover {
  background: #2d2d44;
}

.layer-item.selected {
  background: rgba(99, 102, 241, 0.2);
  border: 1px solid #6366f1;
}

.layer-item.child-layer {
  margin-left: 20px;
  font-size: 12px;
}

.layer-icon {
  font-size: 14px;
}

.layer-name {
  flex: 1;
  color: white;
  font-size: 13px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.layer-remove {
  opacity: 0;
  background: none;
  border: none;
  cursor: pointer;
  font-size: 14px;
  transition: opacity 0.2s;
}

.layer-item:hover .layer-remove {
  opacity: 1;
}

/* Footer */
.designer-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 24px;
  background: #252536;
  border-top: 1px solid #3d3d5c;
  color: #9ca3af;
  font-size: 13px;
}

.footer-info span {
  color: #d1d5db;
}

.unsaved-indicator {
  color: #fbbf24;
}

.empty-message {
  text-align: center;
  padding: 20px;
  color: #6b7280;
  font-size: 13px;
}
</style>
