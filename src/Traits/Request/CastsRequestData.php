<?php

namespace Truvoicer\TfPerspectives\Traits\Request;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

/**
 * @mixin FormRequest
 *
 * @method array all()
 * @method array rules()
 * @method void merge(array $data)
 */
trait CastsRequestData
{
    /**
     * Prepare the data for validation by casting values based on validation rules
     */
    protected function prepareForValidation(): void
    {
        $currentData = $this->all();
        $rules = $this->rules();

        if (empty($rules)) {
            return;
        }

        $processedData = $currentData;

        foreach ($rules as $key => $ruleSet) {
            $rulesArray = $this->parseRuleSet($ruleSet);

            // Handle wildcard fields (like testimonials.*.rating)
            if (Str::contains($key, '*')) {
                $this->processWildcardField($key, $rulesArray, $currentData, $processedData);

                continue;
            }

            // Handle regular fields
            $value = data_get($currentData, $key);

            if ($value !== null) {
                $castedValue = $this->castValueByRules($value, $rulesArray);
                data_set($processedData, $key, $castedValue);
            }
        }

        $this->merge($processedData);
    }

    /**
     * Parse rule set into array of rules
     */
    private function parseRuleSet($ruleSet): array
    {
        if (is_string($ruleSet)) {
            return explode('|', $ruleSet);
        }

        if (is_array($ruleSet)) {
            return $ruleSet;
        }

        return [];
    }

    /**
     * Process wildcard fields in the rules
     */
    private function processWildcardField(string $pattern, array $rules, array $currentData, array &$processedData): void
    {
        // Parse the pattern: e.g., "testimonials.*.rating"
        $patternParts = explode('.*', $pattern);
        $baseKey = $patternParts[0];
        $remainingPattern = count($patternParts) > 1 ? ltrim($patternParts[1], '.') : '';

        $arrayData = data_get($currentData, $baseKey);

        if (! is_array($arrayData)) {
            return;
        }

        $this->processNestedArray(
            $arrayData,
            $remainingPattern,
            $rules,
            $processedData,
            $baseKey
        );
    }

    /**
     * Recursively process nested arrays and cast values based on rules
     */
    private function processNestedArray(array &$data, string $pathPattern, array $rules, array &$processedData, string $currentPath): void
    {
        if (empty($pathPattern)) {
            // No more path parts, process current level
            foreach ($data as $index => &$item) {
                if (is_array($item)) {
                    $this->castArrayValues($item, $rules, $processedData, "{$currentPath}.{$index}");
                } else {
                    $castedValue = $this->castValueByRules($item, $rules);
                    data_set($processedData, "{$currentPath}.{$index}", $castedValue);
                }
            }

            return;
        }

        // Split the path pattern for the next level
        $patternParts = explode('.*', $pathPattern);
        $nextKey = $patternParts[0];
        $remainingPath = count($patternParts) > 1 ? ltrim($patternParts[1], '.') : '';

        foreach ($data as $index => &$item) {
            if (is_array($item) && array_key_exists($nextKey, $item)) {
                $itemPath = "{$currentPath}.{$index}.{$nextKey}";

                if (is_array($item[$nextKey])) {
                    if (! empty($remainingPath)) {
                        // Continue recursing
                        $this->processNestedArray($item[$nextKey], $remainingPath, $rules, $processedData, $itemPath);
                    } else {
                        // Cast values at this level
                        $this->castArrayValues($item[$nextKey], $rules, $processedData, $itemPath);
                    }
                } else {
                    // Cast single value
                    $castedValue = $this->castValueByRules($item[$nextKey], $rules);
                    data_set($processedData, $itemPath, $castedValue);
                }
            }
        }
    }

    /**
     * Cast all values in an array based on rules
     */
    private function castArrayValues(array &$array, array $rules, array &$processedData, string $path): void
    {
        foreach ($array as $key => $value) {
            $currentValuePath = "{$path}.{$key}";

            if (is_array($value)) {
                // Recursively cast nested arrays
                $this->castArrayValues($value, $rules, $processedData, $currentValuePath);
            } else {
                // Cast individual value
                $castedValue = $this->castValueByRules($value, $rules);
                data_set($processedData, $currentValuePath, $castedValue);
            }
        }
    }

    /**
     * Cast a value based on validation rules
     */
    private function castValueByRules(mixed $value, array $rules): mixed
    {
        // Handle null/empty
        if ($value === null || $value === '') {
            return $value;
        }

        // Special handling for checkbox values from multipart forms
        if ($value === 'on' && $this->hasRule($rules, ['boolean', 'bool'])) {
            return true;
        }

        // Handle array case (from multipart form data)
        if (is_array($value)) {
            // If it's an array but should be integer, take first non-empty value
            if ($this->hasRule($rules, ['integer', 'int'])) {
                foreach ($value as $v) {
                    if ($v !== null && $v !== '') {
                        return (int) $v;
                    }
                }

                return 0;
            }

            // If it's an array but should be boolean
            if ($this->hasRule($rules, ['boolean', 'bool'])) {
                return ! empty($value);
            }

            // If it's an array but should be string, implode
            if ($this->hasRule($rules, ['string'])) {
                return implode(',', $value);
            }

            // If it's an array but should be numeric
            if ($this->hasRule($rules, ['numeric'])) {
                foreach ($value as $v) {
                    if (is_numeric($v) && str_contains($v, '.')) {
                        return (float) $v;
                    } elseif (is_numeric($v)) {
                        return (int) $v;
                    }
                }

                return 0;
            }

            // Otherwise return as is
            return $value;
        }

        // Cast based on rule types
        if ($this->hasRule($rules, ['boolean', 'bool'])) {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN);
        }

        if ($this->hasRule($rules, ['integer', 'int'])) {
            return (int) $value;
        }

        if ($this->hasRule($rules, ['numeric'])) {
            if (is_numeric($value) && str_contains($value, '.')) {
                return (float) $value;
            } elseif (is_numeric($value)) {
                return (int) $value;
            }

            return $value;
        }

        if ($this->hasRule($rules, ['string'])) {
            return (string) $value;
        }

        if ($this->hasRule($rules, ['array'])) {
            return [$value];
        }

        // Default: return as is
        return $value;
    }

    /**
     * Check if rules contain any of the given rule names
     */
    private function hasRule(array $rules, array $ruleNames): bool
    {
        foreach ($ruleNames as $ruleName) {
            if (in_array($ruleName, $rules)) {
                return true;
            }
        }

        return false;
    }
}
