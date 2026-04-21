<?php

namespace App\Http\Requests\Admin\Dashboard;

use Illuminate\Foundation\Http\FormRequest;

class EventRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'color' => 'required|string|max:255',
            'event_type' => 'nullable|integer',
            'note' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Tên công việc là trường bắt buộc',
            'name.max' => 'Tên công việc không được vượt quá 255 ký tự',
            'start_date.required' => 'Ngày bắt đầu là trường bắt buộc',
            'start_date.date' => 'Ngày bắt đầu không đúng định dạng',
            'end_date.required' => 'Ngày kết thúc là trường bắt buộc',
            'end_date.date' => 'Ngày kết thúc không đúng định dạng',
            'end_date.after_or_equal' => 'Ngày kết thúc phải sau hoặc bằng ngày bắt đầu',
            'color.required' => 'Màu sắc là trường bắt buộc',
            'color.max' => 'Màu sắc không được vượt quá 255 ký tự',
            'note.max' => 'Ghi chú không được vượt quá 255 ký tự',
        ];
    }
}
