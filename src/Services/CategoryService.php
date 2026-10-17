<?php

namespace Truvoicer\TfPerspectives\Services;

use Truvoicer\TfPerspectives\Http\Controllers\Concerns\TransformsPerspectives;
use Truvoicer\TfPerspectives\Models\PerspectiveCategory;

class CategoryService {

    use TransformsPerspectives;

    public static function getAllCategoriesQuery() {
        return PerspectiveCategory::query()
                ->withCount('perspectives')
                ->orderBy('sort_order');
    }

    public static function getAllCategories() {
        return self::getAllCategoriesQuery()
                ->get()
                ->map(fn(PerspectiveCategory $c) => (new self())->transformPerspectiveCategory($c))
                ->all();
    }
}
