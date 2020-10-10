<?php

namespace Watson\Aggregate;

use Illuminate\Database\Eloquent\Model;

/**
 * Loads the Aggregate options in the new Query method.
 * This loads just like withCount
 */
class AggregateModel extends Model
{

    /**
     * The relationship sums that should be eager loaded on every query.
     *
     * @var array
     */
    protected $withSum = [];

    /**
     * The relationship averages that should be eager loaded on every query.
     *
     * @var array
     */
    protected $withAvg = [];

    /**
     * The relationship maximums that should be eager loaded on every query.
     *
     * @var array
     */
    protected $withMax = [];

    /**
     * The relationship minimums that should be eager loaded on every query.
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
        if(method_exists($this, "withAggregate")) {
            $query->withSum($this->withSum)
                ->withAvg($this->withAvg)
                ->withMax($this->withMax)
                ->withMin($this->withMin);
        }

        return $query;
    }

}

?>
