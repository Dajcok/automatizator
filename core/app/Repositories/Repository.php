<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * @template T of Model
 * @implements RepositoryContract<T>
 */
abstract class Repository
{
    protected Model $model;

    public function __construct($model)
    {
        $this->model = $model;
    }

    public function all($paginated = true): Collection | LengthAwarePaginator
    {
        if (!$paginated) {
            return $this->model->all();
        }

        return $this->model->paginate();
    }

    public function query(array $query)
    {
        return $this->model->where($query)->get();
    }

    public function find(int $id): Model
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): Model
    {
        $model = $this->model->findOrFail($id);
        $model->update($data);
        return $model->refresh();
    }

    public function delete(int $id): int
    {
        return $this->model->destroy($id);
    }

    public function deleteWhere(array $query): int
    {
        return $this->model->where($query)->delete();
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    public function paginate(Builder $model, int $perPage = null): LengthAwarePaginator
    {
        return $model->paginate($perPage);
    }

    public function exists($query)
    {
        return $this->model->where($query)->exists();
    }
}
