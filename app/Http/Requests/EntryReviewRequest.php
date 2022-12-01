<?php

namespace App\Http\Requests;

use Astrotomic\Translatable\Validation\RuleFactory;

class EntryReviewRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        // custom create and update rules
        $rules = RuleFactory::make([
            'user_id' => 'nullable|exists:users,id',
            'entry_id' => 'nullable|exists:entries,id',
            'status' => 'in:0,1'
        ]);

        if ($this->isMethod('POST')) {
            return $rules + RuleFactory::make([
                'user_id' => 'exists:users,id',
                'entry_id' => 'exists:entries,id',
                'text' => 'max:255'
            ]);
        }
        if ($this->isMethod('PUT')) {
            return $rules + RuleFactory::make([
                'user_id' => 'exists:users,id',
                'entry_id' => 'exists:entries,id',
                'text' => 'max:255'
            ]);
        }
        return [
            'status' => 'in:visible,hidden',
        ];
    }
}
