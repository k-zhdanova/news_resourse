<?php

namespace App\Http\Requests;

use Astrotomic\Translatable\Validation\RuleFactory;

class LawRequest extends Request
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
            'status' => 'in:0,1',
            'follow' => 'in:0,1',
        ]);

        if ($this->isMethod('POST')) {
            return $rules + RuleFactory::make([
                '%name%' => 'required|max:255',
                'category_id' => 'required|string',
                'link' => 'required|url'
            ]);
        }
        if ($this->isMethod('PUT')) {
            return $rules + RuleFactory::make([
                '%name%' => 'max:255',
                'category_id' => 'string',
                'link' => 'url'
            ]);
        }
        return [
            'status' => 'in:visible,hidden',
            'lang' => 'in:uk,en'
        ];
    }
}
