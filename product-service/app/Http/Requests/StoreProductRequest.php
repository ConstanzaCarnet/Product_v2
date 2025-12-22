<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreProductRequest extends FormRequest
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
     * Based on specification section 2.2 (ProductCreate DTO) and 3.1 (Field Validations):
     * - name: required, min 3, max 255 characters
     * - description: optional, max 1000 characters
     * - price: required, > 0, max 999999.99, 2 decimals
     * - stock: required, >= 0
     * 
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'price' => ['required', 'numeric', 'gt:0', 'max:999999.99', 'regex:/^\d+(\.\d{1,2})?$/'],
            'stock' => ['required', 'integer', 'min:0'],
            // Prevent modification of auto-generated fields
            'id' => ['prohibited'],
            'created_at' => ['prohibited'],
            'updated_at' => ['prohibited'],
            'active' => ['prohibited'], // Set to true by default, not in request
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
            'name.required' => 'The name is required',
            'name.min' => 'The name must have at least 3 characters',
            'name.max' => 'The name cannot exceed 255 characters',
            'description.max' => 'The description cannot exceed 1000 characters',
            'price.required' => 'The price is required',
            'price.gt' => 'The price must be greater than 0',
            'price.max' => 'The price cannot exceed 999999.99',
            'price.regex' => 'The price must have at most 2 decimal places',
            'stock.required' => 'The stock is required',
            'stock.min' => 'The stock cannot be negative',
            'id.prohibited' => 'The id field cannot be set manually',
            'created_at.prohibited' => 'The created_at field cannot be set manually',
            'updated_at.prohibited' => 'The updated_at field cannot be set manually',
            'active.prohibited' => 'The active field cannot be set on creation (defaults to true)',
        ];
    }

    /**
     * Handle a failed validation attempt.
     * 
     * Decision: Return 422 status with specification format (section 5.2)
     */
    protected function failedValidation(Validator $validator)
    {
        // Group errors by field for cleaner output per specification
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
