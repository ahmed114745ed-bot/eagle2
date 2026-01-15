# Child Widget Customization & Unified Endpoint Guide

## Overview

This guide explains how to use the new child widget customization system and unified endpoint that consolidates all customization features into a single entry point.

## Features

### 1. Child Widget Customization
Users can now customize child widgets/elements within a theme with complete control over:
- **Shape Control**: Type, border radius, dimensions
- **Color Control**: Background and border colors
- **Position Control**: Top, left, width, height positioning
- **Border Control**: Width and style (solid, dashed, dotted)
- **Animation Control**: Type and duration
- **Visibility Toggle**: Show/hide individual children
- **Reordering**: Drag-and-drop to reorder children

### 2. Unified Endpoint
A single comprehensive endpoint that returns/saves/exports everything:
- Widget customization
- All child customizations
- Color presets
- Design templates
- Compiled CSS

## API Endpoints

### Get Complete Configuration
```http
GET /api/configurations/{configId}/widgets/{widgetOverrideId}/complete
```

**Response:**
```json
{
  "status": "success",
  "data": {
    "widget": {
      "id": 1,
      "config_widget_override_id": 1,
      "customizer": {...},
      "css": "background-color: #fff; ...",
      "tailwind_classes": [...]
    },
    "children": [
      {
        "id": 1,
        "name": "Child Element 1",
        "customizer": {...},
        "css": "width: 100px; height: 100px; ...",
        "is_active": true,
        "order": 0
      }
    ],
    "color_presets": [...],
    "design_templates": [...],
    "compiled_css": "/* Widget CSS */\n.widget-1 { ... }\n/* Children CSS */\n.child-1 { ... }",
    "summary": {
      "total_children": 5,
      "active_children": 4,
      "total_presets": 3,
      "total_templates": 2
    }
  }
}
```

### Save Complete Configuration
```http
POST /api/configurations/{configId}/widgets/{widgetOverrideId}/complete
```

**Request:**
```json
{
  "widget": {
    "shape_config": {...},
    "color_config": {...},
    "border_config": {...}
  },
  "children": [
    {
      "id": 1,
      "shape_config": {...},
      "position_config": {...}
    }
  ],
  "color_presets": [...],
  "design_template": {...}
}
```

### Export Complete Configuration
```http
GET /api/configurations/{configId}/widgets/{widgetOverrideId}/complete/export
```

Returns JSON file with all customization data.

### Import Complete Configuration
```http
POST /api/configurations/{configId}/complete/import
```

**Request:**
```json
{
  "configuration": {
    "widget": {...},
    "children": [...],
    "color_presets": [...]
  },
  "override_existing": true
}
```

### Clone Complete Configuration
```http
POST /api/configurations/{configId}/widgets/{widgetOverrideId}/complete/clone
```

**Request:**
```json
{
  "target_widget_override_id": 2
}
```

## Child Customizer Endpoints

### Get All Children
```http
GET /api/child-customizers/widget-override/{configWidgetOverrideId}
```

### Get Specific Child
```http
GET /api/child-customizers/{id}
```

### Create Child
```http
POST /api/child-customizers

{
  "config_widget_override_id": 1,
  "name": "Child Element",
  "description": "Optional description",
  "shape_config": {
    "type": "rectangle",
    "borderRadius": 0
  },
  "color_config": {
    "background": "#ffffff",
    "border": "#000000"
  },
  "position_config": {
    "top": 0,
    "left": 0,
    "width": 100,
    "height": 100
  },
  "border_config": {
    "width": 1,
    "style": "solid"
  },
  "animation_config": {
    "type": "none",
    "duration": 300
  }
}
```

### Update Child
```http
PUT /api/child-customizers/{id}

{
  "shape_config": {...},
  "position_config": {...},
  "is_active": true
}
```

### Delete Child
```http
DELETE /api/child-customizers/{id}
```

### Generate CSS for Child
```http
GET /api/child-customizers/{id}/generate-css
```

### Batch Update Children
```http
POST /api/child-customizers/batch-update

{
  "customizers": [
    {
      "id": 1,
      "order": 0,
      "is_active": true
    },
    {
      "id": 2,
      "order": 1,
      "is_active": true
    }
  ]
}
```

## Vue Components

### UnifiedCustomizerDashboard
The main dashboard component integrating all features.

**Usage:**
```vue
<template>
  <UnifiedCustomizerDashboard
    :config-id="1"
    :widget-override-id="1"
  />
</template>

<script setup>
import UnifiedCustomizerDashboard from '@/components/UnifiedCustomizerDashboard.vue';
</script>
```

**Features:**
- 5 tabs: Widget, Children, Color Presets, Design Templates, Code
- Save all configurations at once
- Export/Import complete setup
- Clone configuration to other widgets
- Real-time CSS preview

### ChildCustomizerComponent
Standalone component for managing child widgets.

**Usage:**
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
  console.log('Child updated');
};

const onChildDeleted = () => {
  console.log('Child deleted');
};
</script>
```

**Features:**
- Drag-and-drop reordering
- Add/edit/delete children
- Visibility toggle
- Real-time preview
- Shape, position, color, border, animation controls

## Database Schema

### child_customizers Table

```sql
CREATE TABLE child_customizers (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    config_widget_override_id BIGINT NOT NULL,
    theme_child_id BIGINT,
    shape_config JSON,
    color_config JSON,
    gradient_config JSON,
    background_config JSON,
    border_config JSON,
    shadow_config JSON,
    animation_config JSON,
    transition_config JSON,
    typography_config JSON,
    layout_config JSON,
    position_config JSON,
    effects_config JSON,
    name VARCHAR(255),
    description TEXT,
    is_active BOOLEAN DEFAULT true,
    order INT DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP
);
```

## Configuration JSON Structures

### Shape Config
```json
{
  "type": "rectangle|circle|square|rounded|diamond",
  "borderRadius": 0
}
```

### Color Config
```json
{
  "background": "#ffffff",
  "border": "#000000"
}
```

### Position Config
```json
{
  "top": 0,
  "left": 0,
  "width": 100,
  "height": 100
}
```

### Border Config
```json
{
  "width": 1,
  "style": "solid|dashed|dotted"
}
```

### Animation Config
```json
{
  "type": "none|fade|slide|bounce|pulse",
  "duration": 300
}
```

## Workflow Example

### 1. Load Complete Configuration
```javascript
const response = await axios.get(
  '/api/configurations/1/widgets/1/complete'
);
const fullConfig = response.data.data;
```

### 2. Modify Widget and Children
```javascript
// Modify widget
fullConfig.widget.customizer.color_config.background = '#f0f0f0';

// Add new child
fullConfig.children.push({
  name: 'New Child',
  shape_config: { type: 'circle', borderRadius: 50 },
  position_config: { top: 10, left: 10, width: 50, height: 50 },
  is_active: true,
  order: fullConfig.children.length
});
```

### 3. Save Everything
```javascript
await axios.post('/api/configurations/1/widgets/1/complete', {
  widget: fullConfig.widget.customizer,
  children: fullConfig.children
});
```

### 4. Export for Backup
```javascript
const response = await axios.get(
  '/api/configurations/1/widgets/1/complete/export'
);
// Download JSON file
```

### 5. Clone to Another Widget
```javascript
await axios.post(
  '/api/configurations/1/widgets/1/complete/clone',
  { target_widget_override_id: 2 }
);
```

## Advanced Features

### Real-time CSS Generation
Each child customizer generates CSS automatically:
```php
$child = ChildCustomizer::find(1);
$css = $child->generateCSS(); // Returns CSS string
```

### Batch Operations
Update multiple children at once:
```http
POST /api/child-customizers/batch-update
{
  "customizers": [
    {"id": 1, "order": 0, "is_active": true},
    {"id": 2, "order": 1, "is_active": false}
  ]
}
```

### Template System
Save current setup as template and reuse:
```javascript
// Save template
await axios.post('/api/design-templates', {
  configuration_id: 1,
  name: 'Modern Card',
  design_config: JSON.stringify(currentConfig)
});

// Apply template
const template = designTemplates[0];
const config = JSON.parse(template.design_config);
```

## Best Practices

1. **Always Load Complete**: Use the unified endpoint to get consistent state
2. **Save Atomically**: Save all changes at once to avoid partial states
3. **Backup Regularly**: Export configuration periodically
4. **Use Templates**: Create templates for recurring designs
5. **Test in Preview**: Always preview before saving
6. **Version Control**: Keep exports in version control

## Troubleshooting

### Issue: CSS not applying
- Check if `is_active` is true for the child
- Verify CSS selector is correct
- Check browser console for errors

### Issue: Children not displaying
- Ensure `position_config` is set correctly
- Check z-index stacking order
- Verify parent widget dimensions

### Issue: Animation not working
- Set animation type (not 'none')
- Ensure duration > 0
- Check if browser supports CSS animations

### Issue: Import fails
- Verify JSON format is valid
- Check all required fields are present
- Ensure IDs don't conflict (use `override_existing: true`)

## Performance Considerations

- Use `completed_css` for production rendering
- Cache color presets on client side
- Limit children to < 50 per widget for optimal performance
- Use batch updates for multiple children changes

## Future Enhancements

- Advanced shape editor (custom SVG)
- Gradient builder
- Animation timeline
- Responsive breakpoints
- Child groups/layers
- Undo/redo functionality
