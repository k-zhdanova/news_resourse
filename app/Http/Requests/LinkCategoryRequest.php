<?php

namespace App\Http\Requests;

use Astrotomic\Translatable\Validation\RuleFactory;

class LinkCategoryRequest extends Request
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
            'status'             => 'in:0,1'
        ]);

        if ($this->isMethod('POST')) {
            return $rules +  RuleFactory::make([
                '%name%'         => 'required|max:255',
                'parent_id' => 'nullable|string'
            ]);
        }
        if ($this->isMethod('PUT')) {
            return $rules +  RuleFactory::make([
                '%name%'         => 'max:255',
                'parent_id' => 'nullable|string'
            ]);
        }
        return [
            'status' => 'in:visible,hidden',
            'lang'   => 'in:uk,en'
        ];
    }
}
