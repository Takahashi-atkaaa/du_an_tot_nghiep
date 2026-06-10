<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

abstract class BaseModel extends Model
{
    public static function tableName(): string
    {
        return (new static())->getTable();
    }

    public static function defaultPerPage(): int
    {
        return 15;
    }

    public function scopeSearchByFields(Builder $query, ?string $term, array $columns): Builder
    {
        if (blank($term) || empty($columns)) {
            return $query;
        }

        $term = trim($term);

        return $query->where(function (Builder $q) use ($columns, $term) {
            foreach ($columns as $column) {
                $q->orWhere($column, 'like', "%{$term}%");
            }
        });
    }

    public function scopeOrdered(Builder $query, string $column = 'created_at', string $direction = 'desc'): Builder
    {
        return $query->orderBy($column, $direction);
    }

    public static function readableName(): string
    {
        return str_replace('_', ' ', strtolower(class_basename(static::class)));
    }
}
