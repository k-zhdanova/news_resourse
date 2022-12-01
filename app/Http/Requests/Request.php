<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Schema;

class Request extends FormRequest
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
        return [];
    }

    public function makeOrderBy($query, $table, $translation_table = null)
    {
        $order_by = $this->input('sort');

        $column    = 'id';
        $direction = 'asc';

        if ($order_by) {
            $order_by = explode('|', $order_by);

            if (!empty($order_by[1]) && in_array($order_by[1], ['asc', 'desc'])) {
                $direction = $order_by[1];
            }

            $columns = Schema::getColumnListing($table);

            if (in_array($order_by[0], $columns)) {
                $column = $order_by[0];
            } else if ($translation_table) {
                $translations = Schema::getColumnListing($translation_table);
                if (in_array($order_by[0], $translations)) {
                    $column = $order_by[0];
                    return $query->orderByTranslation($column, $direction);
                }
            }
        }

        return $query->orderBy($column, $direction);
    }
}
