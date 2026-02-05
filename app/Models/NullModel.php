<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * NullModel - Placeholder for missing package relations
 * 
 * Returns empty results when a package is not installed
 */
class NullModel extends Model
{
    protected $table = 'null_table_that_does_not_exist';
    
    /**
     * Override newQuery to return empty collection
     */
    public function newQuery()
    {
        return new \Illuminate\Database\Eloquent\Builder(
            new \Illuminate\Database\Query\Builder(
                app('db')->connection(),
                app('db')->connection()->getQueryGrammar(),
                app('db')->connection()->getPostProcessor()
            )
        );
    }
}
