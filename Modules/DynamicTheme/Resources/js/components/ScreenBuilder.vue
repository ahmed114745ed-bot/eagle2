<template>
  <div class="screen-builder-root">
  <!-- Notifications -->
  <div class="fixed top-4 right-4 space-y-2 z-50">
    <div
      v-for="n in notifications"
      :key="n.id"
      :class="[
        'px-4 py-2 rounded shadow-lg text-white font-medium',
        n.type === 'success' ? 'bg-green-500' :
        n.type === 'error' ? 'bg-red-500' :
        'bg-blue-500'
      ]"
    >
      {{ n.message }}
    </div>
  </div>

  <div class="bg-white rounded-lg shadow">
    <!-- Header -->
    <div class="p-4 border-b border-gray-200">
      <div class="flex justify-between items-center">
        <div>
          <h2 class="text-lg font-semibold text-gray-900">🛠️ Screen Builder</h2>
          <p class="text-sm text-gray-500">{{ screen.screen_name }} ({{ screen.screen_key }})</p>
        </div>
        <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">
          {{ screenWidgets.length }} widgets
        </span>
      </div>
    </div>

    <!-- Regular Widgets -->
    <div class="p-4">
      <h3 class="text-sm font-medium text-gray-700 mb-3">📋 Regular Widgets</h3>

      <draggable
        v-model="localRegularWidgets"
        :group="{ name: 'widgets', pull: false, put: true }"
        :clone="cloneWidget"
        item-key="id"
        class="min-h-24 bg-gray-50 rounded-lg p-2 space-y-2"
        ghost-class="opacity-50"
        @add="onWidgetAdded"
        @end="onDragEnd"
      >
        <template #item="{ element }">
          <div
            :class="[
              'p-4 bg-white rounded-lg border-2 cursor-pointer transition-all',
              selectedWidget?.id === element.id ? 'border-blue-500 shadow-lg' : 'border-gray-200 hover:border-blue-300',
              !isWidgetVisible(element) ? 'opacity-50' : ''
            ]"
            @click="$emit('edit-widget', element)"
          >
            <div class="flex items-center justify-between lol">
              <div class="flex items-center space-x-3">
                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center text-lg">
                  {{ getWidgetIcon(element.widget_type) }}
                </div>
                <div>
                  <p class="font-medium text-gray-900">{{ element.display_name || element.widget_type || element.widget_key }}</p>
                  <p class="text-sm text-gray-500">{{ element.theme_key || element.widget_key || element.widget_type }}</p>
                </div>
              </div>
              <div class="flex items-center space-x-2">
                <span class="text-xs text-gray-400">Order: {{ element.display_order ?? element.order }}</span>
                <button
                  @click.stop="$emit('toggle-widget-visibility', element)"
                  :class="[
                    'p-1 rounded transition-colors',
                    isWidgetVisible(element) ? 'text-green-600 hover:bg-green-50' : 'text-gray-400 hover:bg-gray-100'
                  ]"
                  :title="isWidgetVisible(element) ? 'Click to hide' : 'Click to show'"
                >
                  <svg v-if="isWidgetVisible(element)" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                  </svg>
                  <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"></path>
                  </svg>
                </button>

                <button
                  @click.stop="$emit('remove-widget', element)"
                  class="p-1 text-red-500 hover:bg-red-50 rounded"
                  title="Remove widget"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </template>
      </draggable>

      <div v-if="localRegularWidgets.length === 0" class="text-center py-8 text-gray-400">
        <p>Drag widgets here from the library</p>
      </div>
    </div>

    <!-- Positioned Widgets -->
    <div class="p-4 border-t border-gray-200">
      <h3 class="text-sm font-medium text-gray-700 mb-3">📍 Positioned Widgets (Floating)</h3>

      <draggable
        v-model="localPositionedWidgets"
        :group="{ name: 'widgets', pull: false, put: true }"
        :clone="cloneWidget"
        item-key="id"
        class="min-h-24 bg-yellow-50 rounded-lg p-2 space-y-2"
        ghost-class="opacity-50"
        @add="onPositionedWidgetAdded"
        @end="onDragEnd"
      >
        <template #item="{ element }">
          <div
            :class="[
              'p-4 bg-white rounded-lg border-2 cursor-pointer transition-all',
              selectedWidget?.id === element.id
                ? 'border-yellow-500 shadow-lg'
                : 'border-yellow-200 hover:border-yellow-400'
            ]"
            @click="$emit('edit-widget', element)"
          >
            <div class="flex items-center justify-between">
              <div class="flex items-center space-x-3">
                <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center text-lg">
                  {{ getWidgetIcon(element.widget_type) }}
                </div>
                <div>
                  <p class="font-medium text-gray-900">{{ element.widget_type }}</p>
                  <p class="text-sm text-gray-500">
                    Position: {{ element.position?.anchor || 'Not set' }}
                  </p>
                </div>
              </div>
              <button
                @click.stop="$emit('remove-widget', element)"
                class="p-1 text-red-500 hover:bg-red-50 rounded"
                title="Remove widget"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
              </button>
            </div>

            <!-- Action Preview -->
            <div v-if="element.action" class="mt-2 p-2 bg-yellow-50 rounded text-xs">
              <span class="font-medium">Action:</span> {{ element.action.type }}
              <span v-if="element.action.screen_key"> → {{ element.action.screen_key }}</span>
              <span v-if="element.action.url"> → {{ element.action.url }}</span>
            </div>
          </div>
        </template>
      </draggable>

      <div v-if="localPositionedWidgets.length === 0" class="text-center py-4 text-gray-400 text-sm">
        <p>Drag floating widgets here (e.g., Games button)</p>
      </div>
    </div>
  </div>
  </div>
</template>

<script>
import { ref, reactive, watch, computed } from 'vue';
import draggable from 'vuedraggable';
import axios from 'axios';

export default {
  name: 'ScreenBuilder',
  components: { draggable },
  props: {
    screen: { type: Object, required: true },
    screenWidgets: { type: Array, required: true },
    widgetOverrides: { type: Array, default: () => [] },
    configurationId: {
    type: Number,
    required: true
  },
  },
  
  emits: ['update-order', 'remove-widget', 'edit-widget', 'add-widget', 'toggle-widget-visibility'],
  setup(props, { emit }) {
    const selectedWidget = ref(null);
    const localRegularWidgets = ref([]);
    const localPositionedWidgets = ref([]);
    const notifications = reactive([]);

    // Modal children
    const showAddChildModal = ref(false);
    const selectedWidgetForChild = ref(null);
    const childForm = reactive({ child_key: '', child_type: 'tab', label: '', order: null, is_visible: true, is_active: false, position: null });
    const childrenMap = reactive({});

    watch(() => props.screenWidgets, (newWidgets) => {
          console.log('All screenWidgets:', newWidgets);

      localRegularWidgets.value = newWidgets.filter(w => !w.is_positioned);
      localPositionedWidgets.value = newWidgets.filter(w => w.is_positioned);
    }, { immediate: true, deep: true });

    const isWidgetVisible = (widget) => {
      const override = props.widgetOverrides.find(wo => wo.screen_widget_id === widget.id);
      return override ? override.is_visible : true;
    };

    const getWidgetIcon = (type) => {
      const icons = {
        'tab_bar': '📑', 'banner': '🖼️', 'room': '🏠', 'ranking': '🏆',
        'categories': '🏷️', 'country_filter': '🌍', 'floating_button': '⭕',
      };
      return icons[type] || '📦';
    };

    const totalWidgetsCount = computed(() => localRegularWidgets.value.length + localPositionedWidgets.value.length);
    const allowedWidgetIds = computed(() => props.screen.allowed_widgets_v2?.map(w => w.id) || []);

    const cloneWidget = (widget) => ({ ...widget });

    const addNotification = (message, type = 'info') => {
      const id = Date.now();
      notifications.push({ id, message, type });
      setTimeout(() => {
        const index = notifications.findIndex(n => n.id === id);
        if (index !== -1) notifications.splice(index, 1);
      }, 3000);
    };

    const onDragEnd = () => {
      emit('update-order', [...localRegularWidgets.value, ...localPositionedWidgets.value]);
    };


        const onWidgetAdded = (event) => {
          const widgetData = event.added ? event.added.element : event.item;
          if (!widgetData) return;

          const widgetId = Number(widgetData.dataset?.widgetId ?? widgetData.id);

          if (!allowedWidgetIds.value.includes(widgetId)) {
            addNotification('🚫 This widget is not allowed!', 'error');
            return;
          }

          if (totalWidgetsCount.value >= props.screen.max_widgets) {
            addNotification(`🚫 Max ${props.screen.max_widgets} widgets allowed`, 'error');
            return;
          }

          emit('add-widget', {
            widget_id: widgetId,
            widget_key: widgetData.widget_key,
            is_positioned: false,
          });
        };



    const onPositionedWidgetAdded = (event) => {
      if (!event.added) return;
      const widgetData = event.added.element;

      if (!allowedWidgetIds.value.includes(widgetData.id)) {
        addNotification('🚫 This widget is not allowed for this screen!', 'error');
        return;
      }

      if (totalWidgetsCount.value >= props.screen.max_widgets) {
        addNotification(`🚫 Maximum ${props.screen.max_widgets} widgets allowed`, 'error');
        return;
      }

      emit('add-widget', { widget_id: widgetData.id, widget_key: widgetData.widget_key, is_positioned: true });
    };

    const fetchChildren = async (widget) => {
      try {
        const res = await axios.get(`/api/screens/${props.screen.id}/widgets/${widget.id}/children`);
        childrenMap[widget.id] = res.data;
      } catch {
        childrenMap[widget.id] = [];
      }
    };

    const openAddChildModal = async (widget) => {
      selectedWidgetForChild.value = widget;
      Object.assign(childForm, { child_key: '', child_type: 'tab', label: '', order: null, is_visible: true, is_active: false, position: null });
      await fetchChildren(widget);
      showAddChildModal.value = true;
    };
    const closeAddChildModal = () => showAddChildModal.value = false;
    const saveChild = async () => {
      try {
        await axios.post(`/api/dashboard/screens/${props.screen.id}/widgets/${selectedWidgetForChild.value.id}/children`, childForm);
        await fetchChildren(selectedWidgetForChild.value);
        showAddChildModal.value = false;
      } catch { addNotification('Failed to add child', 'error'); }
    };

    return {
      selectedWidget,
      localRegularWidgets,
      localPositionedWidgets,
      notifications,
      getWidgetIcon,
      isWidgetVisible,
      cloneWidget,
      onDragEnd,
      onWidgetAdded,
      onPositionedWidgetAdded,
      openAddChildModal,
      closeAddChildModal,
      showAddChildModal,
      selectedWidgetForChild,
      childForm,
      saveChild,
      childrenMap,
      totalWidgetsCount,
      allowedWidgetIds,
      addNotification
    };
  },
};
</script>
