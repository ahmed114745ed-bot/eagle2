<template>
    <div>
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold text-white">🎨 Widget Themes</h2>
            <button
                @click="openCreateModal"
                class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 flex items-center"
            >
                <span class="mr-2">➕</span> Create Theme
            </button>
        </div>

        <!-- Filter by Widget -->
        <div class="mb-6">
            <select
                v-model="filterWidget"
                class="px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white"
            >
                <option value="">All Widgets</option>
                <option v-for="widget in widgets" :key="widget.id" :value="widget.id">
                    {{ widget.display_name }}
                </option>
            </select>
        </div>

        <!-- Themes Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div
                v-for="theme in filteredThemes"
                :key="theme.id"
                class="bg-gray-800 rounded-lg overflow-hidden border border-gray-700"
            >
                <!-- Theme Header -->
                <div class="p-4 border-b border-gray-700">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="text-lg font-semibold text-white">{{ theme.theme_name }}</h3>
                            <p class="text-sm text-gray-400">{{ theme.widget?.display_name }}</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span
                                v-if="theme.is_default"
                                class="px-2 py-1 text-xs bg-green-900 text-green-300 rounded"
                            >
                                Default
                            </span>
                            <span class="px-2 py-1 text-xs bg-gray-600 text-gray-300 rounded">
                                {{ theme.theme_key }}
                            </span>
                        </div>
                    </div>
                </div>

                      
                        
                        <!-- Children List -->
                            <div class="p-4">
                                <h4 class="text-sm font-medium text-gray-400 mb-3">
                                    Children ({{ theme.children?.length || 0 }})
                                </h4>

                              

<div
  v-for="child in theme.children"
  :key="child.id"
  class="flex items-center justify-between bg-gray-700 rounded px-3 py-2 cursor-pointer hover:bg-gray-600"
  @click="openChildAssetsModal(theme, child)"
>
  <div>
    <p class="text-sm text-white">{{ child.label }}</p>
    <p class="text-xs text-gray-400">
      key: {{ child.child_key }} · type: {{ child.child_type }}
    </p>
  </div>

  <div class="flex gap-2">
    <button
      @click.stop="openEditChildModal(theme, child)"
      class="px-2 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-700"
    >
      Edit
    </button>

    <button
      @click.stop="deleteChild(theme, child)"
      class="px-2 py-1 text-xs bg-red-600 text-white rounded hover:bg-red-700"
    >
      Delete
    </button>
  </div>
</div>

                            </div>



                        <!-- Child Assets Modal -->

                            <!-- Child Assets Modal -->
                            <div v-if="showChildAssetsModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                                <div class="bg-gray-800 rounded-lg shadow-xl p-6 w-[700px] max-h-[85vh] overflow-y-auto">

                                    <!-- Header -->
                                    <div class="flex justify-between items-center mb-5">
                                        <h3 class="text-lg font-semibold text-white">
                                            🎯 Assets for "{{ selectedChild.label }}"
                                        </h3>
                                        <button @click="closeChildAssetsModal" class="text-gray-400 hover:text-white">✕</button>
                                    </div>

                                    <!-- Add Asset Button -->
                                    <div class="flex justify-end mb-4">
                                        <button @click="showAddChildAssetForm = !showAddChildAssetForm"
                                            class="px-3 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                                            ➕ Add Asset
                                        </button>
                                    </div>

                                    <!-- Add Asset Form -->
                                    <div v-if="showAddChildAssetForm" class="bg-gray-700 rounded-lg p-4 mb-6 space-y-3">

                                    <div class="grid grid-cols-3 gap-3">
                                        <select v-model="newAsset.input_type" class="px-3 py-2 bg-gray-600 border border-gray-500 rounded text-white text-sm">
                                            <option value="text">نص  </option>
                                            <option value="file">ملف (رفع)</option>
                                        </select>

                                        <input v-model="newAsset.asset_key" type="text" placeholder="Asset Key"
                                            class="px-3 py-2 bg-gray-600 border border-gray-500 rounded text-white text-sm" />

                                        <input v-model="newAsset.asset_label" type="text" placeholder="Asset Label"
                                            class="px-3 py-2 bg-gray-600 border border-gray-500 rounded text-white text-sm" />
                                    </div>

                                    <div class="grid grid-cols-1 gap-3">
                                        <div v-if="newAsset.input_type === 'text'">
                                            <input v-model="newAsset.default_url" type="text" placeholder=""
                                                class="px-3 py-2 bg-gray-600 border border-gray-500 rounded text-white text-sm" />
                                        </div>

                                        <div v-else class="space-y-2">
                                            <div class="flex gap-3 items-center">
                                                <select v-model="newAsset.asset_type"
                                                    class="px-3 py-2 bg-gray-600 border border-gray-500 rounded text-white text-sm">
                                                    <option value="image">Image</option>
                                                    <option value="svga">SVGA</option>
                                                    <option value="vap">VAP</option>
                                                    <option value="alpha">Alpha</option>
                                                </select>

                                                <input id="new-child-asset-file" type="file" class="hidden" :accept="getAcceptTypes(newAsset.asset_type)" @change="onNewAssetFileChange" />
                                                <label for="new-child-asset-file" class="px-3 py-2 bg-blue-600 text-white rounded cursor-pointer hover:bg-blue-700">📤 رفع ملف</label>
                                                <span class="text-sm text-gray-300" v-if="newAsset.file">{{ newAsset.file.name }}</span>
                                                <span class="text-sm text-gray-400" v-else>لم يتم اختيار ملف</span>
                                                <span v-if="uploadingNewAsset" class="text-sm text-gray-400">⏳ جاري الرفع...</span>
                                            </div>

                                            <div class="flex items-center gap-3">
                                                <label class="text-sm text-gray-400">الحجم الأقصى (MB)</label>
                                                <input v-model.number="newAsset.max_file_size" type="number" min="1" class="w-20 px-2 py-1 bg-gray-600 border border-gray-500 rounded text-white text-sm" />
                                                <span class="text-sm text-gray-400">({{ newAsset.max_file_size }} MB)</span>
                                            </div>

                                            <div class="text-sm text-gray-400">Allowed: <strong>{{ newAsset.asset_type }}</strong></div>
                                        </div>
                                    </div>

                                    <textarea v-model="newAsset.description" placeholder="وصف"
                                        class="w-full px-3 py-2 bg-gray-600 border border-gray-500 rounded text-white text-sm"></textarea>

                                    <div class="flex justify-end gap-2">
                                        <button @click="showAddChildAssetForm = false; newAsset.file = null"
                                            class="px-3 py-1 bg-gray-500 text-white rounded text-sm">إلغاء</button>
                                        <button @click="saveChildAsset"
                                            class="px-3 py-1 bg-green-600 text-white rounded text-sm">حفظ</button>
                                    </div>
                                    </div>

                                    <!-- Assets List -->
                                    <div v-if="selectedChild.assets?.length" class="space-y-3">
                                        <div
                                            v-for="asset in selectedChild.assets"
                                            :key="asset.id"
                                            class="bg-gray-700 rounded-lg p-4 space-y-3"
                                        >
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center gap-4">
                                                    <div class="w-10 h-10 flex items-center justify-center">
                                                        <template v-if="asset.type === 'file' && asset.default_url">
                                                            <img
                                                                v-if="asset.asset_type === 'image'"
                                                                :src="asset.default_url"
                                                                alt="Asset thumbnail"
                                                                class="w-10 h-10 object-cover rounded border border-gray-600"
                                                            />
                                                            <span v-else class="text-sm text-gray-200 px-2 py-1 bg-gray-600 rounded">ملف</span>
                                                        </template>
                                                    </div>
                                                    <div>
                                                        <p class="text-white font-medium text-sm">{{ asset.asset_label || asset.asset_key }}</p>
                                                        <p class="text-xs text-gray-400">{{ asset.asset_type }}</p>
                                                    </div>
                                                </div>

                                                <button @click="deleteChildAsset(asset)" class="text-red-400 hover:text-red-300">🗑️</button>
                                            </div>

                                            <!-- Preview: show image (without link) or text value -->
                                            <div class="border-t border-gray-600 pt-3">
                                                <img
                                                    v-if="asset.input_type === 'file' && asset.asset_type === 'image' && asset.default_url"
                                                    :src="asset.default_url"
                                                    alt="Asset preview"
                                                    class="w-24 h-24 object-cover rounded border border-gray-600"
                                                />
                                            </div>
                                        </div>
                                    </div>

                                    <div v-else class="text-center py-8 text-gray-500">
                                        No assets yet.
                                    </div>

                                </div>
                            </div>



                <!-- Actions -->
                <div class="p-4 border-t border-gray-700 flex justify-between">
                    
                       <button
                            @click="openAddChildModal(theme)"
                            class="px-3 py-1 text-sm bg-green-600 text-white rounded hover:bg-green-700"
                        >
                            ➕ Add Child
                        </button>
                 
                    <div class="flex space-x-2">
                        <button
                            @click="openEditModal(theme)"
                            class="px-3 py-1 text-sm text-blue-400 hover:text-blue-300"
                        >
                            ✏️ Edit
                        </button>
                        <button
                            @click="confirmDelete(theme)"
                            class="px-3 py-1 text-sm text-red-400 hover:text-red-300"
                        >
                            🗑️
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="filteredThemes.length === 0" class="text-center py-12">
            <p class="text-gray-500">No themes found. Create your first theme!</p>
        </div>

        <!-- Create/Edit Theme Modal -->
        <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-gray-800 rounded-lg shadow-xl p-6 w-[500px]">
                <h3 class="text-lg font-semibold text-white mb-4">
                    {{ editingTheme ? '✏️ Edit Theme' : '➕ Create Theme' }}
                </h3>
                <form @submit.prevent="saveTheme" class="space-y-4">
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Widget</label>
                        <select
                            v-model="themeForm.widget_id"
                            class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white"
                            required
                        >
                            <option value="">Select Widget</option>
                            <option v-for="widget in widgets" :key="widget.id" :value="widget.id">
                                {{ widget.display_name }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Theme Key</label>
                        <input
                            v-model="themeForm.theme_key"
                            type="text"
                            class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white"
                            placeholder="e.g., theme_blue_gold"
                            required
                        />
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Theme Name</label>
                        <input
                            v-model="themeForm.theme_name"
                            type="text"
                            class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white"
                            placeholder="e.g., Blue & Gold"
                            required
                        />
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Description</label>
                        <textarea
                            v-model="themeForm.description"
                            class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white"
                            rows="2"
                            placeholder="Theme description..."
                        ></textarea>
                    </div>
                    <div class="flex items-center space-x-4">
                        <label class="flex items-center text-gray-300">
                            <input
                                v-model="themeForm.is_active"
                                type="checkbox"
                                class="mr-2 rounded bg-gray-600 border-gray-500"
                            />
                            Active
                        </label>
                        <label class="flex items-center text-gray-300">
                            <input
                                v-model="themeForm.is_default"
                                type="checkbox"
                                class="mr-2 rounded bg-gray-600 border-gray-500"
                            />
                            Default Theme
                        </label>
                    </div>
                    <div class="flex justify-end space-x-3 mt-6">
                        <button
                            type="button"
                            @click="closeModal"
                            class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-500"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700"
                        >
                            {{ editingTheme ? 'Update' : 'Create' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Assets Manager Modal -->
        <div v-if="showAssetsModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-gray-800 rounded-lg shadow-xl p-6 w-[700px] max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-semibold text-white">
                        📦 Assets for "{{ selectedTheme?.theme_name }}"
                    </h3>
                    <button @click="closeAssetsModal" class="text-gray-400 hover:text-white">✕</button>
                </div>

                <!-- Add Asset Form -->
                <div class="bg-gray-700 rounded-lg p-4 mb-6">
                    <h4 class="text-sm font-medium text-gray-300 mb-3">➕ Add New Asset</h4>
                    <div class="grid grid-cols-3 gap-3">
                        <input
                            v-model="newAsset.asset_key"
                            type="text"
                            class="px-3 py-2 bg-gray-600 border border-gray-500 rounded-lg text-white text-sm"
                            placeholder="asset_key"
                        />
                        <select
                            v-model="newAsset.asset_type"
                            class="px-3 py-2 bg-gray-600 border border-gray-500 rounded-lg text-white text-sm"
                        >
                            <option value="image">Image</option>
                            <option value="svga">SVGA</option>
                            <option value="vap">VAP</option>
                            <option value="alpha">Alpha</option>
                        </select>
                        <button
                            @click="addAsset"
                            class="px-3 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm"
                        >
                            ➕ Add
                        </button>
                    </div>
                    <div class="grid grid-cols-2 gap-3 mt-3">
                        <input
                            v-model="newAsset.asset_label"
                            type="text"
                            class="px-3 py-2 bg-gray-600 border border-gray-500 rounded-lg text-white text-sm"
                            placeholder="Asset Label"
                        />
                        <input
                            v-model="newAsset.default_url"
                            type="text"
                            class="px-3 py-2 bg-gray-600 border border-gray-500 rounded-lg text-white text-sm"
                            placeholder="Default URL"
                        />
                    </div>
                </div>

                <!-- Assets List -->
                <div class="space-y-3">
                    <div
                        v-for="asset in selectedTheme?.assets || []"
                        :key="asset.id"
                        class="bg-gray-700 rounded-lg p-4"
                    >
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center space-x-4">
                                <span class="text-2xl">{{ getAssetIcon(asset.asset_type) }}</span>
                                <div>
                                    <p class="text-white font-medium">{{ asset.asset_label || asset.asset_key }}</p>
                                    <p class="text-sm text-gray-400 font-mono">{{ asset.asset_key }}</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-4">
                                <span class="px-2 py-1 text-xs bg-gray-600 text-gray-300 rounded">
                                    {{ asset.asset_type }}
                                </span>
                                <button
                                    @click="deleteAsset(asset)"
                                    class="text-red-400 hover:text-red-300"
                                >
                                    🗑️
                                </button>
                            </div>
                        </div>
                        <!-- File Upload Section -->
                        <div class="border-t border-gray-600 pt-3">
                            <div v-if="asset.default_url" class="flex items-center justify-between mb-2">
                                <div class="flex items-center space-x-2">
                                    <span class="text-green-400">✓</span>
                                    <span class="text-sm text-gray-300 truncate max-w-[200px]">
                                        {{ asset.original_filename || 'File uploaded' }}
                                    </span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <a :href="asset.default_url" target="_blank" class="text-blue-400 hover:text-blue-300 text-sm">
                                        👁️ View
                                    </a>
                                    <button @click="removeAssetFile(asset)" class="text-red-400 hover:text-red-300 text-sm">
                                        🗑️ Remove
                                    </button>
                                </div>
                            </div>
                            <div class="flex items-center space-x-3">
                                <input
                                    :id="'file-' + asset.id"
                                    type="file"
                                    class="hidden"
                                    :accept="getAcceptTypes(asset.asset_type)"
                                    @change="(e) => uploadAssetFile(asset, e)"
                                />
                                <label
                                    :for="'file-' + asset.id"
                                    class="px-3 py-1 bg-blue-600 text-white rounded text-sm cursor-pointer hover:bg-blue-700"
                                >
                                    📤 {{ asset.default_url ? 'Replace File' : 'Upload File' }}
                                </label>
                                <span v-if="uploadingAssetId === asset.id" class="text-sm text-gray-400">
                                    ⏳ Uploading...
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="!selectedTheme?.assets?.length" class="text-center py-8">
                    <p class="text-gray-500">No assets yet. Add your first asset!</p>
                </div>

                <div class="flex justify-end mt-6">
                    <button
                        @click="closeAssetsModal"
                        class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-500"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-gray-800 rounded-lg shadow-xl p-6 w-[400px]">
                <h3 class="text-lg font-semibold text-white mb-4">⚠️ Delete Theme</h3>
                <p class="text-gray-300 mb-6">
                    Are you sure you want to delete "{{ themeToDelete?.theme_name }}"?
                    This will also delete all associated assets.
                </p>
                <div class="flex justify-end space-x-3">
                    <button
                        @click="showDeleteModal = false"
                        class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-500"
                    >
                        Cancel
                    </button>
                    <button
                        @click="deleteTheme"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700"
                    >
                        Delete
                    </button>
                </div>
            </div>
       
       
        </div>


        
            <!-- Add Theme Child Modal -->
        <div v-if="showAddChildModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-gray-800 rounded-lg shadow-xl p-6 w-[500px]">
                <h3 class="text-lg font-semibold text-white mb-4">
                    ➕ Add Child 
                </h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm text-gray-300 mb-1">Child Key</label>
                        <input
                            v-model="childForm.child_key"
                            type="text"
                            class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded text-white"
                        />
                    </div>

                    <div>
                        <label class="block text-sm text-gray-300 mb-1">Label</label>
                        <input
                            v-model="childForm.label"
                            type="text"
                            class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded text-white"
                        />
                    </div>


                    <div>
                        <label class="block text-sm text-gray-300 mb-1">Action</label>
                        <input
                            v-model="childForm.action"
                            type="text"
                            class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded text-white"
                        />
                    </div>
                    

                    <div>
                        <label class="block text-sm text-gray-300 mb-1">Child Type</label>
                        <select
                            v-model="childForm.child_type"
                            class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded text-white"
                        >
                            <option value="tab">Tab</option>
                            <option value="category">Category</option>
                            <option value="special">Special</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <label class="flex items-center text-gray-300">
                            <input type="checkbox" v-model="childForm.is_visible" class="mr-2">
                            Visible
                        </label>

                        <label class="flex items-center text-gray-300">
                            <input type="checkbox" v-model="childForm.is_active" class="mr-2">
                            Active
                        </label>
                    </div>

                    <div>
                        <label class="block text-sm text-gray-300 mb-1">Position</label>
                        <select
                            v-model="childForm.position"
                            class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded text-white"
                        >
                            <option :value="null">—</option>
                            <option value="left">Left</option>
                            <option value="right">Right</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 mt-6">
                    <button @click="closeAddChildModal" class="text-gray-400 hover:text-white">
                        Cancel
                    </button>
                    <button
                        @click="saveChild"
                        class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700"
                    >
                        Save Child
                    </button>
                </div>
            </div>
        </div>

<!-- Edit Theme Child Modal -->
<div   v-if="showEditChildModal"   class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-gray-800 rounded-lg shadow-xl p-6 w-[500px]">
        <h3 class="text-lg font-semibold text-white mb-4">
            ✏️ Edit Child
        </h3>

        <div class="space-y-4">
            <div>
                <label class="block text-sm text-gray-300 mb-1">Child Key</label>
                <input
                    v-model="childForm.child_key"
                    type="text"
                    class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded text-white"
                />
            </div>

            <div>
                <label class="block text-sm text-gray-300 mb-1">Label</label>
                <input
                    v-model="childForm.label"
                    type="text"
                    class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded text-white"
                />
            </div>

            <div>
                <label class="block text-sm text-gray-300 mb-1">Action</label>
                <input
                    v-model="childForm.action"
                    type="text"
                    class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded text-white"
                />
            </div>

            <div>
                <label class="block text-sm text-gray-300 mb-1">Child Type</label>
                <select
                    v-model="childForm.child_type"
                    class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded text-white"
                >
                    <option value="tab">Tab</option>
                    <option value="category">Category</option>
                    <option value="special">Special</option>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <label class="flex items-center text-gray-300">
                    <input type="checkbox" v-model="childForm.is_visible" class="mr-2">
                    Visible
                </label>

                <label class="flex items-center text-gray-300">
                    <input type="checkbox" v-model="childForm.is_active" class="mr-2">
                    Active
                </label>
            </div>

            <div>
                <label class="block text-sm text-gray-300 mb-1">Position</label>
                <select
                    v-model="childForm.position"
                    class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded text-white"
                >
                    <option :value="null">—</option>
                    <option value="left">Left</option>
                    <option value="right">Right</option>
                </select>
            </div>
        </div>

        <div class="flex justify-end space-x-3 mt-6">
            <button
                @click="closeEditChildModal"
                class="text-gray-400 hover:text-white"
            >
                Cancel
            </button>
            <button
                @click="updateChild"
                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
            >
                Update Child
            </button>
        </div>
    </div>
</div>




    </div>






</template>


<script>
import { ref, computed, onMounted } from 'vue'
import { adminApi } from '../services/adminApi'
import axios from 'axios'

export default {
    name: 'ThemesManager',
    emits: ['notify'],
    setup(props, { emit }) {

        /* ================== State ================== */
        const themes = ref([])
        const widgets = ref([])
        const filterWidget = ref('')

        const showAddChildModal = ref(false)
        const showChildAssetsModal = ref(false)
        const showEditChildModal = ref(false)
        const editingChildId = ref(null)

        const selectedThemeForChild = ref(null)
        const selectedTheme = ref(null)
        const selectedChild = ref(null)

        const showModal = ref(false)         
        const editingTheme = ref(null)     
        const themeForm = ref({              
                widget_id: '',
                theme_key: '',
                theme_name: '',
                description: '',
                is_active: true,
                is_default: false
            })

        // Delete confirmation state
        const showDeleteModal = ref(false)
        const themeToDelete = ref(null)

        const confirmDelete = (theme) => {
            themeToDelete.value = theme
            showDeleteModal.value = true
        }

        const deleteTheme = async () => {
            if (!themeToDelete.value) return

            try {
                await adminApi.themes.delete(themeToDelete.value.id)
                themes.value = themes.value.filter(t => t.id !== themeToDelete.value.id)
                showDeleteModal.value = false
                themeToDelete.value = null
                emit('notify', 'Theme deleted successfully')
            } catch (error) {
                console.error('Failed to delete theme', error)
                emit('notify', 'Failed to delete theme')
            }
        }


        const childForm = ref({
            child_key: '',
            label: '',
            action: '',
            child_type: 'tab',
            is_visible: true,
            is_active: true,
            position: null,
        })

  
        const newAsset = ref({
            asset_key: '',
            // input_type: 'text' for URL input, 'file' for uploads
            input_type: 'text',
            asset_type: 'image',
            asset_label: '',
            default_url: '',
            description: '',
            file: null,
            // maximum file size in MB (editable in UI)
            max_file_size: 5,
            child_id: null,
        })
        const showAddChildAssetForm = ref(false)
        const uploadingNewAsset = ref(false)
        const uploadingAssetId = ref(null)

        /* ================== Computed ================== */
        const filteredThemes = computed(() => {
            if (!filterWidget.value) return themes.value
            return themes.value.filter(
                t => String(t.widget_id) === String(filterWidget.value)
            )
        })

        /* ================== Lifecycle ================== */
        onMounted(async () => {
            await loadWidgets()
            await loadThemes()
        })

        /* ================== API ================== */
        const loadWidgets = async () => {
            const res = await adminApi.widgets.list()
            widgets.value = res.data?.data || res.data || []
        }

        const loadThemes = async () => {
            const res = await adminApi.themes.list()
            themes.value = res.data?.data || res.data || []
        }

        /* ================== Children ================== */
        const openAddChildModal = (theme) => {
            selectedThemeForChild.value = theme
            showAddChildModal.value = true
        }

        const closeAddChildModal = () => {
            showAddChildModal.value = false
        }

        const saveChild = async () => {
            const res = await axios.post('/api/dashboard/theme-children', {
                theme_id: selectedThemeForChild.value.id,
                ...childForm.value,
            })

            const child = res.data?.data || res.data

            if (!selectedThemeForChild.value.children) {
                selectedThemeForChild.value.children = []
            }

            selectedThemeForChild.value.children.push(child)

            // reset
            showAddChildModal.value = false
            childForm.value = {
                child_key: '',
                label: '',
                action: '',
                child_type: 'tab',
                is_visible: true,
                is_active: true,
                position: null,
            }

            emit('notify', 'Child added successfully')
        }

        /* ================== Child Assets ================== */
        const openChildAssetsModal = async (theme, child) => {
            selectedTheme.value = theme
            selectedChild.value = {
                ...child,
                assets: [],
            }
             newAsset.value.child_id = child.id

            const res = await axios.get(
                `/api/dashboard/theme-children/${child.id}/assets`
            )

            selectedChild.value.assets = res.data?.data || res.data || []
            showChildAssetsModal.value = true
        }

        const closeChildAssetsModal = () => {
            showChildAssetsModal.value = false
            selectedChild.value = null
        }

        const saveChildAsset = async () => {
                if (!newAsset.value.asset_key) return

                try {
                    let res

                    if (newAsset.value.input_type === 'file') {
                        if (!newAsset.value.file) {
                            emit('notify', 'Please select a file')
                            return
                        }

                        const maxMb = Number(newAsset.value.max_file_size) || 5
                        if (newAsset.value.file.size > maxMb * 1024 * 1024) {
                            emit('notify', `File is too large (max ${maxMb} MB)`)
                            return
                        }

                        uploadingNewAsset.value = true

                        const fd = new FormData()
                        fd.append('file', newAsset.value.file)
                        fd.append('asset_key', newAsset.value.asset_key)
                        fd.append('input_type', newAsset.value.input_type)

                        fd.append('asset_type', newAsset.value.asset_type)
                        fd.append('asset_label', newAsset.value.asset_label)
                        fd.append('description', newAsset.value.description)
                        fd.append('child_id', selectedChild.value.id)

                        res = await axios.post(`/api/dashboard/theme-children/${selectedChild.value.id}/assets`, fd, {
                            headers: { 'Content-Type': 'multipart/form-data' }
                        })
                    } else {
                        const payload = {
                            asset_key: newAsset.value.asset_key,
                            input_type: newAsset.value.input_type,
                            asset_type: newAsset.value.asset_type,
                            asset_label: newAsset.value.asset_label,
                            default_url: newAsset.value.default_url,
                            description: newAsset.value.description,
                            child_id: selectedChild.value.id,
                        }

                        res = await axios.post(`/api/dashboard/theme-children/${selectedChild.value.id}/assets`, payload)
                    }

                    const created = res.data?.data || res.data
                    selectedChild.value.assets.unshift(created)

                    newAsset.value = {
                        asset_key: '',
                        input_type: 'text',
                        asset_type: 'image',
                        asset_label: '',
                        default_url: '',
                        description: '',
                        file: null,
                    }

                    showAddChildAssetForm.value = false
                    emit('notify', 'Asset added successfully')
                } catch (error) {
                    console.error('Failed to save child asset', error)
                    emit('notify', 'Failed to add asset')
                } finally {
                    uploadingNewAsset.value = false
                }
            }


        const onNewAssetFileChange = (e) => {
            newAsset.value.file = e.target.files?.[0] || null
        }

        const uploadAssetFile = async (asset, e) => {
            const file = e.target.files?.[0]
            if (!file) return

            try {
                uploadingAssetId.value = asset.id
                const fd = new FormData()
                fd.append('file', file)
                fd.append('asset_type', asset.asset_type)

                const res = await axios.post(`/api/dashboard/theme-assets/${asset.id}/file`, fd, { headers: { 'Content-Type': 'multipart/form-data' } })
                const data = res.data?.data || res.data

                if (data?.default_url) asset.default_url = data.default_url
                if (data?.original_filename) asset.original_filename = data.original_filename

                emit('notify', 'File uploaded')
            } catch (err) {
                console.error('Upload failed', err)
                emit('notify', 'Upload failed')
            } finally {
                uploadingAssetId.value = null
            }
        }

        const removeAssetFile = async (asset) => {
            try {
                await axios.delete(`/api/dashboard/theme-assets/${asset.id}/file`)
                asset.default_url = null
                asset.original_filename = null
                emit('notify', 'File removed')
            } catch (err) {
                console.error(err)
                emit('notify', 'Failed to remove file')
            }
        }

        const getAcceptTypes = (type) => {
            if (type === 'image') return 'image/*'
            if (type === 'svga') return '.svga'
            if (type === 'vap') return '.vap'
            if (type === 'alpha') return '.json'
            return '*/*'
        }

        const deleteChildAsset = async (asset) => {
            await axios.delete(`/api/dashboard/theme-assets/${asset.id}`)

            selectedChild.value.assets =
                selectedChild.value.assets.filter(a => a.id !== asset.id)

            emit('notify', 'Asset deleted')
        }

        /* ================== Helpers ================== */
        const getAssetIcon = (type) => {
            return {
                image: '🖼️',
                svga: '🎞️',
                vap: '🎥',
                video: '🎬',
                file: '📦',
            }[type] || '📁'
        }


        const openEditChildModal = (theme, child) => {
            selectedThemeForChild.value = theme
            editingChildId.value = child.id

            childForm.value = {
                child_key: child.child_key,
                label: child.label,
                action: child.action,
                child_type: child.child_type,
                is_visible: !!child.is_visible,
                is_active: !!child.is_active,
                position: child.position,
            }

            showEditChildModal.value = true
        }
        const closeEditChildModal = () => {
            showEditChildModal.value = false
            editingChildId.value = null
        }

        const updateChild = async () => {
            const res = await axios.put(
                `/api/dashboard/theme-children/${editingChildId.value}`,
                {
                    theme_id: selectedThemeForChild.value.id,
                    ...childForm.value,
                }
            )

            const updatedChild = res.data?.data || res.data

            // تحديثه محليًا بدون reload
            const index = selectedThemeForChild.value.children.findIndex(
                c => c.id === editingChildId.value
            )

            if (index !== -1) {
                selectedThemeForChild.value.children[index] = updatedChild
            }

            showEditChildModal.value = false
            editingChildId.value = null

            emit('notify', 'Child updated successfully')
        }


        const deleteChild = async (theme, child) => {
            if (!confirm('Are you sure you want to delete this child?')) return

            await axios.delete(`/api/dashboard/theme-children/${child.id}`)

            theme.children = theme.children.filter(c => c.id !== child.id)

            emit('notify', 'Child deleted successfully')
        }


            const openCreateModal = () => {
                editingTheme.value = null
                themeForm.value = {
                    widget_id: '',
                    theme_key: '',
                    theme_name: '',
                    description: '',
                    is_active: true,
                    is_default: false
                }
                showModal.value = true
            }

            const openEditModal = (theme) => {
                editingTheme.value = theme
                themeForm.value = { 
                    widget_id: theme.widget_id,
                    theme_key: theme.theme_key,
                    theme_name: theme.theme_name,
                    description: theme.description,
                    is_active: !!theme.is_active,
                    is_default: !!theme.is_default
                }
                showModal.value = true
            }


            const closeModal = () => {
                showModal.value = false
                editingTheme.value = null
            }



            const saveTheme = async () => {
                try {
                    if (!themeForm.value.widget_id || !themeForm.value.theme_key || !themeForm.value.theme_name) {
                        emit('notify', 'Please fill all required fields')
                        return
                    }

                    let res;

                    if (editingTheme.value) {
                        res = await axios.put(
                            `/api/admin/themes/${editingTheme.value.id}`,
                            themeForm.value
                        )

                        const index = themes.value.findIndex(t => t.id === editingTheme.value.id)
                        if (index !== -1) themes.value[index] = res.data.data || res.data
                        emit('notify', 'Theme updated successfully')
                    } else {
                        res = await axios.post(`/api/admin/themes`, themeForm.value)
                        themes.value.unshift(res.data.data || res.data)
                        emit('notify', 'Theme created successfully')
                    }

                    closeModal()
                } catch (error) {
                    console.error(error)
                    emit('notify', 'Error saving theme')
                }
            }

     

        /* ================== Expose ================== */
        return {
            themes,
            widgets,
            filterWidget,
            filteredThemes,

            openCreateModal,
            openEditModal,
            closeModal,

            showAddChildModal,
            showChildAssetsModal,

            selectedTheme,
            selectedChild,

            childForm,
            newAsset,

            openAddChildModal,
            closeAddChildModal,
            saveChild,

            openChildAssetsModal,
            closeChildAssetsModal,
            saveChildAsset,
            deleteChildAsset,

            getAssetIcon,
            showAddChildAssetForm,
            onNewAssetFileChange,
            uploadAssetFile,
            removeAssetFile,
            getAcceptTypes,
            uploadingAssetId,
            uploadingNewAsset,

            openEditChildModal,

            showEditChildModal,
            openEditChildModal,
            closeEditChildModal,
            updateChild,
            deleteChild,

            showModal,
            editingTheme,
            themeForm,
            saveTheme,

            // Delete modal
            showDeleteModal,
            themeToDelete,
            confirmDelete,
            deleteTheme,

        }
    },
}
</script>

