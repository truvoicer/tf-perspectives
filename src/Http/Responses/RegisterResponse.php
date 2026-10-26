<?php
// packages/truvoicer/tf-perspectives/src/Http/Responses/RegisterResponse.php

namespace Truvoicer\TfPerspectives\Http\Responses;

use Truvoicer\TruFetcherCore\Fortify\FortifyBindings;
use Truvoicer\TruFetcherCore\Http\Responses\RegisterResponse as ResponsesRegisterResponse;

class RegisterResponse extends ResponsesRegisterResponse
{
    public function toResponse($request)
    {
        // return $request->wantsJson()
        //     ? response()->json(['user' => $request->user()], 201)
        //     : redirect()->intended(route('perspectives.index'));
        return FortifyBindings::resolveResponseByRequestContext(self::responseMapping($request), $request);
    }
    public static function responseMapping($request)
    {
        return [
            ...parent::responseMapping($request),
            'perspectives' => function ($request) {
                if ($request->wantsJson() && ! $request->header('X-Inertia')) {
                    return response()->json(['message' => 'Registered successfully.']);
                }

                return redirect()->intended(route('perspectives.index'));
            },
        ];
    }
}
