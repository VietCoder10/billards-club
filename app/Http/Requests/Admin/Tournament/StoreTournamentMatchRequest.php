<?php

namespace App\Http\Requests\Admin\Tournament;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTournamentMatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tournamentId = $this->route('tournament');

        return [
            'round_name' => 'required|string|max:255',
            'player1_id' => [
                'required',
                Rule::exists('tournament_participants', 'id')->where('tournament_id', $tournamentId),
            ],
            'player2_id' => [
                'required',
                'different:player1_id',
                Rule::exists('tournament_participants', 'id')->where('tournament_id', $tournamentId),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'round_name.required' => 'Vui lòng nhập tên vòng đấu.',
            'round_name.max' => 'Tên vòng đấu không được vượt quá 255 ký tự.',
            'player1_id.required' => 'Vui lòng chọn tuyển thủ 1.',
            'player1_id.exists' => 'Tuyển thủ 1 không hợp lệ.',
            'player2_id.required' => 'Vui lòng chọn tuyển thủ 2.',
            'player2_id.exists' => 'Tuyển thủ 2 không hợp lệ.',
            'player2_id.different' => 'Tuyển thủ 2 phải khác tuyển thủ 1.',
        ];
    }
}
