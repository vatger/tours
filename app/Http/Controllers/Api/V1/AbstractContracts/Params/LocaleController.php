<?php

namespace App\Http\Controllers\Api\V1\AbstractContracts\Params;

use App\Models\Locale;
use Illuminate\Http\Request;
use App\Services\QueryService;
use App\Services\TranslatorGoogle;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ApiBaseController;
use App\Http\Resources\Api\V1\Params\LocaleResource;
use App\Http\Requests\Api\V1\Params\StoreLocaleRequest;
use App\Http\Requests\Api\V1\Params\UpdateLocaleRequest;

/**
 * @group API-ADMINISTRATIVE
 *
 * @subgroup Locales - System Params
 * @authenticated
 */
abstract class LocaleController extends ApiBaseController
{
    public function set(Request $request)
    {
        $request->validate([
            'locale' => 'required|in:pt_BR,en,fr,it,de,es'
        ]);

        // Não precisa retornar nada - o middleware SetLocale vai lidar com o header
        return back();
    }

    /**
     * List Locales .
     *
     * @queryParam page int Indique o número da página desejada No-example
     * @queryParam per_page int Indique quantos registros deve conter cada página[default=50] No-example
     * @queryParam filter[id] No-example
     * @queryParam filter[name] No-example
     * @queryParam filter[original_locale] No-example
     * @queryParam filter[Trashed] No-example
     * @queryParam filter[Search] Digite o texto ou parte do texto  que procura na lista No-example
     * @queryParam sort Allowed: id,name,original_locale,created_at Classifica a lista. Para classificação descendente coloque o - na frente(-id) No-example
     *
     */
    public function index()
    {
        $query = QueryService::query(
            Locale::class,
            Locale::ALLOWEDFILTERS(),
            Locale::ALLOWEDINCLUDES(),
            Locale::ALLOWEDSORTS(),
            Locale::DEFAULTSORT()
        )->paginate(request('per_page'))->withQueryString();

        return  LocaleResource::collection($query);
    }

    /**
     * Store a Locale.
     */
    public function store(StoreLocaleRequest $request)
    {
        if (!empty($request->name)) {
            $translated = TranslatorGoogle::translate($request->name);
            $objectToSaveData['name'] = $translated['original_text'];
            $objectToSaveData['display_name'] = $translated['translations'];
            $objectToSaveData['original_locale'] = $translated['source_language'];
        }
        if (!empty($request->alias)) {
            $objectToSaveData['alias'] = $request->alias;
        }

        if (!empty($request->priority)) {
            $objectToSaveData['priority'] = $request->priority;
        }

        DB::beginTransaction();
        try {
            $locale = Locale::create($objectToSaveData);

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
        return  $this->sendResponse(new LocaleResource($locale));
    }

    /**
     * Display the specified locale by ID.
     * @urlParam locale_id int required No-example
     */
    public function show($id)
    {
        $locale = QueryService::findOrFail(Locale::class, 'id', $id);
        return  $this->sendResponse(new LocaleResource($locale));
    }


    /**
     * Update the specified locale in storage.
     * @urlParam locale_id int required No-example
     */
    public function update(UpdateLocaleRequest $request, $id)
    {
        $displayNames = [];
        $objectToSaveData = [];
        $objectToUpdate = QueryService::findOrFail(Locale::class, 'id', $id);
        if (!empty($request->name) && ($request->name != $objectToUpdate->name)) {
            $translated = TranslatorGoogle::translate($request->name);
            $objectToSaveData['name'] = $translated['original_text'];
            $displayNames = $translated['translations'];
            $objectToSaveData['original_locale'] = $translated['source_language'];
        }
        if (!empty($request->alias) && ($request->alias != $objectToUpdate->alias)) {
            $objectToSaveData['alias'] = $request->alias;
        }

        if (!empty($request->priority) && ($request->priority != $objectToUpdate->priority)) {
            $objectToSaveData['priority'] = $request->priority;
        }
        DB::beginTransaction();
        try {
            if ($objectToSaveData) {
                $objectToUpdate->update($objectToSaveData);
            }
            if ($displayNames) {
                $objectToUpdate->setTranslations('display_name', $displayNames);
                $objectToUpdate->save();
            }
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
        return  $this->sendSuccess();
    }

    /**
     * Remove the specified locale from storage.
     * @urlParam locale_id int required No-example
     */
    public function destroy($id)
    {
        $locale = QueryService::findOrFail(Locale::class, 'id', $id);
        $locale->delete();
        return  $this->sendSuccess();
    }
}
