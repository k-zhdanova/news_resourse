<?php

namespace App\Http\Requests;

use Astrotomic\Translatable\Validation\RuleFactory;

class EntryRequest extends Request
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
            'service_id' => 'nullable|exists:services,id',
            'status' => 'in:new,active,finished,refused'
        ]);

        if ($this->isMethod('POST')) {
            return $rules + RuleFactory::make([
                'user_id' => 'required|exists:users,id',
                'service_id' => 'required|exists:services,id',
                'file' => 'required|file|mimes:png,jpeg,txt,pdf,doc,xls,docx,xlsx',
                'files' => 'array',
                'files.*' => 'file|max:5000|mimes:png,jpeg,txt,pdf,doc,xls,docx,xlsx' //форматы mimes:png,gif,jpeg,txt,pdf,doc
            ]);
        }
        if ($this->isMethod('PUT')) {
            return $rules + RuleFactory::make([
                'user_id' => 'exists:users,id',
                'service_id' => 'exists:services,id',
            ]);
        }
        return [
            'status' => 'in:new,active,finished,refused'
        ];
    }
}
