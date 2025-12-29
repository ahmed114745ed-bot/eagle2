<template>
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-gray-800 rounded-lg shadow-xl p-6 w-[800px] max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-semibold text-white">
                    ⚙️ Settings for "{{ widget.display_name }}"
                </h3>
                <button @click="$emit('close')" class="text-gray-400 hover:text-white">✕</button>
            </div>

                <div v-if="errorMessage" class="mb-4 p-3 bg-red-600 text-white rounded">
                ⚠️ {{ errorMessage }}
                </div>
                <div v-if="successMessage" class="mb-4 p-3 bg-green-600 text-white rounded">
                ✅ {{ successMessage }}
                </div>

            <!-- Add New Setting -->
            <div class="bg-gray-700 rounded-lg p-4 mb-6">
                <h4 class="text-sm font-medium text-gray-300 mb-3">➕ Add New Setting</h4>
                <div class="grid grid-cols-4 gap-3">
                    <input
                        v-model="newSetting.setting_key"
                        type="text"
                        class="px-3 py-2 bg-gray-600 border border-gray-500 rounded-lg text-white text-sm"
                        placeholder="setting_key"
                    />
                    <input
                        v-model="newSetting.setting_label"
                        type="text"
                        class="px-3 py-2 bg-gray-600 border border-gray-500 rounded-lg text-white text-sm"
                        placeholder="Setting Label"
                    />
                    <select
                        v-model="newSetting.setting_type"
                        class="px-3 py-2 bg-gray-600 border border-gray-500 rounded-lg text-white text-sm"
                    >
                        <option value="text">Text</option>
                        <option value="number">Number</option>
                        <option value="boolean">Boolean</option>
                        <option value="select">Select</option>
                        <option value="color">Color</option>
                        <option value="json">JSON</option>
                        <option value="range">Range</option>
                    </select>
                    <select
                        v-model="newSetting.setting_category"
                        class="px-3 py-2 bg-gray-600 border border-gray-500 rounded-lg text-white text-sm"
                    >
                        <option value="primary">Primary (Data)</option>
                        <option value="secondary">Secondary (Appearance)</option>
                    </select>
                </div>
                <div class="grid grid-cols-4 gap-3 mt-3">
                    <input
                        v-model="newSetting.default_value"
                        type="text"
                        class="px-3 py-2 bg-gray-600 border border-gray-500 rounded-lg text-white text-sm"
                        placeholder="Default Value"
                    />
                    <input
                        v-model="newSetting.order"
                        type="number"
                        class="px-3 py-2 bg-gray-600 border border-gray-500 rounded-lg text-white text-sm"
                        placeholder="Order"
                    />
                    <label class="flex items-center text-gray-300 text-sm">
                        <input
                            v-model="newSetting.is_hidden"
                            type="checkbox"
                            class="mr-2 rounded bg-gray-600 border-gray-500"
                        />
                        Hidden from Client
                    </label>
                    <button
                        @click="addSetting"
                        class="px-3 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm"
                    >
                        ➕ Add
                    </button>
                </div>

                <div v-if="newSetting.setting_type === 'select'" class="mt-3">
                    <label class="block text-xs text-gray-400 mb-1">Options (one per line)</label>
                    <textarea
                        v-model="newSettingOptionsText"
                        class="w-full px-3 py-2 bg-gray-600 border border-gray-500 rounded-lg text-white text-sm"
                        rows="3"
                        placeholder="e.g.\nsmall\nmedium\nlarge"
                    ></textarea>
                    <p class="text-[11px] text-gray-400 mt-1">هذه الخيارات ستظهر للعميل في الداشبورد عند اختيار نوع الويدجت.</p>
                </div>
            </div>

        <!-- Actions -->
            <div>
                <h4 class="text-sm font-medium text-green-400 mb-3">🎯 Widget Actions</h4>
                <div class="bg-gray-700 rounded-lg p-4 mb-3">
                    <div class="grid grid-cols-4 gap-3">
                        <select
                            v-model="newAction.action_type"
                            class="px-3 py-2 bg-gray-600 border border-gray-500 rounded-lg text-white text-sm"
                        >
                            <option value="screen">Screen Navigation</option>
                            <option value="webview">Open WebView</option>
                            <option value="filter">Filter Data</option>
                            <option value="internal">Internal Action</option>
                        </select>
                        <input
                            v-model="newAction.action_label"
                            type="text"
                            class="px-3 py-2 bg-gray-600 border border-gray-500 rounded-lg text-white text-sm"
                            placeholder="Action Label"
                        />
                        <select
                            v-model="newAction.target_type"
                            class="px-3 py-2 bg-gray-600 border border-gray-500 rounded-lg text-white text-sm"
                        >
                            <option value="screen_key">Screen Key</option>
                            <option value="url">URL</option>
                            <option value="filter_key">Filter Key</option>
                            <option value="action_key">Action Key</option>
                        </select>
                        <button
                            @click="addAction"
                            class="px-3 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm"
                        >
                            ➕ Add Action
                        </button>
                    </div>
                </div>
                <div class="space-y-2">
                    <div
                        v-for="action in actions"
                        :key="action.id"
                        class="flex items-center justify-between bg-gray-700 rounded-lg p-3"
                    >
                        <div class="flex items-center space-x-4">
                            <span class="px-2 py-1 text-xs bg-green-900 text-green-300 rounded">{{ action.action_type }}</span>
                            <span class="text-white">{{ action.action_label }}</span>
                            <span class="text-gray-400 text-sm">→ {{ action.target_type }}</span>
                        </div>
                        <button
                            @click="deleteAction(action)"
                            class="text-red-400 hover:text-red-300 text-sm"
                        >
                            🗑️
                        </button>
                    </div>
                </div>
            </div>
            <!-- Primary Settings -->
            <div class="mb-6">
                <h4 class="text-sm font-medium text-blue-400 mb-3">📊 Primary Settings (Affects Data)</h4>
                <div class="space-y-2">
                    <div
                        v-for="setting in primarySettings"
                        :key="setting.id"
                        class="flex items-center justify-between bg-gray-700 rounded-lg p-3"
                    >
                        <div class="flex items-center space-x-4">
                            <span class="text-gray-400 text-sm font-mono">{{ setting.setting_key }}</span>
                            <span class="text-white">{{ setting.setting_label }}</span>
                            <span class="px-2 py-1 text-xs bg-gray-600 text-gray-300 rounded">{{ setting.setting_type }}</span>
                            <span v-if="setting.is_hidden" class="px-2 py-1 text-xs bg-yellow-900 text-yellow-300 rounded">Hidden</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="text-gray-400 text-sm">Default: {{ setting.default_value }}</span>
                            <button
                                v-if="setting.setting_type === 'select'"
                                @click="openOptionsEditor(setting)"
                                class="text-purple-400 hover:text-purple-300 text-sm"
                            >
                                ⚙️ Options
                            </button>
                            <button
                                @click="editSetting(setting)"
                                class="text-blue-400 hover:text-blue-300 text-sm"
                            >
                                ✏️
                            </button>
                            <button
                                @click="deleteSetting(setting)"
                                class="text-red-400 hover:text-red-300 text-sm"
                            >
                                🗑️
                            </button>
                        </div>
                    </div>
                    <div v-if="primarySettings.length === 0" class="text-gray-500 text-sm p-3">
                        No primary settings defined yet.
                    </div>
                </div>
            </div>

            <!-- Secondary Settings -->
            <div class="mb-6">
                <h4 class="text-sm font-medium text-purple-400 mb-3">🎨 Secondary Settings (Affects Appearance)</h4>
                <div class="space-y-2">
                    <div
                        v-for="setting in secondarySettings"
                        :key="setting.id"
                        class="flex items-center justify-between bg-gray-700 rounded-lg p-3"
                    >
                        <div class="flex items-center space-x-4">
                            <span class="text-gray-400 text-sm font-mono">{{ setting.setting_key }}</span>
                            <span class="text-white">{{ setting.setting_label }}</span>
                            <span class="px-2 py-1 text-xs bg-gray-600 text-gray-300 rounded">{{ setting.setting_type }}</span>
                            <span v-if="setting.is_hidden" class="px-2 py-1 text-xs bg-yellow-900 text-yellow-300 rounded">Hidden</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="text-gray-400 text-sm">Default: {{ setting.default_value }}</span>
                            <button
                                v-if="setting.setting_type === 'select'"
                                @click="openOptionsEditor(setting)"
                                class="text-purple-400 hover:text-purple-300 text-sm"
                            >
                                ⚙️ Options
                            </button>
                            <button
                                @click="editSetting(setting)"
                                class="text-blue-400 hover:text-blue-300 text-sm"
                            >
                                ✏️
                            </button>
                            <button
                                @click="deleteSetting(setting)"
                                class="text-red-400 hover:text-red-300 text-sm"
                            >
                                🗑️
                            </button>
                        </div>
                    </div>
                    <div v-if="secondarySettings.length === 0" class="text-gray-500 text-sm p-3">
                        No secondary settings defined yet.
                    </div>
                </div>
            </div>

    

            <div class="flex justify-end mt-6">
                <button
                    @click="$emit('close')"
                    class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-500"
                >
                    Close
                </button>
            </div>
        </div>
    </div>

    <!-- Options Editor Modal -->
    <div
        v-if="optionsEditor.visible"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
    >
        <div class="bg-gray-800 rounded-lg shadow-xl p-6 w-[500px] max-h-[80vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-white">
                    ⚙️ Edit Options: {{ optionsEditor.setting?.setting_label || optionsEditor.setting?.setting_key }}
                </h3>
                <button @click="closeOptionsEditor" class="text-gray-400 hover:text-white">✕</button>
            </div>

            <label class="block text-xs text-gray-400 mb-1">Options (one per line)</label>
            <textarea
                v-model="optionsEditor.text"
                class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded text-white text-sm"
                rows="6"
            ></textarea>
            <p class="text-[11px] text-gray-400 mt-2">يمكنك استخدام سطر جديد أو فاصلة أو باك-سلاش للفصل بين الخيارات.</p>

            <div class="flex justify-end gap-2 mt-4">
                <button @click="closeOptionsEditor" class="px-3 py-2 bg-gray-600 text-white rounded hover:bg-gray-500 text-sm">
                    إلغاء
                </button>
                <button @click="saveOptionsEditor" class="px-3 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                    حفظ
                </button>
            </div>
        </div>
    </div>
</template>



<script>
import { ref, computed, watch, onMounted } from 'vue';
import axios from 'axios';

export default {
    name: 'SettingsManagerModal',
    props: {
        widget: {
            type: Object,
            required: true,
        },
    },
    emits: ['close', 'saved'],
    setup(props, { emit }) {

        /*** ===== State ===== ***/
        const settings = ref([]);
        const actions = ref([]);
        const errorMessage = ref('');
        const successMessage = ref('');

        const newSetting = ref({
            setting_key: '',
            setting_label: '',
            setting_type: 'text',
            setting_category: 'primary',
            default_value: '',
            is_hidden: false,
            order: 0,
        });

        const newSettingOptionsText = ref('');
        const optionsEditor = ref({ visible: false, setting: null, text: '' });

        const newAction = ref({
            action_type: 'screen',
            action_label: '',
            target_type: 'screen_key',
        });

        /*** ===== Computed ===== ***/
        const normalizeCategory = (category) => (category || 'primary').toString().toLowerCase();

        // Treat missing/unknown category as primary so legacy settings still appear
        const primarySettings = computed(() =>
            settings.value
            .filter(s => normalizeCategory(s.setting_category) === 'primary')
                .sort((a, b) => (a.order || 0) - (b.order || 0))
        );

        const secondarySettings = computed(() =>
            settings.value
            .filter(s => normalizeCategory(s.setting_category) === 'secondary')
                .sort((a, b) => (a.order || 0) - (b.order || 0))
        );

        /*** ===== Watchers ===== ***/
        watch(
            () => props.widget?.id,
            async (id) => {
                if (!id) return;
                await loadSettings();
                await loadActions();
            },
            { immediate: true }
        );

        onMounted(async () => {
            if (props.widget?.id) {
                await loadSettings();
                await loadActions();
            }
        });

        /*** ===== Helpers ===== ***/
        const showError = (msg) => {
            errorMessage.value = msg;
            setTimeout(() => errorMessage.value = '', 4000);
        };

        const showSuccess = (msg) => {
            successMessage.value = msg;
            setTimeout(() => successMessage.value = '', 3000);
        };

        /*** ===== API Calls ===== ***/
        const loadSettings = async () => {
            try {
                const res = await axios.get(`/api/admin/widgets/${props.widget.id}/settings`)
                settings.value = res.data.data || res.data || []
            } catch (err) {
                console.error(err);
                showError('Failed to load settings');
            }
        };

        const loadActions = async () => {
            try {
                const res = await axios.get(`/api/admin/widgets/${props.widget.id}/actions`);
                actions.value = res.data.data || res.data || [];
            } catch (err) {
                console.error(err);
                showError('Failed to load actions');
            }
        };

        const addSetting = async () => {
            if (!newSetting.value.setting_key) return showError('Setting key is required');
            try {
                const payload = { ...newSetting.value };

                if (payload.setting_type === 'select') {
                    const parseOptions = (text) => text
                        // allow newline, comma, Arabic comma، semicolon, pipe, or backslash separators
                        .split(/[\r\n,\\؛;|،]+/)
                        .map(o => o.trim())
                        .filter(Boolean)
                        .map(opt => ({ label: opt, value: opt }));

                    let options = parseOptions(newSettingOptionsText.value);

                    // fallback: try default_value if options textarea empty
                    if (options.length === 0 && payload.default_value) {
                        options = parseOptions(payload.default_value);
                    }

                    if (options.length === 0) {
                        return showError('Please add at least one option');
                    }

                    payload.options = JSON.stringify(options);
                }

                await axios.post(`/api/admin/widgets/${props.widget.id}/settings`, payload);
                await loadSettings();
                newSetting.value = {
                    setting_key: '',
                    setting_label: '',
                    setting_type: 'text',
                    setting_category: 'primary',
                    default_value: '',
                    is_hidden: false,
                    order: 0,
                };
                newSettingOptionsText.value = '';
                showSuccess('Setting added successfully');
                emit('saved');
            } catch (err) {
                console.error(err);
                showError('Failed to add setting');
            }
        };

        const openOptionsEditor = (setting) => {
            const parsed = Array.isArray(setting.options)
                ? setting.options
                : (() => {
                    try {
                        return JSON.parse(setting.options || '[]');
                    } catch (e) {
                        return [];
                    }
                })();

            optionsEditor.value = {
                visible: true,
                setting,
                text: parsed.map(o => o.label || o.value || '').filter(Boolean).join('\n'),
            };
        };

        const saveOptionsEditor = async () => {
            const current = optionsEditor.value.setting;
            if (!current) return;

            const options = optionsEditor.value.text
                .split(/[\r\n,\\؛;|،]+/)
                .map(o => o.trim())
                .filter(Boolean)
                .map(opt => ({ label: opt, value: opt }));

            if (options.length === 0) {
                return showError('Please add at least one option');
            }

            try {
                await axios.put(`/api/admin/widgets/${props.widget.id}/settings/${current.id}`, {
                    ...current,
                    options: JSON.stringify(options),
                });
                await loadSettings();
                showSuccess('Options updated');
                optionsEditor.value = { visible: false, setting: null, text: '' };
            } catch (err) {
                console.error(err);
                showError('Failed to update options');
            }
        };

        const closeOptionsEditor = () => {
            optionsEditor.value = { visible: false, setting: null, text: '' };
        };

        const editSetting = async (setting) => {
            try {
                // هنا يمكن عمل مودال تحرير كامل أو inline editing
                const key = prompt('Edit Setting Key:', setting.setting_key);
                if (key === null) return;
                await axios.put(`/api/admin/widgets/${props.widget.id}/settings/${setting.id}`, {
                    ...setting,
                    setting_key: key
                });
                await loadSettings();
                showSuccess('Setting updated successfully');
                emit('saved');
            } catch (err) {
                console.error(err);
                showError('Failed to edit setting');
            }
        };

        const deleteSetting = async (setting) => {
            if (!confirm('Delete this setting?')) return;
            try {
                await axios.delete(`/api/admin/widgets/${props.widget.id}/settings/${setting.id}`);
                await loadSettings();
                showSuccess('Setting deleted');
                emit('saved');
            } catch (err) {
                console.error(err);
                showError('Failed to delete setting');
            }
        };

        const addAction = async () => {
            if (!newAction.value.action_label) return showError('Action label required');
            try {
                await axios.post(`/api/admin/widgets/${props.widget.id}/actions`, newAction.value);
                await loadActions();
                newAction.value = {
                    action_type: 'screen',
                    action_label: '',
                    target_type: 'screen_key',
                };
                showSuccess('Action added successfully');
                emit('saved');
            } catch (err) {
                console.error(err);
                showError('Failed to add action');
            }
        };

        const deleteAction = async (action) => {
            if (!confirm('Delete this action?')) return;
            try {
                await axios.delete(`/api/admin/widgets/${props.widget.id}/actions/${action.id}`);
                await loadActions();
                showSuccess('Action deleted');
                emit('saved');
            } catch (err) {
                console.error(err);
                showError('Failed to delete action');
            }
        };

        /*** ===== Return ===== ***/
        return {
            settings,
            actions,
            newSetting,
            newSettingOptionsText,
            newAction,
            primarySettings,
            secondarySettings,
            errorMessage,
            successMessage,
            addSetting,
            editSetting,
            deleteSetting,
            addAction,
            deleteAction,
            openOptionsEditor,
            optionsEditor,
            saveOptionsEditor,
            closeOptionsEditor,
        };
    },
};
</script>
