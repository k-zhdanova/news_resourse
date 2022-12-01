<?php

namespace App\Http\Requests;

use Astrotomic\Translatable\Validation\RuleFactory;

class EntryFileRequest extends Request
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

        ]);

        if ($this->isMethod('POST')) {
            return $rules +  RuleFactory::make([
                    'entry_id'      => 'required|exists:entries,id',
                    'files' => 'array|required',
                    'files.*' => 'required|file|max:5000|mimes:png,jpeg,txt,pdf,doc,xls,docx,xlsx'
                ]);
        }
        return [
        ];
    }

}
