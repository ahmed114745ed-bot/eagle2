<template>
    <div class="bg-white rounded-lg shadow">
        <div class="p-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h2 class="text-lg font-semibold text-gray-900">⚙️ Widget Settings</h2>
                <button
                    @click="$emit('close')"
                    class="p-1 text-gray-500 hover:bg-gray-100 rounded"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <p class="text-sm text-gray-500 mt-1">{{ widget.widget_type }}</p>
        </div>

        <div class="p-4 space-y-6 max-h-[600px] overflow-y-auto">
   <!-- Allowed Client Settings (editable) -->
      <div class="mt-6">
        <div class="flex items-center justify-between mb-2">
        </div>

        <div v-if="allowedClientSettings.length" class="space-y-4">
          <div
            v-for="setting in allowedClientSettings"
            :key="setting.setting_key"
            class="bg-gray-50 border border-gray-200 rounded-lg p-3"
          >
            <div class="flex items-center justify-between mb-2">
              <div>
                <p class="text-sm font-semibold text-gray-800">{{ setting.setting_label || formatLabel(setting.setting_key) }}</p>
                <p class="text-xs text-gray-500">{{ setting.setting_type || 'value' }} • {{ setting.setting_category === 'secondary' ? 'مظهر' : 'بيانات' }}</p>
              </div>
              <span v-if="setting.default_value !== undefined" class="text-[11px] text-gray-500">الافتراضي: {{ renderDefault(setting) }}</span>
            </div>

            <!-- Input by type -->
            <div>
              <template v-if="setting.setting_type === 'boolean'">
                <label class="inline-flex items-center space-x-2 text-sm text-gray-700">
                  <input type="checkbox" :checked="getSettingValue(setting) === true" @change="setSettingValue(setting, $event.target.checked)" class="rounded" />
                  <span>مفعل</span>
                </label>
              </template>

              <template v-else-if="setting.setting_type === 'select'">
                <select
                  :value="getSettingValue(setting)"
                  @change="setSettingValue(setting, $event.target.value)"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm"
                >
                  <option value="">اختر خيارًا</option>
                  <option v-for="opt in settingOptions(setting)" :key="opt.value || opt.label" :value="opt.value || opt.label">
                    {{ opt.label || opt.value }}
                  </option>
                </select>
              </template>

              <template v-else-if="setting.setting_type === 'number' || setting.setting_type === 'range'">
                <input
                  type="number"
                  :value="getSettingValue(setting)"
                  @input="setSettingValue(setting, $event.target.value)"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm"
                />
              </template>

              <template v-else-if="setting.setting_type === 'color'">
                <input
                  type="color"
                  :value="getSettingValue(setting) || '#000000'"
                  @input="setSettingValue(setting, $event.target.value)"
                  class="w-20 h-10 p-0 border border-gray-300 rounded"
                />
              </template>

              <template v-else-if="setting.setting_type === 'json'">
                <textarea
                  :value="stringifyValue(getSettingValue(setting))"
                  @input="setSettingValue(setting, $event.target.value, true)"
                  rows="3"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm font-mono"
                ></textarea>
              </template>

              <template v-else>
                <input
                  type="text"
                  :value="getSettingValue(setting)"
                  @input="setSettingValue(setting, $event.target.value)"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm"
                />
              </template>
            </div>
          </div>
        </div>

        <div v-else class="text-sm text-gray-500 bg-gray-50 border border-dashed border-gray-200 rounded-lg p-3">
          لا توجد إعدادات متاحة للعميل لهذا الويدجت.
        </div>
      </div>            
            
            <!-- Theme Selection -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">🎨 Theme</label>
                <select v-model="localSettings.theme_id" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option v-for="theme in widgetThemes" :key="theme.id" :value="theme.id">
                            {{ theme.theme_name }}
                        </option>
                </select>

                <!-- Children of selected theme -->
            <!-- Children of Selected Theme -->
                    <div v-if="selectedThemeChildren.length" class="mt-4 space-y-3">
                      <h4 class="text-lg font-semibold text-gray-700 mb-2">🧩 Children</h4>
                      <div class="space-y-2">
                        <div
                          v-for="child in selectedThemeChildren"
                          :key="child.id"
                          class="bg-gray-50 border border-gray-200 rounded-lg p-3 shadow-sm"
                        >
                          <div class="flex items-start justify-between gap-3">
                            <button
                              class="flex-1 text-left"
                              @click="toggleChildAssets(child)"
                            >
                              <div class="flex items-center justify-between">
                                <div>
                                  <p class="text-base font-semibold text-gray-900">{{ child.label }}</p>
                                  <p class="text-xs text-gray-500">ID: {{ child.id }}</p>
                                </div>
                                <svg
                                  class="w-4 h-4 text-gray-500 transition-transform"
                                  :class="expandedChildId === child.id ? 'rotate-90' : ''"
                                  fill="none"
                                  stroke="currentColor"
                                  viewBox="0 0 24 24"
                                >
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                              </div>
                            </button>

                            <div class="flex items-center gap-2">
                              <button @click.stop="openEditChildModal(child)" class="px-2 py-1 text-xs bg-yellow-500 text-white rounded hover:bg-yellow-600">Edit</button>
                              <button
                                @click.stop="confirmDeleteChild(child)"
                                class="px-2 py-1 text-xs rounded text-white transition"
                                :class="resolveChildVisible(child) ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700'"
                              >
                                {{ resolveChildVisible(child) ? 'Hide (config)' : 'Show (config)' }}
                              </button>
                            </div>
                          </div>

                          <div v-if="expandedChildId === child.id" class="mt-3 border-t border-gray-200 pt-3 space-y-3">
                            <div v-if="loadingChildAssets && selectedChild?.id === child.id" class="text-sm text-gray-500">
                              ⏳ Loading assets...
                            </div>

                            <div v-else-if="selectedChild && selectedChild.id === child.id" class="space-y-3">
                        

                              <div v-if="showAddChildAssetForm && selectedChild?.id === child.id" class="bg-gray-100 border border-gray-200 rounded-lg p-4 space-y-3">
                                <div class="grid grid-cols-3 gap-3">
                                  <select v-model="newAsset.input_type" class="px-3 py-2 bg-white border border-gray-300 rounded text-sm">
                                    <option value="text">نص / رابط</option>
                                    <option value="file">ملف (رفع)</option>
                                  </select>

                                  <input v-model="newAsset.asset_key" type="text" placeholder="Asset Key"
                                         class="px-3 py-2 bg-white border border-gray-300 rounded text-sm" />

                                  <input v-model="newAsset.asset_label" type="text" placeholder="Asset Label"
                                         class="px-3 py-2 bg-white border border-gray-300 rounded text-sm" />
                                </div>

                                <div class="grid grid-cols-1 gap-3">
                                  <div v-if="newAsset.input_type === 'text'">
                                    <input v-model="newAsset.default_url" type="text" placeholder="Default URL / رابط افتراضي"
                                           class="px-3 py-2 bg-white border border-gray-300 rounded text-sm" />
                                  </div>

                                  <div v-else class="space-y-2">
                                    <div class="flex gap-3 items-center flex-wrap">
                                      <select v-model="newAsset.asset_type" class="px-3 py-2 bg-white border border-gray-300 rounded text-sm">
                                        <option value="image">Image</option>
                                        <option value="svga">SVGA</option>
                                        <option value="vap">VAP</option>
                                        <option value="alpha">Alpha</option>
                                      </select>

                                      <input id="new-child-asset-file-ws" type="file" class="hidden" :accept="getAcceptTypes(newAsset.asset_type)" @change="onNewAssetFileChange" />
                                      <label for="new-child-asset-file-ws" class="px-3 py-2 bg-blue-600 text-white rounded cursor-pointer hover:bg-blue-700">📤 رفع ملف</label>
                                      <span class="text-sm text-gray-600" v-if="newAsset.file">{{ newAsset.file.name }}</span>
                                      <span class="text-sm text-gray-500" v-else>لم يتم اختيار ملف</span>
                                      <span v-if="uploadingNewAsset" class="text-sm text-gray-500">⏳ جاري الرفع...</span>
                                    </div>
                                  </div>
                                </div>

                                <textarea v-model="newAsset.description" placeholder="وصف" class="w-full px-3 py-2 bg-white border border-gray-300 rounded text-sm"></textarea>

                                <div class="flex justify-end gap-2">
                                  <button @click="showAddChildAssetForm = false; newAsset.file = null" class="px-3 py-1 bg-gray-200 text-gray-700 rounded text-sm">إلغاء</button>
                                  <button @click="saveChildAsset" class="px-3 py-1 bg-green-600 text-white rounded text-sm">حفظ</button>
                                </div>
                              </div>

                              <div v-if="selectedChild.assets?.length">
                                <h4 class="text-sm font-medium text-gray-600 mb-2">Existing Assets</h4>
                                <ul class="space-y-2">
                                  <li
                                    v-for="asset in selectedChild.assets"
                                    :key="asset.id"
                                    class="bg-white border border-gray-200 rounded p-3 flex gap-4"
                                  >
                                    <div class="relative group shrink-0">
                                      <div
                                        class="w-16 h-16 rounded-lg overflow-hidden border border-gray-200 bg-gradient-to-br from-gray-50 to-gray-100 flex items-center justify-center"
                                      >
                                        <template v-if="asset.type === 'file' && asset.asset_type === 'image' && fileLink(asset)">
                                          <img
                                            :src="fileLink(asset)"
                                            alt="Asset preview"
                                            class="w-full h-full object-contain transition-transform duration-150 group-hover:scale-105"
                                          />
                                        </template>
                                        <span v-else class="text-[11px] text-gray-500">No preview</span>
                                      </div>
                                      <a
                                        v-if="fileLink(asset)"
                                        :href="fileLink(asset)"
                                        target="_blank"
                                        class="absolute -bottom-2 left-1/2 -translate-x-1/2 text-[10px] text-blue-600 opacity-0 group-hover:opacity-100 bg-white px-2 py-0.5 rounded border border-gray-200 shadow"
                                      >فتح</a>
                                    </div>

                                    <div class="flex-1 space-y-2 min-w-0">
                                      <div class="flex items-start justify-between gap-3">
                                        <div class="space-y-1 min-w-0">
                                          <div class="flex items-center gap-2 flex-wrap">
                                            <span class="text-gray-900 font-medium">{{ asset.asset_label || asset.asset_key }}</span>
                                            <span class="text-xs text-gray-600 px-2 py-0.5 bg-gray-100 rounded">{{ asset.type }}</span>
                                            <span v-if="asset.asset_type" class="text-xs text-gray-600 px-2 py-0.5 bg-gray-100 rounded">{{ asset.asset_type }}</span>
                                            <span v-if="asset.is_visible === false" class="text-xs text-red-600 px-2 py-0.5 bg-red-50 rounded">Hidden</span>
                                            <span v-else class="text-xs text-green-600 px-2 py-0.5 bg-green-50 rounded">Visible</span>
                                          </div>
                                          <div v-if="fileLink(asset)" class="text-[11px] text-gray-500 break-all">{{ fileLink(asset) }}</div>
                                        </div>

                                        <div class="flex items-center gap-2">
                                          <button
                                            @click="openEditAssetModal(asset)"
                                            class="px-2 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-700"
                                          >Edit</button>
                                        </div>
                                      </div>

                                      <div
                                        v-if="asset.type === 'text'"
                                        class="border border-gray-100 rounded-lg bg-gray-50 p-3 text-sm text-gray-700 break-all"
                                      >
                                        {{ asset.text || asset.default_url || '—' }}
                                      </div>

                                      <div
                                        v-else-if="asset.type === 'file' && asset.asset_type === 'image' && fileLink(asset)"
                                        class="border border-gray-100 rounded-lg bg-gray-50 p-3 flex items-center justify-center"
                                      >
                                        <a
                                          :href="fileLink(asset)"
                                          target="_blank"
                                          rel="noopener noreferrer"
                                          class="block group"
                                        >
                                          <img
                                            :src="fileLink(asset)"
                                            alt="Asset preview"
                                            class="w-full h-32 object-cover rounded transition-transform duration-150 group-hover:scale-[1.02]"
                                          />
                                          <p class="mt-2 text-[11px] text-blue-600 text-center">فتح في نافذة جديدة</p>
                                        </a>
                                      </div>

                                      <div v-else class="text-sm text-gray-500">لا يوجد عرض متاح.</div>
                                    </div>
                                  </li>
                                </ul>
                              </div>
                              <div v-else class="text-sm text-gray-500">No assets yet.</div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>

   

<!-- Edit Child Modal -->
<div v-if="showEditChildModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
  <div class="bg-gray-800 rounded-lg shadow-xl p-6 w-[520px] max-h-[80vh] overflow-y-auto">
    <h3 class="text-lg font-semibold text-white mb-4">✏️ Edit Child</h3>

    <div class="space-y-3">
      <div class="grid grid-cols-2 gap-3">
        <input v-model="editChildForm.label" type="text" placeholder="Label" class="px-3 py-2 bg-gray-600 border border-gray-500 rounded text-white text-sm" />
      </div>

      <div class="grid grid-cols-2 gap-3">
      

        <select v-model="editChildForm.position" class="px-3 py-2 bg-gray-600 border border-gray-500 rounded text-white text-sm">
          <option :value="null">Position (none)</option>
          <option value="left">Left</option>
          <option value="right">Right</option>
        </select>
      </div>

      <div class="flex items-center gap-4">
        <label class="flex items-center gap-2 text-sm text-gray-200"><input type="checkbox" v-model="editChildForm.is_visible" /> Visible</label>
        <label class="flex items-center gap-2 text-sm text-gray-200"><input type="checkbox" v-model="editChildForm.is_active" /> Active</label>
      </div>

      <div class="flex justify-end gap-2">
        <button @click="closeEditChildModal" class="px-3 py-1 bg-gray-500 text-white rounded text-sm">إلغاء</button>
        <button @click="updateChild" class="px-3 py-1 bg-blue-600 text-white rounded text-sm">حفظ</button>
      </div>
    </div>
  </div>
</div>

<!-- Delete Child Confirmation Modal -->
<div v-if="showDeleteChildConfirm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
  <div class="bg-white rounded-lg shadow p-6 w-[420px]">
    <h3 class="text-lg font-semibold mb-2">تأكيد الازالة</h3>
    <p class="text-sm text-gray-600 mb-4">هل أنت متأكد أنك تريد تغيير حالة العرض لهذا الطفل في هذا الكونفيج فقط: <strong>{{ deletingChild?.label }}</strong>؟</p>
    <div class="flex justify-end gap-2">
      <button @click="cancelDeleteChild" class="px-3 py-1 bg-gray-200 rounded">إلغاء</button>
      <button @click="deleteChild(deletingChild)" class="px-3 py-1 bg-red-600 text-white rounded">تبديل الإخفاء</button>
    </div>
  </div>
</div>


    <!-- Edit Asset Modal -->
    <div v-if="showEditAssetModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-gray-800 rounded-lg shadow-xl p-6 w-[600px] max-h-[80vh] overflow-y-auto">
        <h3 class="text-lg font-semibold text-white mb-4">✏️ Edit Asset</h3>

        <div class="space-y-3">
          <div class="grid grid-cols-2 gap-3">
            <input v-model="editAssetForm.asset_label" type="text" placeholder="Asset Label" class="px-3 py-2 bg-gray-600 border border-gray-500 rounded text-white text-sm" />
          </div>

           <input type="hidden" v-model="editAssetForm.file_path" name="file_path" />
           <input type="hidden" v-model="editAssetForm.max_size" name="max_size" />

          <div class="grid grid-cols-1 gap-3">
           
              <button @click="openLibrary(editAssetForm.input_type)"   class="px-3 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                  📚 اختيار من المكتبة   {{ editAssetForm.input_type }}
                </button>
                <AssetLibraryModal
                v-if="showLibrary"
                 :initial-type="libraryType"
                @close="showLibrary = false"
                @select="onAssetSelect"
              />
            <div v-if="editAssetForm.input_type === 'text'">
              <input v-model="editAssetForm.text" type="text" placeholder="" class="px-3 py-2 bg-gray-600 border border-gray-500 rounded text-white text-sm" />
            </div>

            <div v-else class="space-y-2">
              <div class="flex gap-3 items-center">
                
                <div class="mt-2">
                  <template v-if="editAssetForm.file">
                    <img v-if="editAssetForm.asset_type === 'image'" :src="URL.createObjectURL(editAssetForm.file)" class="w-full h-32 object-cover rounded" />
                    <div v-else class="w-full h-32 bg-gray-100 flex items-center justify-center rounded text-sm text-gray-700">
                      {{ editAssetForm.file.name }}
                    </div>
                  </template>

                  <template v-else-if="editAssetForm.default_url">
                    <img v-if="editAssetForm.asset_type === 'image'" :src="editAssetForm.default_url" class="w-full h-32 object-cover rounded" />
                    <div v-else class="w-full h-32 bg-gray-100 flex items-center justify-center rounded text-sm text-gray-700">
                      {{ editAssetForm.default_url }}
                    </div>
                  </template>
                </div>
                <input id="edit-child-asset-file-ws" type="file" class="hidden" :accept="getAcceptTypes(editAssetForm.asset_type)" @change="onEditAssetFileChange" />
                <label for="edit-child-asset-file-ws" class="px-3 py-2 bg-blue-600 text-white rounded cursor-pointer hover:bg-blue-700">📤 استبدال ملف</label>
                <span class="text-sm text-gray-300" v-if="editAssetForm.file">{{ editAssetForm.file.name }}</span>
   

              </div>

              
            </div>

            <textarea v-model="editAssetForm.description" placeholder="وصف" class="w-full px-3 py-2 bg-gray-600 border border-gray-500 rounded text-white text-sm"></textarea>
          </div>

          <div class="flex justify-end gap-2">
            <button @click="closeEditAssetModal" class="px-3 py-1 bg-gray-500 text-white rounded text-sm">إلغاء</button>
            <button @click="updateAsset" class="px-3 py-1 bg-blue-600 text-white rounded text-sm">حفظ</button>
          </div>
        </div>
      </div>
    </div>

        </div>

        <!-- Save Button -->
        <div class="p-4 border-t border-gray-200">
            <button
                @click="saveSettings"
                class="w-full py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
            >
                💾 Save Settings
            </button>
        </div>
    </div>
  </div>
</template>



<script>
import { ref, computed, watch, onMounted, nextTick } from 'vue';
import axios from 'axios';
import { widgetsApi } from '../services/api';
import { adminApi } from '../services/adminApi';
import AssetLibraryModal from './AssetLibraryModal.vue';

export default {
  name: 'WidgetSettings',
  props: {
    widget: { type: Object, required: true },
    configurationId: { type: Number, required: true },
    screenWidgets: { type: Array, required: true },
    widgetOverrides: { type: Array, default: () => [] },



  },
  emits: ['update', 'close'],
  components: {
    AssetLibraryModal,
  },

 setup(props, { emit }) {
  
const showLibrary = ref(false);
const libraryType = ref(''); // هذا سيحمل نوع العنصر عند الفتح

const openLibrary = (type) => {
  // احرص على تمرير نفس نوع الأصل الجاري تعديله: للنص نمرر 'text' وللملف نمرر asset_type الفعلي مثل image/svga
  if (type === 'file') {
    libraryType.value = editAssetForm.value.input_type || 'file';
  } else {
    libraryType.value = 'text';
  }
  console.log('[WidgetSettings] openLibrary target type', { requested: type, libraryType: libraryType.value, assetType: editAssetForm.value.asset_type, inputType: editAssetForm.value.input_type });
  showLibrary.value = true;  // فتح المودال
};

const onAssetSelect = (asset) => {
  if (!asset) return;

  const targetType = editAssetForm.value.input_type || libraryType.value;

  // enforce same input type as the asset being edited
  if (targetType === 'file') {
    if (asset.type !== 'file') return;
    if (editAssetForm.value.asset_type && asset.asset_type && asset.asset_type !== editAssetForm.value.asset_type) return;
  }
  if (targetType === 'text' && asset.type !== 'text') return;

  if (targetType === 'text') {
    editAssetForm.value.text = asset.text;
    editAssetForm.value.default_url = asset.default_url || asset.asset_label || '';
    editAssetForm.value.asset_type = '';
    editAssetForm.value.file = null;
  } else if (targetType === 'file') {
    editAssetForm.value.asset_type = asset.asset_type || editAssetForm.value.asset_type || 'image';
    editAssetForm.value.file = null;
    editAssetForm.value.default_url = asset.default_url || '';
    editAssetForm.value.file_path = asset.file_path || '';
    editAssetForm.value.max_size = asset.max_size || '';
  }

  editAssetForm.value.asset_key = asset.asset_key || '';
  editAssetForm.value.asset_label = asset.asset_label || '';
  editAssetForm.value.description = asset.description || '';

  showLibrary.value = false;

  console.log('Selected asset:', asset);
};


const onEditAssetFileChange = (e) => {
  editAssetForm.value.file = e.target.files?.[0] || null;
};

    const localSettings = ref({
      theme_id: props.widget.theme_id || null,
      primary_settings: { ...props.widget.primary_settings },
      secondary_settings: { ...props.widget.secondary_settings },
      position: { ...props.widget.position },
      action: props.widget.action || { type: 'screen' },
    });

    // Widget definitions (from library) to know which settings are client-visible (is_hidden = false)
    const widgetDefinitions = ref([]);
    const ensureWidgetDefinitions = async () => {
      if (widgetDefinitions.value.length) return;
      try {
        const res = await widgetsApi.getAll();
        widgetDefinitions.value = res.data?.data || res.data || [];
      } catch (err) {
        console.error('Failed to load widget definitions', err);
        widgetDefinitions.value = [];
      }
    };

    const setSettingValue = (setting, rawValue, isJson = false) => {
      if (!setting?.setting_key) return;
      const cat = normalizeCategory(setting.setting_category);
      const target = cat === 'secondary'
        ? localSettings.value.secondary_settings
        : localSettings.value.primary_settings;

      let value = rawValue;

      if (setting.setting_type === 'number' || setting.setting_type === 'range') {
        const num = rawValue === '' ? null : Number(rawValue);
        value = isNaN(num) ? null : num;
      } else if (setting.setting_type === 'boolean') {
        value = !!rawValue;
      } else if (setting.setting_type === 'json' && isJson) {
        try {
          value = rawValue ? JSON.parse(rawValue) : null;
        } catch (e) {
          value = rawValue; // keep raw until valid
        }
      }

      target[setting.setting_key] = value;
    };

    const stringifyValue = (val) => {
      if (val === null || val === undefined) return '';
      if (typeof val === 'string') return val;
      try {
        return JSON.stringify(val, null, 2);
      } catch (e) {
        return '';
      }
    };

    const renderDefault = (setting) => {
      const def = setting.default_value;
      if (def === null || def === undefined || def === '') return '—';
      if (typeof def === 'boolean') return def ? 'On' : 'Off';
      if (Array.isArray(def)) return def.join(', ');
      if (typeof def === 'object') return JSON.stringify(def);
      return def;
    };

    const settingOptions = (setting) => {
      if (!setting) return [];
      if (Array.isArray(setting.options)) return setting.options;
      if (typeof setting.options === 'string') {
        try {
          const parsed = JSON.parse(setting.options);
          if (Array.isArray(parsed)) return parsed;
        } catch (e) {
          return [];
        }
      }
      return [];
    };

    // Local, in-memory child visibility overrides (per configuration, per widget) so the UI reacts immediately
    const localChildOverrides = ref([]);

    // قائمة ثيمات الويدجت
    const widgetThemes = ref([]);

      
 

    // جلب الثيمات من API
    const loadThemes = async (widgetId ,configurationId) => {
      try {
        const response = await widgetsApi.getThemesByConfigurationId(widgetId, configurationId);
        widgetThemes.value = response.data.data || [];
      } catch (err) {
        widgetThemes.value = [];
        console.error('Failed to load themes:', err);
      }
    };

    onMounted(() => {
      if (props.widget?.id) loadThemes(props.widget.id , props.configurationId);
      ensureWidgetDefinitions();
    });




    const currentWidgetOverride = computed(() => {
      const candidates = [
        props.widget?.config_widget_override_id,
        props.widget?.override_id,
        props.widget?.screen_widget_id,
        props.widget?.screenWidgetId,
        props.widget?.screen_widget?.id,
        props.widget?.pivot?.id,
        props.widget?.id,
      ].filter(Boolean);

      return props.widgetOverrides?.find((ov) =>
        candidates.includes(ov.screen_widget_id) || candidates.includes(ov.id)
      );
    });

    const applyDefaultsNow = () => nextTick(() => ensureDefaults(allowedClientSettings.value));

    const hydrateSettings = (widgetObj, overrideObj) => {
      const ov = overrideObj || {};
      const settingsFromOv = ov.settings || {};
      localSettings.value = {
        theme_id: ov.selected_theme_id ?? settingsFromOv.theme_id ?? widgetObj?.theme_id ?? null,
        primary_settings: { ...(settingsFromOv.primary_settings || {}), ...(widgetObj?.primary_settings || {}) },
        secondary_settings: { ...(settingsFromOv.secondary_settings || {}), ...(widgetObj?.secondary_settings || {}) },
        position: { ...(widgetObj?.position || {}) },
        action: settingsFromOv.action || widgetObj?.action || { type: 'screen' },
      };

      applyDefaultsNow();
    };

watch(
  () => props.widget,
  async (newWidget) => {
    console.log('Widget changed:', newWidget);

    hydrateSettings(newWidget, currentWidgetOverride.value);
    if (newWidget?.id) {
      console.log('Loading themes for widget id:', newWidget.id);
      await loadThemes(newWidget.id ,props.configurationId);
      console.log('Themes loaded:', widgetThemes.value);

    }

    // Ensure widget definitions are loaded so we can filter client-visible settings
    await ensureWidgetDefinitions();
  },
  { immediate: true }
);

watch(
  () => currentWidgetOverride.value,
  (ov) => {
    if (props.widget) {
      hydrateSettings(props.widget, ov);
    }
  },
  { immediate: true }
);

// Re-apply defaults whenever definitions change
watch(
  () => widgetDefinitions.value,
  () => applyDefaultsNow(),
  { deep: true }
);

    const resolveChildVisible = (child) => {
      const key = child.theme_child_id ?? child.id;
      const local = localChildOverrides.value.find((c) => c.theme_child_id === key);
      if (local) return local.is_visible !== false;
      if (child.is_visible !== undefined) return child.is_visible !== false;
      return true;
    };

// Keep local child overrides in sync with persisted overrides when widget override changes
watch(
  () => currentWidgetOverride.value,
  (ov) => {
    const overrideChildren = ov?.settings?.children;
    if (Array.isArray(overrideChildren) && overrideChildren.length) {
      localChildOverrides.value = overrideChildren.map((c) => ({
        theme_child_id: c.theme_child_id ?? c.id,
        is_visible: c.is_visible !== false,
      }));
    } else {
      localChildOverrides.value = [];
    }
  },
  { immediate: true }
);




    const filteredSecondarySettings = computed(() => {
      const settings = { ...localSettings.value.secondary_settings };
      delete settings.assets;
      return settings;
    });

    const normalizeCategory = (category) => (category || 'primary').toString().toLowerCase();

    const allowedClientSettings = computed(() => {
      const widgetDef = widgetDefinitions.value.find((w) => w.widget_key === props.widget?.widget_key);

      const primaryDefs = (widgetDef?.primary_settings || []).map((s) => ({
        ...s,
        setting_category: 'primary',
      }));

      const secondaryDefs = (widgetDef?.secondary_settings || []).map((s) => ({
        ...s,
        setting_category: 'secondary',
      }));

      return [...primaryDefs, ...secondaryDefs]
        .filter((s) => s && s.setting_key)
        .filter((s) => s.is_hidden !== true);
    });

    const firstOptionValue = (setting) => {
      const opts = settingOptions(setting);
      if (!opts.length) return undefined;
      const first = opts[0];
      return first.value ?? first.label ?? '';
    };

    const isEmptyValue = (val) => val === null || val === undefined || val === '';

    const ensureDefaultForSetting = (setting) => {
      if (!setting?.setting_key) return;
      const current = getSettingValue(setting);
      if (!isEmptyValue(current)) return;

      let fallback = setting.default_value;

      if (setting.setting_type === 'select') {
        if (isEmptyValue(fallback)) {
          fallback = firstOptionValue(setting);
        }
      }

      // If still empty, do nothing
      if (isEmptyValue(fallback)) return;

      setSettingValue(setting, fallback, setting.setting_type === 'json');
    };

    const ensureDefaults = (settings) => {
      if (!Array.isArray(settings)) return;
      settings.forEach((s) => ensureDefaultForSetting(s));
    };

    const resolveSettingValue = (setting) => {
      if (!setting?.setting_key) return null;
      const cat = normalizeCategory(setting.setting_category);
      const source = cat === 'secondary'
        ? localSettings.value.secondary_settings
        : localSettings.value.primary_settings;
      return source ? source[setting.setting_key] : null;
    };

    const getSettingValue = (setting) => resolveSettingValue(setting);

    const formatSettingValue = (setting) => {
      const value = resolveSettingValue(setting);
      if (value === null || value === undefined || value === '') return '—';
      if (Array.isArray(value)) return value.join(', ');
      if (typeof value === 'object') return JSON.stringify(value);
      if (typeof value === 'boolean') return value ? 'On' : 'Off';
      return value;
    };

    const formatLabel = (key) =>
      key.replace(/_/g, ' ').replace(/\b\w/g, (l) => l.toUpperCase());

    const updatePrimarySetting = (key, value) => {
      localSettings.value.primary_settings[key] = value;
    };

    const updateSecondarySetting = (key, value) => {
      localSettings.value.secondary_settings[key] = value;
    };

    const buildPayload = () => ({
      configuration_id: props.configurationId,
      theme_id: localSettings.value.theme_id,
      primary_settings: localSettings.value.primary_settings,
      secondary_settings: localSettings.value.secondary_settings,
      position: localSettings.value.position,
      action: localSettings.value.action,
      // Persist per-child visibility keyed by the underlying theme child id (not the override id)
      children: selectedThemeChildren.value.map((c) => ({
        theme_child_id: c.theme_child_id ?? c.id,
        is_visible: resolveChildVisible(c),
      })),
    });

    const saveSettings = () => {
      emit('update', props.widget.id, buildPayload());
    };

    const selectedThemeChildren = computed(() => {
      const theme = widgetThemes.value.find(
        (t) => t.id === localSettings.value.theme_id
      );

      const baseChildren = (theme?.children || []).map((c) => ({ ...c }));

      // Apply local overrides first (live toggles), otherwise fall back to persisted overrides from current widget override
      const overrideChildren = currentWidgetOverride.value?.settings?.children || [];
      const mergedOverrides = localChildOverrides.value.length
        ? localChildOverrides.value
        : overrideChildren.map((c) => ({
            theme_child_id: c.theme_child_id ?? c.id,
            is_visible: c.is_visible !== false,
          }));

      const visMap = new Map(
        mergedOverrides.map((c) => [
          c.theme_child_id,
          c.is_visible,
        ])
      );

      return baseChildren.map((c) => {
        const key = c.theme_child_id ?? c.id;
        if (visMap.has(key)) {
          c.is_visible = visMap.get(key);
        } else {
          c.is_visible = resolveChildVisible(c);
        }
        return c;
      });
    });

    const selectedChild = ref(null);
    const loadingChildAssets = ref(false);
    const showChildAssetsModal = ref(false);
    const expandedChildId = ref(null);

    // Child edit/delete state
    const showEditChildModal = ref(false);
    const editChildForm = ref({
      id: null,
      theme_id: null,
      child_key: '',
      label: '',
      child_type: 'tab',
      action: '',
      is_visible: true,
      is_active: true,
      position: null,
    });

    // Delete confirmation state
    const showDeleteChildConfirm = ref(false);
    const deletingChild = ref(null);

    // Add/Edit/Delete asset UI state
    const showAddChildAssetForm = ref(false);
    const newAsset = ref({
      asset_key: '',
      input_type: 'text',
      asset_type: 'image',
      asset_label: '',
      default_url: '',
      description: '',
      file: null,
      max_file_size: 5,
    });

    const uploadingNewAsset = ref(false);

    const showEditAssetModal = ref(false);
    const editAssetForm = ref({
      id: null,
      asset_key: '',
      input_type: 'text',
      asset_type: 'image',
      asset_label: '',
      default_url: '',
      description: '',
      file: null,
      max_file_size: 5,
    });
    const uploadingAssetId = ref(null);

const loadChildAssets = async (child) => {
  loadingChildAssets.value = true;
  try {
    const res = await axios.get(`/api/dashboard/configurations/${props.configurationId}/children/${child.id}/assets`);
    const assets = Array.isArray(res.data?.data) ? res.data.data : [];
    selectedChild.value = { ...child, assets };
    showChildAssetsModal.value = false;
  } catch (err) {
    console.error('Failed to load child assets:', err);
    selectedChild.value = { ...child, assets: [] };
  } finally {
    loadingChildAssets.value = false;
  }
};

const toggleChildAssets = (child) => {
  if (expandedChildId.value === child.id) {
    expandedChildId.value = null;
    selectedChild.value = null;
    showAddChildAssetForm.value = false;
    return;
  }
  expandedChildId.value = child.id;
  showAddChildAssetForm.value = false;
  loadChildAssets(child);
};







// Child edit/delete helpers
const openEditChildModal = (child) => {
  editChildForm.value = {
    id: child.id,
    theme_id: child.theme_id || localSettings.value.theme_id,
    child_key: child.child_key || '',
    label: child.label || '',
    child_type: child.child_type || 'tab',
    action: child.action || '',
    is_visible: child.is_visible !== undefined ? !!child.is_visible : true,
    is_active: child.is_active !== undefined ? !!child.is_active : true,
    position: child.position || null,
  };
  showEditChildModal.value = true;
};

const closeEditChildModal = () => {
  showEditChildModal.value = false;
  editChildForm.value = {
    id: null,
    theme_id: null,
    child_key: '',
    label: '',
    child_type: 'tab',
    action: '',
    is_visible: true,
    is_active: true,
    position: null,
  };
};

const updateChild = async () => {
  if (!editChildForm.value.id) return;
  try {
    const payload = {
      configuration_id: props.configurationId,
      child_key: editChildForm.value.child_key,
      label: editChildForm.value.label,
      child_type: editChildForm.value.child_type,
      action: editChildForm.value.action,
      is_visible: editChildForm.value.is_visible,
      is_active: editChildForm.value.is_active,
      position: editChildForm.value.position,
    };

    const res = await axios.put(`/api/dashboard/theme-children/${editChildForm.value.id}`, payload);
    const updated = res.data?.data || res.data;

    // update local widgetThemes children
    const th = widgetThemes.value.find((t) => t.id === editChildForm.value.theme_id);
    if (th) {
      const idx = (th.children || []).findIndex((c) => c.id === updated.id);
      if (idx !== -1) {
        th.children.splice(idx, 1, { ...th.children[idx], ...updated });
      }
    }

    // if selectedChild is edited, update it too
    if (selectedChild.value && selectedChild.value.id === updated.id) {
      selectedChild.value = { ...selectedChild.value, ...updated };
    }

    closeEditChildModal();
  } catch (err) {
    console.error('Failed to update child:', err);
    alert('Failed to update child');
  }
};

const confirmDeleteChild = (child) => {
  deletingChild.value = child;
  showDeleteChildConfirm.value = true;
};

const cancelDeleteChild = () => {
  deletingChild.value = null;
  showDeleteChildConfirm.value = false;
};
// Prefer explicit CDN/storage base (falls back to GCS bucket if not provided)
const storageCdnBase = window?.storageCdn || window?.STORAGE_CDN || 'https://storage.googleapis.com/eagle-t';
const storageBase = window?.storageUrl || window?.STORAGE_URL || window?.assetBaseUrl || storageCdnBase;
const buildStorageLink = (path) => {
  if (!path) return null;
  if (/^https?:\/\//i.test(path)) return path;

  const base = storageBase || window.location.origin;
  const normalizedBase = base.endsWith('/') ? base.slice(0, -1) : base;

  // avoid duplicating /storage when the base already points to the storage root or a CDN bucket
  const baseHasStorage = /\/storage\/?$/.test(normalizedBase) || normalizedBase.includes('storage.googleapis.com');
  const normalizedPath = path.startsWith('/') ? path.slice(1) : path.replace(/^storage\//i, '');

  return baseHasStorage
    ? `${normalizedBase}/${normalizedPath}`
    : `${normalizedBase}/storage/${normalizedPath}`;
};

const fileLink = (asset) => {
  if (!asset) return null;
  if (asset.file_url) return asset.file_url;
  const fromPath = buildStorageLink(asset.file_path);
  if (fromPath) return fromPath;
  if (asset.default_url) return asset.default_url;
  return null;
};

const deleteChild = async (child) => {
  // Toggle visibility locally for this configuration, then persist via parent update flow
  const nextVisible = !resolveChildVisible(child);

  // Update in-memory overrides so the UI reflects immediately
  const key = child.theme_child_id ?? child.id;
  const idxLocal = localChildOverrides.value.findIndex((c) => c.theme_child_id === key);
  if (idxLocal !== -1) {
    localChildOverrides.value[idxLocal] = { ...localChildOverrides.value[idxLocal], is_visible: nextVisible };
  } else {
    localChildOverrides.value.push({ theme_child_id: key, is_visible: nextVisible });
  }

  const th = widgetThemes.value.find((t) => t.id === child.theme_id);
  if (th) {
    th.children = (th.children || []).map((c) => {
      const key = c.theme_child_id ?? c.id;
      const targetKey = child.theme_child_id ?? child.id;
      return key === targetKey ? { ...c, is_visible: nextVisible } : c;
    });
  }

  if (selectedChild.value && selectedChild.value.id === child.id) {
    selectedChild.value = { ...selectedChild.value, is_visible: nextVisible };
  }

  if (showDeleteChildConfirm.value) {
    showDeleteChildConfirm.value = false;
    deletingChild.value = null;
  }

  // Persist through parent save handler (sends children array per config) immediately
  emit('update', props.widget.id, buildPayload());
};

// Add Asset helpers
const onNewAssetFileChange = (e) => {
  newAsset.value.file = e.target.files?.[0] || null;
};

const saveChildAsset = async () => {
  if (!newAsset.value.asset_key) return;

  try {
    let res;

    if (newAsset.value.input_type === 'file') {
      if (!newAsset.value.file) {
        alert('Please select a file');
        return;
      }

      const maxMb = Number(newAsset.value.max_file_size) || 5;
      if (newAsset.value.file.size > maxMb * 1024 * 1024) {
        alert(`File is too large (max ${maxMb} MB)`);
        return;
      }

      uploadingNewAsset.value = true;

      const fd = new FormData();
      fd.append('file', newAsset.value.file);
      fd.append('asset_key', newAsset.value.asset_key);
      fd.append('asset_type', newAsset.value.asset_type);
      fd.append('asset_label', newAsset.value.asset_label);
      fd.append('description', newAsset.value.description);
      fd.append('max_size', newAsset.value.max_file_size);

      fd.append('configuration_id', props.configurationId);

      res = await axios.post(`/api/dashboard/theme-children/${selectedChild.value.id}/assets`, fd, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });
    } else {
      const payload = {
        configuration_id: props.configurationId,
        asset_key: newAsset.value.asset_key,
        asset_type: newAsset.value.asset_type,
        asset_label: newAsset.value.asset_label,
        default_url: newAsset.value.default_url,
        description: newAsset.value.description,
        max_size: newAsset.value.max_file_size,
      };

      res = await axios.post(`/api/dashboard/theme-children/${selectedChild.value.id}/assets`, payload);
    }

    const created = res.data?.data || res.data;
    if (!selectedChild.value.assets) selectedChild.value.assets = [];
    selectedChild.value.assets.unshift(created);

    newAsset.value = {
      asset_key: '',
      input_type: 'text',
      asset_type: 'image',
      asset_label: '',
      default_url: '',
      description: '',
      file: null,
      max_file_size: 5,
    };

    showAddChildAssetForm.value = false;
  } catch (err) {
    console.error('Failed to add asset:', err);
    alert('Failed to add asset');
  } finally {
    uploadingNewAsset.value = false;
  }
};

const deleteChildAsset = async (asset) => {
  if (!asset.id) return;
  if (!confirm('تبديل حالة عرض هذا الأصل لهذا الكونفيج فقط؟')) return;

  try {
    const res = await axios.post(`/api/dashboard/configurations/${props.configurationId}/child-asset-overrides/${asset.id}/toggle`);
    const updated = res.data?.data || {};
    selectedChild.value.assets = (selectedChild.value.assets || []).map((a) =>
      a.id === asset.id ? { ...a, is_visible: updated.is_visible } : a
    );
  } catch (err) {
    console.error('Failed to toggle child asset visibility:', err);
    alert('تعذر تحديث حالة الأصل');
  }
};

// Edit asset helpers
const openEditAssetModal = (asset) => {
  editAssetForm.value = {
    id: asset.id,
    asset_key: asset.asset_key,
    input_type: asset.type ,
    asset_type: asset.asset_type || 'image',
    asset_label: asset.asset_label || '',
    default_url: asset.default_url || '',
    description: asset.description || '',
    file: null,
    max_file_size: asset.max_size || 5,
  };
  showEditAssetModal.value = true;
};

const closeEditAssetModal = () => {
  showEditAssetModal.value = false;
  editAssetForm.value = {
    id: null,
    asset_key: '',
    input_type: 'text',
    asset_type: 'image',
    asset_label: '',
    default_url: '',
    description: '',
    file: null,
    max_file_size: 5,
  };
};



const updateAsset = async () => {
  const assetId = editAssetForm.value.id;
  const themeId = selectedChild.value.theme_id;

  try {
    const payload = {
      configuration_id: props.configurationId,
      asset_key: editAssetForm.value.asset_key,
      asset_type: editAssetForm.value.asset_type,
      input_type: editAssetForm.value.input_type,
      asset_label: editAssetForm.value.asset_label,
      default_url: editAssetForm.value.default_url,
      file_path: editAssetForm.value.file_path,
      description: editAssetForm.value.description,
      max_size: editAssetForm.value.max_size,
      text: editAssetForm.value.text
    };

    // update metadata
    await adminApi.themes.updateAssetDash(themeId, assetId, payload);

    // if file provided, upload it
    if (editAssetForm.value.input_type === 'file' && editAssetForm.value.file) {
      const fd = new FormData();
      fd.append('file', editAssetForm.value.file);
      fd.append('configuration_id', props.configurationId);

      const res = await axios.post(`/api/dashboard/theme-assets/${assetId}/file`, fd, {
        headers: { 'Content-Type': 'multipart/form-data' },
        params: { configuration_id: props.configurationId },
      });
      // update the item with returned data
      const updated = res.data?.data || res.data;
      const idx = selectedChild.value.assets.findIndex((a) => a.id === assetId);
      if (idx !== -1) {
        selectedChild.value.assets[idx] = { ...selectedChild.value.assets[idx], ...updated };
      }
    } else {
      // update local copy
      const idx = selectedChild.value.assets.findIndex((a) => a.id === assetId);
      if (idx !== -1) {
        selectedChild.value.assets[idx] = { ...selectedChild.value.assets[idx], ...payload };
      }
    }

    closeEditAssetModal();
  } catch (err) {
    console.error('Failed to update asset:', err);
    alert('Failed to update asset');
  }
};

const getAcceptTypes = (type) => {
  if (type === 'image') return 'image/*';
  if (type === 'svga') return '.svga';
  if (type === 'vap') return '.vap';
  if (type === 'alpha') return '.json';
  return '*/*';
};

    return {
      localSettings,
      widgetThemes,
      filteredSecondarySettings,
      allowedClientSettings,
      formatLabel,
      getSettingValue,
      setSettingValue,
      stringifyValue,
      renderDefault,
      settingOptions,
      formatSettingValue,
      updatePrimarySetting,
      updateSecondarySetting,
      saveSettings,
      selectedThemeChildren,
      selectedChild,
      loadingChildAssets,
      showChildAssetsModal,
      expandedChildId,
      loadChildAssets,
      toggleChildAssets,

      // child asset controls
      showAddChildAssetForm,
      newAsset,
      onNewAssetFileChange,
      saveChildAsset,
      uploadingNewAsset,
      deleteChildAsset,

      showEditAssetModal,
      editAssetForm,
      openEditAssetModal,
      closeEditAssetModal,
      onEditAssetFileChange,
      updateAsset,
      getAcceptTypes,

      // child edit/delete
      showEditChildModal,
      editChildForm,
      openEditChildModal,
      closeEditChildModal,
      updateChild,
      // delete confirmation
      showDeleteChildConfirm,
      deletingChild,
      confirmDeleteChild,
      cancelDeleteChild,
      deleteChild,
      resolveChildVisible,
      currentWidgetOverride,
      widgetDefinitions,
      showLibrary,
      libraryType,
      onAssetSelect,
      onEditAssetFileChange,
      fileLink,
      openLibrary
    };
  },
};
</script>

