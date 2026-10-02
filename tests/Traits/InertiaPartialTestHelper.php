<?php

// tests/Traits/InertiaPartialTestHelper.php

namespace Truvoicer\TfPerspectives\Tests\Traits;

trait InertiaPartialTestHelper
{
    /**
     * Make a partial Inertia request
     */
    protected function partialGet($uri, array $props = [], array $headers = [])
    {
        $headers = array_merge([
            'X-Inertia' => 'true',
            'X-Inertia-Partial-Component' => $this->getCurrentComponent($uri),
            'X-Inertia-Partial-Data' => implode(',', $props),
        ], $headers);

        return $this->withHeaders($headers)->get($uri);
    }

    /**
     * Get the component name from the route (simplified)
     */
    protected function getCurrentComponent($uri)
    {
        // You can implement logic to determine the component name
        // based on the route URI
        if (str_contains($uri, 'report')) {
            return 'admin/comment/report/index';
        }

        return 'admin/comment/index';
    }
}
