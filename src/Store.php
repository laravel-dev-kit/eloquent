<?php

namespace LaravelDevKit\Eloquent;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use LaravelDevKit\Helpers\Data;

class Store
{
    /**
     * Undocumented function
     *
     * @param string $method
     * @param mixed ...$parameters
     * @return void
     */
	public function call(string $method, mixed ...$parameters): void
	{
		if (method_exists($this, $method)) {
			$this->{$method}(...$parameters);
		}
	}

    /**
     * Undocumented function
     *
     * @param array $data
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function create(array $data, mixed ...$parameters): Model
    {
        DB::beginTransaction();

        try {
            return tap($this->model->newModelInstance(), function ($instance) use($data, $parameters) {
                $args = array_merge([$instance, new Data($data)], $parameters);

                $this->call('beforeFill', ...array_values($args));

                $instance->fill($data);

                $this->call('afterFill', ...array_values($args));

                $this->call('beforeSave', ...array_values($args));

                $this->call('beforeCreate', ...array_values($args));

                $instance->save();

                $this->call('afterCreate', ...array_values($args));

                $this->call('afterSave', ...array_values($args));

                DB::commit();
            });
        } catch (\Throwable $th) {
            DB::rollback();
            throw $th;
        }
    }

    /**
     * Undocumented function
     *
     * @param array $data
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function update(Model $model, array $data, mixed ...$parameters): Model
    {
        DB::beginTransaction();

        try {
            return tap($model, function ($instance) use($data, $parameters) {
                $args = array_merge([$instance, new Data($data)], $parameters);

                $this->call('beforeFill', ...array_values($args));

                $instance->fill(
                    array_filter($data, static fn($var) => $var !== null)
                );

                $this->call('afterFill', ...array_values($args));

                $this->call('beforeSave', ...array_values($args));

                $this->call('beforeUpdate', ...array_values($args));

                $instance->save();

                $this->call('afterUpdate', ...array_values($args));

                $this->call('afterSave', ...array_values($args));

                DB::commit();
            });
        } catch (\Throwable $th) {
            DB::rollback();
            throw $th;
        }
    }
}
