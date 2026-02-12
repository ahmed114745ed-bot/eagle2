<?php

namespace Utd\Agency\Services;

use Exception;
use Illuminate\Support\Facades\DB;

/**
 * Service for handling external model interactions
 * This ensures safe access to models that may or may not exist
 */
class ExternalModelService
{
    protected $config;

    public function __construct()
    {
        $this->config = config('agency-dependencies.dependencies.models', []);
    }

    /**
     * Get model class safely
     */
    public function getModelClass($modelName)
    {
        $className = $this->config[$modelName] ?? null;

        if ($className && class_exists($className)) {
            return $className;
        }

        return null;
    }

    /**
     * Check if model exists
     */
    public function modelExists($modelName)
    {
        $className = $this->getModelClass($modelName);

        return $className !== null;
    }

    /**
     * Create query builder for external model
     */
    public function query($modelName)
    {
        $className = $this->getModelClass($modelName);

        if ($className) {
            return $className::query();
        }

        return null;
    }

    /**
     * Safe sum operation
     */
    public function sum($modelName, $column, array $conditions = [])
    {
        $query = $this->query($modelName);

        if (! $query) {
            return 0;
        }

        foreach ($conditions as $field => $value) {
            if (is_array($value)) {
                $query->whereIn($field, $value);
            } else {
                $query->where($field, $value);
            }
        }

        return $query->sum($column) ?? 0;
    }

    /**
     * Safe count operation
     */
    public function count($modelName, array $conditions = [])
    {
        $query = $this->query($modelName);

        if (! $query) {
            return 0;
        }

        foreach ($conditions as $field => $value) {
            $query->where($field, $value);
        }

        return $query->count();
    }

    /**
     * Safe get operation
     */
    public function get($modelName, array $conditions = [], $columns = ['*'])
    {
        $query = $this->query($modelName);

        if (! $query) {
            return collect();
        }

        foreach ($conditions as $field => $value) {
            $query->where($field, $value);
        }

        return $query->select($columns)->get();
    }

    /**
     * Safe find operation
     */
    public function find($modelName, $id, $columns = ['*'])
    {
        $query = $this->query($modelName);

        if (! $query) {
            return null;
        }

        return $query->select($columns)->find($id);
    }

    /**
     * Get table name for model
     */
    public function getTableName($modelName)
    {
        $className = $this->getModelClass($modelName);

        if ($className) {
            return (new $className)->getTable();
        }

        return null;
    }

    /**
     * Execute raw DB query with fallback
     */
    public function rawSum($tableName, $column, array $conditions = [])
    {
        try {
            $query = DB::table($tableName);

            foreach ($conditions as $field => $value) {
                if (is_array($value)) {
                    $query->whereIn($field, $value);
                } else {
                    $query->where($field, $value);
                }
            }

            return $query->sum($column) ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }
}
