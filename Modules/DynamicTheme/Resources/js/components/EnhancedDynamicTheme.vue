<template>
  <div class="dynamic-theme-enhanced">
    <!-- الشريط العلوي -->
    <div class="top-bar bg-gradient-to-r from-blue-600 to-purple-600 text-white p-4 rounded-t-lg">
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-2xl font-bold">🎨 لوحة تحكم الثيم الديناميكي</h1>
          <p class="text-sm text-blue-100">محرر متقدم لرسم وتخصيص الويدجتات</p>
        </div>
        <div class="flex space-x-3">
          <button
            @click="toggleHelper"
            class="px-4 py-2 bg-white/20 hover:bg-white/30 rounded-lg transition-colors"
          >
            ❓ مساعدة
          </button>
          <button
            @click="showSettings"
            class="px-4 py-2 bg-white/20 hover:bg-white/30 rounded-lg transition-colors"
          >
            ⚙️ الإعدادات
          </button>
        </div>
      </div>
    </div>

    <!-- المحتوى الرئيسي -->
    <div class="main-content p-6 bg-gray-50 min-h-screen">
      <!-- الواجهة الموجودة -->
      <div class="existing-ui mb-6">
        <div class="bg-white rounded-lg shadow p-4">
          <h2 class="text-lg font-semibold mb-4">📋 الشاشات والويدجتات</h2>
          <!-- محتوى الواجهة الموجودة -->
          <ScreensList
            :screens="screens"
            @select="selectScreen"
          />
        </div>
      </div>

      <!-- محرر الويدجت -->
      <div v-if="selectedWidget" class="editor-section">
        <WidgetEditorComponent
          :config-widget-override-id="selectedWidget.id"
          @save="onWidgetSave"
          @error="onWidgetError"
        />
      </div>

      <!-- قسم المساعدة -->
      <div v-if="showHelperPanel" class="helper-panel mt-6 bg-blue-50 rounded-lg shadow-lg p-4 border-l-4 border-blue-600">
        <div class="flex justify-between items-start mb-4">
          <h3 class="text-lg font-semibold text-blue-900">📚 دليل الاستخدام السريع</h3>
          <button @click="toggleHelper" class="text-blue-600 hover:text-blue-800">✕</button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-blue-800">
          <div class="bg-white p-3 rounded">
            <h4 class="font-semibold mb-2">🎨 اختيار الألوان</h4>
            <p>استخدم أداة اختيار الألوان لتحديد الألوان الأساسية والثانوية والتأكيد. يمكنك أيضاً استخدام الحفظ المسبق للألوان المفضلة.</p>
          </div>
          <div class="bg-white p-3 rounded">
            <h4 class="font-semibold mb-2">🔧 تخصيص الشكل</h4>
            <p>تحكم في نصف قطر الزوايا والحدود والظلال والخطوط والتباعد باستخدام المنزلقات والمدخلات.</p>
          </div>
          <div class="bg-white p-3 rounded">
            <h4 class="font-semibold mb-2">💾 حفظ القوالب</h4>
            <p>احفظ التصاميم المفضلة كقوالب لاستخدامها لاحقاً على ويدجتات أخرى.</p>
          </div>
          <div class="bg-white p-3 rounded">
            <h4 class="font-semibold mb-2">👁️ المعاينة الفورية</h4>
            <p>شاهد تأثير التغييرات فوراً في المعاينة وتصفح الأكواس المُنتجة.</p>
          </div>
        </div>
      </div>
    </div>

    <!-- إشعارات -->
    <div class="notifications-container fixed top-4 right-4 z-50 space-y-2">
      <transition-group name="notification">
        <div
          v-for="notification in notifications"
          :key="notification.id"
          :class="[
            'px-4 py-3 rounded-lg text-white font-medium shadow-lg animate-slide-in',
            notification.type === 'success' ? 'bg-green-600' :
            notification.type === 'error' ? 'bg-red-600' :
            'bg-blue-600'
          ]"
        >
          {{ notification.message }}
        </div>
      </transition-group>
    </div>

    <!-- تذييل الصفحة -->
    <div class="footer bg-gray-800 text-gray-300 p-4 mt-6 rounded-b-lg">
      <div class="flex justify-between items-center">
        <div class="text-sm">
          <p>🎨 Dynamic Theme Widget Editor v1.0.0</p>
          <p class="text-xs text-gray-400">© 2026 - جميع الحقوق محفوظة</p>
        </div>
        <div class="flex space-x-4 text-sm">
          <a href="/docs/widget-editor" class="hover:text-white">📖 الدليل</a>
          <a href="/api/docs" class="hover:text-white">🔌 API</a>
          <a href="/support" class="hover:text-white">💬 الدعم</a>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import WidgetEditorComponent from './WidgetEditorComponent.vue';
import ScreensList from './ScreensList.vue';

export default {
  name: 'EnhancedDynamicTheme',
  components: {
    WidgetEditorComponent,
    ScreensList,
  },
  data() {
    return {
      screens: [],
      selectedWidget: null,
      notifications: [],
      showHelperPanel: false,
      notificationId: 0,
    };
  },
  mounted() {
    this.loadScreens();
  },
  methods: {
    loadScreens() {
      axios.get('/api/screens')
        .then(response => {
          this.screens = response.data.screens || [];
        })
        .catch(error => {
          this.showNotification('فشل تحميل الشاشات', 'error');
        });
    },
    selectScreen(screen) {
      // يمكن تحديد ويدجت من الشاشة
      this.showNotification(`تم اختيار الشاشة: ${screen.name}`, 'success');
    },
    onWidgetSave(customizer) {
      this.showNotification('✅ تم حفظ التخصيص بنجاح', 'success');
      // تحديث البيانات
    },
    onWidgetError(error) {
      this.showNotification(`❌ خطأ: ${error.message}`, 'error');
    },
    toggleHelper() {
      this.showHelperPanel = !this.showHelperPanel;
    },
    showSettings() {
      this.showNotification('سيتم فتح الإعدادات قريباً', 'info');
    },
    showNotification(message, type = 'info') {
      const id = this.notificationId++;
      this.notifications.push({ id, message, type });

      setTimeout(() => {
        this.notifications = this.notifications.filter(n => n.id !== id);
      }, 3000);
    },
  },
};
</script>

<style scoped>
.dynamic-theme-enhanced {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

@keyframes slideIn {
  from {
    transform: translateX(400px);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

.animate-slide-in {
  animation: slideIn 0.3s ease-out;
}

.notification-enter-active,
.notification-leave-active {
  transition: all 0.3s ease;
}

.notification-enter-from {
  opacity: 0;
  transform: translateX(30px);
}

.notification-leave-to {
  opacity: 0;
  transform: translateX(30px);
}

/* تصميم جميل للشريط العلوي */
.top-bar {
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

/* تحسينات الاستجابة */
@media (max-width: 768px) {
  .main-content {
    padding: 1rem;
  }

  .helper-panel {
    grid-template-columns: 1fr;
  }
}

/* تأثير عند المرور */
button {
  transition: all 0.3s ease;
}

button:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}
</style>
