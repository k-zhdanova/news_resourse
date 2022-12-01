<?php

namespace App\Http\Controllers;

use App\Http\Requests\EntryFileRequest;
use App\Models\EntryFile;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Storage;


class EntryFileController extends Controller
{
    /**
     * @group file
     *
     * API для удаления файлов заявок
     */

    /**
     * Create  file
     *
     * @param EntryFileRequest $request
     * @return Response
     * @bodyParam entry_id required ID заявки
     * @bodyParam files [] required Документы к заявке
     */
    public function create(EntryFileRequest $request)
    {
        $entryFile = new EntryFile();

        $data = $entryFile->saveFile($request, $request->entry_id);

        try {
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Not found.'
            ], 404);
        }

        return response()->json($data);
    }
}
