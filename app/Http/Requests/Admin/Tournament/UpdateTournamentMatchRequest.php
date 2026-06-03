<?php

namespace App\Http\Requests\Admin\Tournament;

use App\Enums\TournamentMatchStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTournamentMatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tournamentId = $this->route('tournament');

        return [
            'player1_score' => 'required|integer|min:0',
            'player2_score' => 'required|integer|min:0',
            'status' => ['required', 'integer', Rule::in(TournamentMatchStatus::getValues())],
            'winner_id' => [
                'nullable',
                Rule::exists('tournament_participants', 'id')->where('tournament_id', $tournamentId),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'player1_score.required' => 'Vui lòng nhập điểm tuyển thủ 1.',
            'player1_score.integer' => 'Điểm tuyển thủ 1 không hợp lệ.',
            'player1_score.min' => 'Điểm tuyển thủ 1 không được nhỏ hơn 0.',
            'player2_score.required' => 'Vui lòng nhập điểm tuyển thủ 2.',
            'player2_score.integer' => 'Điểm tuyển thủ 2 không hợp lệ.',
            'player2_score.min' => 'Điểm tuyển thủ 2 không được nhỏ hơn 0.',
            'status.required' => 'Vui lòng chọn trạng thái trận đấu.',
            'status.in' => 'Trạng thái trận đấu không hợp lệ.',
            'winner_id.exists' => 'Người chiến thắng không hợp lệ.',
        ];
    }
}
