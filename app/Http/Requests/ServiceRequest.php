<?php

namespace App\Http\Requests;

use Astrotomic\Translatable\Validation\RuleFactory;

class ServiceRequest extends Request
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
            'code' => 'max:20',
            '%meta_title%' => 'max:255',
            '%meta_description%' => 'max:255',
            'is_online' => 'in:0,1',
            'status' => 'in:0,1'
        ]);

        if ($this->isMethod('POST')) {
            return $rules + RuleFactory::make([
                '%name%' => 'required|max:255'
            ]);
        }
        if ($this->isMethod('PUT')) {
            return $rules + RuleFactory::make([
                '%name%' => 'max:255'
            ]);
        }
        return [
            'status' => 'in:visible,hidden,online',
            'lang' => 'in:uk,en'
        ];
    }
}
