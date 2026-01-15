# Unified Customizer API Reference

## Quick Reference

### Base URL
```
/api/configurations/{configId}/widgets/{widgetOverrideId}
```

### Main Endpoints Summary

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/complete` | Get complete configuration |
| POST | `/complete` | Save complete configuration |
| GET | `/complete/export` | Export configuration |
| POST | `/complete/import` | Import configuration |
| POST | `/complete/clone` | Clone configuration |

## Detailed Endpoints

### 1. GET /complete
Retrieve all customization data for a widget including children, presets, and templates.

**Parameters:**
- None

**Response:**
```json
{
  "status": "success",
  "data": {
    "widget": {
      "id": 1,
      "customizer": {
        "id": 1,
        "config_widget_override_id": 1,
        "shape_config": {...},
        "color_config": {...},
        "border_config": {...},
        "shadow_config": {...},
        "typography_config": {...},
        "layout_config": {...},
        "effects_config": {...},
        "is_active": true,
        "created_at": "2024-01-15T10:30:00Z",
        "updated_at": "2024-01-15T10:30:00Z"
      },
      "css": "color: #333; background-color: #fff; ...",
      "tailwind_classes": ["bg-white", "text-gray-800", ...]
    },
    "children": [
      {
        "id": 1,
        "name": "Child 1",
        "description": "First child element",
        "customizer": {
          "id": 1,
          "position_config": {
            "top": 0,
            "left": 0,
            "width": 100,
            "height": 100
          },
          "shape_config": {
            "type": "rectangle",
            "borderRadius": 0
          },
          "color_config": {
            "background": "#ffffff",
            "border": "#000000"
          },
          "border_config": {
            "width": 1,
            "style": "solid"
          },
          "animation_config": {
            "type": "fade",
            "duration": 300
          }
        },
        "css": "position: absolute; top: 0px; left: 0px; ...",
        "is_active": true,
        "order": 0
      }
    ],
    "color_presets": [
      {
        "id": 1,
        "name": "Ocean Blue",
        "colors": "{\"primary\": \"#0066cc\", \"secondary\": \"#003366\", \"accent\": \"#00ccff\", \"text\": \"#ffffff\"}",
        "is_default": true,
        "created_at": "2024-01-15T10:30:00Z"
      }
    ],
    "design_templates": [
      {
        "id": 1,
        "name": "Modern Card",
        "description": "Modern card design template",
        "design_config": "{\"shape\": \"rounded\", \"shadow\": \"md\", \"...}",
        "is_public": true,
        "created_at": "2024-01-15T10:30:00Z"
      }
    ],
    "compiled_css": "/* Widget CSS */\n.widget-1 { ... }\n\n/* Children CSS */\n.child-1 { ... }\n.child-2 { ... }",
    "summary": {
      "total_children": 2,
      "active_children": 2,
      "total_presets": 1,
      "total_templates": 1
    }
  }
}
```

**Errors:**
```json
{
  "status": "error",
  "message": "Widget customizer not found"
}
```

---

### 2. POST /complete
Save complete widget and children customization at once.

**Request Body:**
```json
{
  "widget": {
    "shape_config": {
      "type": "rectangle",
      "borderRadius": 5
    },
    "color_config": {
      "background": "#f5f5f5",
      "text": "#333333"
    },
    "border_config": {
      "width": 1,
      "color": "#ddd",
      "style": "solid"
    },
    "shadow_config": {
      "blur": 10,
      "color": "rgba(0,0,0,0.1)"
    }
  },
  "children": [
    {
      "id": 1,
      "name": "Header",
      "shape_config": {
        "type": "rectangle",
        "borderRadius": 0
      },
      "position_config": {
        "top": 0,
        "left": 0,
        "width": 100,
        "height": 50
      },
      "is_active": true,
      "order": 0
    },
    {
      "name": "New Child",
      "shape_config": {...},
      "position_config": {...},
      "order": 1
    }
  ],
  "color_presets": [
    {
      "name": "Default Colors",
      "colors": "{\"primary\": \"#007bff\", \"secondary\": \"#6c757d\"}"
    }
  ]
}
```

**Response:**
```json
{
  "status": "success",
  "message": "Complete configuration saved successfully",
  "data": {
    "widget": {...},
    "children": [...],
    "color_presets": [...]
  }
}
```

**Status Code:** 201 Created

---

### 3. GET /complete/export
Export complete configuration as JSON.

**Query Parameters:**
- None

**Response:**
```json
{
  "status": "success",
  "data": {
    "export_date": "2024-01-15T14:30:00.000Z",
    "version": "1.0",
    "configuration_id": 1,
    "widget_override_id": 1,
    "widget": {...},
    "children": [...],
    "color_presets": [...],
    "design_templates": [...]
  },
  "filename": "widget-customization-1-1.json"
}
```

**Download as File:**
```javascript
const response = await axios.get('/api/configurations/1/widgets/1/complete/export');
const blob = new Blob([JSON.stringify(response.data.data)], { type: 'application/json' });
const url = window.URL.createObjectURL(blob);
const link = document.createElement('a');
link.href = url;
link.download = response.data.filename;
link.click();
```

---

### 4. POST /complete/import
Import complete configuration from JSON.

**Request Body:**
```json
{
  "configuration": {
    "export_date": "2024-01-15T14:30:00.000Z",
    "version": "1.0",
    "widget": {...},
    "children": [...],
    "color_presets": [...]
  },
  "override_existing": true
}
```

**Parameters:**
- `override_existing` (boolean, default: true) - Replace existing data if true

**Response:**
```json
{
  "status": "success",
  "message": "Configuration imported successfully",
  "data": {
    "widget": {...},
    "children": [...],
    "color_presets": [...],
    "design_templates": [...],
    "summary": {
      "widget_imported": true,
      "children_imported": 5,
      "presets_imported": 3,
      "templates_imported": 2
    }
  }
}
```

**Status Code:** 201 Created

---

### 5. POST /complete/clone
Clone complete widget configuration to another widget.

**Request Body:**
```json
{
  "target_widget_override_id": 2
}
```

**Parameters:**
- `target_widget_override_id` (integer, required) - The target widget override ID

**Response:**
```json
{
  "status": "success",
  "message": "Configuration cloned successfully",
  "data": {
    "widget": {
      "id": 2,
      "config_widget_override_id": 2,
      "shape_config": {...},
      "color_config": {...}
    },
    "children": [
      {
        "id": 6,
        "config_widget_override_id": 2,
        "shape_config": {...},
        "position_config": {...}
      }
    ],
    "summary": {
      "widget_cloned": true,
      "children_cloned": 3
    }
  }
}
```

**Status Code:** 201 Created

---

## Child Customizer Endpoints

### GET /api/child-customizers/widget-override/{configWidgetOverrideId}
Get all children for a widget override.

**Response:**
```json
{
  "status": "success",
  "customizers": [...],
  "total": 5
}
```

---

### GET /api/child-customizers/{id}
Get specific child customizer.

**Response:**
```json
{
  "status": "success",
  "customizer": {...},
  "css": "position: absolute; top: 0px; ..."
}
```

---

### POST /api/child-customizers
Create new child customizer.

**Request:**
```json
{
  "config_widget_override_id": 1,
  "theme_child_id": null,
  "name": "New Child",
  "description": "Optional description",
  "shape_config": {
    "type": "rectangle",
    "borderRadius": 5
  },
  "position_config": {
    "top": 0,
    "left": 0,
    "width": 100,
    "height": 100
  }
}
```

---

### PUT /api/child-customizers/{id}
Update child customizer.

**Request:**
```json
{
  "shape_config": {...},
  "position_config": {...},
  "is_active": true,
  "order": 1
}
```

---

### DELETE /api/child-customizers/{id}
Delete child customizer.

**Response:**
```json
{
  "status": "success",
  "message": "Child customizer deleted successfully"
}
```

---

### GET /api/child-customizers/{id}/generate-css
Generate CSS for specific child.

**Response:**
```json
{
  "status": "success",
  "css": "position: absolute; top: 0px; left: 0px; ...",
  "selector": ".child-1",
  "full_css": ".child-1 { position: absolute; top: 0px; ... }"
}
```

---

### POST /api/child-customizers/batch-update
Update multiple children at once.

**Request:**
```json
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

**Response:**
```json
{
  "status": "success",
  "message": "Child customizers updated successfully"
}
```

---

## Error Responses

### Common Error Codes

| Code | Message | Meaning |
|------|---------|---------|
| 400 | Bad Request | Invalid parameters |
| 401 | Unauthorized | Not authenticated |
| 403 | Forbidden | No permission |
| 404 | Not Found | Resource not found |
| 422 | Unprocessable Entity | Validation error |
| 500 | Internal Server Error | Server error |

### Error Response Format
```json
{
  "status": "error",
  "message": "Description of error",
  "error": "Detailed error info"
}
```

---

## Authentication

All endpoints require Sanctum authentication:
```bash
curl -H "Authorization: Bearer TOKEN" \
     -H "Accept: application/json" \
     https://api.example.com/api/configurations/1/widgets/1/complete
```

---

## Rate Limiting

- 60 requests per minute per authenticated user
- 10 requests per minute per IP for unauthenticated endpoints

---

## Pagination (if applicable)

Child customizers are returned ordered by `order` field:
```
GET /api/child-customizers/widget-override/1?sort=order&direction=asc
```

---

## Code Examples

### JavaScript/Axios

```javascript
import axios from 'axios';

const api = axios.create({
  baseURL: '/api',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Accept': 'application/json'
  }
});

// Get complete configuration
const response = await api.get('/configurations/1/widgets/1/complete');

// Save configuration
await api.post('/configurations/1/widgets/1/complete', {
  widget: {...},
  children: [...]
});

// Export
const exportData = await api.get('/configurations/1/widgets/1/complete/export');

// Clone
await api.post('/configurations/1/widgets/1/complete/clone', {
  target_widget_override_id: 2
});
```

### cURL

```bash
# Get complete
curl -X GET "http://localhost/api/configurations/1/widgets/1/complete" \
  -H "Authorization: Bearer TOKEN" \
  -H "Accept: application/json"

# Save complete
curl -X POST "http://localhost/api/configurations/1/widgets/1/complete" \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"widget": {...}, "children": [...]}'

# Export
curl -X GET "http://localhost/api/configurations/1/widgets/1/complete/export" \
  -H "Authorization: Bearer TOKEN" > export.json

# Clone
curl -X POST "http://localhost/api/configurations/1/widgets/1/complete/clone" \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"target_widget_override_id": 2}'
```

---

## Response Time Guidelines

- GET /complete: < 200ms
- POST /complete: < 500ms
- Export/Import: < 1000ms
- Clone: < 1000ms

---

## Versioning

Current API version: **1.0**

Version is included in response headers:
```
API-Version: 1.0
```

---

## Support

For issues or questions:
- Check CHILD_CUSTOMIZER_GUIDE.md for detailed documentation
- Review test cases in Tests/ directory
- Check example implementations
