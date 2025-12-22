<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * 
     * Decision: Public endpoint per specification (no authentication required)
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     * 
     * Based on specification section 2.3 (ProductUpdate DTO) and 3.1:
     * - All fields are optional (partial update)
     * - If sent, must follow same validation rules as creation
     * - Cannot update id, created_at, updated_at
     * 
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'min:3', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'price' => ['sometimes', 'numeric', 'gt:0', 'max:999999.99', 'regex:/^\d+(\.\d{1,2})?$/'],
            'stock' => ['sometimes', 'integer', 'min:0'],
            'active' => ['sometimes', 'boolean'],
            // Prevent modification of auto-generated fields
            'id' => ['prohibited'],
            'created_at' => ['prohibited'],
            'updated_at' => ['prohibited'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     * 
     * Decision: Match specification error messages exactly (section 3.1)
     */
    public function messages(): array
    {
        return [
            'name.min' => 'The name must have at least 3 characters',
            'name.max' => 'The name cannot exceed 255 characters',
            'description.max' => 'The description cannot exceed 1000 characters',
            'price.gt' => 'The price must be greater than 0',
            'price.max' => 'The price cannot exceed 999999.99',
            'price.regex' => 'The price must have at most 2 decimal places',
            'stock.min' => 'The stock cannot be negative',
            'id.prohibited' => 'The id field cannot be updated',
            'created_at.prohibited' => 'The created_at field cannot be updated',
            'updated_at.prohibited' => 'The updated_at field is automatically updated',
        ];
    }

    /**
     * Handle a failed validation attempt.
     * 
     * Decision: Return 422 status with specification format (section 5.2)
     */
    protected function failedValidation(Validator $validator)
    {
        $groupedErrors = [];
        foreach ($validator->errors()->keys() as $field) {
            $groupedErrors[] = [
                'field' => $field,
                'message' => $validator->errors()->first($field),
            ];
        }

        throw new HttpResponseException(
            response()->json([
                'error' => 'Validation Error',
                'message' => count($groupedErrors) > 1 ? 'Validation errors' : $groupedErrors[0]['message'],
                'details' => $groupedErrors,
            ], 422)
        );
    }
}
