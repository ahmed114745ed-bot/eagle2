import axios from 'axios';

const API_BASE_URL = '/api';

/**
 * Configurations API Service
 * Handles all configuration-related API calls
 */

export const configurationsApi = {
    /**
     * Get all configurations
     */
    async getAll() {
        try {
            const response = await axios.get(`${API_BASE_URL}/configurations`);
            return response.data;
        } catch (error) {
            console.error('Error fetching configurations:', error);
            throw error;
        }
    },

    /**
     * Get a specific configuration with full hierarchy
     */
    async getById(id) {
        try {
            const response = await axios.get(`${API_BASE_URL}/configurations/${id}`);
            return response.data;
        } catch (error) {
            console.error('Error fetching configuration:', error);
            throw error;
        }
    },

    /**
     * Get configuration statistics
     */
    async getStats(id) {
        try {
            const response = await axios.get(`${API_BASE_URL}/configurations/${id}/stats`);
            return response.data;
        } catch (error) {
            console.error('Error fetching configuration stats:', error);
            throw error;
        }
    },

    /**
     * Create a new configuration
     */
    async create(data) {
        try {
            const response = await axios.post(`${API_BASE_URL}/configurations`, data);
            return response.data;
        } catch (error) {
            console.error('Error creating configuration:', error);
            throw error;
        }
    },

    /**
     * Update a configuration
     */
    async update(id, data) {
        try {
            const response = await axios.put(`${API_BASE_URL}/configurations/${id}`, data);
            return response.data;
        } catch (error) {
            console.error('Error updating configuration:', error);
            throw error;
        }
    },

    /**
     * Delete a configuration
     */
    async delete(id) {
        try {
            const response = await axios.delete(`${API_BASE_URL}/configurations/${id}`);
            return response.data;
        } catch (error) {
            console.error('Error deleting configuration:', error);
            throw error;
        }
    },

    /**
     * Clone a configuration
     */
    async clone(id, newName) {
        try {
            const response = await axios.post(`${API_BASE_URL}/configurations/${id}/clone`, {
                name: newName
            });
            return response.data;
        } catch (error) {
            console.error('Error cloning configuration:', error);
            throw error;
        }
    },

    /**
     * Activate a configuration
     */
    async activate(id) {
        try {
            const response = await axios.post(`${API_BASE_URL}/configurations/${id}/activate`);
            return response.data;
        } catch (error) {
            console.error('Error activating configuration:', error);
            throw error;
        }
    },

    /**
     * Get configuration activity log
     */
    async getActivity(id) {
        try {
            const response = await axios.get(`${API_BASE_URL}/configurations/${id}/activity`);
            return response.data;
        } catch (error) {
            console.error('Error fetching activity log:', error);
            throw error;
        }
    },

    /**
     * Compare two configurations
     */
    async compare(id1, id2) {
        try {
            const response = await axios.get(`${API_BASE_URL}/configurations/compare`, {
                params: { config1_id: id1, config2_id: id2 }
            });
            return response.data;
        } catch (error) {
            console.error('Error comparing configurations:', error);
            throw error;
        }
    },
};

/**
 * Configuration Overrides API
 * Handles screen, widget, child, and asset overrides
 */

export const configOverridesApi = {
    /**
     * Get screen overrides
     */
    async getScreenOverrides(configId) {
        try {
            const response = await axios.get(
                `${API_BASE_URL}/configurations/${configId}/screen-overrides`
            );
            return response.data;
        } catch (error) {
            console.error('Error fetching screen overrides:', error);
            throw error;
        }
    },

    /**
     * Create/Update screen override
     */
    async saveScreenOverride(configId, screenId, data) {
        try {
            const response = await axios.post(
                `${API_BASE_URL}/configurations/${configId}/screen-overrides`,
                { screen_id: screenId, ...data }
            );
            return response.data;
        } catch (error) {
            console.error('Error saving screen override:', error);
            throw error;
        }
    },

    /**
     * Delete screen override
     */
    async deleteScreenOverride(configId, screenId) {
        try {
            const response = await axios.delete(
                `${API_BASE_URL}/configurations/${configId}/screen-overrides/${screenId}`
            );
            return response.data;
        } catch (error) {
            console.error('Error deleting screen override:', error);
            throw error;
        }
    },

    /**
     * Get widget overrides
     */
    async getWidgetOverrides(configId) {
        try {
            const response = await axios.get(
                `${API_BASE_URL}/configurations/${configId}/widget-overrides`
            );
            return response.data;
        } catch (error) {
            console.error('Error fetching widget overrides:', error);
            throw error;
        }
    },

    /**
     * Create/Update widget override
     */
    async saveWidgetOverride(configId, screenWidgetId, data) {
        try {
            const response = await axios.post(
                `${API_BASE_URL}/configurations/${configId}/widget-overrides`,
                { screen_widget_id: screenWidgetId, ...data }
            );
            return response.data;
        } catch (error) {
            console.error('Error saving widget override:', error);
            throw error;
        }
    },

    /**
     * Delete widget override
     */
    async deleteWidgetOverride(configId, screenWidgetId) {
        try {
            const response = await axios.delete(
                `${API_BASE_URL}/configurations/${configId}/widget-overrides/${screenWidgetId}`
            );
            return response.data;
        } catch (error) {
            console.error('Error deleting widget override:', error);
            throw error;
        }
    },

    /**
     * Get theme child overrides
     */
    async getThemeChildOverrides(configId) {
        try {
            const response = await axios.get(
                `${API_BASE_URL}/configurations/${configId}/theme-child-overrides`
            );
            return response.data;
        } catch (error) {
            console.error('Error fetching theme child overrides:', error);
            throw error;
        }
    },

    /**
     * Create/Update theme child override
     */
    async saveThemeChildOverride(configId, themeChildId, data) {
        try {
            const response = await axios.post(
                `${API_BASE_URL}/configurations/${configId}/theme-child-overrides`,
                { theme_child_id: themeChildId, ...data }
            );
            return response.data;
        } catch (error) {
            console.error('Error saving theme child override:', error);
            throw error;
        }
    },

    /**
     * Delete theme child override
     */
    async deleteThemeChildOverride(configId, themeChildId) {
        try {
            const response = await axios.delete(
                `${API_BASE_URL}/configurations/${configId}/theme-child-overrides/${themeChildId}`
            );
            return response.data;
        } catch (error) {
            console.error('Error deleting theme child override:', error);
            throw error;
        }
    },

    /**
     * Get asset overrides
     */
    async getAssetOverrides(configId) {
        try {
            const response = await axios.get(
                `${API_BASE_URL}/configurations/${configId}/asset-overrides`
            );
            return response.data;
        } catch (error) {
            console.error('Error fetching asset overrides:', error);
            throw error;
        }
    },

    /**
     * Create/Update asset override
     */
    async saveAssetOverride(configId, themeAssetId, data) {
        try {
            const response = await axios.post(
                `${API_BASE_URL}/configurations/${configId}/asset-overrides`,
                { theme_asset_id: themeAssetId, ...data }
            );
            return response.data;
        } catch (error) {
            console.error('Error saving asset override:', error);
            throw error;
        }
    },

    /**
     * Delete asset override
     */
    async deleteAssetOverride(configId, themeAssetId) {
        try {
            const response = await axios.delete(
                `${API_BASE_URL}/configurations/${configId}/asset-overrides/${themeAssetId}`
            );
            return response.data;
        } catch (error) {
            console.error('Error deleting asset override:', error);
            throw error;
        }
    },

    /**
     * Get child asset overrides
     */
    async getChildAssetOverrides(configId) {
        try {
            const response = await axios.get(
                `${API_BASE_URL}/configurations/${configId}/child-asset-overrides`
            );
            return response.data;
        } catch (error) {
            console.error('Error fetching child asset overrides:', error);
            throw error;
        }
    },

    /**
     * Create/Update child asset override
     */
    async saveChildAssetOverride(configId, childId, assetId, data) {
        try {
            const response = await axios.post(
                `${API_BASE_URL}/configurations/${configId}/child-asset-overrides`,
                { child_id: childId, asset_id: assetId, ...data }
            );
            return response.data;
        } catch (error) {
            console.error('Error saving child asset override:', error);
            throw error;
        }
    },

    /**
     * Delete child asset override
     */
    async deleteChildAssetOverride(configId, childId) {
        try {
            const response = await axios.delete(
                `${API_BASE_URL}/configurations/${configId}/child-asset-overrides/${childId}`
            );
            return response.data;
        } catch (error) {
            console.error('Error deleting child asset override:', error);
            throw error;
        }
    },
};

/**
 * Bulk Operations API
 */

export const bulkOperationsApi = {
    /**
     * Bulk update widget visibility
     */
    async bulkUpdateWidgetVisibility(configId, updates) {
        try {
            const response = await axios.post(
                `${API_BASE_URL}/configurations/${configId}/bulk/widget-visibility`,
                { updates }
            );
            return response.data;
        } catch (error) {
            console.error('Error bulk updating widget visibility:', error);
            throw error;
        }
    },

    /**
     * Bulk update screen visibility
     */
    async bulkUpdateScreenVisibility(configId, updates) {
        try {
            const response = await axios.post(
                `${API_BASE_URL}/configurations/${configId}/bulk/screen-visibility`,
                { updates }
            );
            return response.data;
        } catch (error) {
            console.error('Error bulk updating screen visibility:', error);
            throw error;
        }
    },

    /**
     * Bulk reorder items
     */
    async bulkReorder(configId, type, orders) {
        try {
            const response = await axios.post(
                `${API_BASE_URL}/configurations/${configId}/bulk/reorder`,
                { type, orders }
            );
            return response.data;
        } catch (error) {
            console.error('Error bulk reordering:', error);
            throw error;
        }
    },
};

export default {
    configurationsApi,
    configOverridesApi,
    bulkOperationsApi,
};
