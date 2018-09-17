<?php

namespace Watson\Aggregate;

use Illuminate\Support\Str;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Eloquent\Relations\Relation;

class AggregateServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->registerBuilderMacros();
        $this->registerRelationMacros();
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        //
    }

    /**
     * Register the additional aggregate macros with the query builder.
     *
     * @return void
     */
    protected function registerBuilderMacros()
    {
        Builder::macro('withAggregate', function ($relations, $aggregate, $column) {
            if (empty($relations)) {
                return $this;
            }

            if (is_null($this->query->columns)) {
                $this->query->select([$this->query->from.'.*']);
            }

            $relations = is_array($relations) ? $relations : [$relations];

            foreach ($this->parseWithRelations($relations) as $name => $constraints) {
                $segments = explode(' ', $name);

                if (count($segments) == 3 && Str::lower($segments[1]) == 'as') {
                    list($name, $alias) = [$segments[0], $segments[2]];
                }

                $relation = $this->getRelationWithoutConstraints($name);

                $query = $relation->getRelationExistenceAggregatesQuery(
                    $relation->getRelated()->newQuery(), $this, $aggregate, $column
                );

                $query->callScope($constraints);

                $query = $query->mergeConstraintsFrom($relation->getQuery())->toBase();

                if (count($query->columns) > 1) {
                    $query->columns = [$query->columns[0]];
                }

                $columnAlias = $alias ?? Str::snake($name.'_'.strtolower($aggregate));

                $this->selectSub($query, $columnAlias);
            }

            return $this;
        });

        Builder::macro('withSum', function ($relation, $column) {
            return $this->withAggregate($relation, 'sum', $column);
        });

        Builder::macro('withAvg', function ($relation, $column) {
            return $this->withAggregate($relation, 'avg', $column);
        });

        Builder::macro('withMax', function ($relation, $column) {
            return $this->withAggregate($relation, 'max', $column);
        });

        Builder::macro('withMin', function ($relation, $column) {
            return $this->withAggregate($relation, 'min', $column);
        });
    }

    /**
     * Register the additional macros with the relation class.
     *
     * @return void
     */
    protected function registerRelationMacros()
    {
        Relation::macro('getRelationExistenceAggregatesQuery', function (Builder $query, Builder $parentQuery, $aggregate, $column) {
            return $this->getRelationExistenceQuery(
                $query, $parentQuery, new Expression($aggregate."({$column})")
            )->setBindings([], 'select');
        });
    }
}
