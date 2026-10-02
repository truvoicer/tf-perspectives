<?php

namespace Truvoicer\TfPerspectives\Traits\Request;

trait PartialRequest
{
    public function getRules(?array $overrides = [], ?string $prepend = null): array
    {
        $rules = $this->rules();
        if ($prepend) {
            // Prepend all rule keys with the given string
            foreach ($rules as $key => $value) {
                $rules[$prepend.$key] = $value;
                unset($rules[$key]);
            }
        }

        return array_merge($rules, $overrides ?? []);
    }
}
