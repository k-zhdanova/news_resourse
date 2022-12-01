<?php

namespace App\Http\Requests;

use Astrotomic\Translatable\Validation\RuleFactory;

class FeedBackRequest extends Request
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
            'email' => 'nullable|email',
            'status' => 'nullable|in:1,2,3,'
        ]);

        if ($this->isMethod('POST')) {
            return $rules + RuleFactory::make([
                'email' => 'nullable|max:255',
                'text' => 'nullable|max:255',
                'date' => 'required',
                'age' => 'required|int',
                'sex' => 'required|in:male,female',
                'is_satisfied' => 'required|in:1,2',
                'reception_friendly' => 'required|in:1,2,3,4,5',
                'reception_competent' => 'required|in:1,2,3,4,5',
                'center_friendly' => 'required|in:1,2,3,4,5',
                'center_competent' => 'required|in:1,2,3,4,5',
                'impression' => 'required|in:1,2,3,4,5',
                'website' => 'required|in:1,2,3,4,5',
            ]);
        }
        if ($this->isMethod('PUT')) {
            return $rules + RuleFactory::make([
                'status' => 'required|in:1,2,3',
                'updated_by' => 'nullable|exists:users,id',
            ]);
        }
        return [
            'status' => 'in:1,2,3'
        ];
    }
}
