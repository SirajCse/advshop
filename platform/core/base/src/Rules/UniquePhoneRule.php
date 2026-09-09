<?php

namespace Botble\Base\Rules;

use Botble\Base\Models\Concerns\HasPhoneNumber;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Model;

final class UniquePhoneRule implements ValidationRule
{
    protected ?int $ignoreId = null;

    protected string $column = 'phone';

    public function __construct(protected string $modelClass)
    {
    }

    public static function make(string $modelClass): static
    {
        return new static($modelClass);
    }

    public function ignore(?int $id): static
    {
        $this->ignoreId = $id;

        return $this;
    }

    public function column(string $column): static
    {
        $this->column = $column;

        return $this;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value)) {
            return;
        }

        /** @var Model $model */
        $model = new $this->modelClass();

        if (! in_array(HasPhoneNumber::class, class_uses_recursive($model))) {
            $fail(__('Model must use HasPhoneNumber trait.'));

            return;
        }

        $query = $model->newQuery()->wherePhone($value, $this->column);

        if ($this->ignoreId) {
            $query->where($model->getKeyName(), '!=', $this->ignoreId);
        }

        if ($query->exists()) {
            $fail(__('The :attribute has already been taken.'));
        }
    }
}
