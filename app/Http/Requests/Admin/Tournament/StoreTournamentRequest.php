<?php

namespace App\Http\Requests\Admin\Tournament;

use Illuminate\Foundation\Http\FormRequest;

class StoreTournamentRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'registration_deadline' => 'required|date|before_or_equal:start_date',
            'max_participants' => 'nullable|integer|min:0',
            'entry_fee' => 'nullable|numeric|min:0',
            'prize_pool' => 'nullable|string|max:255',
            'status' => 'required|integer|in:0,1,2,3,4',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Tên giải đấu là bắt buộc.',
            'start_date.required' => 'Ngày bắt đầu là bắt buộc.',
            'end_date.required' => 'Ngày kết thúc là bắt buộc.',
            'end_date.after_or_equal' => 'Ngày kết thúc phải sau hoặc bằng ngày bắt đầu.',
            'registration_deadline.required' => 'Hạn đăng ký là bắt buộc.',
            'registration_deadline.before_or_equal' => 'Hạn đăng ký phải trước hoặc bằng ngày bắt đầu.',
        ];
    }
}
