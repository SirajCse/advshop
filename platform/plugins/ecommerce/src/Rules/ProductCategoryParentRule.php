<?php

namespace Botble\Ecommerce\Rules;

use Botble\Ecommerce\Models\ProductCategory;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ProductCategoryParentRule implements ValidationRule
{
    public function __construct(protected ?int $currentId = null)
    {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value) || ! $this->currentId) {
            return;
        }

        $parentId = (int) $value;

        if ($parentId === $this->currentId) {
            $fail(trans('plugins/ecommerce::product-categories.parent_self_reference'));

            return;
        }

        if ($this->isDescendantOf($parentId, $this->currentId)) {
            $fail(trans('plugins/ecommerce::product-categories.parent_circular_reference'));
        }
    }

    protected function isDescendantOf(int $candidateId, int $ancestorId): bool
    {
        $visited = [];
        $queue = [$ancestorId];

        while (! empty($queue)) {
            $childIds = ProductCategory::query()
                ->whereIn('parent_id', $queue)
                ->whereNotIn('id', $visited)
                ->pluck('id')
                ->all();

            if (empty($childIds)) {
                return false;
            }

            if (in_array($candidateId, $childIds, true)) {
                return true;
            }

            $visited = array_merge($visited, $childIds);
            $queue = $childIds;
        }

        return false;
    }
}
