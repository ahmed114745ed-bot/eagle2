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
        <!-- Left Panel - Widgets, Children & Assets Library -->
        <div class="left-panel">
          <!-- Widget Selector (Select dropdown) -->
          <div class="panel-section">
            <h3 class="section-title">📦 اختر الويدجت</h3>
            <select v-model="selectedWidgetId" @change="onWidgetSelect" class="widget-select">
              <option :value="null">-- اختر ويدجت --</option>
              <option v-for="widget in availableWidgets" :key="widget.id" :value="widget.id">
                {{ widget.display_name || widget.widget_key }}
              </option>
            </select>
          </div>

          <!-- Widget Layout Settings (for selected widget) -->
          <div v-if="selectedWidgetData" class="panel-section">
            <h3 class="section-title">⚙️ إعدادات الويدجت</h3>
            <div class="widget-settings">
              <!-- Layout Mode -->
              <div class="setting-group">
                <label>نوع التخطيط</label>
                <div class="layout-buttons">
                  <button @click="setWidgetLayout('absolute')" class="layout-btn-sm" :class="{ active: selectedWidgetData.layout_mode === 'absolute' }">
                    📍 حر
                  </button>
                  <button @click="setWidgetLayout('horizontal')" class="layout-btn-sm" :class="{ active: selectedWidgetData.layout_mode === 'horizontal' }">
                    ↔️ أفقي
                  </button>
                  <button @click="setWidgetLayout('vertical')" class="layout-btn-sm" :class="{ active: selectedWidgetData.layout_mode === 'vertical' }">
                    ↕️ عمودي
                  </button>
                </div>
              </div>
              
              <!-- Layout options (for horizontal/vertical) -->
              <div v-if="selectedWidgetData.layout_mode !== 'absolute'" class="setting-group">
                <!-- Scroll Mode Selection -->
                <label>نوع التمرير</label>
                <div class="scroll-mode-buttons">
                  <button @click="setScrollMode('manual')" class="layout-btn-sm" :class="{ active: !selectedWidgetData.auto_scroll }">
                    👆 يدوي لانهائي
                  </button>
                  <button @click="setScrollMode('auto')" class="layout-btn-sm" :class="{ active: selectedWidgetData.auto_scroll }">
                    🔄 تلقائي
                  </button>
                </div>
                
                <!-- Slider speed (only if auto scroll is enabled) -->
                <div v-if="selectedWidgetData.auto_scroll" class="setting-row">
                  <div class="setting-field">
                    <label>سرعة السلايدر (ثواني/عنصر)</label>
                    <input type="number" v-model.number="selectedWidgetData.scroll_speed" @change="onWidgetSettingChange" class="setting-input" min="1" max="20" step="0.5" />
                  </div>
                </div>
                
                <div class="setting-row">
                  <div class="setting-field">
                    <label>المسافة</label>
                    <input type="number" v-model.number="selectedWidgetData.layout_gap" @change="onWidgetSettingChange" class="setting-input" min="0" />
                  </div>
                  <div class="setting-field">
                    <label>الهامش</label>
                    <input type="number" v-model.number="selectedWidgetData.layout_padding" @change="onWidgetSettingChange" class="setting-input" min="0" />
                  </div>
                </div>
                
                <!-- زر إعادة ترتيب الأبناء -->
                <button @click="repositionChildrenByLayout(selectedWidgetData)" class="layout-btn-sm" style="width: 100%; margin-top: 8px;">
                  🔄 إعادة ترتيب المواقع
                </button>
              </div>
              
              <!-- Widget Padding -->
              <div class="setting-group">
                <label>📐 حشو الويدجت (Padding)</label>
                <div class="padding-inputs-row">
                  <div class="padding-input-group">
                    <span class="padding-label">↑</span>
                    <input type="number" v-model.number="selectedWidgetData.padding_top" @change="onWidgetSettingChange" class="setting-input-sm" min="0" placeholder="0" />
                  </div>
                  <div class="padding-input-group">
                    <span class="padding-label">↓</span>
                    <input type="number" v-model.number="selectedWidgetData.padding_bottom" @change="onWidgetSettingChange" class="setting-input-sm" min="0" placeholder="0" />
                  </div>
                  <div class="padding-input-group">
                    <span class="padding-label">→</span>
                    <input type="number" v-model.number="selectedWidgetData.padding_right" @change="onWidgetSettingChange" class="setting-input-sm" min="0" placeholder="0" />
                  </div>
                  <div class="padding-input-group">
                    <span class="padding-label">←</span>
                    <input type="number" v-model.number="selectedWidgetData.padding_left" @change="onWidgetSettingChange" class="setting-input-sm" min="0" placeholder="0" />
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Screen Settings (background color) -->
          <div class="panel-section">
            <h3 class="section-title">📱 إعدادات الشاشة</h3>
            <div class="widget-settings">
              <div class="setting-group">
                <label>لون خلفية الشاشة</label>
                <div class="color-picker-row">
                  <input type="color" v-model="screenBackgroundColor" @input="onScreenSettingChange" class="color-picker" />
                  <input type="text" v-model="screenBackgroundColor" @change="onScreenSettingChange" class="setting-input flex-1" placeholder="#f5f5f5" />
                </div>
              </div>
            </div>
          </div>

          <!-- Children List (for selected widget) -->
          <div v-if="selectedWidgetData" class="panel-section flex-1">
            <h3 class="section-title">👶 الأبناء - {{ themeChildren.length }}</h3>
            <div class="children-list">
              <div v-if="themeChildren.length === 0" class="empty-message">
                لا يوجد أطفال لهذا الويدجت
              </div>
              
              <!-- Children list -->
              <div
                v-for="child in themeChildren"
                :key="child.id"
                class="child-item"
                :class="{ 'active': selectedChildId === child.id, 'placed': isChildPlacedInWidget(child.id) }"
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
            </div>
          </div>
          
          <!-- No widget selected message -->
          <div v-else class="panel-section flex-1">
            <div class="empty-message">
              👆 اختر ويدجت من القائمة
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
                :class="{ 'active': selectedAssetId === asset.id, 'text-asset': isTextAsset(asset), 'svga-asset': isSvgaAsset(asset) }"
                draggable="true"
                @dragstart="onAssetDragStart($event, asset)"
                @click="selectAsset(asset)"
              >
                <div class="asset-preview">
                  <!-- Text assets -->
                  <div v-if="isTextAsset(asset)" class="text-asset-preview">
                    <span class="text-icon">📝</span>
                    <span class="text-content">{{ asset.text_content || asset.name || asset.asset_key }}</span>
                  </div>
                  <!-- SVGA assets -->
                  <div v-else-if="isSvgaAsset(asset)" class="svga-asset-preview">
                    <div 
                      :ref="el => { if(el) onSvgaMounted(asset, el) }"
                      class="svga-canvas-container"
                      :data-url="asset.file_url || asset.url"
                      :data-asset-id="asset.id"
                    ></div>
                    <div class="svga-overlay" v-if="!isSvgaLoaded(asset.id)">
                      <span class="svga-loading-icon">🎬</span>
                    </div>
                    <span class="svga-badge">SVGA</span>
                  </div>
                  <!-- Image assets -->
                  <img v-else-if="asset.file_url || asset.url" :src="asset.file_url || asset.url" @error="onAssetImageError($event, asset)" />
                  <span v-else class="asset-placeholder">📦</span>
                </div>
                <span class="asset-name">{{ asset.name || asset.asset_key }}</span>
                <span class="asset-type-badge">{{ asset.asset_type || 'image' }}</span>
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
              :style="getMobileScreenStyle()"
              @dragover.prevent="onDragOver"
              @drop="onDrop"
              @click="onScreenClick"
            >
              <!-- Grid -->
              <div v-if="showGrid" class="mobile-grid"></div>
              
              <!-- Placed Widgets -->
              <div
                v-for="widget in placedWidgets"
                :key="widget.id"
                class="placed-widget"
                :class="{ 'selected': selectedWidgetId === widget.id }"
                :style="getWidgetStyle(widget)"
                @mousedown.stop="startDragWidget($event, widget)"
                @click.stop="selectPlacedWidget(widget)"
              >
                <div class="widget-label">{{ widget.display_name || widget.widget_key }}</div>
                
                <!-- Widget content container (scrollable if horizontal/vertical layout) -->
                <div 
                  class="widget-content"
                  :class="getWidgetContentClass(widget)"
                  :style="getWidgetContentStyle(widget)"
                  @wheel.stop="onWidgetScroll($event, widget)"
                >
                  <!-- Infinite scroll wrapper (duplicates children for seamless loop) -->
                  <template v-if="widget.layout_mode === 'horizontal' && widget.infinite_scroll">
                    <div class="infinite-scroll-track" :class="{ 'auto-scroll': widget.auto_scroll, 'manual-scroll': !widget.auto_scroll }" :style="{ animationDuration: getScrollDuration(widget) }">
                      <!-- Original children -->
                      <div
                        v-for="child in widget.children || []"
                        :key="child.id"
                        class="placed-child layout-child"
                        :class="{ 'selected': selectedWidgetId === widget.id && selectedChildId === child.theme_child_id }"
                        :style="getChildStyleInWidget(child, widget)"
                        @mousedown.stop="startDragPlacedChild($event, child, widget)"
                        @click.stop="selectPlacedChild(child, widget)"
                      >
                        <div class="child-label-small">{{ child.name }}</div>
                        <div
                          v-for="asset in child.assets || []"
                          :key="asset.id"
                          class="placed-asset"
                          :class="{ 'selected': selectedAssetId === asset.id, 'text-asset': isTextAsset(asset), 'svga-asset': isSvgaAsset(asset) }"
                          :style="getAssetStyle(asset)"
                          @mousedown.stop="startDragPlacedAsset($event, child, asset, widget)"
                          @click.stop="selectPlacedAsset(asset, widget, child)"
                        >
                          <div v-if="isTextAsset(asset)" class="text-asset-content" :style="getTextAssetStyle(asset)">
                            {{ asset.text_content || asset.name || asset.asset_key }}
                          </div>
                          <!-- SVGA Asset -->
                          <div v-else-if="isSvgaAsset(asset)" class="svga-asset-container" :ref="el => { if(el) onSvgaMounted(asset, el) }" :data-url="asset.file_url || asset.url"></div>
                          <img v-else-if="asset.file_url || asset.url" :src="asset.file_url || asset.url" draggable="false" />
                          <span v-else class="asset-placeholder-small">{{ asset.name }}</span>
                          
                          <!-- Asset resize handles -->
                          <div v-if="selectedAssetId === asset.id" class="resize-handles">
                            <div class="resize-handle se" @mousedown.stop="startResize($event, 'asset', asset, 'se')"></div>
                            <div class="resize-handle sw" @mousedown.stop="startResize($event, 'asset', asset, 'sw')"></div>
                            <div class="resize-handle ne" @mousedown.stop="startResize($event, 'asset', asset, 'ne')"></div>
                            <div class="resize-handle nw" @mousedown.stop="startResize($event, 'asset', asset, 'nw')"></div>
                          </div>
                        </div>
                        
                        <!-- Child resize handles -->
                        <div v-if="selectedWidgetId === widget.id && selectedChildId === child.theme_child_id" class="resize-handles">
                          <div class="resize-handle se" @mousedown.stop="startResize($event, 'child', child, 'se')"></div>
                          <div class="resize-handle sw" @mousedown.stop="startResize($event, 'child', child, 'sw')"></div>
                          <div class="resize-handle ne" @mousedown.stop="startResize($event, 'child', child, 'ne')"></div>
                          <div class="resize-handle nw" @mousedown.stop="startResize($event, 'child', child, 'nw')"></div>
                        </div>
                      </div>
                      <!-- Duplicated children for seamless loop -->
                      <div
                        v-for="child in widget.children || []"
                        :key="'dup_' + child.id"
                        class="placed-child layout-child"
                        :style="getChildStyleInWidget(child, widget)"
                      >
                        <div class="child-label-small">{{ child.name }}</div>
                        <div
                          v-for="asset in child.assets || []"
                          :key="'dup_' + asset.id"
                          class="placed-asset"
                          :class="{ 'text-asset': isTextAsset(asset), 'svga-asset': isSvgaAsset(asset) }"
                          :style="getAssetStyle(asset)"
                        >
                          <div v-if="isTextAsset(asset)" class="text-asset-content" :style="getTextAssetStyle(asset)">
                            {{ asset.text_content || asset.name || asset.asset_key }}
                          </div>
                          <!-- SVGA Asset -->
                          <div v-else-if="isSvgaAsset(asset)" class="svga-asset-container" :ref="el => { if(el) onSvgaMounted(asset, el) }" :data-url="asset.file_url || asset.url"></div>
                          <img v-else-if="asset.file_url || asset.url" :src="asset.file_url || asset.url" draggable="false" />
                          <span v-else class="asset-placeholder-small">{{ asset.name }}</span>
                        </div>
                      </div>
                    </div>
                  </template>
                  
                  <!-- Normal children (non-infinite scroll) -->
                  <template v-else>
                    <div
                      v-for="child in widget.children || []"
                      :key="child.id"
                      class="placed-child"
                      :class="{ 'selected': selectedWidgetId === widget.id && selectedChildId === child.theme_child_id, 'layout-child': widget.layout_mode !== 'absolute' }"
                      :style="getChildStyleInWidget(child, widget)"
                      @mousedown.stop="startDragPlacedChild($event, child, widget)"
                      @click.stop="selectPlacedChild(child, widget)"
                    >
                      <div class="child-label-small">{{ child.name }}</div>
                      
                      <!-- Assets inside child -->
                      <div
                        v-for="asset in child.assets || []"
                        :key="asset.id"
                        class="placed-asset"
                        :class="{ 'selected': selectedAssetId === asset.id, 'text-asset': isTextAsset(asset), 'svga-asset': isSvgaAsset(asset) }"
                        :style="getAssetStyle(asset)"
                        @mousedown.stop="startDragPlacedAsset($event, child, asset, widget)"
                        @click.stop="selectPlacedAsset(asset, widget, child)"
                      >
                        <div v-if="isTextAsset(asset)" class="text-asset-content" :style="getTextAssetStyle(asset)">
                          {{ asset.text_content || asset.name || asset.asset_key }}
                        </div>
                        <!-- SVGA Asset -->
                        <div v-else-if="isSvgaAsset(asset)" class="svga-asset-container" :ref="el => { if(el) onSvgaMounted(asset, el) }" :data-url="asset.file_url || asset.url"></div>
                        <img v-else-if="asset.file_url || asset.url" :src="asset.file_url || asset.url" draggable="false" @error="onAssetImageError($event, asset)" />
                        <span v-else class="asset-placeholder-small">{{ asset.name }}</span>
                        
                        <!-- Asset resize handles -->
                        <div v-if="selectedAssetId === asset.id" class="resize-handles">
                          <div class="resize-handle se" @mousedown.stop="startResize($event, 'asset', asset, 'se')"></div>
                          <div class="resize-handle sw" @mousedown.stop="startResize($event, 'asset', asset, 'sw')"></div>
                          <div class="resize-handle ne" @mousedown.stop="startResize($event, 'asset', asset, 'ne')"></div>
                          <div class="resize-handle nw" @mousedown.stop="startResize($event, 'asset', asset, 'nw')"></div>
                        </div>
                      </div>
                      
                      <!-- Child resize handles -->
                      <div v-if="selectedWidgetId === widget.id && selectedChildId === child.theme_child_id" class="resize-handles">
                        <div class="resize-handle se" @mousedown.stop="startResize($event, 'child', child, 'se')"></div>
                        <div class="resize-handle sw" @mousedown.stop="startResize($event, 'child', child, 'sw')"></div>
                        <div class="resize-handle ne" @mousedown.stop="startResize($event, 'child', child, 'ne')"></div>
                        <div class="resize-handle nw" @mousedown.stop="startResize($event, 'child', child, 'nw')"></div>
                      </div>
                    </div>
                  </template>
                  
                  <!-- Scroll Indicator -->
                  <div v-if="widget.layout_mode === 'horizontal' && (widget.children || []).length > 0 && !widget.infinite_scroll" class="scroll-indicator horizontal">
                    ← مرر →
                  </div>
                  
                  <!-- Infinite scroll indicator -->
                  <div v-if="widget.infinite_scroll" class="infinite-scroll-badge" :class="{ 'auto': widget.auto_scroll }">
                    {{ widget.auto_scroll ? '∞ تلقائي' : '∞ يدوي' }}
                  </div>
                </div>
                
                <!-- Widget resize handles -->
                <div v-if="selectedWidgetId === widget.id" class="resize-handles">
                  <div class="resize-handle se" @mousedown.stop="startResize($event, 'widget', widget, 'se')"></div>
                  <div class="resize-handle sw" @mousedown.stop="startResize($event, 'widget', widget, 'sw')"></div>
                  <div class="resize-handle ne" @mousedown.stop="startResize($event, 'widget', widget, 'ne')"></div>
                  <div class="resize-handle nw" @mousedown.stop="startResize($event, 'widget', widget, 'nw')"></div>
                </div>
              </div>
              
              <!-- Drop indicator -->
              <div v-if="isDraggingOver" class="drop-indicator">
                أفلت الويدجت هنا
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
            <button @click="showDesignSettings = !showDesignSettings" class="control-btn settings-toggle" :class="{ 'active': showDesignSettings }" title="إعدادات التصميم">
              ⚙️
            </button>
          </div>
          
          <!-- Collapsible Settings Panel removed - settings are in left panel -->
        </div>

        <!-- Right Panel - Properties -->
        <div class="right-panel">
          <!-- Selected Element Properties -->
          <div v-if="selectedElement" class="panel-section properties-section full-height">
            <div class="section-header">
              <h3 class="section-title">⚙️ خصائص {{ getElementTypeName() }}</h3>
              <span class="element-badge">{{ selectedElement.name || selectedElement.display_name || selectedElement.widget_key }}</span>
            </div>
            <div class="properties-form scrollable">
              
              <!-- Tabs for properties sections -->
              <div class="property-tabs">
                <button @click="activePropertyTab = 'position'" class="property-tab" :class="{ active: activePropertyTab === 'position' }">📍 الموضع</button>
                <button @click="activePropertyTab = 'style'" class="property-tab" :class="{ active: activePropertyTab === 'style' }">🎨 المظهر</button>
                <button @click="activePropertyTab = 'advanced'" class="property-tab" :class="{ active: activePropertyTab === 'advanced' }">⚡ متقدم</button>
              </div>
              
              <!-- Position Tab -->
              <div v-show="activePropertyTab === 'position'" class="property-tab-content">
                <div class="property-section-title">📐 الأبعاد</div>
                <div class="property-row">
                  <div class="property-group half">
                    <label>العرض <span class="unit">px</span></label>
                    <input type="number" v-model.number="selectedElement.width" @change="onPropertyChange" class="property-input" />
                  </div>
                  <div class="property-group half">
                    <label>الارتفاع <span class="unit">px</span></label>
                    <input type="number" v-model.number="selectedElement.height" @change="onPropertyChange" class="property-input" />
                  </div>
                </div>
                
                <div class="property-section-title">📍 الموقع</div>
                <div class="property-row">
                  <div class="property-group half">
                    <label>X <span class="unit">px</span></label>
                    <input type="number" v-model.number="selectedElement.x" @change="onPropertyChange" class="property-input" />
                  </div>
                  <div class="property-group half">
                    <label>Y <span class="unit">px</span></label>
                    <input type="number" v-model.number="selectedElement.y" @change="onPropertyChange" class="property-input" />
                  </div>
                </div>
                
                <div class="property-section-title">🔄 التحويل</div>
                <div class="property-row">
                  <div class="property-group half">
                    <label>الدوران <span class="unit">°</span></label>
                    <input type="number" v-model.number="selectedElement.rotation" min="-360" max="360" @change="onPropertyChange" class="property-input" />
                  </div>
                  <div class="property-group half">
                    <label>التكبير <span class="unit">x</span></label>
                    <input type="number" v-model.number="selectedElement.scale" min="0.1" max="5" step="0.1" @change="onPropertyChange" class="property-input" />
                  </div>
                </div>
                
                <div class="property-group">
                  <label>الترتيب (Z-Index)</label>
                  <input type="number" v-model.number="selectedElement.z_index" @change="onPropertyChange" class="property-input" />
                </div>
              </div>
              
              <!-- Style Tab -->
              <div v-show="activePropertyTab === 'style'" class="property-tab-content">
                <!-- Opacity Slider -->
                <div class="property-group">
                  <label>الشفافية <span class="value-badge">{{ Math.round((selectedElement.opacity || 1) * 100) }}%</span></label>
                  <div class="slider-container">
                    <input type="range" min="0" max="1" step="0.05" v-model.number="selectedElement.opacity" @input="onPropertyChange" class="slider-input" />
                  </div>
                </div>
                
                <!-- Debug: Show element type info -->
                <div v-if="selectedElementType === 'asset'" class="property-group" style="background: #1e293b; padding: 8px; border-radius: 6px; margin-bottom: 8px;">
                  <div style="font-size: 11px; color: #94a3b8;">
                    نوع العنصر: <strong style="color: #22d3ee;">{{ selectedElementType }}</strong> |
                    نوع الأصل: <strong style="color: #a78bfa;">{{ selectedElement.type || 'غير محدد' }}</strong> |
                    نص؟: <strong :style="{ color: isTextAsset(selectedElement) ? '#4ade80' : '#f87171' }">{{ isTextAsset(selectedElement) ? 'نعم ✓' : 'لا ✗' }}</strong>
                  </div>
                </div>
                
                <!-- Text Color (for text assets) -->
                <div v-if="selectedElementType === 'asset' && isTextAsset(selectedElement)" class="property-group">
                  <label>🎨 لون النص</label>
                  <div class="color-picker-row">
                    <input type="color" :value="getColorValue(selectedElement.text_color || '#ffffff')" @input="selectedElement.text_color = $event.target.value; onPropertyChange()" class="color-picker" />
                    <input type="text" v-model="selectedElement.text_color" @change="onPropertyChange" class="property-input flex-1" placeholder="#ffffff" />
                  </div>
                </div>
                
                <!-- Font Size (for text assets) -->
                <div v-if="selectedElementType === 'asset' && isTextAsset(selectedElement)" class="property-group">
                  <label>📏 حجم الخط <span class="unit">px</span></label>
                  <input type="number" v-model.number="selectedElement.font_size" min="8" max="100" @change="onPropertyChange" class="property-input" placeholder="14" />
                </div>
                
                <!-- Font Weight (for text assets) -->
                <div v-if="selectedElementType === 'asset' && isTextAsset(selectedElement)" class="property-group">
                  <label>🔤 سُمك الخط</label>
                  <select v-model="selectedElement.font_weight" @change="onPropertyChange" class="property-input">
                    <option value="normal">عادي</option>
                    <option value="bold">عريض</option>
                    <option value="100">100</option>
                    <option value="200">200</option>
                    <option value="300">300</option>
                    <option value="400">400</option>
                    <option value="500">500</option>
                    <option value="600">600</option>
                    <option value="700">700</option>
                    <option value="800">800</option>
                    <option value="900">900</option>
                  </select>
                </div>
                
                <!-- Text Content (for text assets) -->
                <div v-if="selectedElementType === 'asset' && isTextAsset(selectedElement)" class="property-group">
                  <label>📝 محتوى النص</label>
                  <textarea v-model="selectedElement.text_content" @change="onPropertyChange" class="property-textarea" rows="3" placeholder="أدخل النص هنا..."></textarea>
                </div>
                
                <!-- Font Family (for text assets) -->
                <div v-if="selectedElementType === 'asset' && isTextAsset(selectedElement)" class="property-group">
                  <label>🔠 عائلة الخط</label>
                  <select v-model="selectedElement.font_family" @change="onPropertyChange" class="property-input">
                    <option value="inherit">افتراضي</option>
                    <option value="Arial, sans-serif">Arial</option>
                    <option value="'Helvetica Neue', Helvetica, sans-serif">Helvetica</option>
                    <option value="'Times New Roman', Times, serif">Times New Roman</option>
                    <option value="Georgia, serif">Georgia</option>
                    <option value="'Courier New', Courier, monospace">Courier New</option>
                    <option value="Verdana, Geneva, sans-serif">Verdana</option>
                    <option value="'Trebuchet MS', sans-serif">Trebuchet MS</option>
                    <option value="'Cairo', sans-serif">Cairo (عربي)</option>
                    <option value="'Tajawal', sans-serif">Tajawal (عربي)</option>
                    <option value="'Amiri', serif">Amiri (عربي)</option>
                    <option value="'Noto Sans Arabic', sans-serif">Noto Sans Arabic</option>
                  </select>
                </div>
                
                <!-- Text Alignment (for text assets) -->
                <div v-if="selectedElementType === 'asset' && isTextAsset(selectedElement)" class="property-group">
                  <label>↔️ محاذاة النص</label>
                  <div class="text-align-buttons">
                    <button @click="selectedElement.text_align = 'right'; onPropertyChange()" class="align-btn" :class="{ active: selectedElement.text_align === 'right' }" title="يمين">
                      ➡️
                    </button>
                    <button @click="selectedElement.text_align = 'center'; onPropertyChange()" class="align-btn" :class="{ active: selectedElement.text_align === 'center' || !selectedElement.text_align }" title="وسط">
                      ↔️
                    </button>
                    <button @click="selectedElement.text_align = 'left'; onPropertyChange()" class="align-btn" :class="{ active: selectedElement.text_align === 'left' }" title="يسار">
                      ⬅️
                    </button>
                  </div>
                </div>
                
                <!-- Line Height (for text assets) -->
                <div v-if="selectedElementType === 'asset' && isTextAsset(selectedElement)" class="property-group">
                  <label>📐 ارتفاع السطر <span class="value-badge">{{ selectedElement.line_height || 1.4 }}</span></label>
                  <div class="slider-container">
                    <input type="range" min="0.8" max="3" step="0.1" v-model.number="selectedElement.line_height" @input="onPropertyChange" class="slider-input" />
                  </div>
                </div>
                
                <!-- Letter Spacing (for text assets) -->
                <div v-if="selectedElementType === 'asset' && isTextAsset(selectedElement)" class="property-group">
                  <label>🔤 المسافة بين الحروف <span class="unit">px</span></label>
                  <input type="number" v-model.number="selectedElement.letter_spacing" min="-5" max="20" step="0.5" @change="onPropertyChange" class="property-input" placeholder="0" />
                </div>
                
                <!-- Text Shadow (for text assets) -->
                <div v-if="selectedElementType === 'asset' && isTextAsset(selectedElement)" class="property-group">
                  <label>🌫️ ظل النص</label>
                  <div class="text-shadow-controls">
                    <div class="property-row">
                      <div class="property-group half">
                        <label>X</label>
                        <input type="number" v-model.number="textShadowX" @change="updateTextShadow" class="property-input" placeholder="0" />
                      </div>
                      <div class="property-group half">
                        <label>Y</label>
                        <input type="number" v-model.number="textShadowY" @change="updateTextShadow" class="property-input" placeholder="0" />
                      </div>
                    </div>
                    <div class="property-row">
                      <div class="property-group half">
                        <label>Blur</label>
                        <input type="number" v-model.number="textShadowBlur" min="0" @change="updateTextShadow" class="property-input" placeholder="0" />
                      </div>
                      <div class="property-group half">
                        <label>اللون</label>
                        <input type="color" v-model="textShadowColor" @input="updateTextShadow" class="color-picker full-width" />
                      </div>
                    </div>
                    <button @click="clearTextShadow" class="clear-shadow-btn">✕ إزالة الظل</button>
                  </div>
                </div>
                
                <!-- Text Decoration (for text assets) -->
                <div v-if="selectedElementType === 'asset' && isTextAsset(selectedElement)" class="property-group">
                  <label>✨ زخرفة النص</label>
                  <select v-model="selectedElement.text_decoration" @change="onPropertyChange" class="property-input">
                    <option value="none">بدون</option>
                    <option value="underline">تحته خط</option>
                    <option value="line-through">خط في المنتصف</option>
                    <option value="overline">خط فوقه</option>
                  </select>
                </div>
                
                <!-- Text Transform (for text assets) -->
                <div v-if="selectedElementType === 'asset' && isTextAsset(selectedElement)" class="property-group">
                  <label>🔡 تحويل النص</label>
                  <select v-model="selectedElement.text_transform" @change="onPropertyChange" class="property-input">
                    <option value="none">بدون</option>
                    <option value="uppercase">أحرف كبيرة</option>
                    <option value="lowercase">أحرف صغيرة</option>
                    <option value="capitalize">أول حرف كبير</option>
                  </select>
                </div>
                
                <!-- Background Color (for children) -->
                <div v-if="selectedElementType === 'child'" class="property-group">
                  <label>لون الخلفية</label>
                  <div class="color-picker-row">
                    <input type="color" :value="getColorValue(selectedElement.background_color)" @input="selectedElement.background_color = $event.target.value; onPropertyChange()" class="color-picker" />
                    <input type="text" v-model="selectedElement.background_color" @change="onPropertyChange" class="property-input flex-1" placeholder="transparent" />
                    <button @click="selectedElement.background_color = 'transparent'; onPropertyChange()" class="clear-btn" title="شفاف">✕</button>
                  </div>
                </div>
                
                <!-- Padding (for children) -->
                <div v-if="selectedElementType === 'child'" class="property-group">
                  <div class="property-section-title">📐 الحشو الداخلي (Padding)</div>
                  <div class="property-row">
                    <div class="property-group quarter">
                      <label>أعلى</label>
                      <input type="number" v-model.number="selectedElement.padding_top" min="0" @change="onPropertyChange" class="property-input" placeholder="0" />
                    </div>
                    <div class="property-group quarter">
                      <label>أسفل</label>
                      <input type="number" v-model.number="selectedElement.padding_bottom" min="0" @change="onPropertyChange" class="property-input" placeholder="0" />
                    </div>
                    <div class="property-group quarter">
                      <label>يمين</label>
                      <input type="number" v-model.number="selectedElement.padding_right" min="0" @change="onPropertyChange" class="property-input" placeholder="0" />
                    </div>
                    <div class="property-group quarter">
                      <label>يسار</label>
                      <input type="number" v-model.number="selectedElement.padding_left" min="0" @change="onPropertyChange" class="property-input" placeholder="0" />
                    </div>
                  </div>
                </div>
                
                <!-- Border -->
                <div class="property-section-title">🔲 الحدود</div>
                <div class="property-row">
                  <div class="property-group third">
                    <label>السمك</label>
                    <input type="number" v-model.number="selectedElement.border_width" min="0" max="20" @change="onPropertyChange" class="property-input" placeholder="0" />
                  </div>
                  <div class="property-group third">
                    <label>النمط</label>
                    <select v-model="selectedElement.border_style" @change="onPropertyChange" class="property-input">
                      <option value="solid">صلب</option>
                      <option value="dashed">متقطع</option>
                      <option value="dotted">منقط</option>
                      <option value="none">بدون</option>
                    </select>
                  </div>
                  <div class="property-group third">
                    <label>اللون</label>
                    <input type="color" :value="getColorValue(selectedElement.border_color)" @input="selectedElement.border_color = $event.target.value; onPropertyChange()" class="color-picker full-width" />
                  </div>
                </div>
                
                <!-- Border Radius - 4 Corners -->
                <div class="property-section-title">🔘 الزوايا الدائرية</div>
                <div class="corners-grid-modern">
                  <div class="corner-input-modern">
                    <span class="corner-label">↖</span>
                    <input type="number" v-model.number="selectedElement.border_radius_tl" min="0" max="200" @change="onPropertyChange" class="property-input" />
                  </div>
                  <div class="corner-input-modern">
                    <span class="corner-label">↗</span>
                    <input type="number" v-model.number="selectedElement.border_radius_tr" min="0" max="200" @change="onPropertyChange" class="property-input" />
                  </div>
                  <div class="corner-input-modern">
                    <span class="corner-label">↙</span>
                    <input type="number" v-model.number="selectedElement.border_radius_bl" min="0" max="200" @change="onPropertyChange" class="property-input" />
                  </div>
                  <div class="corner-input-modern">
                    <span class="corner-label">↘</span>
                    <input type="number" v-model.number="selectedElement.border_radius_br" min="0" max="200" @change="onPropertyChange" class="property-input" />
                  </div>
                </div>
                <div class="corner-sync-modern">
                  <input type="number" v-model.number="cornerSyncValue" min="0" max="200" class="property-input" placeholder="الكل" />
                  <button @click="syncCorners" class="sync-btn-modern" title="تطبيق على جميع الزوايا">🔗 توحيد</button>
                </div>
              </div>
              
              <!-- Advanced Tab -->
              <div v-show="activePropertyTab === 'advanced'" class="property-tab-content">
                <!-- Make asset as background (for assets only) -->
                <div v-if="selectedElementType === 'asset'" class="property-group">
                  <label class="toggle-label">
                    <input type="checkbox" v-model="selectedElement.is_background" @change="setAssetAsBackground" class="toggle-input" />
                    <span class="toggle-slider"></span>
                    <span>جعله خلفية كاملة للابن</span>
                  </label>
                  <p class="property-hint">سيملأ الأصل كامل مساحة الابن</p>
                </div>
                
                <!-- Visibility -->
                <div class="property-group">
                  <label class="toggle-label">
                    <input type="checkbox" v-model="selectedElement.is_visible" @change="onPropertyChange" class="toggle-input" />
                    <span class="toggle-slider"></span>
                    <span>مرئي</span>
                  </label>
                </div>
                
                <!-- Quick Actions -->
                <div class="property-section-title">⚡ إجراءات سريعة</div>
                <div class="quick-actions-grid">
                  <button @click="resetElementTransform" class="quick-action-btn-modern">↺ إعادة تعيين</button>
                  <button @click="fitToScreen" class="quick-action-btn-modern">📐 ملء</button>
                  <button @click="centerElement" class="quick-action-btn-modern">⊕ توسيط</button>
                  <button @click="duplicateSelected" class="quick-action-btn-modern">📋 نسخ</button>
                </div>
              </div>
            </div>
          </div>
          
          <div v-else class="panel-section full-height">
            <div class="empty-properties-modern">
              <div class="empty-icon-container">
                <span class="empty-icon">👆</span>
              </div>
              <h4>لم يتم تحديد عنصر</h4>
              <p>اختر طفلاً أو أصلاً من الشاشة لعرض خصائصه</p>
              
              <!-- Quick stats -->
              <div class="quick-stats" v-if="placedChildren.length > 0">
                <div class="stat-item">
                  <span class="stat-value">{{ placedChildren.length }}</span>
                  <span class="stat-label">طفل</span>
                </div>
                <div class="stat-item">
                  <span class="stat-value">{{ totalAssets }}</span>
                  <span class="stat-label">أصل</span>
                </div>
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
import { designerApi, configurationsApi } from '../services/api';
import VirtualScroller from 'vue3-virtual-scroller';
import 'vue3-virtual-scroller/dist/vue3-virtual-scroller.css';

export default {
  name: 'VisualDesigner',
  components: {
    VirtualScroller
  },
  props: {
    widget: {
      type: Object,
      default: null
    },
    widgets: {
      type: Array,
      default: () => []
    },
    themes: {
      type: Array,
      default: () => []
    },
    configurationId: {
      type: [Number, String],
      default: null
    },
    screenId: {
      type: [Number, String],
      default: null
    },
    screen: {
      type: Object,
      default: null
    },
    screenOverrides: {
      type: Array,
      default: () => []
    }
  },
  emits: ['close', 'save'],
  data() {
    return {
      isOpen: false,
      selectedThemeId: null,
      selectedChildId: null,
      selectedAssetId: null,
      selectedElementType: null, // 'widget' | 'child' | 'asset'
      selectedWidgetId: null, // الويدجت المختار حالياً
      placedWidgets: [], // كل الويدجتات الموضوعة في الشاشة
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
      screenBackgroundColor: '#f5f5f5', // لون خلفية الشاشة
      
      // Layout settings
      layoutMode: 'absolute', // 'absolute' | 'horizontal' | 'vertical'
      layoutGap: 16, // Gap between children in flex layout
      layoutPadding: 16, // Padding for the container
      layoutItemWidth: 150, // Default item width in horizontal mode
      layoutItemHeight: 200, // Default item height in horizontal mode
      layoutSnapToGrid: false, // Snap children to item dimensions
      layoutShowScrollIndicator: true, // Show scroll indicator in preview
      
      // Corner sync value
      cornerSyncValue: 0,
      
      // Active property tab
      activePropertyTab: 'position',
      
      // Show design settings panel
      showDesignSettings: false,
      
      // Auto save timeout
      autoSaveTimeout: null,
      
      // Text Shadow controls
      textShadowX: 0,
      textShadowY: 0,
      textShadowBlur: 0,
      textShadowColor: '#000000',
      
      // SVGA tracking
      svgaLoadedMap: {}, // Track loaded SVGA players by asset ID
      svgaPlayersMap: {}, // Store SVGA player instances
      svgaLibraryLoaded: false, // Track if SVGA library is loaded
    };
  },
  computed: {
    widgetName() {
      return this.selectedWidgetData?.display_name || this.selectedWidgetData?.widget_key || 'اختر ويدجت';
    },
    // حساب ارتفاع الشاشة الديناميكي بناءً على الويدجات
    dynamicScreenHeight() {
      if (!this.placedWidgets || this.placedWidgets.length === 0) {
        return this.screenHeight;
      }
      
      // حساب أقصى نقطة يصل إليها أي ويدجت
      let maxY = 0;
      for (const widget of this.placedWidgets) {
        const widgetBottom = (widget.y || 0) + (widget.height || 100);
        if (widgetBottom > maxY) {
          maxY = widgetBottom;
        }
      }
      
      // إضافة هامش إضافي للأسفل (100px)
      const calculatedHeight = maxY + 100;
      
      // الحد الأدنى هو الارتفاع الافتراضي
      return Math.max(this.screenHeight, calculatedHeight);
    },
    // بيانات الويدجت المختار من placedWidgets
    selectedWidgetData() {
      return this.placedWidgets.find(w => w.id === this.selectedWidgetId);
    },
    // الويدجت المختار (للتوافق)
    selectedWidget() {
      return this.selectedWidgetData;
    },
    // كل الويدجتات المتاحة (من props أو widget واحد)
    availableWidgets() {
      if (this.widgets && this.widgets.length > 0) {
        return this.widgets;
      }
      return this.widget ? [this.widget] : [];
    },
    selectedTheme() {
      // مقارنة مرنة للتعامل مع اختلاف الأنواع (string vs number)
      return this.themes.find(t => String(t.id) === String(this.selectedThemeId));
    },
    // أبناء الويدجت المختار (من الثيم المختار أو من الويدجت نفسه)
    themeChildren() {
      if (!this.selectedWidgetData) return [];
      
      const selectedWidget = this.selectedWidgetData;
      
      // أولاً: حاول الحصول على الأبناء من الثيم الخاص بهذا الويدجت
      const themeId = selectedWidget.theme_id || this.selectedThemeId;
      const theme = this.themes.find(t => String(t.id) === String(themeId));
      if (theme?.children && theme.children.length > 0) {
        return theme.children;
      }
      
      // ثانياً: حاول من أبناء الويدجت الموضوعة (placedWidgets.children)
      if (selectedWidget.children && selectedWidget.children.length > 0) {
        return selectedWidget.children.map(c => ({
          id: c.theme_child_id,
          label: c.name || c.child_key,
          child_key: c.child_key,
          width: c.width,
          height: c.height,
          assets: c.assets || []
        }));
      }
      
      // ثالثاً: حاول من الويدجت الأصلي المطابق في availableWidgets
      const originalWidget = this.availableWidgets.find(w => w.id === selectedWidget.id);
      if (originalWidget?.settings?.children && originalWidget.settings.children.length > 0) {
        return originalWidget.settings.children.map(c => ({
          id: c.theme_child_id || c.id,
          label: c.name || c.child_key,
          child_key: c.child_key,
          width: c.width,
          height: c.height,
          assets: c.assets || []
        }));
      }
      
      return [];
    },
    // الأبناء الموضوعة للويدجت المختار
    currentWidgetChildren() {
      return this.selectedWidgetData?.children || [];
    },
    selectedChild() {
      // أولاً: حاول إيجاد الطفل من themeChildren
      const themeChild = this.themeChildren.find(c => c.id === this.selectedChildId);
      if (themeChild) return themeChild;
      
      // ثانياً: حاول من selectedWidgetData.children
      const widget = this.selectedWidgetData;
      if (widget) {
        const placedChild = (widget.children || []).find(c => c.theme_child_id === this.selectedChildId);
        if (placedChild) {
          return {
            id: placedChild.theme_child_id,
            label: placedChild.name || placedChild.child_key,
            child_key: placedChild.child_key,
            assets: placedChild.assets || []
          };
        }
      }
      return null;
    },
    selectedElement() {
      if (this.selectedElementType === 'widget') {
        return this.selectedWidgetData;
      } else if (this.selectedElementType === 'child') {
        const widget = this.selectedWidgetData;
        if (!widget) return null;
        
        // البحث في أبناء الويدجت الموضوعة
        let foundChild = (widget.children || []).find(c => c.theme_child_id === this.selectedChildId);
        
        // إذا لم يوجد، أضف الطفل للويدجت من themeChildren
        if (!foundChild) {
          const themeChild = this.themeChildren.find(c => c.id === this.selectedChildId);
          if (themeChild) {
            const newChild = {
              id: `child_${themeChild.id}_${Date.now()}`,
              theme_child_id: themeChild.id,
              name: themeChild.label || themeChild.child_key,
              child_key: themeChild.child_key,
              width: themeChild.width || 80,
              height: themeChild.height || 80,
              x: 0,
              y: 0,
              opacity: 1,
              z_index: (widget.children || []).length,
              is_visible: true,
              rotation: 0,
              scale: 1,
              background_color: 'transparent',
              border_width: 0,
              border_style: 'solid',
              border_color: 'transparent',
              border_radius_tl: 0,
              border_radius_tr: 0,
              border_radius_bl: 0,
              border_radius_br: 0,
              assets: this.buildChildAssets([], themeChild.assets || themeChild.theme?.assets || [])
            };
            // إضافة الطفل للويدجت
            if (!widget.children) widget.children = [];
            widget.children.push(newChild);
            foundChild = newChild;
            this.hasUnsavedChanges = true;
          }
        }
        
        return foundChild;
      } else if (this.selectedElementType === 'asset') {
        const widget = this.selectedWidgetData;
        if (!widget) return null;
        for (const child of widget.children || []) {
          const asset = (child.assets || []).find(a => a.id === this.selectedAssetId);
          if (asset) {
            return asset;
          }
        }
      }
      return null;
    },
    canUndo() {
      return this.historyIndex > 0;
    },
    canRedo() {
      return this.historyIndex < this.history.length - 1;
    },
    totalAssets() {
      let total = 0;
      for (const widget of this.placedWidgets) {
        for (const child of widget.children || []) {
          total += (child.assets || []).length;
        }
      }
      return total;
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
    // دعم اللمس للسحب والإفلات
    if (this.$refs.mobileScreen) {
      this.$refs.mobileScreen.addEventListener('touchstart', this.onTouchStart, { passive: false });
      this.$refs.mobileScreen.addEventListener('touchmove', this.onTouchMove, { passive: false });
      this.$refs.mobileScreen.addEventListener('touchend', this.onTouchEnd, { passive: false });
    }
    // تحميل مكتبة SVGA إذا لم تكن محملة
    this.loadSvgaLibrary().catch(err => {
      console.warn('⚠️ Failed to load SVGA library:', err);
    });
  },
  beforeUnmount() {
    document.removeEventListener('mousemove', this.onMouseMove);
    document.removeEventListener('mouseup', this.onMouseUp);
    document.removeEventListener('keydown', this.onKeyDown);
    if (this.$refs.mobileScreen) {
      this.$refs.mobileScreen.removeEventListener('touchstart', this.onTouchStart);
      this.$refs.mobileScreen.removeEventListener('touchmove', this.onTouchMove);
      this.$refs.mobileScreen.removeEventListener('touchend', this.onTouchEnd);
    }
    // Cleanup SVGA players
    this.cleanupSvgaPlayers();
  },
  methods: {
    open() {
      this.isOpen = true;
      this.initializeWidgets();
    },
    async close() {
      if (this.hasUnsavedChanges) {
        await this.saveDesign();
      }
      this.isOpen = false;
      this.$emit('close');
    },
    
    // Initialize all widgets
    initializeWidgets() {
      // Load screen background color from screenOverrides (tied to screen + configuration)
      let foundBackgroundColor = null;
      if (this.screenId && this.screenOverrides) {
        const screenOverride = this.screenOverrides.find(so => so.screen_id === this.screenId);
        if (screenOverride?.background_color) {
          foundBackgroundColor = screenOverride.background_color;
        }
      }
      // Fallback to screen prop or default
      if (!foundBackgroundColor && this.screen?.background_color) {
        foundBackgroundColor = this.screen.background_color;
      }
      if (foundBackgroundColor) {
        this.screenBackgroundColor = foundBackgroundColor;
      }
      
      // Load placed widgets from configuration or create default positions
      // Calculate cumulative Y position for stacking widgets vertically
      let cumulativeY = 10;
      this.placedWidgets = this.availableWidgets.map((widget, index) => {
        const settings = widget.settings || {};
        const themeId = settings.theme_id || widget.selected_theme_id || widget.widget_theme_id;
        const theme = this.themes.find(t => String(t.id) === String(themeId));
        
        // Calculate widget height
        const widgetHeight = settings.height || settings.widget_height || theme?.widget_height || 140;
        const currentY = settings.y ?? cumulativeY;
        
        // Update cumulative Y for next widget (add gap of 10px)
        if (settings.y === undefined || settings.y === null) {
          cumulativeY += widgetHeight + 10;
        }
        
        return {
          id: widget.id,
          widget_key: widget.widget_key,
          display_name: widget.display_name || widget.widget_key,
          theme_id: themeId,
          // Position & Size - stack vertically
          x: settings.x ?? 10,
          y: currentY,
          width: settings.width || settings.widget_width || theme?.widget_width || 120,
          height: settings.height || settings.widget_height || theme?.widget_height || 140,
          // Layout settings
          layout_mode: settings.layout_mode || 'absolute',
          layout_gap: settings.layout_gap ?? 8,
          layout_padding: settings.layout_padding ?? 8,
          child_width: settings.child_width || settings.layout_item_width || 80,
          child_height: settings.child_height || settings.layout_item_height || 100,
          // infinite_scroll يكون true افتراضياً للتخطيط الأفقي/العمودي
          infinite_scroll: settings.infinite_scroll ?? ((settings.layout_mode || 'absolute') !== 'absolute'),
          auto_scroll: settings.auto_scroll ?? false,
          scroll_speed: settings.scroll_speed ?? 3,
          // Style
          opacity: settings.opacity ?? 1,
          z_index: settings.z_index ?? index,
          background_color: settings.background_color || 'transparent',
          border_radius: settings.border_radius ?? 8,
          // Widget padding
          padding_top: settings.padding_top ?? 0,
          padding_bottom: settings.padding_bottom ?? 0,
          padding_right: settings.padding_right ?? 0,
          padding_left: settings.padding_left ?? 0,
          // Children - load from settings if available, otherwise load from theme or widget
          children: this.buildWidgetChildren(settings.children, theme, widget)
        };
      });
      
      // Select first widget if available
      if (this.placedWidgets.length > 0) {
        this.selectedWidgetId = this.placedWidgets[0].id;
      }
      
      // Save initial state to history
      this.saveToHistory();
    },
    
    // Build widget children from settings or theme
    buildWidgetChildren(savedChildren, theme, originalWidget = null) {
      const themeChildren = theme?.children || [];
      const savedChildrenMap = new Map();
      
      // Create a map of saved children by theme_child_id
      (savedChildren || []).forEach(child => {
        savedChildrenMap.set(child.theme_child_id, child);
      });
      
      // تحديد مصدر الأبناء بترتيب الأولوية
      let childrenSource = [];
      
      if (savedChildren && savedChildren.length > 0) {
        // 1. الأبناء المحفوظة
        childrenSource = savedChildren;
      } else if (themeChildren.length > 0) {
        // 2. أبناء الثيم
        childrenSource = themeChildren.map(tc => ({ theme_child_id: tc.id }));
      } else if (originalWidget?.settings?.children && originalWidget.settings.children.length > 0) {
        // 3. أبناء الويدجت الأصلي
        childrenSource = originalWidget.settings.children;
      }
      
      return childrenSource.map((child, index) => {
        const themeChildId = child.theme_child_id || child.id;
        const themeChild = themeChildren.find(c => c.id === themeChildId);
        const savedChild = savedChildrenMap.get(themeChildId);
        
        // Merge saved child data with theme child defaults
        const mergedChild = savedChild || child;
        
        // الحصول على الأصول من مصادر متعددة
        let childAssets = mergedChild.assets;
        if (!childAssets || childAssets.length === 0) {
          childAssets = themeChild?.assets || themeChild?.theme?.assets || [];
        }
        
        return {
          id: mergedChild.id || `child_${themeChildId}`,
          theme_child_id: themeChildId,
          name: themeChild?.label || themeChild?.child_key || mergedChild.name || mergedChild.child_key,
          child_key: themeChild?.child_key || mergedChild.child_key,
          width: mergedChild.width ?? themeChild?.width ?? 80,
          height: mergedChild.height ?? themeChild?.height ?? 80,
          x: mergedChild.x ?? themeChild?.x ?? (index * 10),
          y: mergedChild.y ?? themeChild?.y ?? (index * 10),
          opacity: mergedChild.opacity ?? themeChild?.opacity ?? 1,
          z_index: mergedChild.z_index ?? themeChild?.z_index ?? index,
          is_visible: mergedChild.is_visible ?? themeChild?.is_visible ?? true,
          rotation: mergedChild.rotation ?? themeChild?.rotation ?? 0,
          scale: mergedChild.scale ?? themeChild?.scale ?? 1,
          background_color: mergedChild.background_color ?? themeChild?.background_color ?? 'transparent',
          border_width: mergedChild.border_width ?? themeChild?.border_width ?? 0,
          border_style: mergedChild.border_style ?? themeChild?.border_style ?? 'solid',
          border_color: mergedChild.border_color ?? themeChild?.border_color ?? 'transparent',
          border_radius_tl: mergedChild.border_radius_tl ?? themeChild?.border_radius_tl ?? 0,
          border_radius_tr: mergedChild.border_radius_tr ?? themeChild?.border_radius_tr ?? 0,
          border_radius_bl: mergedChild.border_radius_bl ?? themeChild?.border_radius_bl ?? 0,
          border_radius_br: mergedChild.border_radius_br ?? themeChild?.border_radius_br ?? 0,
          padding_top: mergedChild.padding_top ?? themeChild?.padding_top ?? 0,
          padding_bottom: mergedChild.padding_bottom ?? themeChild?.padding_bottom ?? 0,
          padding_right: mergedChild.padding_right ?? themeChild?.padding_right ?? 0,
          padding_left: mergedChild.padding_left ?? themeChild?.padding_left ?? 0,
          assets: this.buildChildAssets(childAssets, themeChild?.assets || themeChild?.theme?.assets)
        };
      });
    },
    
    // Build child assets from saved data or theme
    buildChildAssets(savedAssets, themeAssets) {
      // إنشاء خريطة للأصول من الثيم للحصول على type
      const themeAssetsMap = new Map();
      (themeAssets || []).forEach(ta => {
        themeAssetsMap.set(ta.id, ta);
      });
      
      const assetSource = savedAssets && savedAssets.length > 0 
        ? savedAssets 
        : (themeAssets || []).map(ta => ({
            id: ta.id,
            asset_key: ta.asset_key || ta.name,
            name: ta.name || ta.asset_key,
            file_url: ta.file_url || ta.file_path,
            url: ta.file_url || ta.file_path,
            asset_type: ta.asset_type || 'image',
            type: ta.type, // نص أو ملف - من قاعدة البيانات
            text: ta.text, // محتوى النص من قاعدة البيانات
            text_content: ta.text_content || ta.text
          }));
      
      return assetSource.map((asset, index) => {
        // الحصول على بيانات الأصل من الثيم (للحصول على type)
        const themeAsset = themeAssetsMap.get(asset.id);
        
        return {
          ...asset,
          id: asset.id,
          asset_key: asset.asset_key || asset.name,
          name: asset.name || asset.asset_key,
          file_url: asset.file_url || asset.url,
          url: asset.file_url || asset.url,
          // Read asset_type from multiple sources
          asset_type: asset.asset_type || 'image',
          // type field for text check (نص أو ملف) - جلب من themeAsset إذا غير موجود
          type: asset.type || themeAsset?.type,
          // Read text_content from multiple sources
          text_content: asset.text_content || asset.text || themeAsset?.text_content || themeAsset?.text,
          x: asset.x ?? 0,
          y: asset.y ?? 0,
          width: asset.width ?? 40,
          height: asset.height ?? 40,
          opacity: asset.opacity ?? 1,
          is_visible: asset.is_visible ?? true,
          is_background: asset.is_background ?? false,
          object_fit: asset.is_background ? 'cover' : (asset.object_fit ?? 'contain'),
          // Use index + 1 as default z_index to ensure assets are above background (z_index: 0)
          z_index: asset.z_index ?? (index + 1),
          rotation: asset.rotation ?? 0,
          scale: asset.scale ?? 1,
          border_width: asset.border_width ?? 0,
          border_style: asset.border_style ?? 'solid',
          border_color: asset.border_color ?? 'transparent',
          border_radius_tl: asset.border_radius_tl ?? 0,
          border_radius_tr: asset.border_radius_tr ?? 0,
          border_radius_bl: asset.border_radius_bl ?? 0,
          border_radius_br: asset.border_radius_br ?? 0,
          // Text styling properties
          text_color: asset.text_color || '#ffffff',
          font_size: asset.font_size ?? 14,
          font_weight: asset.font_weight || 'normal',
          font_family: asset.font_family || 'inherit',
          text_align: asset.text_align || 'center',
          line_height: asset.line_height ?? 1.4,
          letter_spacing: asset.letter_spacing ?? 0,
          text_shadow: asset.text_shadow || 'none',
          text_decoration: asset.text_decoration || 'none',
          text_transform: asset.text_transform || 'none',
        };
      });
    },
    
    // Get element type name for display
    getElementTypeName() {
      switch (this.selectedElementType) {
        case 'widget': return 'الويدجت';
        case 'child': return 'الطفل';
        case 'asset': return 'الأصل';
        default: return 'العنصر';
      }
    },
    
    // Get valid color value for color input (transparent is not valid)
    getColorValue(color) {
      if (!color || color === 'transparent' || color === '') {
        return '#000000';
      }
      return color;
    },
    
    // Widget methods
    isWidgetPlaced(widgetId) {
      return this.placedWidgets.some(w => w.id === widgetId);
    },
    
    selectWidget(widget) {
      this.selectedWidgetId = widget.id;
      this.selectedElementType = 'widget';
      this.selectedChildId = null;
      this.selectedAssetId = null;
      // تحديث selectedThemeId ليكون الثيم الخاص بهذا الويدجت
      if (widget.theme_id) {
        this.selectedThemeId = widget.theme_id;
      }
    },
    
    // عند اختيار ويدجت من القائمة
    onWidgetSelect(event) {
      const widgetId = parseInt(event.target.value);
      const widget = this.placedWidgets.find(w => w.id === widgetId);
      if (widget) {
        this.selectWidget(widget);
      }
    },
    
    // تغيير نوع التخطيط للويدجت المختار - لا يغير مواقع/أحجام الأبناء تلقائياً
    setWidgetLayout(mode) {
      if (!this.selectedWidgetData) return;
      this.selectedWidgetData.layout_mode = mode;
      // تفعيل التمرير اللانهائي تلقائياً للتخطيط الأفقي/العمودي
      if (mode !== 'absolute') {
        this.selectedWidgetData.infinite_scroll = true;
      }
      this.hasUnsavedChanges = true;
      // لا نستدعي repositionChildrenByLayout تلقائياً للحفاظ على الأحجام والمواقع
      this.saveToHistory();
    },
    
    // تغيير وضع التمرير (يدوي لانهائي/تلقائي)
    setScrollMode(mode) {
      if (!this.selectedWidgetData) return;
      this.selectedWidgetData.auto_scroll = (mode === 'auto');
      // التمرير دائماً لانهائي (يدوي أو تلقائي)
      this.selectedWidgetData.infinite_scroll = true;
      this.hasUnsavedChanges = true;
      this.saveToHistory();
    },
    
    // عند تغيير إعدادات الويدجت - لا يغير أحجام الأبناء تلقائياً
    onWidgetSettingChange() {
      if (!this.selectedWidgetData) return;
      this.hasUnsavedChanges = true;
      // لا نستدعي repositionChildrenByLayout تلقائياً - المستخدم يضغط الزر يدوياً إذا أراد
      this.saveToHistory();
    },
    
    // إعادة ترتيب الأبناء حسب نوع التخطيط - يغير المواقع فقط ويحافظ على الأحجام
    repositionChildrenByLayout(widget) {
      if (!widget || !widget.children || widget.children.length === 0) return;
      
      const padding = widget.layout_padding || 8;
      const gap = widget.layout_gap || 8;
      
      widget.children.forEach((child, index) => {
        // استخدام حجم الطفل الحالي أو الافتراضي من الويدجت
        const childWidth = child.width || widget.child_width || 80;
        const childHeight = child.height || widget.child_height || 100;
        
        if (widget.layout_mode === 'horizontal') {
          child.x = padding + index * (childWidth + gap);
          child.y = padding;
          // لا نغير الحجم - نحافظ على الحجم المحفوظ
        } else if (widget.layout_mode === 'vertical') {
          child.x = padding;
          child.y = padding + index * (childHeight + gap);
          // لا نغير الحجم - نحافظ على الحجم المحفوظ
        }
        // للتخطيط المطلق لا نغير المواقع
      });
      
      this.hasUnsavedChanges = true;
    },
    
    selectPlacedWidget(widget) {
      this.selectedWidgetId = widget.id;
      this.selectedElementType = 'widget';
      this.selectedChildId = null;
      this.selectedAssetId = null;
    },
    
    onWidgetDragStart(e, widget) {
      e.dataTransfer.setData('text/plain', JSON.stringify({ type: 'widget', widget }));
      this.dragType = 'new-widget';
      this.dragData = widget;
    },
    
    startDragWidget(e, widget) {
      this.isDragging = true;
      this.dragType = 'placed-widget';
      this.dragData = widget;
      this.dragStartX = e.clientX;
      this.dragStartY = e.clientY;
      this.dragStartElementX = widget.x;
      this.dragStartElementY = widget.y;
      this.selectPlacedWidget(widget);
    },
    
    // Widget styles
    getWidgetStyle(widget) {
      // Widget padding
      const paddingTop = (widget.padding_top || 0) * this.zoom + 'px';
      const paddingBottom = (widget.padding_bottom || 0) * this.zoom + 'px';
      const paddingRight = (widget.padding_right || 0) * this.zoom + 'px';
      const paddingLeft = (widget.padding_left || 0) * this.zoom + 'px';
      
      return {
        left: widget.x * this.zoom + 'px',
        top: widget.y * this.zoom + 'px',
        width: widget.width * this.zoom + 'px',
        height: widget.height * this.zoom + 'px',
        opacity: widget.opacity ?? 1,
        zIndex: widget.z_index || 0,
        backgroundColor: widget.background_color || 'transparent',
        borderRadius: (widget.border_radius || 8) + 'px',
        padding: `${paddingTop} ${paddingRight} ${paddingBottom} ${paddingLeft}`,
        boxSizing: 'border-box',
      };
    },
    
    getWidgetContentClass(widget) {
      return {
        'horizontal-scroll': widget.layout_mode === 'horizontal' && !widget.infinite_scroll,
        'vertical-scroll': widget.layout_mode === 'vertical',
        'infinite-scroll-wrapper': widget.layout_mode === 'horizontal' && widget.infinite_scroll,
      };
    },
    
    getWidgetContentStyle(widget) {
      if (widget.layout_mode === 'horizontal') {
        const baseStyle = {
          display: 'flex',
          flexDirection: 'row',
          gap: (widget.layout_gap || 8) * this.zoom + 'px',
          padding: (widget.layout_padding || 8) * this.zoom + 'px',
          height: '100%',
          alignItems: 'flex-start',
        };
        
        if (widget.infinite_scroll) {
          // Infinite scroll (slider) - no scrollbar, uses animation
          return {
            ...baseStyle,
            overflow: 'hidden',
            '--scroll-duration': ((widget.scroll_speed || 3) * (widget.children?.length || 3)) + 's',
          };
        } else {
          // Normal horizontal scroll
          return {
            ...baseStyle,
            overflowX: 'auto',
            overflowY: 'hidden',
          };
        }
      } else if (widget.layout_mode === 'vertical') {
        return {
          display: 'flex',
          flexDirection: 'column',
          gap: (widget.layout_gap || 8) * this.zoom + 'px',
          padding: (widget.layout_padding || 8) * this.zoom + 'px',
          overflowX: 'hidden',
          overflowY: 'auto',
          width: '100%',
        };
      }
      return {
        position: 'relative',
        width: '100%',
        height: '100%',
      };
    },
    
    getChildStyleInWidget(child, widget) {
      const isLayoutMode = widget.layout_mode && widget.layout_mode !== 'absolute';
      const isVisible = child.is_visible !== false;
      
      // الحواف الدائرية
      const borderRadius = `${child.border_radius_tl || 0}px ${child.border_radius_tr || 0}px ${child.border_radius_br || 0}px ${child.border_radius_bl || 0}px`;
      
      // الحشو الداخلي (Padding)
      const paddingTop = (child.padding_top || 0) * this.zoom + 'px';
      const paddingBottom = (child.padding_bottom || 0) * this.zoom + 'px';
      const paddingRight = (child.padding_right || 0) * this.zoom + 'px';
      const paddingLeft = (child.padding_left || 0) * this.zoom + 'px';
      
      // أنماط مشتركة
      const commonStyles = {
        opacity: child.opacity ?? 1,
        display: isVisible ? 'block' : 'none',
        backgroundColor: child.background_color || 'transparent',
        borderWidth: (child.border_width || 0) + 'px',
        borderStyle: child.border_style || 'solid',
        borderColor: child.border_color || 'transparent',
        borderRadius: borderRadius,
        transform: `rotate(${child.rotation || 0}deg) scale(${child.scale || 1})`,
        padding: `${paddingTop} ${paddingRight} ${paddingBottom} ${paddingLeft}`,
        boxSizing: 'border-box',
      };
      
      if (isLayoutMode) {
        // أولوية لحجم الطفل الفردي، ثم الحجم الافتراضي للويدجت
        const childWidth = child.width || widget.child_width || 80;
        const childHeight = child.height || widget.child_height || 80;
        return {
          ...commonStyles,
          width: childWidth * this.zoom + 'px',
          height: childHeight * this.zoom + 'px',
          minWidth: childWidth * this.zoom + 'px',
          minHeight: childHeight * this.zoom + 'px',
          flexShrink: 0,
          position: 'relative',
        };
      }
      
      return {
        ...commonStyles,
        position: 'absolute',
        left: child.x * this.zoom + 'px',
        top: child.y * this.zoom + 'px',
        width: child.width * this.zoom + 'px',
        height: child.height * this.zoom + 'px',
        zIndex: child.z_index || 0,
      };
    },
    
    // حساب مدة الـ animation للسلايدر
    getScrollDuration(widget) {
      const childrenCount = (widget.children || []).length || 1;
      const speed = widget.scroll_speed || 3; // ثواني لكل عنصر
      return (speed * childrenCount) + 's';
    },
    
    // Widget layout methods
    setWidgetLayoutMode(mode) {
      if (!this.selectedWidget) return;
      this.selectedWidget.layout_mode = mode;
      this.hasUnsavedChanges = true;
      this.saveToHistory();
      
      if (mode !== 'absolute') {
        this.autoArrangeWidgetChildren();
      }
    },
    
    onWidgetLayoutChange() {
      this.hasUnsavedChanges = true;
      this.saveToHistory();
    },
    
    autoArrangeWidgetChildren() {
      if (!this.selectedWidget) return;
      const widget = this.selectedWidget;
      
      (widget.children || []).forEach((child, index) => {
        child.width = widget.child_width || 80;
        child.height = widget.child_height || 80;
        
        if (widget.layout_mode === 'horizontal') {
          child.x = index * (child.width + (widget.layout_gap || 8));
          child.y = 0;
        } else if (widget.layout_mode === 'vertical') {
          child.x = 0;
          child.y = index * (child.height + (widget.layout_gap || 8));
        }
      });
      
      this.hasUnsavedChanges = true;
      this.saveToHistory();
    },
    
    onWidgetScroll(e, widget) {
      // Allow natural scrolling in horizontal/vertical layout
      if (widget.layout_mode === 'absolute') {
        e.preventDefault();
        return;
      }
      
      // التمرير اليدوي اللانهائي
      if (widget.infinite_scroll && !widget.auto_scroll) {
        const track = e.currentTarget.querySelector('.infinite-scroll-track');
        if (track) {
          // الحصول على الإزاحة الحالية
          const currentTransform = track.style.transform || 'translateX(0px)';
          const match = currentTransform.match(/translateX\((-?\d+(?:\.\d+)?)px\)/);
          let currentX = match ? parseFloat(match[1]) : 0;
          
          // تطبيق حركة السكرول
          currentX -= e.deltaY || e.deltaX;
          
          // حساب عرض نصف المحتوى (المحتوى الأصلي)
          const halfWidth = track.scrollWidth / 2;
          
          // إعادة الإزاحة عند الوصول للنهاية (loop)
          if (currentX < -halfWidth) {
            currentX += halfWidth;
          } else if (currentX > 0) {
            currentX -= halfWidth;
          }
          
          track.style.transform = `translateX(${currentX}px)`;
          e.preventDefault();
        }
      }
    },
    
    // Check if child is placed in current widget
    isChildPlacedInWidget(childId) {
      if (!this.selectedWidget) return false;
      return (this.selectedWidget.children || []).some(c => c.theme_child_id === childId);
    },
    
    // Modified child methods to work with widgets
    selectPlacedChild(child, widget) {
      if (widget) {
        this.selectedWidgetId = widget.id;
      }
      this.selectedChildId = child.theme_child_id;
      this.selectedElementType = 'child';
      this.selectedAssetId = null;
    },
    
    selectPlacedAsset(asset, widget, child) {
      if (widget) {
        this.selectedWidgetId = widget.id;
      }
      if (child) {
        this.selectedChildId = child.theme_child_id;
      }
      this.selectedAssetId = asset.id;
      this.selectedElementType = 'asset';
      
      // Parse text shadow if it's a text asset
      if (this.isTextAsset(asset)) {
        this.parseTextShadow(asset.text_shadow);
      }
    },
    
    startDragPlacedChild(e, child, widget) {
      this.isDragging = true;
      this.dragType = 'placed-child';
      this.dragData = { child, widget };
      this.dragStartX = e.clientX;
      this.dragStartY = e.clientY;
      this.dragStartElementX = child.x;
      this.dragStartElementY = child.y;
      this.selectPlacedChild(child, widget);
    },
    
    startDragPlacedAsset(e, child, asset, widget) {
      this.isDragging = true;
      this.dragType = 'placed-asset';
      this.dragData = { child, asset, widget };
      this.dragStartX = e.clientX;
      this.dragStartY = e.clientY;
      this.dragStartElementX = asset.x;
      this.dragStartElementY = asset.y;
      this.selectPlacedAsset(asset, widget, child);
    },
    
    initializeFromWidget() {
      // Legacy support - redirect to initializeWidgets
      this.initializeWidgets();
    },
    
    onKeyDown(e) {
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

      // Delete or Backspace to delete selected element
      if (e.key === 'Delete' || e.key === 'Backspace') {
        // Don't delete if user is typing in an input field
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.isContentEditable) {
          return;
        }
        e.preventDefault();
        this.deleteSelected();
        return;
      }

      // Escape to deselect
      if (e.key === 'Escape') {
        this.selectedWidgetId = null;
        this.selectedChildId = null;
        this.selectedAssetId = null;
        this.selectedElementType = null;
      }
    },
    onThemeChange() {
      // Clear selections when theme changes
      this.selectedChildId = null;
      this.selectedAssetId = null;
      this.selectedElementType = null;
    },
    isChildPlaced(childId) {
      const widget = this.selectedWidgetData;
      if (!widget) return false;
      return (widget.children || []).some(c => c.theme_child_id === childId);
    },
    selectChild(child) {
      this.selectedChildId = child.id;
      this.selectedAssetId = null;
      
      // If the child is in the widget, select it for editing properties
      const widget = this.selectedWidgetData;
      if (widget) {
        const placedChild = (widget.children || []).find(c => c.theme_child_id === child.id);
        if (placedChild) {
          this.selectedElementType = 'child';
        } else {
          // الطفل موجود في القائمة لكن غير موضوع، نضيفه تلقائياً
          this.selectedElementType = 'child';
        }
      }
    },
    selectAsset(asset) {
      this.selectedAssetId = asset.id;
    },
    // تم حذف selectPlacedChild و selectPlacedAsset المكررة - الدوال الصحيحة موجودة أعلاه
    
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
      const rect = this.$refs.mobileScreen.getBoundingClientRect();
      const x = Math.round((e.clientX - rect.left) / this.zoom);
      const y = Math.round((e.clientY - rect.top) / this.zoom);
      
      // Try to parse JSON data first
      try {
        const data = JSON.parse(e.dataTransfer.getData('text/plain'));
        if (data.type === 'widget') {
          this.placeWidget(data.widget, x, y);
          return;
        }
      } catch (err) {
        // Not JSON, try legacy format
      }
      
      const type = e.dataTransfer.getData('type');
      if (type === 'child') {
        const childId = parseInt(e.dataTransfer.getData('childId'));
        this.placeChildInWidget(childId, x, y);
      } else if (type === 'asset') {
        const assetId = parseInt(e.dataTransfer.getData('assetId'));
        this.placeAssetInChild(assetId, x, y);
      }
    },
    
    // Place widget on screen
    placeWidget(widgetData, x, y) {
      // Check if already placed
      if (this.isWidgetPlaced(widgetData.id)) {
        // Just select it
        this.selectedWidgetId = widgetData.id;
        return;
      }
      
      const settings = widgetData.settings || {};
      const themeId = settings.theme_id || widgetData.selected_theme_id || widgetData.widget_theme_id;
      const theme = this.themes.find(t => String(t.id) === String(themeId));
      
      const newWidget = {
        id: widgetData.id,
        widget_key: widgetData.widget_key,
        display_name: widgetData.display_name || widgetData.widget_key,
        theme_id: themeId,
        x: x,
        y: y,
        width: settings.width || settings.widget_width || theme?.widget_width || 120,
        height: settings.height || settings.widget_height || theme?.widget_height || 140,
        layout_mode: 'absolute',
        layout_gap: 8,
        layout_padding: 8,
        child_width: 80,
        child_height: 80,
        opacity: 1,
        z_index: this.placedWidgets.length,
        background_color: 'transparent',
        border_radius: 8,
        children: []
      };
      
      this.placedWidgets.push(newWidget);
      this.selectPlacedWidget(newWidget);
      this.hasUnsavedChanges = true;
      this.saveToHistory();
    },
    
    // Place child inside selected widget
    placeChildInWidget(childId, x, y) {
      if (!this.selectedWidget) {
        alert('يرجى اختيار ويدجت أولاً');
        return;
      }
      
      // Check if already placed in this widget
      if (this.isChildPlacedInWidget(childId)) {
        return;
      }
      
      const themeChild = this.themeChildren.find(c => c.id === childId);
      if (!themeChild) return;
      
      // Calculate position relative to widget
      const widget = this.selectedWidget;
      const relativeX = Math.max(0, x - widget.x);
      const relativeY = Math.max(0, y - widget.y);
      
      const newChild = {
        id: `child_${Date.now()}_${childId}`,
        theme_child_id: themeChild.id,
        name: themeChild.label || themeChild.child_key || `طفل ${themeChild.id}`,
        child_key: themeChild.child_key,
        width: widget.child_width || themeChild.width || 80,
        height: widget.child_height || themeChild.height || 80,
        x: relativeX,
        y: relativeY,
        opacity: 1,
        z_index: (widget.children || []).length,
        assets: []
      };
      
      if (!widget.children) widget.children = [];
      widget.children.push(newChild);
      this.selectPlacedChild(newChild, widget);
      this.hasUnsavedChanges = true;
      this.saveToHistory();
    },
    
    // Place asset inside selected child
    placeAssetInChild(assetId, x, y) {
      if (!this.selectedWidget) {
        alert('يرجى اختيار ويدجت أولاً');
        return;
      }
      
      const widget = this.selectedWidget;
      const placedChild = (widget.children || []).find(c => c.theme_child_id === this.selectedChildId);
      if (!placedChild) {
        alert('يرجى اختيار طفل موضوع في الويدجت أولاً');
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
      
      // Calculate position relative to child (and widget)
      const childAbsX = widget.x + placedChild.x;
      const childAbsY = widget.y + placedChild.y;
      const relativeX = Math.max(0, x - childAbsX);
      const relativeY = Math.max(0, y - childAbsY);
      
      const newAsset = {
        id: themeAsset.id,
        asset_key: themeAsset.asset_key,
        name: themeAsset.name || themeAsset.asset_key,
        file_url: themeAsset.file_url || themeAsset.url,
        url: themeAsset.file_url || themeAsset.url,
        asset_type: themeAsset.asset_type,
        text_content: themeAsset.text_content,
        width: themeAsset.width || 40,
        height: themeAsset.height || 40,
        x: relativeX,
        y: relativeY,
        opacity: 1,
        z_index: (placedChild.assets || []).length,
      };
      
      if (!placedChild.assets) placedChild.assets = [];
      placedChild.assets.push(newAsset);
      this.selectPlacedAsset(newAsset, widget);
      this.hasUnsavedChanges = true;
      this.saveToHistory();
    },
    
    // Legacy methods for backward compatibility
    placeChild(childId, x, y) {
      this.placeChildInWidget(childId, x, y);
    },
    placeAsset(assetId, x, y) {
      this.placeAssetInChild(assetId, x, y);
    },
    
    isChildPlaced(childId) {
      return this.isChildPlacedInWidget(childId);
    },
    
    // Get all placed children from all widgets (flattened)
    getAllPlacedChildren() {
      const allChildren = [];
      for (const widget of this.placedWidgets) {
        for (const child of widget.children || []) {
          allChildren.push(child);
        }
      }
      return allChildren;
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
    startCornerResize(e, child, corner) {
      e.preventDefault();
      this.isResizing = true;
      this.resizeType = 'corner';
      this.resizeTarget = child;
      this.resizeCorner = corner;
      this.dragStartY = e.clientY;
      this.cornerStartValue = child[`border_radius_${corner}`] || 0;
    },
    
    onMouseMove(e) {
      if (this.isDragging) {
        const deltaX = (e.clientX - this.dragStartX) / this.zoom;
        const deltaY = (e.clientY - this.dragStartY) / this.zoom;
        
        if (this.dragType === 'placed-widget') {
          // سحب الويدجت
          this.dragData.x = Math.max(0, Math.round(this.dragStartElementX + deltaX));
          this.dragData.y = Math.max(0, Math.round(this.dragStartElementY + deltaY));
          this.hasUnsavedChanges = true;
        } else if (this.dragType === 'placed-child') {
          // سحب الابن داخل الويدجت
          this.dragData.x = Math.max(0, Math.round(this.dragStartElementX + deltaX));
          this.dragData.y = Math.max(0, Math.round(this.dragStartElementY + deltaY));
          this.hasUnsavedChanges = true;
        } else if (this.dragType === 'placed-asset') {
          // سحب الـ asset داخل الابن
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
      if (this.isResizing && this.resizeType === 'corner') {
        const deltaY = e.clientY - this.dragStartY;
        let newRadius = Math.max(0, Math.round(this.cornerStartValue + deltaY));
        this.resizeTarget[`border_radius_${this.resizeCorner}`] = newRadius;
        this.hasUnsavedChanges = true;
        this.saveToHistory();
        this.autoSave();
      }
    },
    onMouseUp() {
      // إعادة تعيين حالة السحب والتغيير
      const wasDragging = this.isDragging;
      const wasResizing = this.isResizing;
      
      this.isDragging = false;
      this.isResizing = false;
      this.dragType = null;
      this.dragData = null;
      this.resizeTarget = null;
      this.resizeType = null;
      this.resizeDirection = null;
      this.resizeCorner = null;
      
      // حفظ التاريخ إذا حدث تغيير
      if (wasDragging || wasResizing) {
        this.saveToHistory();
        this.autoSave();
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
        this.autoSave();
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
        this.autoSave();
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
    
    // Text Shadow methods
    updateTextShadow() {
      if (!this.selectedElement || !this.isTextAsset(this.selectedElement)) return;
      
      if (this.textShadowX === 0 && this.textShadowY === 0 && this.textShadowBlur === 0) {
        this.selectedElement.text_shadow = 'none';
      } else {
        this.selectedElement.text_shadow = `${this.textShadowX}px ${this.textShadowY}px ${this.textShadowBlur}px ${this.textShadowColor}`;
      }
      this.hasUnsavedChanges = true;
      this.onPropertyChange();
    },
    
    clearTextShadow() {
      this.textShadowX = 0;
      this.textShadowY = 0;
      this.textShadowBlur = 0;
      this.textShadowColor = '#000000';
      if (this.selectedElement) {
        this.selectedElement.text_shadow = 'none';
        this.hasUnsavedChanges = true;
        this.onPropertyChange();
      }
    },
    
    parseTextShadow(shadowStr) {
      if (!shadowStr || shadowStr === 'none') {
        this.textShadowX = 0;
        this.textShadowY = 0;
        this.textShadowBlur = 0;
        this.textShadowColor = '#000000';
        return;
      }
      
      // Parse shadow string like "2px 2px 4px #000000"
      const match = shadowStr.match(/(-?\d+)px\s+(-?\d+)px\s+(\d+)px\s+(#[a-fA-F0-9]{6}|rgba?\([^)]+\))/);
      if (match) {
        this.textShadowX = parseInt(match[1]);
        this.textShadowY = parseInt(match[2]);
        this.textShadowBlur = parseInt(match[3]);
        this.textShadowColor = match[4];
      }
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
      
      // إذا كان العنصر المحدد هو أصل (asset)، يجب أن يملأ حدود الطفل وليس الشاشة
      if (this.selectedElementType === 'asset') {
        // البحث عن الطفل الذي يحتوي على هذا الأصل
        for (const child of this.placedChildren) {
          const asset = (child.assets || []).find(a => a.id === this.selectedAssetId);
          if (asset) {
            // ملء حدود الطفل
            asset.x = 0;
            asset.y = 0;
            asset.width = child.width;
            asset.height = child.height;
            this.hasUnsavedChanges = true;
            this.saveToHistory();
            return;
          }
        }
      } else {
        // إذا كان العنصر هو طفل، يملأ الشاشة
        this.selectedElement.x = 0;
        this.selectedElement.y = 0;
        this.selectedElement.width = this.screenWidth;
        this.selectedElement.height = this.screenHeight;
        this.hasUnsavedChanges = true;
        this.saveToHistory();
      }
    },
    
    // Center element in screen or parent
    centerElement() {
      if (!this.selectedElement) return;
      
      if (this.selectedElementType === 'asset') {
        // Center asset in parent child
        for (const child of this.placedChildren) {
          const asset = (child.assets || []).find(a => a.id === this.selectedAssetId);
          if (asset) {
            asset.x = Math.round((child.width - asset.width) / 2);
            asset.y = Math.round((child.height - asset.height) / 2);
            this.hasUnsavedChanges = true;
            this.saveToHistory();
            return;
          }
        }
      } else {
        // Center child in screen
        this.selectedElement.x = Math.round((this.screenWidth - this.selectedElement.width) / 2);
        this.selectedElement.y = Math.round((this.screenHeight - this.selectedElement.height) / 2);
        this.hasUnsavedChanges = true;
        this.saveToHistory();
      }
    },
    
    // Set asset as full background of child
    setAssetAsBackground() {
      if (this.selectedElementType !== 'asset' || !this.selectedElement) return;
      
      // Find the parent child and the asset within placedWidgets
      for (const widget of this.placedWidgets) {
        for (const child of widget.children || []) {
          const assetIndex = (child.assets || []).findIndex(a => a.id === this.selectedAssetId);
          if (assetIndex !== -1) {
            const asset = child.assets[assetIndex];
            
            if (asset.is_background) {
              // Make asset fill the child completely
              asset.x = 0;
              asset.y = 0;
              asset.width = child.width;
              asset.height = child.height;
              asset.z_index = -1; // Put at bottom (behind everything)
              asset.object_fit = 'cover'; // Fill without gaps
              
              // Increase z_index of all other assets so they appear on top
              child.assets.forEach((a, idx) => {
                if (idx !== assetIndex && (a.z_index || 0) <= 0) {
                  a.z_index = Math.max(1, (a.z_index || 0) + 1);
                }
              });
            } else {
              // Reset to normal
              asset.z_index = assetIndex;
              asset.object_fit = 'contain';
            }
            
            this.hasUnsavedChanges = true;
            this.saveToHistory();
            this.autoSave();
            return;
          }
        }
      }
    },
    
    // Styles
    getChildStyle(child) {
      const borderRadius = this.getBorderRadiusStyle(child);
      let style = {
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
      if (child.background_image) {
        style.backgroundImage = `url('${child.background_image}')`;
        style.backgroundSize = 'cover';
        style.backgroundPosition = 'center';
      }
      return style;
    },
    getAssetStyle(asset) {
      const borderRadius = this.getBorderRadiusStyle(asset);
      return {
        left: asset.x * this.zoom + 'px',
        top: asset.y * this.zoom + 'px',
        width: asset.width * this.zoom + 'px',
        height: asset.height * this.zoom + 'px',
        opacity: asset.opacity ?? 1,
        zIndex: asset.z_index ?? 0,
        display: asset.is_visible !== false ? 'block' : 'none',
        transform: `rotate(${asset.rotation || 0}deg) scale(${asset.scale || 1})`,
        borderRadius: borderRadius,
        borderWidth: (asset.border_width || 0) + 'px',
        borderStyle: asset.border_style || 'solid',
        borderColor: asset.border_color || 'transparent',
        '--asset-object-fit': asset.is_background ? 'cover' : (asset.object_fit || 'contain'),
      };
    },
    
    // Check if asset is a text type (نص أو ملف)
    isTextAsset(asset) {
      // التحقق من نوع الأصل من حقل type (نص أو ملف)
      return asset?.type === 'text' || asset?.type === 'نص';
    },
    
    // Check if asset is SVGA type
    isSvgaAsset(asset) {
      if (!asset) return false;
      
      // Check by asset_type field first
      if (asset.asset_type === 'svga') {
        return true;
      }
      
      // Check by file extension (handle URLs with query params)
      const url = asset.file_url || asset.url || '';
      if (!url) {
        return false;
      }
      
      // Remove query params before getting extension
      const urlPath = url.split('?')[0];
      const extension = urlPath.split('.').pop()?.toLowerCase();
      return extension === 'svga' || extension === 'zz';
    },
    
    // Check if SVGA is loaded for an asset
    isSvgaLoaded(assetId) {
      return this.svgaLoadedMap[assetId] === true;
    },
    
    // Initialize SVGA player for an element
    initSvgaPlayer(element, url) {
      if (!element || !url) return;
      
      // Skip if already loaded successfully with same URL
      if (element.dataset.svgaLoaded === 'true' && element.dataset.svgaUrl === url) {
        // Verify canvas exists
        const canvas = element.querySelector('canvas');
        if (canvas) return;
        // Canvas missing, allow reinit
      }
      
      // Skip if currently loading same URL
      if (element.dataset.svgaLoaded === 'loading' && element.dataset.svgaUrl === url) {
        return;
      }
      
      // Check if SVGA library is loaded
      if (typeof SVGA === 'undefined') {
        console.warn('SVGA library not loaded. Loading from CDN...');
        this.loadSvgaLibrary().then(() => {
          this.initSvgaPlayerInternal(element, url);
        }).catch((err) => {
          console.error('Failed to load SVGA library:', err);
          this.showSvgaFallback(element, url);
        });
        return;
      }
      
      this.initSvgaPlayerInternal(element, url);
    },
    
    // Show fallback for SVGA when library fails
    showSvgaFallback(element, url) {
      if (!element) return;
      element.innerHTML = `
        <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;width:100%;height:100%;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);border-radius:8px;padding:8px;">
          <div style="font-size:24px;margin-bottom:4px;">🎬</div>
          <div style="color:white;font-size:10px;text-align:center;">SVGA</div>
          <div style="color:rgba(255,255,255,0.7);font-size:8px;margin-top:2px;">انقر للتشغيل</div>
        </div>
      `;
      element.style.cursor = 'pointer';
      element.onclick = () => {
        window.open(url, '_blank');
      };
    },
    
    // Internal method to initialize SVGA player
    initSvgaPlayerInternal(element, url) {
      if (!element) return;
      
      // Skip if already loaded with same URL
      if (element.dataset.svgaLoaded === 'true' && element.dataset.svgaUrl === url) {
        return;
      }
      
      const assetId = element.dataset.assetId;
      
      try {
        // Clear previous content
        element.innerHTML = '';
        element.dataset.svgaLoaded = 'loading';
        element.dataset.svgaUrl = url;
        
        // Ensure element has dimensions
        element.style.width = '100%';
        element.style.height = '100%';
        element.style.minWidth = '40px';
        element.style.minHeight = '40px';
        element.style.position = 'relative';
        element.style.textAlign = 'left'; // Fix for SVGA player alignment
        element.style.background = 'transparent';
        
        // Show loading indicator
        element.innerHTML = '<div style="display:flex;align-items:center;justify-content:center;width:100%;height:100%;color:#10b981;font-size:20px;background:transparent;">⏳</div>';
        
        // Timeout for loading - show error if takes too long
        let loadTimedOut = false;
        const loadTimeout = setTimeout(() => {
          if (element.dataset.svgaLoaded === 'loading') {
            loadTimedOut = true;
            element.dataset.svgaLoaded = 'false';
            if (assetId) this.svgaLoadedMap[assetId] = false;
            this.showSvgaFallback(element, url);
          }
        }, 10000); // 10 seconds timeout
        
        const parser = new SVGA.Parser();
        
        // Set download mode for cross-origin URLs
        if (parser.load) {
          parser.load(url, (videoItem) => {
            clearTimeout(loadTimeout);
            if (loadTimedOut) return; // Already timed out
            
            if (!videoItem) {
              console.error('❌ [initSvgaPlayerInternal] No videoItem returned for:', url);
              element.dataset.svgaLoaded = 'false';
              if (assetId) this.svgaLoadedMap[assetId] = false;
              this.showSvgaFallback(element, url);
              return;
            }
            
            // Clear loading indicator first
            element.innerHTML = '';
            
            // Create player AFTER clearing content
            const player = new SVGA.Player(element);
            player.setVideoItem(videoItem);
            player.loops = 0; // Infinite loop
            player.clearsAfterStop = false;
            player.startAnimation();
            element.dataset.svgaLoaded = 'true';
            if (assetId) {
              this.svgaLoadedMap[assetId] = true;
              this.svgaPlayersMap[assetId] = player;
            }
            
            // Ensure canvas is visible and properly sized with multiple retries
            const fixCanvas = (retries = 3) => {
              const canvas = element.querySelector('canvas');
              if (canvas) {
                canvas.style.cssText = 'width:100% !important;height:100% !important;display:block !important;visibility:visible !important;opacity:1 !important;position:absolute !important;top:0 !important;left:0 !important;object-fit:contain;background:transparent;';
                // Force resize
                const rect = element.getBoundingClientRect();
                if (rect.width > 0 && rect.height > 0) {
                  player.setContentMode('AspectFill');
                  player.startAnimation();
                }
              } else if (retries > 0) {
                setTimeout(() => fixCanvas(retries - 1), 100);
              } else {
                console.warn('⚠️ Canvas not found for SVGA element after retries');
              }
            };
            setTimeout(() => fixCanvas(), 100);
          }, (error) => {
            clearTimeout(loadTimeout);
            if (loadTimedOut) return;
            
            console.error('❌ [initSvgaPlayerInternal] Failed to load SVGA:', url, error);
            element.dataset.svgaLoaded = 'false';
            if (assetId) this.svgaLoadedMap[assetId] = false;
            // Try fetch method as fallback
            this.loadSvgaWithFetch(element, url, assetId);
          });
        } else {
          // Fallback: try using downloader approach
          this.loadSvgaWithFetch(element, url, assetId);
        }
      } catch (error) {
        console.error('❌ [initSvgaPlayerInternal] Error initializing SVGA player:', error);
        element.dataset.svgaLoaded = 'false';
        if (assetId) this.svgaLoadedMap[assetId] = false;
        this.showSvgaFallback(element, url);
      }
    },
    
    // Fallback method to load SVGA using fetch
    async loadSvgaWithFetch(element, url, assetId) {
      try {
        console.log('🔄 [loadSvgaWithFetch] Trying fetch method for:', url);
        const response = await fetch(url, { mode: 'cors' });
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        const arrayBuffer = await response.arrayBuffer();
        
        const parser = new SVGA.Parser();
        parser.load(arrayBuffer, (videoItem) => {
          if (!videoItem) {
            element.dataset.svgaLoaded = 'false';
            if (assetId) this.svgaLoadedMap[assetId] = false;
            this.showSvgaFallback(element, url);
            return;
          }
          
          element.innerHTML = '';
          const player = new SVGA.Player(element);
          player.setVideoItem(videoItem);
          player.loops = 0;
          player.clearsAfterStop = false;
          player.startAnimation();
          element.dataset.svgaLoaded = 'true';
          if (assetId) {
            this.svgaLoadedMap[assetId] = true;
            this.svgaPlayersMap[assetId] = player;
          }
          console.log('✅ [loadSvgaWithFetch] Successfully loaded SVGA via fetch:', url);
        });
      } catch (error) {
        console.error('❌ [loadSvgaWithFetch] Failed:', error);
        element.dataset.svgaLoaded = 'false';
        if (assetId) this.svgaLoadedMap[assetId] = false;
        this.showSvgaFallback(element, url);
      }
    },
    
    // Load SVGA library dynamically
    loadSvgaLibrary() {
      return new Promise((resolve, reject) => {
        if (typeof SVGA !== 'undefined') {
          this.svgaLibraryLoaded = true;
          resolve();
          return;
        }
        
        // Try multiple CDN sources
        const cdnUrls = [
          'https://cdn.jsdelivr.net/npm/svgaplayerweb@2.3.1/build/svga.min.js',
          'https://unpkg.com/svgaplayerweb@2.3.1/build/svga.min.js',
          'https://cdnjs.cloudflare.com/ajax/libs/svgaplayerweb/2.3.1/svga.min.js'
        ];
        
        let currentIndex = 0;
        
        const tryLoadScript = () => {
          if (currentIndex >= cdnUrls.length) {
            reject(new Error('Failed to load SVGA library from all CDN sources'));
            return;
          }
          
          const script = document.createElement('script');
          script.src = cdnUrls[currentIndex];
          script.onload = () => {
            this.svgaLibraryLoaded = true;
            console.log('✅ SVGA library loaded from:', cdnUrls[currentIndex]);
            resolve();
          };
          script.onerror = () => {
            console.warn('⚠️ Failed to load SVGA from:', cdnUrls[currentIndex]);
            currentIndex++;
            tryLoadScript();
          };
          document.head.appendChild(script);
        };
        
        tryLoadScript();
      });
    },
    
    // Get unique ID for SVGA player
    getSvgaPlayerId(asset) {
      return `svga_player_${asset.id}_${Date.now()}`;
    },
    
    // Handle SVGA element mounted
    onSvgaMounted(asset, element) {
      if (!element || !asset) return;
      
      const url = asset.file_url || asset.url;
      if (!url) {
        console.warn('[onSvgaMounted] No URL for asset:', asset);
        this.showSvgaFallback(element, '');
        return;
      }
      
      const assetId = asset.id;
      element.dataset.assetId = assetId;
      
      // Create unique key for this element
      const elementKey = `svga_${assetId}_${url}`;
      
      // Check if already initialized with same URL and loaded successfully
      if (element.dataset.svgaLoaded === 'true' && element.dataset.svgaUrl === url) {
        // Check if canvas actually exists
        const existingCanvas = element.querySelector('canvas');
        if (existingCanvas) {
          return;
        }
        // Canvas missing, reinitialize
        element.dataset.svgaLoaded = 'false';
      }
      
      // Skip if currently loading
      if (element.dataset.svgaLoaded === 'loading' && element.dataset.svgaUrl === url) {
        return;
      }
      
      // Mark the URL being loaded
      element.dataset.svgaUrl = url;
      
      console.log('🎬 [onSvgaMounted] Starting SVGA load for:', url);
      
      // Use requestAnimationFrame to ensure DOM is ready
      requestAnimationFrame(() => {
        setTimeout(() => {
          this.initSvgaPlayer(element, url);
        }, 100);
      });
    },
    
    // Cleanup all SVGA players
    cleanupSvgaPlayers() {
      for (const assetId in this.svgaPlayersMap) {
        try {
          const player = this.svgaPlayersMap[assetId];
          if (player && typeof player.stopAnimation === 'function') {
            player.stopAnimation();
          }
          if (player && typeof player.clear === 'function') {
            player.clear();
          }
        } catch (e) {
          console.warn('Error cleaning up SVGA player:', e);
        }
      }
      this.svgaPlayersMap = {};
      this.svgaLoadedMap = {};
    },
    
    // Style for text asset content
    getTextAssetStyle(asset) {
      // Handle text alignment for flexbox justify-content
      let justifyContent = 'center';
      if (asset.text_align === 'left') justifyContent = 'flex-start';
      else if (asset.text_align === 'right') justifyContent = 'flex-end';
      
      return {
        fontSize: (asset.font_size || 14) * this.zoom + 'px',
        fontWeight: asset.font_weight || 'normal',
        fontFamily: asset.font_family || 'inherit',
        color: asset.text_color || '#ffffff',
        textAlign: asset.text_align || 'center',
        lineHeight: asset.line_height || 1.4,
        letterSpacing: (asset.letter_spacing || 0) + 'px',
        textShadow: asset.text_shadow || 'none',
        textDecoration: asset.text_decoration || 'none',
        textTransform: asset.text_transform || 'none',
        width: '100%',
        height: '100%',
        display: 'flex',
        alignItems: 'center',
        justifyContent: justifyContent,
        overflow: 'hidden',
        wordBreak: 'break-word',
        padding: '4px',
      };
    },
    
    // Handle image loading errors
    onAssetImageError(event, asset) {
      console.warn('Failed to load asset image:', asset.file_url || asset.url);
      // Replace with placeholder
      event.target.style.display = 'none';
      const placeholder = document.createElement('span');
      placeholder.textContent = asset.name || '📦';
      placeholder.className = 'asset-error-placeholder';
      event.target.parentElement.appendChild(placeholder);
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
    
    // Screen Size Controls
    onScreenSizeChange() {
      this.hasUnsavedChanges = true;
    },
    setScreenPreset(preset) {
      switch (preset) {
        case 'iphone':
          this.screenWidth = 375;
          this.screenHeight = 667;
          break;
        case 'android':
          this.screenWidth = 360;
          this.screenHeight = 640;
          break;
        case 'tablet':
          this.screenWidth = 768;
          this.screenHeight = 1024;
          break;
        case 'custom':
          // Keep current values
          break;
      }
      this.hasUnsavedChanges = true;
    },
    
    // Layout Mode Methods
    setLayoutMode(mode) {
      this.layoutMode = mode;
      this.hasUnsavedChanges = true;
      this.saveToHistory();
      
      // Auto arrange when switching to layout mode
      if (mode !== 'absolute') {
        this.autoArrangeChildren();
      }
    },
    
    onLayoutChange() {
      this.hasUnsavedChanges = true;
      this.saveToHistory();
    },
    
    // Auto arrange children based on layout mode
    autoArrangeChildren() {
      if (this.layoutMode === 'absolute') return;
      
      this.placedChildren.forEach((child, index) => {
        child.width = this.layoutItemWidth;
        child.height = this.layoutItemHeight;
        
        if (this.layoutMode === 'horizontal') {
          child.x = index * (this.layoutItemWidth + this.layoutGap);
          child.y = 0;
        } else if (this.layoutMode === 'vertical') {
          child.x = 0;
          child.y = index * (this.layoutItemHeight + this.layoutGap);
        }
      });
      
      this.hasUnsavedChanges = true;
      this.saveToHistory();
    },
    
    // Mobile screen class for layout mode
    getMobileScreenClass() {
      return {
        'layout-mode': this.layoutMode !== 'absolute',
        'horizontal-layout': this.layoutMode === 'horizontal',
        'vertical-layout': this.layoutMode === 'vertical',
      };
    },
    
    // Mobile screen style
    getMobileScreenStyle() {
      return {
        width: this.screenWidth * this.zoom + 'px',
        height: this.dynamicScreenHeight * this.zoom + 'px',
        minHeight: this.screenHeight * this.zoom + 'px',
        backgroundColor: this.screenBackgroundColor || '#f5f5f5',
      };
    },
    
    // Screen settings change handler
    onScreenSettingChange() {
      this.hasUnsavedChanges = true;
      this.saveToHistory();
    },
    
    // Scroll container class
    getScrollContainerClass() {
      return {
        'horizontal': this.layoutMode === 'horizontal',
        'vertical': this.layoutMode === 'vertical',
      };
    },
    
    // Scroll container style
    getScrollContainerStyle() {
      if (this.layoutMode === 'horizontal') {
        return {
          display: 'flex',
          flexDirection: 'row',
          gap: this.layoutGap * this.zoom + 'px',
          padding: this.layoutPadding * this.zoom + 'px',
          overflowX: 'auto',
          overflowY: 'hidden',
          height: '100%',
          alignItems: 'flex-start',
        };
      } else if (this.layoutMode === 'vertical') {
        return {
          display: 'flex',
          flexDirection: 'column',
          gap: this.layoutGap * this.zoom + 'px',
          padding: this.layoutPadding * this.zoom + 'px',
          overflowX: 'hidden',
          overflowY: 'auto',
          width: '100%',
          alignItems: 'flex-start',
        };
      }
      return {};
    },
    
    // Style for children in layout mode
    getLayoutChildStyle(child) {
      const borderRadius = this.getBorderRadiusStyle(child);
      return {
        width: child.width * this.zoom + 'px',
        height: child.height * this.zoom + 'px',
        minWidth: child.width * this.zoom + 'px',
        minHeight: child.height * this.zoom + 'px',
        flexShrink: 0,
        display: child.is_visible ? 'block' : 'none',
        transform: `rotate(${child.rotation || 0}deg) scale(${child.scale || 1})`,
        opacity: child.opacity ?? 1,
        backgroundColor: child.background_color || 'transparent',
        borderWidth: (child.border_width || 2) + 'px',
        borderStyle: child.border_style || 'dashed',
        borderColor: child.border_color || '#6366f1',
        borderRadius: borderRadius,
        zIndex: child.z_index || 0,
        position: 'relative',
      };
    },
    
    // Handle scroll in mobile screen preview
    onMobileScreenScroll(e) {
      if (this.layoutMode === 'absolute') return;
      
      const scrollContainer = this.$refs.scrollContainer;
      if (!scrollContainer) return;
      
      e.preventDefault();
      
      if (this.layoutMode === 'horizontal') {
        scrollContainer.scrollLeft += e.deltaY;
      } else {
        scrollContainer.scrollTop += e.deltaY;
      }
    },
    
    // Save
    async saveDesign() {
      if (this.isSaving) return; // Prevent multiple saves
      this.isSaving = true;
      
      try {
        // Prepare data for saving with ALL widgets and their children
        const designData = {
          theme_id: this.selectedThemeId,
          // Widget dimensions (screen size)
          widget_width: this.screenWidth,
          widget_height: this.screenHeight,
          // screen_background_color is now saved in screenOverrides (not widget settings)
          // All placed widgets with their layout and children
          widgets: this.placedWidgets.map(widget => ({
            widget_id: widget.id,
            widget_key: widget.widget_key,
            display_name: widget.display_name,
            x: widget.x,
            y: widget.y,
            width: widget.width,
            height: widget.height,
            z_index: widget.z_index || 1,
            // Layout settings for this widget
            layout_mode: widget.layout_mode || 'absolute',
            layout_gap: widget.layout_gap || 10,
            layout_padding: widget.layout_padding || 10,
            layout_item_width: widget.layout_item_width || widget.child_width || 100,
            layout_item_height: widget.layout_item_height || widget.child_height || 100,
            child_width: widget.child_width || 80,
            child_height: widget.child_height || 100,
            infinite_scroll: widget.infinite_scroll || false,
            auto_scroll: widget.auto_scroll || false,
            scroll_speed: widget.scroll_speed || 3,
            layout_show_scroll_indicator: widget.layout_show_scroll_indicator !== false,
            // Children inside this widget
            children: (widget.children || []).map(child => ({
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
              padding_top: child.padding_top,
              padding_bottom: child.padding_bottom,
              padding_right: child.padding_right,
              padding_left: child.padding_left,
              assets: (child.assets || []).map(asset => ({
                id: asset.id,
                asset_key: asset.asset_key,
                name: asset.name,
                file_url: asset.file_url,
                url: asset.file_url,
                asset_type: asset.asset_type || 'image',
                type: asset.type, // نص أو ملف
                text_content: asset.text_content,
                text_color: asset.text_color || '#ffffff',
                font_size: asset.font_size || 14,
                font_weight: asset.font_weight || 'normal',
                font_family: asset.font_family || 'inherit',
                text_align: asset.text_align || 'center',
                line_height: asset.line_height || 1.4,
                letter_spacing: asset.letter_spacing || 0,
                text_shadow: asset.text_shadow || 'none',
                text_decoration: asset.text_decoration || 'none',
                text_transform: asset.text_transform || 'none',
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
                is_background: asset.is_background || false,
                object_fit: asset.object_fit || 'contain',
              }))
            }))
          })),
          // Legacy: also include flattened children for backward compatibility
          children: this.getAllPlacedChildren().map(child => ({
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
            padding_top: child.padding_top,
            padding_bottom: child.padding_bottom,
            padding_right: child.padding_right,
            padding_left: child.padding_left,
            assets: (child.assets || []).map(asset => ({
              id: asset.id,
              asset_key: asset.asset_key,
              name: asset.name,
              asset_type: asset.asset_type || 'image',
              type: asset.type, // نص أو ملف
              text_content: asset.text_content,
              text_color: asset.text_color || '#ffffff',
              font_size: asset.font_size || 14,
              font_weight: asset.font_weight || 'normal',
              font_family: asset.font_family || 'inherit',
              text_align: asset.text_align || 'center',
              line_height: asset.line_height || 1.4,
              letter_spacing: asset.letter_spacing || 0,
              text_shadow: asset.text_shadow || 'none',
              text_decoration: asset.text_decoration || 'none',
              text_transform: asset.text_transform || 'none',
              file_url: asset.file_url,
              url: asset.file_url,
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
              is_background: asset.is_background || false,
              object_fit: asset.object_fit || 'contain',
            }))
          }))
        };
        // Emit to parent to save in configuration
        this.$emit('save', designData);
        // حفظ فعلي في config المختار
        if (this.configurationId) {
          const widgetsOverride = this.placedWidgets.map(widget => ({
            widget_id: widget.id,
            // استخدم theme_id الخاص بكل ويدجت وليس العام
            theme_id: widget.theme_id || this.selectedThemeId,
            x: widget.x,
            y: widget.y,
            width: widget.width,
            height: widget.height,
            layout_mode: widget.layout_mode || 'absolute',
            layout_gap: widget.layout_gap ?? 8,
            layout_padding: widget.layout_padding ?? 8,
            layout_item_width: widget.layout_item_width || widget.child_width || 80,
            layout_item_height: widget.layout_item_height || widget.child_height || 100,
            child_width: widget.child_width || 80,
            child_height: widget.child_height || 100,
            infinite_scroll: widget.infinite_scroll || false,
            auto_scroll: widget.auto_scroll || false,
            scroll_speed: widget.scroll_speed || 3,
            z_index: widget.z_index ?? 0,
            opacity: widget.opacity ?? 1,
            background_color: widget.background_color || 'transparent',
            border_radius: widget.border_radius ?? 8,
            children: (widget.children || []).map(child => ({
              theme_child_id: child.theme_child_id,
              name: child.name,
              child_key: child.child_key,
              width: child.width,
              height: child.height,
              x: child.x,
              y: child.y,
              is_visible: child.is_visible !== false,
              rotation: child.rotation ?? 0,
              scale: child.scale ?? 1,
              opacity: child.opacity ?? 1,
              z_index: child.z_index ?? 0,
              background_color: child.background_color || 'transparent',
              border_width: child.border_width ?? 0,
              border_style: child.border_style || 'solid',
              border_color: child.border_color || 'transparent',
              border_radius_tl: child.border_radius_tl ?? 0,
              border_radius_tr: child.border_radius_tr ?? 0,
              border_radius_bl: child.border_radius_bl ?? 0,
              border_radius_br: child.border_radius_br ?? 0,
              padding_top: child.padding_top ?? 0,
              padding_bottom: child.padding_bottom ?? 0,
              padding_right: child.padding_right ?? 0,
              padding_left: child.padding_left ?? 0,
              assets: (child.assets || []).map(asset => ({
                id: asset.id,
                asset_key: asset.asset_key,
                name: asset.name,
                asset_type: asset.asset_type || 'image',
                type: asset.type, // نص أو ملف
                text_content: asset.text_content,
                text_color: asset.text_color || '#ffffff',
                font_size: asset.font_size || 14,
                font_weight: asset.font_weight || 'normal',
                font_family: asset.font_family || 'inherit',
                text_align: asset.text_align || 'center',
                line_height: asset.line_height || 1.4,
                letter_spacing: asset.letter_spacing || 0,
                text_shadow: asset.text_shadow || 'none',
                text_decoration: asset.text_decoration || 'none',
                text_transform: asset.text_transform || 'none',
                file_url: asset.file_url || asset.url,
                url: asset.file_url || asset.url,
                width: asset.width,
                height: asset.height,
                x: asset.x,
                y: asset.y,
                opacity: asset.opacity ?? 1,
                z_index: asset.z_index ?? 1,
                rotation: asset.rotation ?? 0,
                scale: asset.scale ?? 1,
                is_visible: asset.is_visible !== false,
                is_background: asset.is_background || false,
                object_fit: asset.object_fit || 'contain',
                border_width: asset.border_width ?? 0,
                border_style: asset.border_style || 'solid',
                border_color: asset.border_color || 'transparent',
                border_radius_tl: asset.border_radius_tl ?? 0,
                border_radius_tr: asset.border_radius_tr ?? 0,
                border_radius_bl: asset.border_radius_bl ?? 0,
                border_radius_br: asset.border_radius_br ?? 0,
              })),
            })),
          }));
          try {
            // Save widget overrides via the correct endpoint
            const overridesPayload = widgetsOverride.map(widget => ({
              screen_widget_id: widget.widget_id,
              screen_id: null, // Will be filled by controller
              is_visible: true,
              display_order: widget.z_index ?? 0,
              selected_theme_id: widget.theme_id || this.selectedThemeId,
              settings: {
                theme_id: widget.theme_id || this.selectedThemeId,
                // screen_background_color is now saved in screenOverrides (not widget settings)
                x: widget.x,
                y: widget.y,
                width: widget.width,
                height: widget.height,
                layout_mode: widget.layout_mode,
                layout_gap: widget.layout_gap,
                layout_padding: widget.layout_padding,
                layout_item_width: widget.layout_item_width,
                layout_item_height: widget.layout_item_height,
                child_width: widget.child_width,
                child_height: widget.child_height,
                infinite_scroll: widget.infinite_scroll,
                auto_scroll: widget.auto_scroll,
                scroll_speed: widget.scroll_speed,
                z_index: widget.z_index,
                opacity: widget.opacity,
                background_color: widget.background_color,
                border_radius: widget.border_radius,
                padding_top: widget.padding_top ?? 0,
                padding_bottom: widget.padding_bottom ?? 0,
                padding_right: widget.padding_right ?? 0,
                padding_left: widget.padding_left ?? 0,
                children: widget.children,
              }
            }));
            await configurationsApi.updateWidgetOverrides(this.configurationId, overridesPayload);
            
            // Save screen background color to screenOverrides (tied to screen + configuration)
            if (this.screenId) {
              try {
                await configurationsApi.saveScreenOverride(this.configurationId, this.screenId, {
                  background_color: this.screenBackgroundColor
                });
              } catch (screenErr) {
                console.error('خطأ في حفظ لون خلفية الشاشة:', screenErr);
              }
            }
          } catch (e) {
            console.error('خطأ في حفظ التعديلات في الكونفيج المختار', e);
          }
        }
        
        // Also save ALL properties to database if we have real IDs
        const childrenToUpdate = [];
        const assetsToUpdate = [];
        for (const widget of this.placedWidgets) {
          for (const child of widget.children || []) {
            if (child.theme_child_id) {
              childrenToUpdate.push({
                id: child.theme_child_id,
                width: child.width,
                height: child.height,
                x: child.x,
                y: child.y,
                rotation: child.rotation ?? 0,
                scale: child.scale ?? 1,
                opacity: child.opacity ?? 1,
                z_index: child.z_index ?? 0,
                is_visible: child.is_visible !== false,
                background_color: child.background_color || 'transparent',
                border_width: child.border_width ?? 0,
                border_style: child.border_style || 'solid',
                border_color: child.border_color || 'transparent',
                border_radius_tl: child.border_radius_tl ?? 0,
                border_radius_tr: child.border_radius_tr ?? 0,
                border_radius_bl: child.border_radius_bl ?? 0,
                border_radius_br: child.border_radius_br ?? 0,
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
                  opacity: asset.opacity ?? 1,
                  z_index: asset.z_index ?? 0,
                  rotation: asset.rotation ?? 0,
                  scale: asset.scale ?? 1,
                  is_visible: asset.is_visible !== false,
                  border_width: asset.border_width ?? 0,
                  border_style: asset.border_style || 'solid',
                  border_color: asset.border_color || 'transparent',
                  border_radius_tl: asset.border_radius_tl ?? 0,
                  border_radius_tr: asset.border_radius_tr ?? 0,
                  border_radius_bl: asset.border_radius_bl ?? 0,
                  border_radius_br: asset.border_radius_br ?? 0,
                });
              }
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
    },
    onTouchStart(e) {
      if (e.touches.length !== 1) return;
      const touch = e.touches[0];
      const target = e.target;
      // دعم السحب للطفل أو الأصل
      if (target.classList.contains('placed-child')) {
        this.isDragging = true;
        this.dragType = 'placed-child';
        this.dragData = this.placedChildren.find(c => c.theme_child_id === target.getAttribute('data-child-id'));
        this.dragStartX = touch.clientX;
        this.dragStartY = touch.clientY;
        this.dragStartElementX = this.dragData.x;
        this.dragStartElementY = this.dragData.y;
      } else if (target.classList.contains('placed-asset')) {
        const childId = target.getAttribute('data-child-id');
        const assetId = target.getAttribute('data-asset-id');
        const child = this.placedChildren.find(c => c.theme_child_id === childId);
        if (child) {
          const asset = (child.assets || []).find(a => a.id == assetId);
          if (asset) {
            this.isDragging = true;
            this.dragType = 'placed-asset';
            this.dragData = { child, asset };
            this.dragStartX = touch.clientX;
            this.dragStartY = touch.clientY;
            this.dragStartElementX = asset.x;
            this.dragStartElementY = asset.y;
          }
        }
      }
    },
    onTouchMove(e) {
      if (!this.isDragging || e.touches.length !== 1) return;
      const touch = e.touches[0];
      const deltaX = (touch.clientX - this.dragStartX) / this.zoom;
      const deltaY = (touch.clientY - this.dragStartY) / this.zoom;
      if (this.dragType === 'placed-child') {
        this.dragData.x = Math.max(0, Math.round(this.dragStartElementX + deltaX));
        this.dragData.y = Math.max(0, Math.round(this.dragStartElementY + deltaY));
        this.hasUnsavedChanges = true;
        this.saveToHistory();
        this.autoSave();
      } else if (this.dragType === 'placed-asset') {
        this.dragData.asset.x = Math.max(0, Math.round(this.dragStartElementX + deltaX));
        this.dragData.asset.y = Math.max(0, Math.round(this.dragStartElementY + deltaY));
        this.hasUnsavedChanges = true;
        this.saveToHistory();
        this.autoSave();
      }
    },
    onTouchEnd(e) {
      this.isDragging = false;
      this.dragType = null;
      this.dragData = null;
    },
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

.left-panel {
  overflow-y: auto;
  overflow-x: hidden;
}

.right-panel {
  overflow-y: auto;
}

.center-panel {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: flex-start;
  background: #1a1a2e;
  padding: 20px;
  overflow-y: auto;
  overflow-x: hidden;
  gap: 12px;
}

.panel-section {
  padding: 16px;
  border-bottom: 1px solid #3d3d5c;
}

/* Widget Select Dropdown */
.widget-select {
  width: 100%;
  padding: 10px 12px;
  background: #1e1e2e;
  border: 1px solid #3d3d5c;
  border-radius: 8px;
  color: white;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.2s;
}

.widget-select:hover {
  border-color: #6366f1;
}

.widget-select:focus {
  outline: none;
  border-color: #6366f1;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
}

/* Widget Layout Settings */
.widget-layout-settings {
  background: rgba(99, 102, 241, 0.05);
  border-radius: 8px;
  padding: 12px;
  margin-top: 8px;
}

.setting-group {
  margin-bottom: 12px;
}

.setting-group:last-child {
  margin-bottom: 0;
}

.setting-label {
  display: block;
  font-size: 12px;
  color: #a0a0b0;
  margin-bottom: 6px;
}

.setting-row {
  display: flex;
  gap: 8px;
  align-items: center;
}

.setting-field {
  flex: 1;
}

.setting-input {
  width: 100%;
  padding: 8px 10px;
  background: #1e1e2e;
  border: 1px solid #3d3d5c;
  border-radius: 6px;
  color: white;
  font-size: 13px;
}

.setting-input:focus {
  outline: none;
  border-color: #6366f1;
}

/* Small setting input for padding */
.setting-input-sm {
  width: 50px;
  padding: 4px 6px;
  background: #1e1e2e;
  border: 1px solid #3d3d5c;
  border-radius: 4px;
  color: white;
  font-size: 12px;
  text-align: center;
}

.setting-input-sm:focus {
  outline: none;
  border-color: #6366f1;
}

/* Padding inputs row */
.padding-inputs-row {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
  margin-top: 6px;
}

.padding-input-group {
  display: flex;
  align-items: center;
  gap: 4px;
}

.padding-label {
  font-size: 12px;
  color: #9ca3af;
  min-width: 14px;
  text-align: center;
}

/* Layout Buttons Small */
.layout-btn-sm {
  padding: 6px 10px;
  background: #1e1e2e;
  border: 1px solid #3d3d5c;
  border-radius: 6px;
  color: #a0a0b0;
  font-size: 12px;
  cursor: pointer;
  transition: all 0.2s;
}

.layout-btn-sm:hover {
  border-color: #6366f1;
  color: white;
}

.layout-btn-sm.active {
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  border-color: transparent;
  color: white;
}

/* Scroll Mode Buttons */
.scroll-mode-buttons {
  display: flex;
  gap: 8px;
  margin-top: 6px;
  margin-bottom: 10px;
}

/* Checkbox Label */
.checkbox-label {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  font-size: 13px;
  color: #e0e0e0;
  padding: 8px 0;
}

.checkbox-label input[type="checkbox"] {
  width: 16px;
  height: 16px;
  accent-color: #6366f1;
  cursor: pointer;
}

/* Horizontal Scroll Container */
.horizontal-scroll-container {
  display: flex;
  gap: var(--layout-gap, 8px);
  overflow-x: auto;
  overflow-y: hidden;
  padding: var(--layout-padding, 8px);
  scroll-behavior: smooth;
  -webkit-overflow-scrolling: touch;
}

.horizontal-scroll-container::-webkit-scrollbar {
  height: 4px;
}

.horizontal-scroll-container::-webkit-scrollbar-track {
  background: rgba(0, 0, 0, 0.1);
  border-radius: 2px;
}

.horizontal-scroll-container::-webkit-scrollbar-thumb {
  background: rgba(99, 102, 241, 0.5);
  border-radius: 2px;
}

/* Infinite Scroll Animation */
.infinite-scroll-container {
  display: flex;
  gap: var(--layout-gap, 8px);
  animation: infinite-scroll 20s linear infinite;
  width: max-content;
}

.infinite-scroll-wrapper {
  overflow: hidden;
  position: relative;
}

.infinite-scroll-track {
  display: flex;
  gap: inherit;
  width: max-content;
}

/* التمرير التلقائي - حركة مستمرة */
.infinite-scroll-track.auto-scroll {
  animation: infinite-scroll-slide linear infinite;
  animation-duration: var(--scroll-duration, 15s);
}

.infinite-scroll-track.auto-scroll:hover {
  animation-play-state: paused;
}

/* التمرير اليدوي - بدون حركة تلقائية */
.infinite-scroll-track.manual-scroll {
  animation: none;
}

@keyframes infinite-scroll {
  0% {
    transform: translateX(0);
  }
  100% {
    transform: translateX(-50%);
  }
}

@keyframes infinite-scroll-slide {
  0% {
    transform: translateX(0);
  }
  100% {
    transform: translateX(-50%);
  }
}

.infinite-scroll-badge {
  position: absolute;
  top: 2px;
  right: 2px;
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  color: white;
  font-size: 8px;
  padding: 2px 6px;
  border-radius: 8px;
  z-index: 10;
  opacity: 0.9;
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
  flex-wrap: wrap;
  gap: 8px;
  max-height: 300px;
  overflow-y: auto;
  align-items: flex-start;
}

.child-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
  padding: 8px;
  background: #1e1e2e;
  border: 2px solid transparent;
  border-radius: 10px;
  cursor: grab;
  transition: all 0.2s;
  width: calc(50% - 4px);
  min-width: 80px;
  max-width: 100px;
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
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
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
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  max-height: 300px;
  overflow-y: auto;
  align-items: flex-start;
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

/* Text Asset Styles */
.text-asset .asset-preview {
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
}

.text-asset-preview {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 4px;
  text-align: center;
  padding: 4px;
}

.text-asset-preview .text-icon {
  font-size: 16px;
}

.text-asset-preview .text-content {
  font-size: 9px;
  color: #fff;
  max-width: 100%;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.asset-type-badge {
  font-size: 8px;
  background: rgba(99, 102, 241, 0.3);
  color: #a5b4fc;
  padding: 1px 4px;
  border-radius: 3px;
  margin-top: 2px;
}

/* SVGA Asset Styles */
.svga-asset .asset-preview,
.svga-asset-preview {
  background: linear-gradient(135deg, #10b981, #059669);
  position: relative;
  min-width: 50px;
  min-height: 50px;
}

.svga-asset-preview {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  width: 50px;
  height: 50px;
  position: relative;
  overflow: hidden;
}

.svga-asset-preview .svga-player-container {
  width: 46px;
  height: 46px;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
}

.svga-asset-preview .svga-player-container canvas,
.svga-asset-preview canvas {
  max-width: 100% !important;
  max-height: 100% !important;
  width: auto !important;
  height: auto !important;
  display: block !important;
}

.svga-badge {
  position: absolute;
  bottom: 2px;
  right: 2px;
  font-size: 8px;
  background: rgba(16, 185, 129, 0.9);
  color: #fff;
  padding: 1px 4px;
  border-radius: 3px;
  font-weight: bold;
  z-index: 10;
}

.svga-asset-container {
  width: 100%;
  height: 100%;
  display: block;
  background: transparent !important;
  min-width: 40px;
  min-height: 40px;
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  overflow: hidden;
}

.svga-asset-container canvas {
  width: 100% !important;
  height: 100% !important;
  max-width: 100% !important;
  max-height: 100% !important;
  display: block !important;
  object-fit: contain;
  position: absolute !important;
  top: 0 !important;
  left: 0 !important;
}

.placed-asset.svga-asset {
  overflow: visible;
  background: transparent !important;
  border: none;
}

.placed-asset.svga-asset.selected {
  border: 1px dashed rgba(16, 185, 129, 0.7);
}

/* SVGA Overlay and Placeholder */
.svga-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 4px;
  z-index: 5;
}

.svga-loading-icon {
  font-size: 20px;
  animation: svga-pulse 1.5s ease-in-out infinite;
}

@keyframes svga-pulse {
  0%, 100% {
    transform: scale(1);
    opacity: 1;
  }
  50% {
    transform: scale(1.2);
    opacity: 0.7;
  }
}

.svga-placeholder {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 4px;
  color: white;
  font-size: 24px;
  z-index: 5;
}

.svga-canvas {
  width: 100%;
  height: 100%;
  display: block;
  object-fit: contain;
}

.svga-canvas-container {
  width: 100%;
  height: 100%;
  min-width: 40px;
  min-height: 40px;
  display: block;
  position: relative;
}

.svga-canvas-full {
  width: 100% !important;
  height: 100% !important;
  display: block !important;
  object-fit: contain;
  position: absolute;
  top: 0;
  left: 0;
}

.placed-asset.svga-asset .svga-asset-container {
  width: 100%;
  height: 100%;
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
}

.placed-asset.svga-asset .svga-asset-container canvas,
.svga-asset-container canvas {
  max-width: 100% !important;
  max-height: 100% !important;
  width: 100% !important;
  height: 100% !important;
  object-fit: contain;
  display: block !important;
  visibility: visible !important;
  opacity: 1 !important;
  background: transparent !important;
}

/* Override any SVGA player default styles */
.svga-asset-container div,
.placed-asset.svga-asset .svga-asset-container div {
  background: transparent !important;
}

.text-asset-content {
  padding: 4px;
  box-sizing: border-box;
}

.asset-error-placeholder {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  height: 100%;
  background: rgba(255, 0, 0, 0.1);
  color: #f87171;
  font-size: 12px;
  border: 1px dashed #f87171;
  border-radius: 4px;
}

/* Mobile Frame */
.mobile-frame {
  background: #000;
  border-radius: 40px;
  padding: 12px;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5), inset 0 0 0 3px #333;
  flex-shrink: 0;
  height: calc(100vh - 180px);
  min-height: 700px;
  max-height: 900px;
  overflow: hidden;
  display: flex;
  flex-direction: column;
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
  min-height: 667px;
  background: #f5f5f5;
  border-radius: 4px;
  position: relative;
  overflow-y: auto;
  overflow-x: hidden;
  transform-origin: top center;
  flex: 1;
  scrollbar-width: thin;
  scrollbar-color: #888 transparent;
}

.mobile-screen::-webkit-scrollbar {
  width: 6px;
}

.mobile-screen::-webkit-scrollbar-track {
  background: transparent;
}

.mobile-screen::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 3px;
}

.mobile-screen::-webkit-scrollbar-thumb:hover {
  background: #666;
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

/* Widget List in Left Panel */
.widgets-list {
  max-height: 200px;
  overflow-y: auto;
}

.widget-list-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px;
  background: #252536;
  border: 1px solid #3d3d5c;
  border-radius: 8px;
  margin-bottom: 8px;
  cursor: pointer;
  transition: all 0.2s;
}

.widget-list-item:hover {
  background: #3d3d5c;
}

.widget-list-item.active {
  border-color: #10b981;
  background: rgba(16, 185, 129, 0.1);
}

.widget-list-item.placed {
  border-color: #6366f1;
}

.widget-icon {
  font-size: 20px;
}

.widget-info {
  flex: 1;
  display: flex;
  flex-direction: column;
  min-width: 0;
}

.widget-name {
  font-size: 13px;
  font-weight: 500;
  color: #e2e8f0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.widget-type {
  font-size: 11px;
  color: #9ca3af;
}

.placed-badge {
  color: #10b981;
  font-size: 14px;
}

/* Placed Widget */
.placed-widget {
  position: absolute;
  background: rgba(16, 185, 129, 0.05);
  border: 2px solid #10b981;
  border-radius: 8px;
  cursor: move;
  transition: box-shadow 0.2s;
  overflow: hidden;
}

.placed-widget.selected {
  border-width: 3px;
  box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.3);
}

.placed-widget .widget-label {
  position: absolute;
  top: -22px;
  left: 0;
  background: #10b981;
  color: white;
  font-size: 10px;
  padding: 2px 8px;
  border-radius: 4px;
  white-space: nowrap;
  z-index: 10;
  opacity: 0;
  transition: opacity 0.2s ease;
}

.placed-widget:hover .widget-label,
.placed-widget.selected .widget-label {
  opacity: 1;
}

.widget-content {
  width: 100%;
  height: 100%;
  position: relative;
}

.widget-content.horizontal-scroll {
  display: flex;
  flex-direction: row;
  overflow-x: auto;
  overflow-y: hidden;
  scroll-behavior: smooth;
  -webkit-overflow-scrolling: touch;
  scrollbar-width: thin;
  scrollbar-color: rgba(99, 102, 241, 0.5) transparent;
  cursor: grab;
}

.widget-content.horizontal-scroll:active {
  cursor: grabbing;
}

.widget-content.horizontal-scroll::-webkit-scrollbar {
  height: 6px;
}

.widget-content.horizontal-scroll::-webkit-scrollbar-thumb {
  background: rgba(99, 102, 241, 0.7);
  border-radius: 3px;
}

.widget-content.horizontal-scroll::-webkit-scrollbar-track {
  background: rgba(0, 0, 0, 0.1);
  border-radius: 3px;
}

.widget-content.vertical-scroll {
  scrollbar-width: thin;
  scrollbar-color: rgba(99, 102, 241, 0.5) transparent;
}

.widget-content.vertical-scroll::-webkit-scrollbar {
  width: 4px;
}

.widget-content.vertical-scroll::-webkit-scrollbar-thumb {
  background: rgba(99, 102, 241, 0.5);
  border-radius: 2px;
}

/* Placed Elements */
.placed-child {
  position: absolute;
  background: rgba(99, 102, 241, 0.1);
  border: 2px dashed #6366f1;
  border-radius: 8px;
  cursor: move;
  transition: box-shadow 0.2s;
  overflow: hidden;
}

.placed-child.layout-child {
  position: relative;
  flex-shrink: 0;
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

.placed-child .child-label-small {
  position: absolute;
  top: 2px;
  left: 2px;
  background: rgba(99, 102, 241, 0.8);
  color: white;
  font-size: 8px;
  padding: 1px 4px;
  border-radius: 3px;
  white-space: nowrap;
  z-index: 5;
  opacity: 0;
  transition: opacity 0.2s ease;
}

.placed-child:hover .child-label-small,
.placed-child.selected .child-label-small {
  opacity: 1;
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
  object-fit: var(--asset-object-fit, contain);
}

.asset-placeholder-small {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  height: 100%;
  background: transparent;
  color: #9ca3af;
  font-size: 10px;
  text-align: center;
  padding: 4px;
  border: 1px dashed #6b7280;
  border-radius: 4px;
}

/* Text assets should not have yellow background */
.text-asset .asset-placeholder-small,
.text-asset .text-asset-content {
  background: transparent;
  color: inherit;
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
  margin-top: 12px;
  padding: 6px 12px;
  background: #252536;
  border-radius: 12px;
  flex-shrink: 0;
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

.control-btn.settings-toggle {
  margin-left: auto;
}

/* Design Settings Panel */
.design-settings-panel {
  background: #252536;
  border-radius: 12px;
  padding: 12px;
  width: 100%;
  max-width: 450px;
  flex-shrink: 0;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.settings-section {
  padding-bottom: 10px;
  border-bottom: 1px solid #3d3d5c;
}

.settings-section:last-child {
  border-bottom: none;
  padding-bottom: 0;
}

.settings-section-title {
  color: #e2e8f0;
  font-size: 13px;
  margin: 0 0 8px 0;
  font-weight: 500;
}

.auto-arrange-btn-small {
  padding: 6px 12px;
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  border: none;
  border-radius: 6px;
  color: white;
  font-size: 11px;
  cursor: pointer;
  transition: all 0.2s;
}

.auto-arrange-btn-small:hover {
  opacity: 0.9;
}

/* Widget Size Controls */
.widget-size-controls {
  margin-top: 8px;
  padding: 10px;
  background: #252536;
  border-radius: 12px;
  width: 100%;
  max-width: 380px;
  flex-shrink: 0;
}

.size-title {
  color: #e2e8f0;
  font-size: 14px;
  margin: 0 0 10px 0;
}

.size-inputs {
  display: flex;
  gap: 10px;
  margin-bottom: 10px;
}

.size-input-group {
  flex: 1;
  min-width: 0;
}

.size-input-group label {
  display: block;
  color: #9ca3af;
  font-size: 12px;
  margin-bottom: 4px;
}

.size-input {
  width: 100%;
  padding: 6px 8px;
  background: #1e1e2e;
  border: 1px solid #3d3d5c;
  border-radius: 6px;
  color: white;
  font-size: 13px;
}

.size-input:focus {
  border-color: #6366f1;
  outline: none;
}

.size-presets {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}

.preset-btn {
  padding: 4px 8px;
  background: #1e1e2e;
  border: 1px solid #3d3d5c;
  border-radius: 6px;
  color: #9ca3af;
  font-size: 11px;
  cursor: pointer;
  transition: all 0.2s;
}

.preset-btn:hover {
  background: #3d3d5c;
  color: white;
}

.preset-btn.active {
  background: #6366f1;
  border-color: #6366f1;
  color: white;
}

/* Widget Layout Controls */
.widget-layout-controls {
  margin-top: 8px;
  padding: 10px;
  background: #252536;
  border-radius: 12px;
  width: 100%;
  max-width: 380px;
  flex-shrink: 0;
}

.layout-mode-selector {
  display: flex;
  gap: 8px;
  margin-bottom: 12px;
}

.layout-btn {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
  padding: 8px;
  background: #1e1e2e;
  border: 1px solid #3d3d5c;
  border-radius: 8px;
  color: #9ca3af;
  font-size: 11px;
  cursor: pointer;
  transition: all 0.2s;
}

.layout-btn:hover {
  background: #3d3d5c;
  color: white;
}

.layout-btn.active {
  background: #6366f1;
  border-color: #6366f1;
  color: white;
}

.layout-icon {
  font-size: 18px;
}

.layout-settings {
  display: flex;
  flex-direction: column;
  gap: 8px;
  padding-top: 8px;
  border-top: 1px solid #3d3d5c;
  margin-top: 8px;
}

.layout-setting-row {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.layout-setting-group {
  flex: 1;
  min-width: 60px;
}

.layout-setting-group label {
  display: block;
  color: #9ca3af;
  font-size: 10px;
  margin-bottom: 2px;
}

.layout-options {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.toggle-label-small {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #9ca3af;
  font-size: 12px;
  cursor: pointer;
}

.toggle-label-small input {
  width: 14px;
  height: 14px;
  accent-color: #6366f1;
}

.auto-arrange-btn {
  width: 100%;
  padding: 8px;
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  border: none;
  border-radius: 6px;
  color: white;
  font-size: 12px;
  cursor: pointer;
  transition: all 0.2s;
}

.auto-arrange-btn:hover {
  opacity: 0.9;
  transform: translateY(-1px);
}

/* Scroll Container Styles */
.mobile-screen.layout-mode {
  overflow: hidden;
}

.scroll-container {
  width: 100%;
  height: 100%;
  scrollbar-width: thin;
  scrollbar-color: rgba(99, 102, 241, 0.5) transparent;
}

.scroll-container::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}

.scroll-container::-webkit-scrollbar-track {
  background: rgba(0, 0, 0, 0.1);
  border-radius: 3px;
}

.scroll-container::-webkit-scrollbar-thumb {
  background: rgba(99, 102, 241, 0.5);
  border-radius: 3px;
}

.scroll-container::-webkit-scrollbar-thumb:hover {
  background: rgba(99, 102, 241, 0.7);
}

.scroll-container.horizontal {
  white-space: nowrap;
}

/* Layout Child (in scroll mode) */
.placed-child.layout-child {
  position: relative;
  flex-shrink: 0;
}

.placed-child.layout-child .child-label {
  top: -20px;
}

/* Scroll Indicator */
.scroll-indicator {
  position: absolute;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(99, 102, 241, 0.15);
  color: #6366f1;
  font-size: 11px;
  padding: 4px 12px;
  border-radius: 20px;
  pointer-events: none;
  animation: pulse 2s infinite;
}

.scroll-indicator.horizontal {
  bottom: 8px;
  left: 50%;
  transform: translateX(-50%);
}

.scroll-indicator.vertical {
  right: 8px;
  top: 50%;
  transform: translateY(-50%) rotate(90deg);
}

@keyframes pulse {
  0%, 100% { opacity: 0.5; }
  50% { opacity: 1; }
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

.property-textarea {
  padding: 8px 12px;
  background: #1e1e2e;
  border: 1px solid #3d3d5c;
  border-radius: 6px;
  color: white;
  font-size: 14px;
  width: 100%;
  resize: vertical;
  min-height: 60px;
  font-family: inherit;
}

.property-textarea:focus {
  outline: none;
  border-color: #6366f1;
}

.text-align-buttons {
  display: flex;
  gap: 4px;
}

.align-btn {
  flex: 1;
  padding: 8px;
  background: #1e1e2e;
  border: 1px solid #3d3d5c;
  border-radius: 6px;
  color: white;
  cursor: pointer;
  transition: all 0.2s;
  font-size: 14px;
}

.align-btn:hover {
  background: #2d2d4a;
  border-color: #6366f1;
}

.align-btn.active {
  background: #6366f1;
  border-color: #6366f1;
}

.text-shadow-controls {
  background: #1a1a2a;
  padding: 12px;
  border-radius: 8px;
  border: 1px solid #3d3d5c;
}

.text-shadow-controls .property-row {
  margin-bottom: 8px;
}

.clear-shadow-btn {
  width: 100%;
  padding: 6px 12px;
  background: transparent;
  border: 1px solid #ef4444;
  color: #ef4444;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s;
  font-size: 12px;
  margin-top: 8px;
}

.clear-shadow-btn:hover {
  background: #ef4444;
  color: white;
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

/* New Professional Properties Panel Styles */
.panel-section.full-height {
  flex: 1;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.section-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 12px;
}

.element-badge {
  background: rgba(99, 102, 241, 0.2);
  color: #a5b4fc;
  padding: 4px 10px;
  border-radius: 12px;
  font-size: 11px;
  font-weight: 500;
}

.property-tabs {
  display: flex;
  gap: 4px;
  margin-bottom: 16px;
  background: #1e1e2e;
  padding: 4px;
  border-radius: 8px;
}

.property-tab {
  flex: 1;
  padding: 8px 6px;
  background: transparent;
  border: none;
  color: #6b7280;
  font-size: 11px;
  cursor: pointer;
  border-radius: 6px;
  transition: all 0.2s;
}

.property-tab:hover {
  color: #a5b4fc;
  background: rgba(99, 102, 241, 0.1);
}

.property-tab.active {
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
  color: white;
  font-weight: 500;
}

.property-tab-content {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.property-section-title {
  font-size: 11px;
  font-weight: 600;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-top: 8px;
  padding-bottom: 6px;
  border-bottom: 1px solid #3d3d5c;
}

.property-group.third {
  width: calc(33.333% - 6px);
}

.property-group.quarter {
  width: calc(25% - 6px);
}

.property-row {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

label .unit {
  color: #6b7280;
  font-size: 10px;
  font-weight: normal;
}

label .value-badge {
  background: rgba(99, 102, 241, 0.2);
  color: #a5b4fc;
  padding: 2px 6px;
  border-radius: 4px;
  font-size: 10px;
  margin-left: 6px;
}

.clear-btn {
  padding: 6px 10px;
  background: #1e1e2e;
  border: 1px solid #3d3d5c;
  border-radius: 6px;
  color: #f87171;
  cursor: pointer;
  transition: all 0.2s;
}

.clear-btn:hover {
  background: rgba(248, 113, 113, 0.2);
}

.color-picker.full-width {
  width: 100%;
  height: 36px;
}

/* Modern Corners Grid */
.corners-grid-modern {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 8px;
}

.corner-input-modern {
  display: flex;
  align-items: center;
  gap: 6px;
  background: #1e1e2e;
  padding: 6px 8px;
  border-radius: 6px;
}

.corner-input-modern .corner-label {
  font-size: 14px;
  color: #6b7280;
}

.corner-input-modern .property-input {
  flex: 1;
  padding: 6px 8px;
  font-size: 12px;
  text-align: center;
}

.corner-sync-modern {
  display: flex;
  gap: 8px;
  align-items: center;
  margin-top: 8px;
}

.corner-sync-modern .property-input {
  flex: 1;
}

.sync-btn-modern {
  padding: 8px 12px;
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
  border: none;
  border-radius: 6px;
  color: white;
  font-size: 11px;
  cursor: pointer;
  transition: all 0.2s;
  white-space: nowrap;
}

.sync-btn-modern:hover {
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(99, 102, 241, 0.4);
}

/* Toggle Switch */
.toggle-label {
  display: flex;
  align-items: center;
  gap: 10px;
  cursor: pointer;
  color: #d1d5db;
  font-size: 13px;
}

.toggle-input {
  position: relative;
  width: 44px;
  height: 24px;
  appearance: none;
  background: #3d3d5c;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.3s;
}

.toggle-input:checked {
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
}

.toggle-input::before {
  content: '';
  position: absolute;
  top: 2px;
  left: 2px;
  width: 20px;
  height: 20px;
  background: white;
  border-radius: 50%;
  transition: transform 0.3s;
}

.toggle-input:checked::before {
  transform: translateX(20px);
}

.property-hint {
  font-size: 11px;
  color: #6b7280;
  margin-top: 4px;
  line-height: 1.4;
}

/* Quick Actions Grid */
.quick-actions-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 8px;
}

.quick-action-btn-modern {
  padding: 10px 12px;
  background: #1e1e2e;
  border: 1px solid #3d3d5c;
  border-radius: 8px;
  color: #9ca3af;
  font-size: 11px;
  cursor: pointer;
  transition: all 0.2s;
  text-align: center;
}

.quick-action-btn-modern:hover {
  background: #2d2d44;
  color: white;
  border-color: #6366f1;
}

/* Empty Properties Modern */
.empty-properties-modern {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 30px 20px;
  text-align: center;
  flex: 1;
}

.empty-icon-container {
  width: 60px;
  height: 60px;
  background: rgba(99, 102, 241, 0.1);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 16px;
}

.empty-properties-modern .empty-icon {
  font-size: 28px;
  margin-bottom: 0;
}

.empty-properties-modern h4 {
  color: #d1d5db;
  font-size: 14px;
  margin: 0 0 8px 0;
}

.empty-properties-modern p {
  color: #6b7280;
  font-size: 12px;
  margin: 0;
}

/* Quick Stats */
.quick-stats {
  display: flex;
  gap: 16px;
  margin-top: 20px;
  padding-top: 20px;
  border-top: 1px solid #3d3d5c;
}

.stat-item {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.stat-value {
  font-size: 20px;
  font-weight: 600;
  color: #6366f1;
}

.stat-label {
  font-size: 11px;
  color: #6b7280;
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

/* إضافة ستايل لمقابض تعديل الزوايا */
.corner-handle {
  position: absolute;
  width: 14px;
  height: 14px;
  background: #f59e0b;
  border: 2px solid #fff;
  border-radius: 50%;
  cursor: pointer;
  z-index: 10;
}
.corner-handle.tl { top: -10px; left: -10px; }
.corner-handle.tr { top: -10px; right: -10px; }
.corner-handle.bl { bottom: -10px; left: -10px; }
.corner-handle.br { bottom: -10px; right: -10px; }
</style>
