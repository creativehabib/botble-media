<?php

namespace Botble\Support\Repositories\Eloquent;

use Botble\Support\Repositories\Interfaces\RepositoryInterface;
use Closure;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

abstract class RepositoriesAbstract implements RepositoryInterface
{
    protected Model $modelInstance;

    protected EloquentBuilder $model;

    protected EloquentBuilder $originalModel;

    public function __construct(Model $model)
    {
        $this->modelInstance = $model;
        $this->model = $model->newQuery();
        $this->originalModel = $model->newQuery();
    }

    protected function applyConditions(array $conditions, Builder|EloquentBuilder|null $query = null): void
    {
        $query = $query ?: $this->model;

        foreach ($conditions as $field => $value) {
            if ($value instanceof Closure) {
                $query->where($value);

                continue;
            }

            if (is_numeric($field)) {
                $query->where($value);

                continue;
            }

            if (is_array($value)) {
                if (count($value) === 2) {
                    $query->where($field, $value[0], $value[1]);
                } else {
                    $query->whereIn($field, $value);
                }

                continue;
            }

            $query->where($field, $value);
        }

        if ($query === $this->model) {
            $this->model = $query;
        }
    }

    protected function resetModel(): void
    {
        $this->model = $this->modelInstance->newQuery();
    }
}
