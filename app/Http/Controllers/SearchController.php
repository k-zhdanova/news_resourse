<?php

namespace App\Http\Controllers;

use App\Helpers\PaginationHelper;
use App\Http\Requests\SearchRequest;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * @group Search
 *
 * API для глобального поиска.
 */
class SearchController extends Controller
{
    // порядок моделей определяет сортировку результатов поиска
    public const MODELS = [
        'App\Models\ServiceTranslation' => 'service',
        'App\Models\NewsTranslation'    => 'news',
        'App\Models\PageTranslation'    => 'page',
        'App\Models\LinkTranslation'    => 'link',
        'App\Models\LawTranslation'     => 'law',
    ];

    private const TAGABLE_MODELS = [
        'App\Models\ServiceTranslation'
    ];

    /**
     * @param SearchRequest $request
     * @queryParam q string строка поиска
     * @queryParam lang string optional На каком языке возвращать результаты (возможные значения uk, en)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(SearchRequest $request)
    {
        $search_string      = $request->get('q');
        $lang               = $request->get('lang');
        $searchableData     = new Collection();
        $model_sort_index   = 0;

        foreach(self::MODELS as $model => $name) {
            $model_sort_index++;
            $fields = (new $model())->searchable;

            $query = $model::search($search_string);
            $query->when($lang, function ($query) use ($lang) {
                $query->where('locale', $lang);
            });

            $data = $query->get();

            foreach($data as $item) {
                $id = "{$name}_id";

                if (!isset($item->$id)) {
                    continue;
                }

                $parent = $item->parent;


                if (
                    is_null($parent)                                                        // если parent model soft-deleted или не определена
                    || is_null($parent->published_at)                                       // или если запись не опубликована
                    || strtotime($parent->published_at) > Carbon::now()->getTimestamp()) {  // дата публикации - в будущем
                    continue;
                }

                $parsedData                 = $item->only($fields);
                $parsedData['model']        = $name;
                $parsedData['model_sort']   = $model_sort_index;
                $parsedData['locale']       = $item->locale;
                $parsedData['id']           = $item->$id;
                $parsedData['uri']          = $parent->uri ?? $parent->link ?? '';
                $parsedData['tags']         = [];
                $parsedData['updated_at']   = $parent->updated_at;

                if (in_array($model, self::TAGABLE_MODELS)) {
                    $parsedData['tags'] = $parent->getTags($item->locale);
                }

                $searchableData->push($parsedData);
            }
        }

        // результаты сортируем в соответствии с порядком моделей в self::MODELS, а внутри каждой модели - по убыванию даты обновления
        $searchableData = $searchableData->sortBy([
            ['model_sort', 'asc'],
            ['updated_at', 'desc'],
        ]);

        return response()->json(PaginationHelper::paginate($searchableData, config('custom.search_limit_per_page')));
    }
}
