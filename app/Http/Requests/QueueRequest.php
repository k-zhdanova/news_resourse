<?php

namespace App\Http\Requests;

use Astrotomic\Translatable\Validation\RuleFactory;

class QueueRequest extends Request
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
            'name' => 'max:255',
        ]);

        return [];
    }
}
