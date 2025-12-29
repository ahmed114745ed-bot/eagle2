import axios from 'axios';

const API_BASE = '/api/admin';

const apiClient = axios.create({
    baseURL: API_BASE,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
});

// Add request interceptor for auth token if needed
apiClient.interceptors.request.use((config) => {
    // Add auth token from localStorage or session
    const token = localStorage.getItem('admin_token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

export const adminApi = {
    // Widgets
    widgets: {
        list: () => apiClient.get('/widgets'),
        getAll: () => apiClient.get('/widgets'),
        get: (id) => apiClient.get(`/widgets/${id}`),
        create: (data) => apiClient.post('/widgets', data),
        update: (id, data) => apiClient.put(`/widgets/${id}`, data),
        delete: (id) => apiClient.delete(`/widgets/${id}`),

        // Settings
        getSettings: (widgetId) => apiClient.get(`/widgets/${widgetId}/settings`),
        addSetting: (widgetId, data) => apiClient.post(`/widgets/${widgetId}/settings`, data),
        updateSetting: (widgetId, settingId, data) =>
            apiClient.put(`/widgets/${widgetId}/settings/${settingId}`, data),
        deleteSetting: (widgetId, settingId) =>
            apiClient.delete(`/widgets/${widgetId}/settings/${settingId}`),

        // Actions
        getActions: (widgetId) => apiClient.get(`/widgets/${widgetId}/actions`),
        addAction: (widgetId, data) => apiClient.post(`/widgets/${widgetId}/actions`, data),
        updateAction: (widgetId, actionId, data) =>
            apiClient.put(`/widgets/${widgetId}/actions/${actionId}`, data),
        deleteAction: (widgetId, actionId) =>
            apiClient.delete(`/widgets/${widgetId}/actions/${actionId}`),
    },

    // Themes
    themes: {
        list: () => apiClient.get('/themes'),
        getAll: () => apiClient.get('/themes'),
        get: (id) => apiClient.get(`/themes/${id}`),
        create: (data) => apiClient.post('/themes', data),
        update: (id, data) => apiClient.put(`/themes/${id}`, data),
        delete: (id) => apiClient.delete(`/themes/${id}`),

        // Assets
        addAsset: (themeId, data) => apiClient.post(`/themes/${themeId}/assets`, data),
        updateAsset: (themeId, assetId, data) =>
            apiClient.put(`/themes/${themeId}/assets/${assetId}`, data),
        deleteAsset: (themeId, assetId) =>
            apiClient.delete(`/themes/${themeId}/assets/${assetId}`),
        updateAssetDash: (themeId, assetId, data) =>
            apiClient.put(`/themes/${themeId}/assets-dashboard/${assetId}`, data),
    },

    // Asset Upload
    assets: {
        upload: (formData) => apiClient.post('/upload', formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        }),
        uploadForAsset: (assetId, formData) => apiClient.post(`/assets/${assetId}/upload`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        }),
        deleteFile: (assetId) => apiClient.delete(`/assets/${assetId}/file`),
    },

    // Screens
    screens: {
        list: () => apiClient.get('/screens'),
        getAll: () => apiClient.get('/screens'),
        get: (id) => apiClient.get(`/screens/${id}`),
        create: (data) => apiClient.post('/screens', data),
        update: (id, data) => apiClient.put(`/screens/${id}`, data),
        delete: (id) => apiClient.delete(`/screens/${id}`),
        // Widgets on screen
        updateWidgets: (screenId, data) => apiClient.put(`/screens/${screenId}/widgets`, data),
        preview: (screenId) => apiClient.get(`/screens/${screenId}/preview`),
        duplicate: (screenId) => apiClient.post(`/screens/${screenId}/duplicate`),

        allowedWidgets: (screenId) => axios.get(`/api/dashboard/screens/${screenId}/allowed-widgets`),
        updateAllowedWidgets: (screenId, data) =>
        axios.put(`/api/dashboard/screens/${screenId}/allowed-widgets`, data),
        
        updateWidgets: (screenId, data) =>
        axios.put(`/api/dashboard/screens/${screenId}/widgets`, data),
    },

    // Import/Export
    import: {
        upload: (formData) => apiClient.post('/import', formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        }),
        downloadTemplate: (type) => apiClient.get(`/export/template/${type}`, {
            responseType: 'blob',
        }),
    },

    export: {
        widgets: () => apiClient.get('/export/widgets', { responseType: 'blob' }),
        themes: () => apiClient.get('/export/themes', { responseType: 'blob' }),
        screens: () => apiClient.get('/export/screens', { responseType: 'blob' }),
    },
};

export default adminApi;
