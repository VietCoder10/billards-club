<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * バリデーションエラーをJSONで返したい場合は、これを継承してください。
 */
abstract class ApiFormRequest extends FormRequest
{
    /**
     * Handle a failed validation attempt.
     *
     * @param \Illuminate\Contracts\Validation\Validator $validator
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     * @see parent::failedValidation
     */
    protected function failedValidation(Validator $validator): void
    {
        $response = \Response::apiUnprocessableEntity($validator->errors()->toArray());

        throw new HttpResponseException($response);
    }
}
