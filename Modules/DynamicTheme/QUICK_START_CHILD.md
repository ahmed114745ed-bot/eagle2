# Quick Start Guide - Child Widget Customization

## 5-Minute Setup

### Step 1: Run Migration
```bash
php artisan migrate --path=Modules/DynamicTheme/Database/Migrations
```

### Step 2: Register Vue Components
In your main Vue app file:
```javascript
import UnifiedCustomizerDashboard from '@/modules/dynamic-theme/components/UnifiedCustomizerDashboard.vue';
import ChildCustomizerComponent from '@/modules/dynamic-theme/components/ChildCustomizerComponent.vue';
```

### Step 3: Use in Your Page
```vue
<template>
  <UnifiedCustomizerDashboard 
    :config-id="configurationId" 
    :widget-override-id="widgetOverrideId" 
  />
</template>

<script setup>
import { ref } from 'vue';

const configurationId = ref(1);
const widgetOverrideId = ref(1);
</script>
```

### Step 4: Test API
```bash
curl -H "Authorization: Bearer TOKEN" \
     http://localhost/api/configurations/1/widgets/1/complete
```

---

## Basic API Usage

### Get Everything in One Call
```javascript
const response = await axios.get(
  '/api/configurations/1/widgets/1/complete'
);

const {
  widget,
  children,
  color_presets,
  design_templates,
  compiled_css
} = response.data.data;
```

### Save Everything at Once
```javascript
await axios.post(
  '/api/configurations/1/widgets/1/complete',
  {
    widget: {
      shape_config: { type: 'rectangle', borderRadius: 5 },
      color_config: { background: '#f5f5f5' }
    },
    children: [
      {
        name: 'Header',
        shape_config: { type: 'rectangle' },
        position_config: { top: 0, left: 0, width: 100, height: 50 }
      }
    ]
  }
);
```

### Add a Child Widget
```javascript
await axios.post('/api/child-customizers', {
  config_widget_override_id: 1,
  name: 'New Child',
  shape_config: { type: 'circle', borderRadius: 50 },
  position_config: { top: 10, left: 10, width: 50, height: 50 },
  color_config: { background: '#ffffff' }
});
```

### Export Configuration
```javascript
const response = await axios.get(
  '/api/configurations/1/widgets/1/complete/export'
);
// Download JSON file with all data
```

### Clone to Another Widget
```javascript
await axios.post(
  '/api/configurations/1/widgets/1/complete/clone',
  { target_widget_override_id: 2 }
);
```

---

## Common Tasks

### Task 1: Change Child Position
```javascript
// Get complete data
const response = await axios.get('/api/configurations/1/widgets/1/complete');
const children = response.data.data.children;

// Modify first child
const firstChild = children[0];
firstChild.customizer.position_config = {
  top: 20,
  left: 30,
  width: 150,
  height: 80
};

// Save
await axios.put(
  `/api/child-customizers/${firstChild.id}`,
  { position_config: firstChild.customizer.position_config }
);
```

### Task 2: Toggle Child Visibility
```javascript
await axios.put(
  `/api/child-customizers/${childId}`,
  { is_active: !isCurrentlyActive }
);
```

### Task 3: Reorder Children
```javascript
await axios.post('/api/child-customizers/batch-update', {
  customizers: [
    { id: 1, order: 0, is_active: true },
    { id: 2, order: 1, is_active: true },
    { id: 3, order: 2, is_active: true }
  ]
});
```

### Task 4: Create Color Preset
```javascript
await axios.post('/api/color-presets', {
  configuration_id: 1,
  name: 'Ocean Theme',
  colors: JSON.stringify({
    primary: '#0066cc',
    secondary: '#003366',
    accent: '#00ccff'
  })
});
```

### Task 5: Apply Preset to Widget
```javascript
// First get the preset
const response = await axios.get('/api/color-presets?configuration_id=1');
const preset = response.data.presets[0];

// Apply to widget
const colors = JSON.parse(preset.colors);
await axios.put(
  `/api/widget-customizers/${widgetCustomizerId}`,
  { color_config: colors }
);
```

---

## Component Usage Examples

### Use Child Customizer Component Alone
```vue
<template>
  <ChildCustomizerComponent
    :config-widget-override-id="1"
    @updated="onChildUpdated"
    @deleted="onChildDeleted"
  />
</template>

<script setup>
import ChildCustomizerComponent from '@/components/ChildCustomizerComponent.vue';

const onChildUpdated = () => {
  console.log('Child was updated');
};

const onChildDeleted = () => {
  console.log('Child was deleted');
};
</script>
```

### Use Unified Dashboard Component
```vue
<template>
  <div>
    <h1>Widget Designer</h1>
    <UnifiedCustomizerDashboard
      :config-id="1"
      :widget-override-id="1"
    />
  </div>
</template>

<script setup>
import UnifiedCustomizerDashboard from '@/components/UnifiedCustomizerDashboard.vue';
</script>
```

---

## Data Structures Reference

### Shape Config
```javascript
{
  type: 'rectangle',        // rectangle, circle, square, rounded, diamond
  borderRadius: 5           // pixels (0-50)
}
```

### Position Config
```javascript
{
  top: 0,      // pixels from top
  left: 0,     // pixels from left
  width: 100,  // width in pixels
  height: 100  // height in pixels
}
```

### Color Config
```javascript
{
  background: '#ffffff',    // hex color
  border: '#000000'         // hex color
}
```

### Border Config
```javascript
{
  width: 1,                 // pixels (0-10)
  style: 'solid'            // solid, dashed, dotted
}
```

### Animation Config
```javascript
{
  type: 'fade',             // none, fade, slide, bounce, pulse
  duration: 300             // milliseconds
}
```

---

## Debugging Tips

### Check Network Requests
```javascript
// Enable axios logging
axios.interceptors.request.use(config => {
  console.log('Request:', config);
  return config;
});

axios.interceptors.response.use(response => {
  console.log('Response:', response);
  return response;
});
```

### Inspect Component State
```javascript
// In Vue DevTools, inspect:
// - activeTab (current tab)
// - childrenList (all children)
// - selectedChildId (selected child)
// - compiledCSS (generated CSS)
```

### Check CSS Generation
```javascript
// Get CSS for a child
const response = await axios.get(`/api/child-customizers/1/generate-css`);
console.log('Generated CSS:', response.data.css);
```

### Validate JSON Config
```javascript
// Make sure JSON configs are valid
try {
  const colors = JSON.parse(preset.colors);
  console.log('Valid JSON:', colors);
} catch (e) {
  console.error('Invalid JSON:', e);
}
```

---

## Common Errors & Solutions

| Error | Solution |
|-------|----------|
| 404 Not Found | Check widget_override_id exists in database |
| 422 Validation Error | Check required fields in request |
| 401 Unauthorized | Verify Bearer token is correct |
| CSS not applying | Check if `is_active` is true |
| Children not showing | Verify position_config has valid values |
| Import fails | Check JSON format is valid |

---

## Performance Tips

1. **Load complete data once** instead of multiple API calls
2. **Use batch-update** for reordering children
3. **Cache color presets** on client side
4. **Export periodically** as backup
5. **Limit children to <50** per widget

---

## Next Steps

1. ✅ Set up database migration
2. ✅ Register Vue components
3. ✅ Use unified dashboard in your page
4. ✅ Test API endpoints
5. 📖 Read CHILD_CUSTOMIZER_GUIDE.md for advanced features
6. 📖 Read API_REFERENCE.md for complete API details

---

## Support

### Documentation Files
- **CHILD_CUSTOMIZER_GUIDE.md** - Complete feature guide
- **API_REFERENCE.md** - Full API documentation
- **COMPLETE_SUMMARY.md** - System overview

### Code Examples
- Check Modules/DynamicTheme/Tests/ for test examples
- Check component files for Vue patterns
- Check controller files for API patterns

### Quick Links
```
📁 Controllers:   Modules/DynamicTheme/Http/Controllers/Api/
📁 Models:        Modules/DynamicTheme/Entities/
📁 Components:    Modules/DynamicTheme/Resources/js/components/
📁 Routes:        Modules/DynamicTheme/Routes/
📁 Database:      Modules/DynamicTheme/Database/Migrations/
📁 Docs:          Modules/DynamicTheme/*.md
```

---

## You're Ready! 🚀

The system is fully set up and ready to use. Start by:
1. Running the migration
2. Importing the components
3. Adding the dashboard to your page
4. Testing with sample data

Any questions? Check the comprehensive guides!
