<?php

namespace App\Http\Requests\KiemKho;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validate body cho bulk-action:
 *  - action: delete | restore | forceDelete | cancel
 *  - ids: mảng ID phiếu (1..200 cái)
 */
class BulkActionKiemKhoRequest extends FormRequest
{
    public const ACTIONS = ['delete', 'restore', 'force_delete', 'cancel'];

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'action' => ['required', 'string', 'in:' . implode(',', self::ACTIONS)],
            'ids' => ['required', 'array', 'min:1', 'max:200'],
            'ids.*' => ['integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'action.in' => 'Hành động không hợp lệ. Chỉ chấp nhận: ' . implode(', ', self::ACTIONS),
            'ids.required' => 'Vui lòng chọn ít nhất 1 phiếu.',
            'ids.max' => 'Không được chọn quá 200 phiếu cùng lúc.',
        ];
    }
}
