<?php

namespace Watson\Aggregate;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Loads the Aggregate options in the new Query method.
 * This loads just like withCount
 */
abstract class AggregateModel extends Model
{

    /**
     * The relationship sums that should be eager loaded on every query.
     * Each entry is an array shaped like so:
     *  [relationship, column]
     *
     * @var array
     */
    protected $withSum = [];

    /**
     * The relationship averages that should be eager loaded on every query.
     * Each entry is an array shaped like so:
     *  [relationship, column]
     *
     * @var array
     */
    protected $withAvg = [];

    /**
     * The relationship maximums that should be eager loaded on every query.
     * Each entry is an array shaped like so:
     *  [relationship, column]
     *
     * @var array
     */
    protected $withMax = [];

    /**
     * The relationship minimums that should be eager loaded on every query.
     * Each entry is an array shaped like so:
     *  [relationship, column]
     *
     * @var array
     */
    protected $withMin = [];

    /**
     * Get a new query builder that doesn't have any global scopes.
     *
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function newQueryWithoutScopes()
    {
        $query = parent::newQueryWithoutScopes();

        // Check that the Aggregate Service Provider is loaded.
        if(Builder::hasGlobalMacro("withAggregate")) {
            foreach($this->withSum as $entry) {
                [$relationship, $column] = is_array($entry) ? $entry : [$entry];
                $query->withSum($relationship, $column);
            }
            foreach($this->withAvg as $entry) {
                [$relationship, $column] = is_array($entry) ? $entry : [$entry];
                $query->withAvg($relationship, $column);
            }
            foreach($this->withMax as $entry) {
                [$relationship, $column] = is_array($entry) ? $entry : [$entry];
                $query->withMax($relationship, $column);
            }
            foreach($this->withMin as $entry) {
                [$relationship, $column] = is_array($entry) ? $entry : [$entry];
                $query->withMin($relationship, $column);
            }
        }

        return $query;
    }

}

?>
