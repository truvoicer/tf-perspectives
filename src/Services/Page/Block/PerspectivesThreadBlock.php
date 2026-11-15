<?php

namespace Truvoicer\TfPerspectives\Services\Page\Block;

use Inertia\Inertia;
use Truvoicer\TruFetcherCore\Models\Page;
use Truvoicer\TruFetcherCore\Models\PageColumnBlock;
use Truvoicer\TruFetcherCore\Services\Page\Block\PageBlock;

class PerspectivesThreadBlock extends PageBlock
{
    public function inertiaDependencies(
        PageColumnBlock $block,
        Page $page,
    ): array {

        return [
            'user' => Inertia::optional(
                function () {
                    $user = $this->getUser();

                    return $user ? $this->userResource::make($user) : null;
                }
            ),
        ];
    }

    public function responseData(
        PageColumnBlock $block,
        Page $page,
    ): array {
        $user = $this->getUser();

        return [
            'user' => $user ? $this->userResource::make($user) : null,
        ];
    }

    public function formRequestRules(
        PageColumnBlock $block,
    ): array {
        return [];
    }
}
