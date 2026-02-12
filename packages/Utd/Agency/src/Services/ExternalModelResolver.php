<?php

namespace Utd\Agency\Services;

use Exception;
use Illuminate\Support\Facades\Log;

class ExternalModelResolver
{
    /**
     * Configuration cache
     */
    protected $config;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->config = config('agency-dependencies.dependencies', []);
    }

    /**
     * Resolve a model class from configuration
     *
     * @param  string  $key  The model key from config
     * @return string|null The fully qualified class name or null if not found
     */
    public function resolveModel(string $key): ?string
    {
        $modelClass = $this->config['models'][$key] ?? null;

        if (! $modelClass || ! class_exists($modelClass)) {
            Log::warning("Agency Package: Model '$key' not found or not available", [
                'key' => $key,
                'class' => $modelClass,
            ]);

            return null;
        }

        return $modelClass;
    }

    /**
     * Create a query builder for a model
     *
     * @param  string  $key  The model key from config
     * @return \Illuminate\Database\Eloquent\Builder|null
     */
    public function query(string $key)
    {
        $modelClass = $this->resolveModel($key);

        if (! $modelClass) {
            return null;
        }

        try {
            return $modelClass::query();
        } catch (Exception $e) {
            Log::error("Agency Package: Failed to create query for model '$key'", [
                'key' => $key,
                'class' => $modelClass,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Find a model by ID
     *
     * @param  string  $key  The model key from config
     * @param  mixed  $id  The model ID
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function find(string $key, $id)
    {
        $modelClass = $this->resolveModel($key);

        if (! $modelClass) {
            return null;
        }

        try {
            return $modelClass::find($id);
        } catch (Exception $e) {
            Log::error("Agency Package: Failed to find model '$key' with ID $id", [
                'key' => $key,
                'id' => $id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Create a new model instance
     *
     * @param  string  $key  The model key from config
     * @param  array  $attributes  Model attributes
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function create(string $key, array $attributes = [])
    {
        $modelClass = $this->resolveModel($key);

        if (! $modelClass) {
            return null;
        }

        try {
            return $modelClass::create($attributes);
        } catch (Exception $e) {
            Log::error("Agency Package: Failed to create model '$key'", [
                'key' => $key,
                'attributes' => $attributes,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Check if a model is available
     *
     * @param  string  $key  The model key from config
     */
    public function isAvailable(string $key): bool
    {
        $modelClass = $this->resolveModel($key);

        return $modelClass !== null;
    }

    /**
     * Get the model class directly
     *
     * @param  string  $key  The model key from config
     */
    public function getClass(string $key): ?string
    {
        return $this->resolveModel($key);
    }
}
