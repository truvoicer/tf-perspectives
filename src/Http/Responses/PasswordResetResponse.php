<?php
// packages/truvoicer/tf-perspectives/src/Http/Responses/PasswordResetResponse.php

namespace Truvoicer\TfPerspectives\Http\Responses;

use Truvoicer\TruFetcherCore\Fortify\FortifyBindings;
use Truvoicer\TruFetcherCore\Http\Responses\PasswordResetResponse as ResponsesPasswordResetResponse;

class PasswordResetResponse extends ResponsesPasswordResetResponse
{
    public function __construct(protected ?string $status = null) {}

    public function toResponse($request)
    {
        // return $request->wantsJson()
        //     ? response()->json(['message' => trans($this->status ?? 'passwords.reset')])
        //     : redirect()->route('perspectives.login')->with('status', trans($this->status ?? 'passwords.reset'));

        return FortifyBindings::resolveResponseByRequestContext(self::responseMapping($request), $request);
    }

    public static function responseMapping($request)
    {
        return [
            ...parent::responseMapping($request),
            'perspectives' => function ($request) {
                if ($request->wantsJson() && ! $request->header('X-Inertia')) {
                    return response()->json(['message' => trans($this->status ?? 'passwords.reset')]);
                }

                return redirect()->route('perspectives.login')->with('status', trans($this->status ?? 'passwords.reset'));
            },
        ];
    }
}
