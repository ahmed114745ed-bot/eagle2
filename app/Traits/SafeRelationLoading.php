<?php

namespace App\Traits;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Trait SafeRelationLoading
 * 
 * Provides safe loading of relations when tables might not exist.
 * Useful for package-based architectures where tables may not be migrated.
 */
trait SafeRelationLoading
{
    /**
     * Cache for table existence checks
     */
    protected static $tableExistsCache = [];

    /**
     * Check if a table exists in the database
     * 
     * @param string $tableName
     * @return bool
     */
    protected function tableExists(string $tableName): bool
    {
        if (isset(self::$tableExistsCache[$tableName])) {
            return self::$tableExistsCache[$tableName];
        }

        try {
            self::$tableExistsCache[$tableName] = Schema::hasTable($tableName);
        } catch (\Exception $e) {
            Log::debug("Table existence check failed for {$tableName}: " . $e->getMessage());
            self::$tableExistsCache[$tableName] = false;
        }

        return self::$tableExistsCache[$tableName];
    }

    /**
     * Safely load missing relations
     * 
     * @param array|string $relations
     * @return $this
     */
    public function safeLoadMissing($relations)
    {
        try {
            return $this->loadMissing($relations);
        } catch (\Illuminate\Database\QueryException $e) {
            // Log the error for debugging
            Log::debug('Failed to load relation: ' . $e->getMessage());
            return $this;
        } catch (\Exception $e) {
            Log::debug('Failed to load relation: ' . $e->getMessage());
            return $this;
        }
    }

    /**
     * Safely load relations
     * 
     * @param array|string $relations
     * @return $this
     */
    public function safeLoad($relations)
    {
        try {
            return $this->load($relations);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::debug('Failed to load relation: ' . $e->getMessage());
            return $this;
        } catch (\Exception $e) {
            Log::debug('Failed to load relation: ' . $e->getMessage());
            return $this;
        }
    }

    /**
     * Safely get relation value
     * 
     * @param string $relationName
     * @param mixed $default
     * @return mixed
     */
    public function safeRelation(string $relationName, $default = null)
    {
        try {
            if ($this->relationLoaded($relationName)) {
                return $this->$relationName;
            }
            
            $this->safeLoadMissing($relationName);
            return $this->$relationName ?? $default;
        } catch (\Exception $e) {
            Log::debug("Failed to get relation {$relationName}: " . $e->getMessage());
            return $default;
        }
    }

    /**
     * Create a null relation for missing tables
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    protected function nullRelation()
    {
        return $this->hasOne(self::class, 'id', 'id')->whereRaw('1 = 0');
    }
}
