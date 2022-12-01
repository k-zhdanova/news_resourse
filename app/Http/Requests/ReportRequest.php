<?php

namespace App\Http\Requests;

use Astrotomic\Translatable\Validation\RuleFactory;
use App\Http\Requests\Request;

class ReportRequest extends Request
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
        $rules = RuleFactory::make([
            'status' => 'in:0,1'
        ]);

        if ($this->isMethod('POST') || $this->isMethod('PUT')) {
            return $rules + RuleFactory::make([
                'file' => 'nullable|regex:(data:application/pdf;base64,)',
            ]);
        }

        return [
            'status' => 'in:visible,hidden'
        ];
    }
}
