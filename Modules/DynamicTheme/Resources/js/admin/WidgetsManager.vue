<template>
    <div>
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold text-white">📦 Widgets Manager</h2>
            <button
                @click="showCreateModal = true"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
            >
                ➕ Create Widget
            </button>
        </div>

        <!-- Widgets List -->
        <div class="bg-gray-800 rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-700">
                <thead class="bg-gray-900">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Widget</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Key</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Settings</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Themes</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-400 uppercase">Actions</th>
                    </tr>
                </thead>
             
             <tbody class="divide-y divide-gray-700">
    <template v-for="widget in widgets" :key="widget.id">
        <!-- Parent Widget Row -->
        <tr class="hover:bg-gray-750">
            <td class="px-6 py-4">
                <div class="flex items-center">
                    <span class="text-2xl mr-3">{{ getWidgetIcon(widget.widget_type) }}</span>
                    <div>
                        <div class="text-sm font-medium text-white">
                            {{ widget.display_name }}
                        </div>
                        <div class="text-sm text-gray-400">
                            {{ widget.description?.substring(0, 50) }}...
                        </div>
                        <div class="text-sm text-gray-400">
                            Parent:
                            <span v-if="widget.parent">
                                {{ widget.parent.display_name }}
                            </span>
                            <span v-else>—</span>
                        </div>
                    </div>
                </div>
            </td>

            <td class="px-6 py-4 text-sm text-gray-300">
                {{ widget.widget_type }}
            </td>

            <td class="px-6 py-4 text-sm text-gray-300 font-mono">
                {{ widget.widget_key }}
            </td>

            <td class="px-6 py-4">
                <span class="px-2 py-1 text-xs bg-purple-900 text-purple-300 rounded">
                    {{ widget.settings_count || 0 }} settings
                </span>
            </td>

            <td class="px-6 py-4">
                <span class="px-2 py-1 text-xs bg-blue-900 text-blue-300 rounded">
                    {{ widget.themes_count || 0 }} themes
                </span>
            </td>

            <td class="px-6 py-4">
                <span
                    :class="[
                        'px-2 py-1 text-xs rounded',
                        widget.is_active
                            ? 'bg-green-900 text-green-300'
                            : 'bg-red-900 text-red-300'
                    ]"
                >
                    {{ widget.is_active ? 'Active' : 'Inactive' }}
                </span>
            </td>

            <td class="px-6 py-4 text-right space-x-2">
                <button @click="editWidget(widget)" class="text-blue-400 hover:text-blue-300">
                    ✏️ Edit
                </button>
                <button @click="manageSettings(widget)" class="text-purple-400 hover:text-purple-300">
                    ⚙️ Settings
                </button>
                <button @click="confirmDelete(widget)" class="text-red-400 hover:text-red-300">
                    🗑️ Delete
                </button>
             
            </td>
        </tr>

        <!-- Children Row -->
        <tr
            v-if="widget.children && widget.children.length"
            class="bg-gray-900/40"
        >
            <td colspan="7" class="px-6 py-3">
                <div class="flex flex-wrap gap-2">
                    <span
                        v-for="child in widget.children"
                        :key="child.id"
                        class="px-2 py-1 text-xs rounded bg-gray-700 text-gray-200"
                    >
                        {{ child.display_name }}
                    </span>
                </div>
            </td>
        </tr>
    </template>
</tbody>

            </table>
        </div>

        <!-- Create/Edit Widget Modal -->
        <div v-if="showCreateModal || editingWidget" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-gray-800 rounded-lg shadow-xl p-6 w-[600px] max-h-[90vh] overflow-y-auto">
                <h3 class="text-lg font-semibold text-white mb-4">
                    {{ editingWidget ? '✏️ Edit Widget' : '➕ Create Widget' }}
                </h3>

                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Widget Type</label>
                            <input
                                v-model="formData.widget_type"
                                type="text"
                                class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-blue-500"
                                placeholder="e.g., tab_bar, banner, room"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Widget Key</label>
                            <input
                                v-model="formData.widget_key"
                                type="text"
                                class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-blue-500"
                                placeholder="e.g., main_navigation"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Display Name</label>
                        <input
                            v-model="formData.display_name"
                            type="text"
                            class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-blue-500"
                            placeholder="e.g., Tab Bar Navigation"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Description</label>
                        <textarea
                            v-model="formData.description"
                            rows="2"
                            class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-blue-500"
                            placeholder="Describe what this widget does..."
                        ></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Min App Version</label>
                            <input
                                v-model="formData.min_app_version"
                                type="text"
                                class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-blue-500"
                                placeholder="e.g., 2.0.0"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Icon</label>
                            <input
                                v-model="formData.icon"
                                type="text"
                                class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-blue-500"
                                placeholder="e.g., tabs, grid, image"
                            />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">

                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">
                                Parent Widget
                            </label>

                            <select
                                v-model="formData.parent_id"
                                class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-blue-500"
                            >
                                <option :value="null">— No Parent (Root Widget)</option>

                                <option
                                    v-for="parent in parentWidgets"
                                    :key="parent.id"
                                    :value="parent.id"
                                >
                                    {{ parent.display_name }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="flex space-x-6">
                        <label class="flex items-center text-gray-300">
                            <input
                                v-model="formData.is_repeatable"
                                type="checkbox"
                                class="mr-2 rounded bg-gray-700 border-gray-600"
                            />
                            Is Repeatable
                        </label>
                        <label class="flex items-center text-gray-300">
                            <input
                                v-model="formData.is_active"
                                type="checkbox"
                                class="mr-2 rounded bg-gray-700 border-gray-600"
                            />
                            Is Active
                        </label>
                         <label class="flex items-center text-gray-300">
                            <input
                                v-model="formData.has_pages"
                                type="checkbox"
                                class="mr-2 rounded bg-gray-700 border-gray-600"
                            />
                            Is has Pages
                        </label>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 mt-6">
                    <button
                        @click="closeModal"
                        class="px-4 py-2 text-gray-400 hover:text-white"
                    >
                        Cancel
                    </button>
                    <button
                        @click="saveWidget"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                    >
                        {{ editingWidget ? 'Update' : 'Create' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Settings Manager Modal -->
        <SettingsManagerModal
            v-if="showSettingsModal"
            :widget="selectedWidgetForSettings"
            @close="showSettingsModal = false"
            @saved="$emit('refresh')"
        />

        <!-- Delete Confirmation -->
        <div v-if="deletingWidget" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-gray-800 rounded-lg shadow-xl p-6 w-96">
                <h3 class="text-lg font-semibold text-white mb-4">⚠️ Confirm Delete</h3>
                <p class="text-gray-300 mb-6">
                    Are you sure you want to delete "{{ deletingWidget.display_name }}"? This will also delete all associated themes and settings.
                </p>
                <div class="flex justify-end space-x-3">
                    <button
                        @click="deletingWidget = null"
                        class="px-4 py-2 text-gray-400 hover:text-white"
                    >
                        Cancel
                    </button>
                    <button
                        @click="doDelete"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700"
                    >
                        Delete
                    </button>
                </div>
            </div>
        </div>



        <!-- Add Widget Child Modal -->
        <div
            v-if="showAddChildModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
        >
            <div class="bg-gray-800 rounded-lg shadow-xl p-6 w-[500px]">
                <h3 class="text-lg font-semibold text-white mb-4">
                    ➕ Add Child to {{ selectedWidgetForChild.display_name }}
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
          <!--  end Add Widget Child Modal -->

</div>

    </div>
</template>



<script>
import { ref, reactive, onMounted, watch } from 'vue'
import axios from 'axios'
import SettingsManagerModal from './SettingsManagerModal.vue'

export default {
    name: 'WidgetsManager',
    components: { SettingsManagerModal },

    props: {
        widgets: {
            type: Array,
            required: true
        }
    },

    emits: ['create', 'update', 'delete', 'refresh'],

    setup(props, { emit }) {

        /* ========================
         * Screen ID (من الصفحة)
         * ======================== */
        const screenId = document.body.dataset.screenId

        /* ========================
         * State
         * ======================== */
        const showCreateModal = ref(false)
        const editingWidget = ref(null)
        const deletingWidget = ref(null)

        const showSettingsModal = ref(false)
        const selectedWidgetForSettings = ref(null)

        const parentWidgets = ref([])

        const formData = ref({
            widget_type: '',
            widget_key: '',
            display_name: '',
            description: '',
            is_repeatable: false,
            has_children: false,
            parent_id: null,
            min_app_version: '2.0.0',
            icon: '',
            is_active: true,
            has_pages : false
        })

        /* ========================
         * Add Child State
         * ======================== */
        const showAddChildModal = ref(false)
        const selectedWidgetForChild = ref(null)

        const childForm = reactive({
            child_key: '',
            child_type: 'tab',
            label: '',
            order: null,
            is_visible: true,
            is_active: false,
            position: null
        })

        /* ========================
         * Helpers
         * ======================== */
        const widgetIcons = {
            tab_bar: '📑',
            banner: '🖼️',
            room: '🏠',
            ranking: '🏆',
            categories: '📂',
            country_filter: '🌍',
            floating_button: '⭕'
        }

        const getWidgetIcon = (type) => widgetIcons[type] || '📦'

        /* ========================
         * API
         * ======================== */
        const loadParentWidgets = async () => {
            const res = await axios.get('/api/dashboard/widgets-parents')
            parentWidgets.value = res.data.data
        }

        /* ========================
         * Widget Actions
         * ======================== */
        const editWidget = (widget) => {
            editingWidget.value = widget
            formData.value = { ...widget }
            showCreateModal.value = true
        }

        const manageSettings = (widget) => {
            selectedWidgetForSettings.value = widget
            showSettingsModal.value = true
        }

        const confirmDelete = (widget) => {
            deletingWidget.value = widget
        }

        const doDelete = () => {
            emit('delete', deletingWidget.value.id)
            deletingWidget.value = null
        }

        const closeModal = () => {
            showCreateModal.value = false
            editingWidget.value = null
            formData.value = {
                widget_type: '',
                widget_key: '',
                display_name: '',
                description: '',
                is_repeatable: false,
                has_children: false,
                parent_id: null,
                min_app_version: '2.0.0',
                icon: '',
                is_active: true,
                has_pages: false
            }
        }

        const saveWidget = () => {
            if (editingWidget.value) {
                emit('update', editingWidget.value.id, formData.value)
            } else {
                emit('create', formData.value)
            }
            closeModal()
        }

        /* ========================
         * Child Actions
         * ======================== */
        const openAddChildModal = (widget) => {
            selectedWidgetForChild.value = widget

            Object.assign(childForm, {
                child_key: '',
                child_type: 'tab',
                label: '',
                order: null,
                is_visible: true,
                is_active: false,
                position: null
            })

            showAddChildModal.value = true
        }

        const closeAddChildModal = () => {
            showAddChildModal.value = false
            selectedWidgetForChild.value = null
        }

        const saveChild = async () => {
            try {
                await axios.post(
                    `/api/dashboard/screens/widgets/${selectedWidgetForChild.value.id}/children`,
                    childForm
                )

                closeAddChildModal()
                emit('refresh') // تحديث البيانات
            } catch (error) {
                console.error(error)
                alert('Failed to add child')
            }
        }

        /* ========================
         * Watches
         * ======================== */
        watch(
            () => formData.value.parent_id,
            (value) => {
                if (value) {
                    formData.value.has_children = false
                }
            }
        )

        /* ========================
         * Lifecycle
         * ======================== */
        onMounted(() => {
            loadParentWidgets()
        })

        /* ========================
         * Expose
         * ======================== */
        return {
            // widgets
            showCreateModal,
            editingWidget,
            deletingWidget,
            showSettingsModal,
            selectedWidgetForSettings,
            parentWidgets,
            formData,
            getWidgetIcon,
            editWidget,
            manageSettings,
            confirmDelete,
            doDelete,
            closeModal,
            saveWidget,

            // children
            showAddChildModal,
            selectedWidgetForChild,
            childForm,
            openAddChildModal,
            closeAddChildModal,
            saveChild
        }
    }
}
</script>
