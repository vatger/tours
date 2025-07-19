<?php

namespace App\Http\Controllers\Api\V1\AbstractContracts\Gender;

use Inertia\Inertia;
use App\Models\Gender;
use App\Traits\ApiResponse;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Services\QueryService;
use App\Services\StatusService;
use App\Services\UploadService;


use App\Services\TranslatorGoogle;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\Api\V1\Gender\GenderResource;
use App\Http\Requests\Api\V1\Gender\StoreGenderRequest;
use App\Http\Requests\Api\V1\Gender\UpdateGenderRequest;
use App\Http\Requests\Api\V1\Gender\ChangeGenderIconRequest;
use App\Http\Requests\Api\V1\Gender\ChangeGenderIsActiveRequest;
use Ramsey\Uuid\Type\Integer;

/**
 * @version V1
 *
 * @group API-ADMINISTRATIVE
 *
 * @subgroup Gender
 * @authenticated
 */
abstract class GenderController extends Controller
{
    use ApiResponse;
    /**
     * list Gender.
     * @queryParam page int Indique o número da página desejada No-example
     * @queryParam per_page int Indique quantos registros deve conter cada página[default=50] No-example
     * @queryParam include Allowed: activeMotives,activities Relacionamentos que podem ser incluídos na resposta No-example
     * @queryParam filter[id] No-example
     * @queryParam filter[name] No-example
     * @queryParam filter[CreatedAt] No-example
     * @queryParam filter[CreatedAtAfter] No-example
     * @queryParam filter[CreatedAtBefore] No-example
     * @queryParam filter[CreatedAtBetween] No-example
     * @queryParam filter[CreatedAtCurrentDay] No-example
     * @queryParam filter[CreatedAtCurrentWeek] No-example
     * @queryParam filter[CreatedAtCurrentMonth] No-example
     * @queryParam filter[CreatedAtLastDays] No-example
     * @queryParam filter[CreatedAtNextDays] No-example
     * @queryParam filter[UpdatedAt] No-example
     * @queryParam filter[UpdatedAtAfter] No-example
     * @queryParam filter[UpdatedAtBefore] No-example
     * @queryParam filter[UpdatedAtBetween] No-example
     * @queryParam filter[UpdatedAtCurrentDay] No-example
     * @queryParam filter[UpdatedAtCurrentWeek] No-example
     * @queryParam filter[UpdatedAtCurrentMonth] No-example
     * @queryParam filter[UpdatedAtLastDays] No-example
     * @queryParam filter[UpdatedAtNextDays] No-example
     * @queryParam filter[DeletedAt] No-example
     * @queryParam filter[DeletedAtAfter] No-example
     * @queryParam filter[DeletedAtBefore] No-example
     * @queryParam filter[DeletedAtBetween] No-example
     * @queryParam filter[DeletedAtCurrentDay] No-example
     * @queryParam filter[DeletedAtCurrentWeek] No-example
     * @queryParam filter[DeletedAtCurrentMonth] No-example
     * @queryParam filter[DeletedAtLastDays] No-example
     * @queryParam filter[DeletedAtNextDays] No-example
     * @queryParam filter[IsActive] No-example
     * @queryParam filter[IsNotActive] No-example
     * @queryParam filter[WithTrashed] No-example
     * @queryParam filter[OnlyTrashed] No-example
     * @queryParam filter[Search] No-example
     * @queryParam sort Allowed: id,name,is_active,created_at,updated_at,deleted_at Classifica a lista. Para classificação descendente coloque o - na frente(-id) No-example
     */

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'sort', 'per_page', 'only_trashed']);
        Log::debug("filters", $filters);

        $finalFilters = [
            'search' => $request->filter['Search'] ?? null,
            'sort' => $request->sort,
            'per_page' => (int)$request->per_page,
            'only_trashed' => $request->filter['OnlyTrashed'] ?? false,
        ];
        $query = QueryService::query(
            Gender::class,
            Gender::ALLOWEDFILTERS(),
            Gender::ALLOWEDINCLUDES(),
            Gender::ALLOWEDSORTS(),
            Gender::DEFAULTSORT(),
            Gender::DEFAULTINCLUDES()
        )->paginate(request('per_page'))->withQueryString();
        if (request()->wantsJson()) {
            return  $this->sendResponse(new GenderResource($query));
        } else {
            return Inertia::render('params/genders/Index', [
                'genders' => GenderResource::collection($query),
                'filters' => $finalFilters
            ]);
        }
    }

    /**
     * Show Gender by ID.
     * @urlParam gender_id int required No-example
     * @queryParam include Allowed: activeMotives,activities Relacionamentos que podem ser incluídos na resposta No-example
     */
    public function show(string $genderId)
    {
        $item = QueryService::getOrFailById(Gender::class, $genderId, Gender::ALLOWEDINCLUDES(), Gender::DEFAULTINCLUDES());
        if (request()->wantsJson()) {
            return  $this->sendResponse(new GenderResource($item));
        } else {
            return back()->with(
                'success',
                __('The action was executed successfully.')
            );
        }
    }

    /**
     * Store Gender.
     */
    public function store(StoreGenderRequest $request)
    {
        $data = [];
        foreach ($request->all() as $key => $value) {
            if (!is_null($value)) {
                $data[$key] = $value;
            }
        }
        // TRANSLATEDS
        if (!empty($request->name)) {
            $translated = TranslatorGoogle::translate($request->name);
            $data['name'] = $translated['original_text'];
            $data['name_translated'] = $translated['translations'];
            $data['original_locale'] = $translated['source_language'];
        }


        // UPLOAD de icon
        $storageIcon = Gender::ICON_STORAGE;
        $savedNameIcon = '';
        $originalFileName = '';
        $fileSize = 0;
        $fileExtension = '';
        $uploadData = [];
        try {
            if ($request->hasFile('icon')) {
                $uploadData = UploadService::putFile($request->file('icon'), $storageIcon);
            } else {
                if (!empty($request->icon) && strpos($request->icon, ';base64')) {
                    $uploadData = UploadService::putFile($request->icon, $storageIcon);
                }
            }
            if ($uploadData) {
                $savedNameIcon = $uploadData['saved_name'];
                $originalFileName = $uploadData['original_name'];
                $fileSize =  $uploadData['size'];
                $fileExtension = $uploadData['extension'];
            }
        } catch (\Throwable $th) {
            throw $th;
        }
        if ($savedNameIcon) {
            $data['icon'] = $savedNameIcon;
            $data['icon_file_name'] = $originalFileName;
            $data['icon_file_size'] = $fileSize;
            $data['icon_file_extension'] = $fileExtension;
        }
        DB::beginTransaction();
        try {
            $item = Gender::create($data);
            $motive = 'Registro Inicial';
            if ($item->is_active) {
                StatusService::active($item, $motive, Auth::user()->id);
            } else {
                StatusService::deactive($item, $motive, Auth::user()->id);
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();

            if ($savedNameIcon) {
                UploadService::removeFile($savedNameIcon, $storageIcon);
            }
            throw $th;
        }
        if (request()->wantsJson()) {
            return  $this->sendResponse(new GenderResource($item));
        } else {
            return back()->with('success', __('The action was executed successfully.'));
        }
    }

    /**
     * Update Gender by ID.
     * @urlParam gender_id int required No-example
     */
    public function update(UpdateGenderRequest $request, string $genderId)
    {
        $item = QueryService::getOrFailById(Gender::class, $genderId);
        $data = [];
        foreach ($request->all() as $key => $value) {
            if (!is_null($value) && $item->{$key} !== $value) {
                $data[$key] = $value;
            }
        }
        $nameTranslateds = [];
        if (!empty($request->name) && ($request->name != $item->name)) {
            $translated = TranslatorGoogle::translate($request->name);
            $data['name'] = $translated['original_text'];
            $nameTranslateds = $translated['translations'];
            $data['original_locale'] = $translated['source_language'];
        }


        // UPLOAD de icon
        $oldSavedNameIcon = $item->icon;
        $storageIcon = Gender::ICON_STORAGE;
        $savedNameIcon = '';
        $originalFileName = '';
        $fileSize = 0;
        $fileExtension = '';
        $uploadData = [];
        try {
            if ($request->hasFile('icon')) {
                $uploadData = UploadService::putFile($request->file('icon'), $storageIcon);
            } else {
                if (!empty($request->icon) && strpos($request->icon, ';base64')) {
                    $uploadData = UploadService::putFile($request->icon, $storageIcon);
                }
            }
            if ($uploadData) {
                $savedNameIcon = $uploadData['saved_name'];
                $originalFileName = $uploadData['original_name'];
                $fileSize =  $uploadData['size'];
                $fileExtension = $uploadData['extension'];
            }
        } catch (\Throwable $th) {
            throw $th;
        }
        if ($savedNameIcon) {
            $data['icon'] = $savedNameIcon;
            $data['icon_file_name'] = $originalFileName;
            $data['icon_file_size'] = $fileSize;
            $data['icon_file_extension'] = $fileExtension;
        }
        DB::beginTransaction();
        try {
            if (!empty($data)) {
                $item->update($data);
            }
            if ($nameTranslateds) {
                $item->setTranslations('name_translated', $nameTranslateds);
                $item->save();
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();

            if ($savedNameIcon) {
                UploadService::removeFile($savedNameIcon, $storageIcon);
            }
            throw $th;
        }

        if ($savedNameIcon && $oldSavedNameIcon) {
            UploadService::removeFile($oldSavedNameIcon, $storageIcon);
        }
        if (request()->wantsJson()) {
            return  $this->sendResponse(new GenderResource($item));
        } else {
            return back()->with('success', __('The action was executed successfully.'));
        }
    }

    /**
     * Delete Gender by ID.
     * @urlParam gender_id int required No-example
     */
    public function destroy(string $genderId)
    {
        $item = QueryService::getOrFailById(Gender::class, $genderId);
        DB::beginTransaction();
        try {
            $item->delete();
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
        if (request()->wantsJson()) {
            return  $this->sendSuccess();
        } else {
            return back()->with(
                'success',
                __('The action was executed successfully.')
            );
        }
    }

    /**
     * Restore Gender.
     * @urlParam gender_id int required No-example
     */
    public function restore($genderId)
    {
        $item = QueryService::getOrFailTrashedById(Gender::class, $genderId);
        DB::beginTransaction();
        try {
            $item->restore();
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
        if (request()->wantsJson()) {
            return  $this->sendSuccess();
        } else {
            return back()->with(
                'success',
                __('The action was executed successfully.')
            );
        }
    }

    /**
     * Force Delete Gender.
     * @urlParam gender_id int required No-example
     */
    public function forceDelete($genderId)
    {
        $item = QueryService::getOrFailTrashedById(Gender::class, $genderId);
        DB::beginTransaction();
        try {
            $item->forceDelete();
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
        if (request()->wantsJson()) {
            return  $this->sendSuccess();
        } else {
            return back()->with(
                'success',
                __('The action was executed successfully.')
            );
        }
    }


    /**
     * Change icon.
     * @urlParam gender_id int required No-example
     */
    public function changeIcon(ChangeGenderIconRequest $request, string $genderId)
    {
        $item = QueryService::getOrFailById(Gender::class, $genderId);
        $oldSavedNameIcon = $item->icon;
        $storageIcon = Gender::ICON_STORAGE;
        $savedNameIcon = '';
        $originalFileName = '';
        $fileSize = 0;
        $fileExtension = '';
        $uploadData = [];

        try {
            if ($request->hasFile('icon')) {
                $uploadData = UploadService::putFile($request->file('icon'), $storageIcon);
            } else {
                if (!empty($request->icon) && strpos($request->icon, ';base64')) {
                    $uploadData = UploadService::putFile($request->icon, $storageIcon);
                }
            }
            if ($uploadData) {
                $savedNameIcon = $uploadData['saved_name'];
                $originalFileName = $uploadData['original_name'];
                $fileSize =  $uploadData['size'];
                $fileExtension = $uploadData['extension'];
            }
        } catch (\Throwable $th) {
            throw $th;
        }
        if ($savedNameIcon) {
            $data['icon'] = $savedNameIcon;
            $data['icon_file_name'] = $originalFileName;
            $data['icon_file_size'] = $fileSize;
            $data['icon_file_extension'] = $fileExtension;

            //SAVE DATA
            DB::beginTransaction();
            try {
                $item->update($data);
                if ($oldSavedNameIcon) {
                    UploadService::removeFile($oldSavedNameIcon, $storageIcon);
                }
                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();
                if ($savedNameIcon) {
                    UploadService::removeFile($savedNameIcon, $storageIcon);
                }
                throw $th;
            }
        }

        if (request()->wantsJson()) {
            return $this->sendSuccess();
        } else {
            return back()->with(
                'success',
                __('The action was executed successfully.')
            );
        }
    }

    /**
     * Clear icon.
     * @urlParam gender_id int required No-example
     */
    public function clearIcon(string $genderId)
    {
        $item = QueryService::getOrFailById(Gender::class, $genderId);
        $oldSavedNameIcon = $item->icon;
        $storageIcon = Gender::ICON_STORAGE;
        $savedNameIcon = '';
        $originalFileName = '';
        $fileSize = 0;
        $fileExtension = '';
        $data = [];

        $data['icon'] = $savedNameIcon;
        $data['icon_file_name'] = $originalFileName;
        $data['icon_file_size'] = $fileSize;
        $data['icon_file_extension'] = $fileExtension;
        //SAVE DATA
        DB::beginTransaction();
        try {
            $item->update($data);
            if ($oldSavedNameIcon) {
                UploadService::removeFile($oldSavedNameIcon, $storageIcon);
            }
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }

        if (request()->wantsJson()) {
            return $this->sendSuccess();
        } else {
            return back()->with(
                'success',
                __('The action was executed successfully.')
            );
        }
    }



    /**
     * Active Gender.
     * @urlParam gender_id int required No-example
     */
    public function active(ChangeGenderIsActiveRequest $request, string $genderId)
    {
        $item = QueryService::getOrFailById(Gender::class,  $genderId);
        if ($item->is_active) {
            return  $this->sendSuccess();
        }
        DB::beginTransaction();
        try {
            $item->update([
                'is_active' => 1,
            ]);
            $motive = 'Sem motivo indicado';
            if ($request->motive) {
                $motive = $request->motive;
            }

            if ($item->is_active) {
                StatusService::active($item, $motive, Auth::user()->id);
            } else {
                StatusService::deactive($item, $motive, Auth::user()->id);
            }
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
        if (request()->wantsJson()) {
            return $this->sendSuccess();
        } else {
            return back()->with(
                'success',
                __('The action was executed successfully.')
            );
        }
    }

    /**
     * Deactive Gender.
     * @urlParam gender_id int required No-example
     */
    public function deactive(ChangeGenderIsActiveRequest $request, string $genderId)
    {
        $item = QueryService::getOrFailById(Gender::class, $genderId);
        if (!$item->is_active) {
            return  $this->sendSuccess();
        }
        DB::beginTransaction();
        try {
            $item->update([
                'is_active' => 0,
            ]);
            $motive = 'Sem motivo indicado';
            if ($request->motive) {
                $motive = $request->motive;
            }

            if ($item->is_active) {
                StatusService::active($item, $motive, Auth::user()->id);
            } else {
                StatusService::deactive($item, $motive, Auth::user()->id);
            }
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
        if (request()->wantsJson()) {
            return $this->sendSuccess();
        } else {
            return back()->with(
                'success',
                __('The action was executed successfully.')
            );
        }
    }

    /**
     * Toggle IsActive.
     * @urlParam gender_id int required No-example
     */
    public function toggleIsActive(string $genderId)
    {
        $item = QueryService::getOrFailById(Gender::class, $genderId);

        DB::beginTransaction();
        try {
            if (!$item->is_active) {
                $item->update([
                    'is_active' => 1,
                ]);
            } else {
                $item->update([
                    'is_active' => 0,
                ]);
            }
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
        if (request()->wantsJson()) {
            return $this->sendSuccess();
        } else {
            return back()->with(
                'success',
                __('The action was executed successfully.')
            );
        }
    }
}
