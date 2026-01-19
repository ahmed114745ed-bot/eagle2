import axios from 'axios';

const api = axios.create({
    baseURL: '/api',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
});

// Request interceptor
api.interceptors.request.use(
    (config) => {
        // Add CSRF token if available
        const token = document.querySelector('meta[name="csrf-token"]');
        if (token) {
            config.headers['X-CSRF-TOKEN'] = token.getAttribute('content');
        }
        return config;
    },
    (error) => Promise.reject(error)
);

// Response interceptor
api.interceptors.response.use(
    (response) => response,
    (error) => {
        console.error('API Error:', error.response?.data || error.message);
        return Promise.reject(error);
    }
);

// ==================== Screens API ====================

export const screensApi = {
    // Get all screens
    getAll: () => api.get('/dashboard/screens'),

    // Get single screen with widgets
    get: (key, configurationId = null, includeHidden = false) => {
        const params = [];
        if (configurationId) params.push(`configuration_id=${configurationId}`);
        if (includeHidden) params.push('include_hidden=true');
        const query = params.length ? `?${params.join('&')}` : '';
        return api.get(`/v1/screens/${key}${query}`);
    },

    // Create new screen
    create: (data) => api.post('/dashboard/screens', data),

    // Update screen
    update: (id, data) => api.put(`/dashboard/screens/${id}`, data),

    // Delete screen
    delete: (id) => api.delete(`/dashboard/screens/${id}`),

    // Duplicate screen
    duplicate: (id) => api.post(`/dashboard/screens/${id}/duplicate`),
};

// ==================== Widgets API ====================

export const widgetsApi = {
    // Get all widgets (library)
    getAll: () => api.get('/dashboard/widgets'),

    // Get widget details
    get: (id) => api.get(`/v1/widgets/${id}`),

    // Get widget themes
    getThemes: (widgetId) => api.get(`/dashboard/widgets/${widgetId}/themes`),
    getThemesByConfigurationId: (widgetId, configurationId) => {
        const query = configurationId ? `?configuration_id=${configurationId}` : '';
        return api.get(`/dashboard/widgets/${widgetId}/themes${query}`);
    },
};

// ==================== Screen Widgets API ====================

export const screenWidgetsApi = {
    // Add widget to screen
    add: (screenId, data) => api.post(`/dashboard/screens/${screenId}/widgets`, data),

    // Update widget on screen
    update: (screenId, widgetId, data) => api.put(`/dashboard/screens/${screenId}/widgets/${widgetId}`, data),

    // Remove widget from screen
    remove: (screenId, widgetId) => api.delete(`/dashboard/screens/${screenId}/widgets/${widgetId}`),

    // Reorder widgets
    reorder: (screenId, widgetIds) => api.post(`/dashboard/screens/${screenId}/widgets/reorder`, { widget_ids: widgetIds }),

    // Update widget settings
    updateSettings: (screenId, widgetId, settings) => api.patch(`/dashboard/screens/${screenId}/widgets/${widgetId}/settings`, settings),
};

// ==================== Import/Export API ====================

export const importExportApi = {
    // Download widgets template
    downloadWidgetsTemplate: () => {
        window.location.href = '/api/dashboard/import-export/widgets/template';
    },

    // Download themes template
    downloadThemesTemplate: () => {
        window.location.href = '/api/dashboard/import-export/themes/template';
    },

    // Import widgets
    importWidgets: (file) => {
        const formData = new FormData();
        formData.append('file', file);
        return api.post('/dashboard/import-export/widgets/import', formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
    },

    // Import themes
    importThemes: (file) => {
        const formData = new FormData();
        formData.append('file', file);
        return api.post('/dashboard/import-export/themes/import', formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
    },

    // Export widgets
    exportWidgets: () => {
        window.location.href = '/api/dashboard/import-export/widgets/export';
    },

    // Export screen config
    exportScreen: (screenId) => {
        return api.get(`/dashboard/screens/${screenId}/export`);
    },

    // Import screen config
    importScreen: (file) => {
        const formData = new FormData();
        formData.append('file', file);
        return api.post('/dashboard/import-export/screen/import', formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
    },
};

// ==================== Assets API ====================

export const assetsApi = {
    // Get all assets
    getAll: () => api.get('/v1/assets'),

    // Upload asset
    upload: (file, type = 'image') => {
        const formData = new FormData();
        formData.append('file', file);
        formData.append('type', type);
        return api.post('/dashboard/assets/upload', formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
    },

    // Delete asset
    delete: (id) => api.delete(`/dashboard/assets/${id}`),
};

// ==================== Visual Designer API ====================

export const designerApi = {
    // Update child position/size
    updateChildPosition: (childId, data) => 
        api.put(`/dashboard/theme-children/${childId}/position`, data),
    
    // Update asset position/size
    updateAssetPosition: (assetId, data) => 
        api.put(`/dashboard/theme-assets/${assetId}/position`, data),
    
    // Batch update positions
    batchUpdatePositions: (data) => 
        api.post('/dashboard/designer/batch-update-positions', data),
    
    // Update widget dimensions
    updateWidgetDimensions: (widgetId, data) =>
        api.put(`/dashboard/widgets/${widgetId}/dimensions`, data),
};

// ==================== Configurations API ====================

export const configurationsApi = {
    // List all configurations
    list: () => api.get('/dashboard/configurations'),

    // Get single configuration
    get: (id) => api.get(`/dashboard/configurations/${id}`),

    // Create new configuration
    create: (data) => api.post('/dashboard/configurations', data),

    // Update configuration
    update: (id, data) => api.put(`/dashboard/configurations/${id}`, data),

    // Delete configuration
    delete: (id) => api.delete(`/dashboard/configurations/${id}`),

    // Clone configuration
    clone: (id, newName) => api.post(`/dashboard/configurations/${id}/clone`, { name: newName }),

    // Activate configuration
    activate: (id) => api.post(`/dashboard/configurations/${id}/activate`),

    // Get full configuration for editing
    getFull: (id) => api.get(`/dashboard/configurations/${id}/full`),

    // Update screen overrides
    updateScreenOverrides: (id, overrides) =>
        api.put(`/dashboard/configurations/${id}/screens`, { overrides }),

    // Update widget overrides
    updateWidgetOverrides: (id, overrides) =>
        api.put(`/dashboard/configurations/${id}/widgets`, { overrides }),

    // Upload asset override
    uploadAssetOverride: (id, themeAssetId, file) => {
        const formData = new FormData();
        formData.append('theme_asset_id', themeAssetId);
        formData.append('file', file);
        return api.post(`/dashboard/configurations/${id}/assets`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
    },

    // Remove asset override
    removeAssetOverride: (id, themeAssetId) =>
        api.delete(`/dashboard/configurations/${id}/assets/${themeAssetId}`),
};

// ==================== Dashboard API (Aggregated) ====================

export const dashboardApi = {
    screens: screensApi,
    widgets: widgetsApi,
    screenWidgets: screenWidgetsApi,
    importExport: importExportApi,
    assets: assetsApi,
    configurations: configurationsApi,
    designer: designerApi,
};

export default api;
