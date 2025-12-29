<template>
  <div class="bg-white rounded-lg shadow">
    <div class="p-4 border-b border-gray-200">
      <h2 class="text-lg font-semibold text-gray-900">📦 Widgets Library</h2>
    </div>

    <div class="p-2 max-h-96 overflow-y-auto">
      <draggable
        :list="widgets"
        :group="{ name: 'widgets', pull: 'clone', put: false }"
        :sort="false"
        item-key="id"
      >
        <template #item="{ element }">
          <div
            class="p-3 bg-gray-50 rounded-lg cursor-grab hover:bg-gray-100 border-2 border-transparent hover:border-blue-300 transition-all"
              :data-widget-id="element.id"
              :data-widget-key="element.widget_key"
          >
            <div class="flex items-center space-x-3">
              <div class="flex-shrink-0 w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                <span class="text-xl">{{ getWidgetIcon(element.widget_type) }}</span>
              </div>
              <div class="flex-1 min-w-0">
                <p class="font-medium text-gray-900 truncate">{{ element.display_name }}</p>
                <p class="text-sm text-gray-500 truncate">{{ element.widget_type }}</p>
              </div>
              <div class="flex-shrink-0">
                <span
                  v-if="element.is_repeatable"
                  class="px-2 py-1 text-xs bg-purple-100 text-purple-800 rounded-full"
                  title="Can be added multiple times"
                >
                  ∞
                </span>
              </div>
            </div>
            <p v-if="element.description" class="mt-2 text-xs text-gray-400 truncate">
              {{ element.description }}
            </p>
          </div>
        </template>
      </draggable>
    </div>

    <div class="p-4 border-t border-gray-200 bg-gray-50 rounded-b-lg">
      <p class="text-xs text-gray-500 text-center">
        Drag widgets to the screen builder
      </p>
    </div>
  </div>
</template>

<script>
import draggable from 'vuedraggable';

export default {
  name: 'WidgetsLibrary',
  components: { draggable },
  props: { widgets: Array },
  setup() {
    const getWidgetIcon = (type) => {
      const icons = {
        'tab_bar': '📑',
        'banner': '🖼️',
        'room': '🏠',
        'ranking': '🏆',
        'categories': '🏷️',
        'country_filter': '🌍',
        'floating_button': '⭕',
      };
      return icons[type] || '📦';
    };
    return { getWidgetIcon };
  },
};
</script>
