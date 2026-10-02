<?php

namespace Truvoicer\TfPerspectives\Traits\Request;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Truvoicer\TfPerspectives\Helpers\Block\BlockEnumHelper;
use Truvoicer\TfPerspectives\Models\Page;
use Truvoicer\TfPerspectives\Models\PageColumnBlock;

trait ValidatesFormRequests
{
    /**
     * Manually validate a request using a FormRequest class
     *
     * @param  Request  $request  The current request instance
     * @param  string  $formRequestClass  The FormRequest class to use for validation
     * @param  bool  $includeQueryParams  Whether to include query parameters in validation data
     * @return FormRequest The validated FormRequest instance
     */
    protected function validateWithFormRequest(
        Request $request,
        string $formRequestClass,
        bool $includeQueryParams = false
    ): FormRequest {

        // Create instance of the FormRequest
        $formRequest = app($formRequestClass);

        // Prepare request data - include query params if requested
        $requestData = $request->request->all();
        if ($includeQueryParams) {
            $requestData = array_merge($requestData, $request->query());
        }

        // Initialize the FormRequest with the modified request data
        /** @var FormRequest $formRequest */
        $formRequest->setContainer(app())
            ->setRedirector(app('redirect'))
            ->initialize(
                $request->query(),
                $requestData, // Use modified request data
                $request->attributes->all(),
                $request->cookies->all(),
                $request->files->all(),
                $request->server->all(),
                $request->getContent()
            );

        // Validate
        $formRequest->validated();

        return $formRequest;
    }

    public function columnBlockValidation(
        PageColumnBlock $block,
        string $pageBlockFactory,
        Request $request,
        ?bool $throwException = false
    ): bool|array {
        try {
            $blockEnum = BlockEnumHelper::tryFrom($block->block);
            if (! $blockEnum) {
                throw new \Exception("Invalid block enum: {$block->block}");
            }
            $blockInstance = $pageBlockFactory::make($blockEnum->value);

            // Get rules for this block
            $blockRules = $blockInstance->formRequestRules($block);

            $validator = Validator::make(
                array_merge(
                    $request->query->all(),
                    $request->all(),
                ),
                $blockRules
            );

            $validator->validate();

            return [
                'enum' => $blockEnum,
                'rules' => $blockRules,
                'validator' => $validator,
            ];
        } catch (\Exception $e) {
            if ($throwException) {
                throw $e;
            } else {
                return false;
            }
        }
    }

    public function pageBlockValidation(
        Page $page,
        string $pageBlockFactory,
        Request $request,
    ): array {
        // Get blocks from page
        $rows = $page->rows ?? [];
        $data = [];
        foreach ($rows as $row) {
            $columns = $row->columns ?? [];
            foreach ($columns as $column) {
                $columnBlocks = $column->blocks ?? [];

                foreach ($columnBlocks as $block) {
                    $validate = $this->columnBlockValidation(
                        $block,
                        $pageBlockFactory,
                        $request
                    );
                    if ($validate) {
                        $data[] = $validate;
                    }
                }
            }
        }

        return $data;
    }
}
