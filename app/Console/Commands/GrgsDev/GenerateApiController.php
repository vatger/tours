<?php

namespace App\Console\Commands\GrgsDev;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class GenerateApiController extends Command
{
    protected $signature = 'grgsdev:controller {model}';
    protected $description = 'Generate API Abstract Controller and Concret Controller(if not exists) based on a JSON file';

    public function handle()
    {
        $modelName = $this->argument('model');
        $jsonPath = base_path("documentation/models/json/{$modelName}.json");

        if (!File::exists($jsonPath)) {
            $this->error("JSON file for model {$modelName} not found at path {$jsonPath}.");
            return;
        }

        $jsonContent = json_decode(File::get($jsonPath), true);

        if (!$jsonContent) {
            $this->error('Invalid JSON content.');
            return;
        }

        $attributes = $jsonContent['attributes'] ?? [];
        $relations = $jsonContent['relations'] ?? [];
        $softDeletes = $jsonContent['softDeletes'] ?? false;
        $timestamps = $jsonContent['timestamps'] ?? true;
        $version = $jsonContent['version'] ?? 'V1';
        $logsActivity = $jsonContent['logsActivity'] ?? false;
        $clearsResponseCache = $jsonContent['clearsResponseCache'] ?? false;
        $useIsActive = $jsonContent['useIsActive'] ?? false;
        $useApprovedStatus = $jsonContent['useApprovedStatus'] ?? false;

        // Gerar API Controller
        $this->generateApiController($modelName, $timestamps, $softDeletes, $relations, $version, $attributes, $useIsActive, $useApprovedStatus, $logsActivity);
        $this->generateConcretController($modelName, $version);

        $this->info("Controller for {$modelName} generated successfully.");
    }

    protected function generateApiController($modelName, $timestamps, $softDeletes, $relations, $version, $attributes, $useIsActive, $useApprovedStatus, $logsActivity)
    {
        $className = "{$modelName}Controller";
        $namespace = "App\\Http\\Controllers\\Api\\{$version}\\AbstractContracts\\{$modelName}";
        $filePath = app_path("Http/Controllers/Api/{$version}/AbstractContracts/{$modelName}/{$className}.php");
        $modelSnaked = Str::snake($modelName);
        $modelCamel = Str::camel($modelName);

        $useUploadService = '';
        $updateUpload = '';
        $changeClearUpload = '';
        $removeOldUpload = '';
        $removeCreatedUpload = '';
        $useChangeUpload = '';
        $hasBoolean = false;
        foreach ($attributes as $attribute) {
            if ($attribute['type'] === 'file' || $attribute['type'] === 'image' || $attribute['type'] === 'video') {
                $name = $attribute['name'];
                $nameUpper = Str::upper($name);
                $capitalLetter = ucwords(Str::camel($name));
                $modelSnaked = Str::snake($modelName);
                $modelCamel = Str::camel($modelName);

                $useChangeUpload .= "use App\Http\Requests\Api\\{$version}\\{$modelName}\Change{$modelName}{$capitalLetter}Request;\n";
            }
            if ($attribute['type'] === 'boolean') {
                $hasBoolean = true;
            }
        }
        $createTranslated = $this->generateStoreTranslatedCode($attributes);
        $createIsActiveApproved = '';
        $useAuthFacade = '';
        if ($useIsActive || $useApprovedStatus) {
            $useAuthFacade = "use Illuminate\Support\Facades\Auth;\nuse App\Services\StatusService;";
            $createIsActiveApproved = $this->generateCreateIsActiveApprovedCode($modelName, $useIsActive, $useApprovedStatus);
        }
        $updateTranslated = $this->generateTranslatedWithComparisonCode($attributes);
        $saveTranslated = $this->generateSaveTranslationsCode($attributes);
        $createUpload = $this->generateCreateUploadLogic($attributes, $modelName);
        if ($createUpload) {
            $useUploadService = 'use App\Services\UploadService;';
            $removeCreatedUpload = $this->generateRemoveCreateUploadLogic($attributes);
            $updateUpload = $this->generateUpdateUploadLogic($attributes, $modelName);
            $removeOldUpload = $this->generateRemoveOldUploadLogic($attributes);

            $changeClearUpload = $this->generateClearChangeUploadLogic($attributes, $modelName);
        }
        $changeActiveDeactiveLogic = '';
        $useChangeIsActiveRequest = '';
        $changeApprovedStatusLogic = '';
        $useChangeApprovedStatusRequest = '';
        $booleanActiveDeactiveLogic = '';
        if ($hasBoolean) {
            $booleanActiveDeactiveLogic = $this->generateBooleanActiveInactiveLogic($attributes, $modelName);
        }
        if ($useIsActive) {
            $name = 'is_active';
            $capitalLetter = ucwords(Str::camel($name));
            $modelSnaked = Str::snake($modelName);
            $modelCamel = Str::camel($modelName);
            $useChangeIsActiveRequest = "use App\Http\Requests\Api\\{$version}\\{$modelName}\Change{$modelName}{$capitalLetter}Request;";
            $changeActiveDeactiveLogic = $this->generateActiveInactiveLogic($modelName);
        }
        if ($useApprovedStatus) {
            $name = 'approved_status';
            $capitalLetter = ucwords(Str::camel($name));
            $modelSnaked = Str::snake($modelName);
            $modelCamel = Str::camel($modelName);
            $useChangeApprovedStatusRequest = "use App\Http\Requests\Api\\{$version}\\{$modelName}\Change{$modelName}{$capitalLetter}Request;";
            $changeApprovedStatusLogic = $this->generateApprovedStatusLogic($modelName);
        }

        $relationMethods = [];
        foreach ($relations as $relation) {
            if ($relation['type'] === 'belongsToMany') {
                $relationMethods[] = $this->generateAttachDetachMethods($relation['name'], $modelName, $modelSnaked, $modelCamel);
            }
        }
        $relationMethods = implode("\n\n", $relationMethods);

        $softDeleteMethods = $softDeletes ? $this->generateSoftDeleteMethods($modelName, $modelSnaked, $modelCamel, $modelName) : '';

        $indexComment = $this->generateApiDocumentation($modelName, $attributes, $relations, $timestamps, $softDeletes, $logsActivity, $useIsActive, $useApprovedStatus);
        $showIncludeDoc  = $this->generateApiShowDocumentation($relations, $logsActivity, $useIsActive, $useApprovedStatus);
        $classTemplate = <<<EOD
<?php

namespace {$namespace};

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
{$useAuthFacade}
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\\{$version}\\{$modelName}\Store{$modelName}Request;
use App\Http\Requests\Api\\{$version}\\{$modelName}\Update{$modelName}Request;
{$useChangeUpload}
{$useChangeApprovedStatusRequest}
{$useChangeIsActiveRequest}
use App\Http\Resources\Api\\{$version}\\{$modelName}\\{$modelName}Resource;
use App\Models\\{$modelName};
use App\Services\QueryService;
use App\Services\TranslatorGoogle;
{$useUploadService}
use App\Traits\ApiResponse;

/**
 * @version {$version}
 *
 * @group API-ADMINISTRATIVE
 *
 * @subgroup {$modelName}
 * @authenticated
 */
abstract class {$className} extends Controller
{
    use ApiResponse;
    {$indexComment}
    public function index()
    {
        \$query = QueryService::query(
            $modelName::class,
            $modelName::ALLOWEDFILTERS(),
            $modelName::ALLOWEDINCLUDES(),
            $modelName::ALLOWEDSORTS(),
            $modelName::DEFAULTSORT(),
            $modelName::DEFAULTINCLUDES()
        )->paginate(request('per_page'))->withQueryString();
        return {$modelName}Resource::collection(\$query);
    }

    /**
     * Show {$modelName} by ID.
     * @urlParam {$modelSnaked}_id int required No-example
     {$showIncludeDoc}
     */
    public function show(string \${$modelCamel}Id)
    {
        \$item = QueryService::getOrFailById({$modelName}::class, \${$modelCamel}Id, {$modelName}::ALLOWEDINCLUDES(), {$modelName}::DEFAULTINCLUDES());
        if (request()->wantsJson()) {
            return  \$this->sendResponse(new {$modelName}Resource(\$item));
        } else {
            return redirect()->back()->with(
                'toasts',
                [
                    'id' => Str::uuid(),
                    'type' => 'success',
                    'message' => __('The action was executed successfully.'),
                ]
            );
        }
    }

    /**
     * Store {$modelName}.
     */
    public function store(Store{$modelName}Request \$request)
    {
        \$data = [];
        foreach (\$request->all() as \$key => \$value) {
            if (!is_null(\$value)) {
                \$data[\$key] = \$value;
            }
        }
        {$createTranslated}
        {$createUpload}
        DB::beginTransaction();
        try {
            \$item = {$modelName}::create(\$data);
            {$createIsActiveApproved}
            DB::commit();
        } catch (\Throwable \$th) {
            DB::rollBack();
            {$removeCreatedUpload}
            throw \$th;
        }
        if (request()->wantsJson()) {
            return  \$this->sendResponse(new {$modelName}Resource(\$item));
        } else {
            return redirect()->back()->with(
                'toasts',
                [
                    'id' => Str::uuid(),
                    'type' => 'success',
                    'message' => __('The action was executed successfully.'),
                ]
            );
        }
    }

    /**
     * Update {$modelName} by ID.
     * @urlParam {$modelSnaked}_id int required No-example
     */
    public function update(Update{$modelName}Request \$request, string \${$modelCamel}Id)
    {
        \$item = QueryService::getOrFailById({$modelName}::class, \${$modelCamel}Id);
        \$data = [];
        foreach (\$request->all() as \$key => \$value) {
            if (!is_null(\$value) && \$item->{\$key} !== \$value) {
                \$data[\$key] = \$value;
            }
        }
        {$updateTranslated}
        {$updateUpload}
        DB::beginTransaction();
        try {
            if (!empty(\$data)) {
                \$item->update(\$data);
            }
            {$saveTranslated}
            DB::commit();
        } catch (\Throwable \$th) {
            DB::rollBack();
            {$removeCreatedUpload}
            throw \$th;
        }
        {$removeOldUpload}
        if (request()->wantsJson()) {
            return  \$this->sendResponse(new {$modelName}Resource(\$item));
        } else {
            return redirect()->back()->with(
                'toasts',
                [
                    'id' => Str::uuid(),
                    'type' => 'success',
                    'message' => __('The action was executed successfully.'),
                ]
            );
        }
    }

    /**
     * Delete {$modelName} by ID.
     * @urlParam {$modelSnaked}_id int required No-example
     */
    public function destroy(string \${$modelCamel}Id)
    {
        \$item = QueryService::getOrFailById({$modelName}::class, \${$modelCamel}Id);
        DB::beginTransaction();
        try {
            \$item->delete();
            DB::commit();
        } catch (\Throwable \$th) {
            DB::rollBack();
            throw \$th;
        }
        if (request()->wantsJson()) {
            return  \$this->sendSuccess();
        } else {
            return redirect()->back()->with(
                'toasts',
                [
                    'id' => Str::uuid(),
                    'type' => 'success',
                    'message' => __('The action was executed successfully.'),
                ]
            );
        }
    }

    {$softDeleteMethods}

    {$changeClearUpload}

    {$booleanActiveDeactiveLogic}

    {$changeActiveDeactiveLogic}

    {$changeApprovedStatusLogic}

    {$relationMethods}
}
EOD;
        if (!File::isDirectory(dirname($filePath))) {
            File::makeDirectory(dirname($filePath), 0755, true);
        }

        File::put($filePath, $classTemplate);
        $this->info("Abstract Controller {$modelName}Controller gerado com sucesso.");
    }

    protected function generateAttachDetachMethods($relationName, $modelName, $modelSnaked, $modelCamel)
    {
        return <<<EOD
/**
 * Add {$relationName} to {$modelName}.
 * @urlParam {$modelSnaked}_id int required No-example
 */
public function attach{$relationName}({$modelName} \$model, Request \$request)
{
    \$model->{$relationName}()->attach(\$request->input('{$relationName}'));
    return  \$this->sendSuccess();
}

/**
 * Remove {$relationName} from {$modelName}.
 * @urlParam {$modelSnaked}_id int required No-example
 */
public function detach{$relationName}({$modelName} \$model, Request \$request)
{
    \$model->{$relationName}()->detach(\$request->input('{$relationName}'));
    return  \$this->sendSuccess();
}
EOD;
    }

    protected function generateSoftDeleteMethods($model, $modelSnaked, $modelCamel, $modelName)
    {
        return <<<EOD
/**
     * Restore {$model}.
     * @urlParam {$modelSnaked}_id int required No-example
     */
    public function restore(\${$modelCamel}Id)
    {
        \$item = QueryService::getOrFailTrashedById({$modelName}::class, \${$modelCamel}Id);
        DB::beginTransaction();
        try {
            \$item->restore();
            DB::commit();
        } catch (\Throwable \$th) {
            DB::rollBack();
            throw \$th;
        }
        if (request()->wantsJson()) {
            return  \$this->sendSuccess();
        } else {
            return redirect()->back()->with(
                'toasts',
                [
                    'id' => Str::uuid(),
                    'type' => 'success',
                    'message' => __('The action was executed successfully.'),
                ]
            );
        }
    }

    /**
     * Force Delete {$model}.
     * @urlParam {$modelSnaked}_id int required No-example
     */
    public function forceDelete(\${$modelCamel}Id)
    {
        \$item = QueryService::getOrFailTrashedById({$modelName}::class, \${$modelCamel}Id);
        DB::beginTransaction();
        try {
            \$item->forceDelete();
            DB::commit();
        } catch (\Throwable \$th) {
            DB::rollBack();
            throw \$th;
        }
        if (request()->wantsJson()) {
            return  \$this->sendSuccess();
        } else {
            return redirect()->back()->with(
                'toasts',
                [
                    'id' => Str::uuid(),
                    'type' => 'success',
                    'message' => __('The action was executed successfully.'),
                ]
            );
        }
    }
EOD;
    }

    protected function generateCreateUploadLogic($attributes, $model)
    {

        $uploadLogic = "";
        foreach ($attributes as $attribute) {
            if ($attribute['type'] === 'file' || $attribute['type'] === 'image' || $attribute['type'] === 'video') {
                $name = $attribute['name'];
                $nameUpper = Str::upper($name);
                $capitalLetter = ucwords(Str::camel($name));

                $uploadLogic .= "
        // UPLOAD de {$attribute['name']}
        \$storage{$capitalLetter} = {$model}::{$nameUpper}_STORAGE;
        \$savedName{$capitalLetter} = '';
        \$originalFileName = '';
        \$fileSize = 0;
        \$fileExtension = '';
        \$uploadData = [];
        try {
            if (\$request->hasFile('$name')) {
                \$uploadData = UploadService::putFile(\$request->file('$name'), \$storage{$capitalLetter});
            } else {
                if (!empty(\$request->{$name}) && strpos(\$request->{$name}, ';base64')) {
                    \$uploadData = UploadService::putFile(\$request->{$name}, \$storage{$capitalLetter});
                }
            }
            if (\$uploadData) {
                \$savedName{$capitalLetter} = \$uploadData['saved_name'];
                \$originalFileName = \$uploadData['original_name'];
                \$fileSize =  \$uploadData['size'];
                \$fileExtension = \$uploadData['extension'];
            }
        } catch (\Throwable \$th) {
            throw \$th;
        }
        if (\$savedName{$capitalLetter}) {
            \$data['{$name}'] = \$savedName{$capitalLetter};
            \$data['{$name}_file_name'] = \$originalFileName;
            \$data['{$name}_file_size'] = \$fileSize;
            \$data['{$name}_file_extension'] = \$fileExtension;
        }";
            }
        }
        return $uploadLogic;
    }

    protected function generateRemoveCreateUploadLogic($attributes)
    {

        $uploadLogic = "";
        foreach ($attributes as $attribute) {
            if ($attribute['type'] === 'file' || $attribute['type'] === 'image' || $attribute['type'] === 'video') {
                $name = $attribute['name'];
                $capitalLetter = ucwords(Str::camel($name));

                $uploadLogic .= "
            if (\$savedName{$capitalLetter}) {
                UploadService::removeFile(\$savedName{$capitalLetter}, \$storage{$capitalLetter});
            }";
            }
        }
        return $uploadLogic;
    }

    protected function generateUpdateUploadLogic($attributes, $model)
    {

        $uploadLogic = "";
        foreach ($attributes as $attribute) {
            if ($attribute['type'] === 'file' || $attribute['type'] === 'image' || $attribute['type'] === 'video') {
                $name = $attribute['name'];
                $nameUpper = Str::upper($name);
                $capitalLetter = ucwords(Str::camel($name));

                $uploadLogic .= "
        // UPLOAD de {$attribute['name']}
        \$oldSavedName{$capitalLetter} = \$item->{$name};
        \$storage{$capitalLetter} = {$model}::{$nameUpper}_STORAGE;
        \$savedName{$capitalLetter} = '';
        \$originalFileName = '';
        \$fileSize = 0;
        \$fileExtension = '';
        \$uploadData = [];
        try {
            if (\$request->hasFile('$name')) {
                \$uploadData = UploadService::putFile(\$request->file('$name'), \$storage{$capitalLetter});
            } else {
                if (!empty(\$request->{$name}) && strpos(\$request->{$name}, ';base64')) {
                    \$uploadData = UploadService::putFile(\$request->{$name}, \$storage{$capitalLetter});
                }
            }
            if (\$uploadData) {
                \$savedName{$capitalLetter} = \$uploadData['saved_name'];
                \$originalFileName = \$uploadData['original_name'];
                \$fileSize =  \$uploadData['size'];
                \$fileExtension = \$uploadData['extension'];
            }
        } catch (\Throwable \$th) {
            throw \$th;
        }
        if (\$savedName{$capitalLetter}) {
            \$data['{$name}'] = \$savedName{$capitalLetter};
            \$data['{$name}_file_name'] = \$originalFileName;
            \$data['{$name}_file_size'] = \$fileSize;
            \$data['{$name}_file_extension'] = \$fileExtension;
        }";
            }
        }
        return $uploadLogic;
    }

    protected function generateRemoveOldUploadLogic($attributes)
    {

        $uploadLogic = "";
        foreach ($attributes as $attribute) {
            if ($attribute['type'] === 'file' || $attribute['type'] === 'image' || $attribute['type'] === 'video') {
                $name = $attribute['name'];
                $capitalLetter = ucwords(Str::camel($name));

                $uploadLogic .= "
        if (\$savedName{$capitalLetter} && \$oldSavedName{$capitalLetter}) {
            UploadService::removeFile(\$oldSavedName{$capitalLetter}, \$storage{$capitalLetter});
        }";
            }
        }
        return $uploadLogic;
    }

    protected function generateClearChangeUploadLogic($attributes, $model)
    {

        $uploadLogic = "";
        foreach ($attributes as $attribute) {
            if ($attribute['type'] === 'file' || $attribute['type'] === 'image' || $attribute['type'] === 'video') {
                $name = $attribute['name'];
                $nameUpper = Str::upper($name);
                $capitalLetter = ucwords(Str::camel($name));
                $modelSnaked = Str::snake($model);
                $modelCamel = Str::camel($model);

                $uploadLogic .= "
    /**
     * Change {$name}.
     * @urlParam {$modelSnaked}_id int required No-example
     */
    public function change{$capitalLetter}(Change{$model}{$capitalLetter}Request \$request, string \${$modelCamel}Id)
    {
        \$item = QueryService::getOrFailById({$model}::class, \${$modelCamel}Id);
        \$oldSavedName{$capitalLetter} = \$item->{$name};
        \$storage{$capitalLetter} = {$model}::{$nameUpper}_STORAGE;
        \$savedName{$capitalLetter} = '';
        \$originalFileName = '';
        \$fileSize = 0;
        \$fileExtension = '';
        \$uploadData = [];

        try {
            if (\$request->hasFile('$name')) {
                \$uploadData = UploadService::putFile(\$request->file('$name'), \$storage{$capitalLetter});
            } else {
                if (!empty(\$request->{$name}) && strpos(\$request->{$name}, ';base64')) {
                    \$uploadData = UploadService::putFile(\$request->{$name}, \$storage{$capitalLetter});
                }
            }
            if (\$uploadData) {
                \$savedName{$capitalLetter} = \$uploadData['saved_name'];
                \$originalFileName = \$uploadData['original_name'];
                \$fileSize =  \$uploadData['size'];
                \$fileExtension = \$uploadData['extension'];
            }
        } catch (\Throwable \$th) {
            throw \$th;
        }
        if (\$savedName{$capitalLetter}) {
            \$data['{$name}'] = \$savedName{$capitalLetter};
            \$data['{$name}_file_name'] = \$originalFileName;
            \$data['{$name}_file_size'] = \$fileSize;
            \$data['{$name}_file_extension'] = \$fileExtension;

            //SAVE DATA
            DB::beginTransaction();
            try {
                \$item->update(\$data);
                if (\$oldSavedName{$capitalLetter}) {
                    UploadService::removeFile(\$oldSavedName{$capitalLetter}, \$storage{$capitalLetter});
                }
                DB::commit();
            } catch (\Throwable \$th) {
                DB::rollBack();
                if (\$savedName{$capitalLetter}) {
                    UploadService::removeFile(\$savedName{$capitalLetter}, \$storage{$capitalLetter});
                }
                throw \$th;
            }
        }

        if (request()->wantsJson()) {
            return \$this->sendSuccess();
        } else {
            return redirect()->back()->with(
                'toasts',
                [
                    'id' => Str::uuid(),
                    'type' => 'success',
                    'message' => __('The action was executed successfully.'),
                ]
            );
        }
    }

    /**
     * Clear {$name}.
     * @urlParam {$modelSnaked}_id int required No-example
     */
    public function clear{$capitalLetter}(string \${$modelCamel}Id)
    {
        \$item = QueryService::getOrFailById({$model}::class, \${$modelCamel}Id);
        \$oldSavedName{$capitalLetter} = \$item->{$name};
        \$storage{$capitalLetter} = {$model}::{$nameUpper}_STORAGE;
        \$savedName{$capitalLetter} = '';
        \$originalFileName = '';
        \$fileSize = 0;
        \$fileExtension = '';
        \$data = [];

        \$data['{$name}'] = \$savedName{$capitalLetter};
        \$data['{$name}_file_name'] = \$originalFileName;
        \$data['{$name}_file_size'] = \$fileSize;
        \$data['{$name}_file_extension'] = \$fileExtension;
        //SAVE DATA
        DB::beginTransaction();
        try {
            \$item->update(\$data);
            if (\$oldSavedName{$capitalLetter}) {
                UploadService::removeFile(\$oldSavedName{$capitalLetter}, \$storage{$capitalLetter});
            }
            DB::commit();
        } catch (\Throwable \$th) {
            DB::rollBack();
            throw \$th;
        }

        if (request()->wantsJson()) {
            return \$this->sendSuccess();
        } else {
            return redirect()->back()->with(
                'toasts',
                [
                    'id' => Str::uuid(),
                    'type' => 'success',
                    'message' => __('The action was executed successfully.'),
                ]
            );
        }
    }";
            }
        }
        return $uploadLogic;
    }


    protected function generateBooleanActiveInactiveLogic($attributes, $model)
    {
        $modelSnaked = Str::snake($model);
        $modelCamel = Str::camel($model);

        $uploadLogic = "";
        foreach ($attributes as $attribute) {
            if ($attribute['type'] === 'boolean') {
                $name = $attribute['name'];
                $nameUpper = Str::upper($name);
                $capitalLetter = ucwords(Str::camel($name));
                $uploadLogic .= "/**
     * Mark as {$capitalLetter}.
     * @urlParam {$modelSnaked}_id int required No-example
     */
    public function change{$capitalLetter}ToTrue(string \${$modelCamel}Id)
    {
        \$item = QueryService::getOrFailById({$model}::class,  \${$modelCamel}Id);
        if (\$item->{$name}) {
            return  \$this->sendSuccess();
        }
        DB::beginTransaction();
        try {
            \$item->update([
                '{$name}' => 1,
            ]);
            DB::commit();
        } catch (\Throwable \$th) {
            DB::rollBack();
            throw \$th;
        }
        return  \$this->sendSuccess();
    }

    /**
     * Mark as NOT {$capitalLetter}.
     * @urlParam {$modelSnaked}_id int required No-example
     */
    public function change{$capitalLetter}ToFalse(string \${$modelCamel}Id)
    {
        \$item = QueryService::getOrFailById({$model}::class,  \${$modelCamel}Id);
        if (!\$item->{$name}) {
            return  \$this->sendSuccess();
        }
        DB::beginTransaction();
        try {
            \$item->update([
                '{$name}' => 0,
            ]);
            DB::commit();
        } catch (\Throwable \$th) {
            DB::rollBack();
            throw \$th;
        }
        return  \$this->sendSuccess();
    }

    /**
     * Toggle {$capitalLetter}.
     * @urlParam {$modelSnaked}_id int required No-example
     */
    public function toggle{$capitalLetter}(string \${$modelCamel}Id)
    {
        \$item = QueryService::getOrFailById({$model}::class,  \${$modelCamel}Id);

        DB::beginTransaction();
        try {
            if (!\$item->{$name}) {
                \$item->update([
                    '{$name}' => 1,
                ]);
            } else {
                \$item->update([
                    '{$name}' => 0,
                ]);
            }
            DB::commit();
        } catch (\Throwable \$th) {
            DB::rollBack();
            throw \$th;
        }
        return  \$this->sendSuccess();
    }";
            }
        }

        return $uploadLogic;
    }


    protected function generateActiveInactiveLogic($model)
    {

        $uploadLogic = "";

        $name = 'is_active';
        $nameUpper = Str::upper($name);
        $capitalLetter = ucwords(Str::camel($name));
        $modelSnaked = Str::snake($model);
        $modelCamel = Str::camel($model);

        $uploadLogic .= "/**
     * Active {$model}.
     * @urlParam {$modelSnaked}_id int required No-example
     */
    public function active(Change{$model}{$capitalLetter}Request \$request, string \${$modelCamel}Id)
    {
        \$item = QueryService::getOrFailById({$model}::class,  \${$modelCamel}Id);
        if (\$item->is_active) {
            return  \$this->sendSuccess();
        }
        DB::beginTransaction();
        try {
            \$item->update([
                'is_active' => 1,
            ]);
            \$motive = 'Sem motivo indicado';
            if (\$request->motive) {
                \$motive = \$request->motive;
            }

            if (\$item->is_active) {
                StatusService::active(\$item, \$motive, Auth::user()->id);
            } else {
                StatusService::deactive(\$item, \$motive, Auth::user()->id);
            }
            DB::commit();
        } catch (\Throwable \$th) {
            DB::rollBack();
            throw \$th;
        }
        return  \$this->sendSuccess();
    }

    /**
     * Deactive {$model}.
     * @urlParam {$modelSnaked}_id int required No-example
     */
    public function deactive(Change{$model}{$capitalLetter}Request \$request, string \${$modelCamel}Id)
    {
        \$item = QueryService::getOrFailById({$model}::class, \${$modelCamel}Id);
        if (!\$item->is_active) {
            return  \$this->sendSuccess();
        }
        DB::beginTransaction();
        try {
            \$item->update([
                'is_active' => 0,
            ]);
            \$motive = 'Sem motivo indicado';
            if (\$request->motive) {
                \$motive = \$request->motive;
            }

            if (\$item->is_active) {
                StatusService::active(\$item, \$motive, Auth::user()->id);
            } else {
                StatusService::deactive(\$item, \$motive, Auth::user()->id);
            }
            DB::commit();
        } catch (\Throwable \$th) {
            DB::rollBack();
            throw \$th;
        }
        return  \$this->sendSuccess();
    }

    /**
     * Toggle IsActive.
     * @urlParam gender_id int required No-example
     */
    public function toggleIsActive(string \${$modelCamel}Id)
    {
        \$item = QueryService::getOrFailById({$model}::class, \${$modelCamel}Id);

        DB::beginTransaction();
        try {
            if (!\$item->is_active) {
                \$item->update([
                    'is_active' => 1,
                ]);
            } else {
                \$item->update([
                    'is_active' => 0,
                ]);
            }
            DB::commit();
        } catch (\Throwable \$th) {
            DB::rollBack();
            throw \$th;
        }
        return  \$this->sendSuccess();
    }";

        return $uploadLogic;
    }

    protected function generateApprovedStatusLogic($model)
    {
        $uploadLogic = "";
        $name = 'approved_status';
        $nameUpper = Str::upper($name);
        $capitalLetter = ucwords(Str::camel($name));
        $modelSnaked = Str::snake($model);
        $modelCamel = Str::camel($model);

        $uploadLogic .= "/**
     * Turn Analisys {$model}.
     * @urlParam {$modelSnaked}_id int required No-example
     */
    public function analisys(Change{$model}{$capitalLetter}Request \$request, string \${$modelCamel}Id)
    {
        \$item = QueryService::getOrFailById({$model}::class, \${$modelCamel}Id);
        if (\$item->approved_status == {$model}::APPROVED_STATUS_ANALISYS) {
            return \$this->sendSuccess();
        }
        DB::beginTransaction();
        try {
            \$motive = 'Sem motivo indicado';
            if (\$request->motive) {
                \$motive = \$request->motive;
            }
            StatusService::analisys(\$item, \$motive, request()->user()->id);
            DB::commit();
        } catch (\Throwable \$th) {
            DB::rollBack();
            throw \$th;
        }
        return \$this->sendSuccess();
    }

    /**
     * Approve {$model}.
     * @urlParam {$modelSnaked}_id int required No-example
     *
     */
    public function approve(Change{$model}{$capitalLetter}Request \$request, string \${$modelCamel}Id)
    {
        \$item = QueryService::getOrFailById({$model}::class, \${$modelCamel}Id);
        DB::beginTransaction();
        try {
            \$motive = 'Sem motivo indicado';
            if (\$request->motive) {
                \$motive = \$request->motive;
            }
            StatusService::approve(\$item, \$motive, request()->user()->id);
            DB::commit();
        } catch (\Throwable \$th) {
            DB::rollBack();
            throw \$th;
        }
        return \$this->sendSuccess();
    }

    /**
     * Unapprove {$model}.
     * @urlParam {$modelSnaked}_id int required No-example
     *
     */
    public function unapprove(Change{$model}{$capitalLetter}Request \$request, string \${$modelCamel}Id)
    {
        \$item = QueryService::getOrFailById({$model}::class, \${$modelCamel}Id);
        if (\$item->approved_status == {$model}::APPROVED_STATUS_UNAPPROVED) {
            return \$this->sendSuccess();
        }
        DB::beginTransaction();
        try {
            \$motive = 'Sem motivo indicado';
            if (\$request->motive) {
                \$motive = \$request->motive;
            }
            StatusService::unapprove(\$item, \$motive, request()->user()->id);
            DB::commit();
        } catch (\Throwable \$th) {
            DB::rollBack();
            throw \$th;
        }
        return \$this->sendSuccess();
    }

    /**
     * Block {$model}.
     * @urlParam {$modelSnaked}_id int required No-example
     *
     */
    public function block(Change{$model}{$capitalLetter}Request \$request, string \${$modelCamel}Id)
    {
        \$item = QueryService::getOrFailById({$model}::class, \${$modelCamel}Id);
        if (\$item->approved_status == {$model}::APPROVED_STATUS_BLOCKED) {
            return \$this->sendSuccess();
        }
        DB::beginTransaction();
        try {
            \$motive = 'Sem motivo indicado';
            if (\$request->motive) {
                \$motive = \$request->motive;
            }
            StatusService::block(\$item, \$motive, request()->user()->id);
            DB::commit();
        } catch (\Throwable \$th) {
            DB::rollBack();
            throw \$th;
        }
        return \$this->sendSuccess();
    }

    /**
     * Cancel {$model}.
     * @urlParam {$modelSnaked}_id int required No-example
     *
     */
    public function cancel(Change{$model}{$capitalLetter}Request \$request, string \${$modelCamel}Id)
    {
        \$item = QueryService::getOrFailById({$model}::class, \${$modelCamel}Id);
        if (\$item->approved_status == {$model}::APPROVED_STATUS_CANCELED) {
            return \$this->sendSuccess();
        }
        DB::beginTransaction();
        try {
            \$motive = 'Sem motivo indicado';
            if (\$request->motive) {
                \$motive = \$request->motive;
            }
            StatusService::cancel(\$item, \$motive, request()->user()->id);
            DB::commit();
        } catch (\Throwable \$th) {
            DB::rollBack();
            throw \$th;
        }
        return \$this->sendSuccess();
    }";

        return $uploadLogic;
    }

    protected function generateConcretController($model, $version)
    {
        $filePath = app_path("Http/Controllers/Api/{$version}/{$model}/{$model}Controller.php");
        if (!File::isDirectory(dirname($filePath))) {
            File::makeDirectory(dirname($filePath), 0755, true);
        }
        // Verifica se o modelo concreto já existe
        if (!File::exists($filePath)) {
            $modelContent = <<<PHP
    <?php

    namespace App\Http\Controllers\Api\\{$version}\\{$model};

    use App\Http\Controllers\Api\\{$version}\AbstractContracts\\{$model}\\{$model}Controller as AbstractContracts{$model}Controller;

    /**
     * @version {$version}
     *
     * @group API-ADMINISTRATIVE
     *
     * @subgroup {$model}
     * @authenticated
     */
    class {$model}Controller extends AbstractContracts{$model}Controller {
        // news implementations if necessary
    }

    PHP;

            File::put($filePath, $modelContent);
            $this->info("Concret controller {$model}Controller gerado com sucesso.");
        }
    }

    protected function generateApiDocumentation($modelName, $attributes, $relations, $timestamps, $softDeletes, $logsActivity, $useIsActive, $useApprovedStatus)
    {
        $attributeId = [
            "name" => "id",
            "type" => "unsignedBigInteger",
            "length" => null,
            "max" => null,
            "min" => null,
            "required" => false,
            "nullable" => true,
            "default" => null,
            "unique" => false,
            "sortAble" => true,
            "filterAble" => true,
            "exactFilter" => true,
            "searchAble" => true,
            "translated" => false,
        ];
        array_unshift($attributes, $attributeId);
        if ($useIsActive) {
            $attrib = [
                "name" => "is_active",
                "sortAble" => true,

            ];
            array_push($attributes, $attrib);
        }
        if ($timestamps) {
            $attrib = [
                "name" => "created_at",
                "sortAble" => true,

            ];
            array_push($attributes, $attrib);
            $attrib = [
                "name" => "updated_at",
                "sortAble" => true,

            ];
            array_push($attributes, $attrib);
        }

        if ($softDeletes) {
            $attrib = [
                "name" => "deleted_at",
                "sortAble" => true,

            ];
            array_push($attributes, $attrib);
        }

        // Filtros: atributos com filterAble = true
        $filters = array_filter($attributes, function ($attr) {
            return isset($attr['filterAble']) && $attr['filterAble'] === true;
        });

        // Sort: atributos com sortAble = true
        $sortableAttributes = array_filter($attributes, function ($attr) {
            return isset($attr['sortAble']) && $attr['sortAble'] === true;
        });
        // Include: lista de relacionamentos (se existirem)
        $includeRelations = !empty($relations) ? array_map(function ($relation) {
            return $relation['name'];
        }, $relations) : [];

        if ($useIsActive) {
            $includeRelations[] = 'activeMotives';
        }
        if ($useApprovedStatus) {
            $includeRelations[] = 'approvedMotives';
        }


        if ($logsActivity) {
            $includeRelations[] = 'activities';
        }
        // Construindo a documentação dinâmica
        $doc = "/**\n";
        $doc .= "     * list {$modelName}.\n";
        $doc .= "     * @queryParam page int Indique o número da página desejada No-example\n";
        $doc .= "     * @queryParam per_page int Indique quantos registros deve conter cada página[default=50] No-example\n";

        // Gerar a linha dos includes
        if (!empty($includeRelations)) {
            $doc .= "     * @queryParam include Allowed: " . implode(",", $includeRelations) . " Relacionamentos que podem ser incluídos na resposta No-example\n";
        }

        // Gerar as linhas dos filtros
        foreach ($filters as $filter) {
            $name = $filter['name'];

            // Se o tipo for 'date', 'timestamp', ou 'datetime', cria filtros adicionais
            if (in_array($filter['type'], ['date', 'timestamp', 'timeStamp', 'datetime', 'dateTime'])) {
                $transformedName = Str::ucfirst(Str::camel($filter['name'])); // Nome transformado
                $doc .= "     * @queryParam filter[{$transformedName}] No-example\n"; // Filtro original
                $doc .= "     * @queryParam filter[{$transformedName}After] No-example\n";
                $doc .= "     * @queryParam filter[{$transformedName}Before] No-example\n";
                $doc .= "     * @queryParam filter[{$transformedName}Between] No-example\n";
                $doc .= "     * @queryParam filter[{$transformedName}CurrentDay] No-example\n";
                $doc .= "     * @queryParam filter[{$transformedName}CurrentWeek] No-example\n";
                $doc .= "     * @queryParam filter[{$transformedName}CurrentMonth] No-example\n";
                $doc .= "     * @queryParam filter[{$transformedName}LastDays] No-example\n";
                $doc .= "     * @queryParam filter[{$transformedName}NextDays] No-example\n";
            } else {
                if (in_array($filter['type'], ['boolean'])) {
                    $transformedName = Str::ucfirst(Str::camel($filter['name'])); // Nome transformado
                    $doc .= "     * @queryParam filter[{$name}] No-example\n";
                    $doc .= "     * @queryParam filter[{$transformedName}] No-example\n"; // Filtro original
                    $doc .= "     * @queryParam filter[Not{$transformedName}] No-example\n";
                } else {
                    // Filtro normal
                    $doc .= "     * @queryParam filter[{$name}] No-example\n";
                }
            }
        }
        if ($timestamps) {
            $transformedName = Str::ucfirst(Str::camel('created_at')); // Nome transformado
            $doc .= "     * @queryParam filter[{$transformedName}] No-example\n"; // Filtro original
            $doc .= "     * @queryParam filter[{$transformedName}After] No-example\n";
            $doc .= "     * @queryParam filter[{$transformedName}Before] No-example\n";
            $doc .= "     * @queryParam filter[{$transformedName}Between] No-example\n";
            $doc .= "     * @queryParam filter[{$transformedName}CurrentDay] No-example\n";
            $doc .= "     * @queryParam filter[{$transformedName}CurrentWeek] No-example\n";
            $doc .= "     * @queryParam filter[{$transformedName}CurrentMonth] No-example\n";
            $doc .= "     * @queryParam filter[{$transformedName}LastDays] No-example\n";
            $doc .= "     * @queryParam filter[{$transformedName}NextDays] No-example\n";

            $transformedName = Str::ucfirst(Str::camel('updated_at')); // Nome transformado
            $doc .= "     * @queryParam filter[{$transformedName}] No-example\n"; // Filtro original
            $doc .= "     * @queryParam filter[{$transformedName}After] No-example\n";
            $doc .= "     * @queryParam filter[{$transformedName}Before] No-example\n";
            $doc .= "     * @queryParam filter[{$transformedName}Between] No-example\n";
            $doc .= "     * @queryParam filter[{$transformedName}CurrentDay] No-example\n";
            $doc .= "     * @queryParam filter[{$transformedName}CurrentWeek] No-example\n";
            $doc .= "     * @queryParam filter[{$transformedName}CurrentMonth] No-example\n";
            $doc .= "     * @queryParam filter[{$transformedName}LastDays] No-example\n";
            $doc .= "     * @queryParam filter[{$transformedName}NextDays] No-example\n";
        }
        if ($softDeletes) {
            $transformedName = Str::ucfirst(Str::camel('deleted_at')); // Nome transformado
            $doc .= "     * @queryParam filter[{$transformedName}] No-example\n"; // Filtro original
            $doc .= "     * @queryParam filter[{$transformedName}After] No-example\n";
            $doc .= "     * @queryParam filter[{$transformedName}Before] No-example\n";
            $doc .= "     * @queryParam filter[{$transformedName}Between] No-example\n";
            $doc .= "     * @queryParam filter[{$transformedName}CurrentDay] No-example\n";
            $doc .= "     * @queryParam filter[{$transformedName}CurrentWeek] No-example\n";
            $doc .= "     * @queryParam filter[{$transformedName}CurrentMonth] No-example\n";
            $doc .= "     * @queryParam filter[{$transformedName}LastDays] No-example\n";
            $doc .= "     * @queryParam filter[{$transformedName}NextDays] No-example\n";
        }
        if ($useIsActive) {
            //WithTrashed,OnlyTrashed
            $transformedName = 'IsActive'; // Nome transformado
            $doc .= "     * @queryParam filter[{$transformedName}] No-example\n"; // Filtro original

            $transformedName = 'IsNotActive'; // Nome transformado
            $doc .= "     * @queryParam filter[{$transformedName}] No-example\n"; // Filtro original
        }

        if ($useApprovedStatus) {
            //WithTrashed,OnlyTrashed
            $transformedName = 'Analisys'; // Nome transformado
            $doc .= "     * @queryParam filter[{$transformedName}] No-example\n"; // Filtro original

            $transformedName = 'Approved'; // Nome transformado
            $doc .= "     * @queryParam filter[{$transformedName}] No-example\n"; // Filtro original
            $transformedName = 'Unapproved'; // Nome transformado
            $doc .= "     * @queryParam filter[{$transformedName}] No-example\n"; // Filtro original
            $transformedName = 'Blocked'; // Nome transformado
            $doc .= "     * @queryParam filter[{$transformedName}] No-example\n"; // Filtro original
            $transformedName = 'Canceled'; // Nome transformado
            $doc .= "     * @queryParam filter[{$transformedName}] No-example\n"; // Filtro original

        }


        if ($softDeletes) {
            //WithTrashed,OnlyTrashed
            $transformedName = 'WithTrashed'; // Nome transformado
            $doc .= "     * @queryParam filter[{$transformedName}] No-example\n"; // Filtro original

            $transformedName = 'OnlyTrashed'; // Nome transformado
            $doc .= "     * @queryParam filter[{$transformedName}] No-example\n"; // Filtro original
        }
        $transformedName = 'Search'; // Nome transformado
        $doc .= "     * @queryParam filter[{$transformedName}] No-example\n"; // Filtro original

        // Gerar a linha do sort
        if (!empty($sortableAttributes)) {
            $sortableNames = array_map(function ($attr) {
                return $attr['name'];
            }, $sortableAttributes);
            $doc .= "     * @queryParam sort Allowed: " . implode(",", $sortableNames) . " Classifica a lista. Para classificação descendente coloque o - na frente(-id) No-example\n";
        }

        $doc .= "     */\n";

        return $doc;
    }

    protected function generateApiShowDocumentation($relations, $logsActivity, $useIsActive, $useApprovedStatus)
    {
        // Include: lista de relacionamentos (se existirem)
        $includeRelations = !empty($relations) ? array_map(function ($relation) {
            return $relation['name'];
        }, $relations) : [];
        if ($useIsActive) {
            $includeRelations[] = 'activeMotives';
        }
        if ($useApprovedStatus) {
            $includeRelations[] = 'approvedMotives';
        }

        if ($logsActivity) {
            $includeRelations[] = 'activities';
        }

        $doc = "*";
        if (!empty($includeRelations)) {
            $doc = "* @queryParam include Allowed: " . implode(",", $includeRelations) . " Relacionamentos que podem ser incluídos na resposta No-example";
        }
        return $doc;
    }

    protected function generateStoreTranslatedCode($attributes)
    {

        $translatedAttributes = array_filter($attributes, function ($attr) {
            return isset($attr['translated']) && $attr['translated'] === true;
        });

        $translatedCode = "// TRANSLATEDS\n";

        foreach ($translatedAttributes as $attribute) {
            $name = $attribute['name'];

            // Gerar código dinamicamente para atributos traduzíveis
            $translatedCode .= "        if (!empty(\$request->{$name})) {\n";
            $translatedCode .= "            \$translated = TranslatorGoogle::translate(\$request->{$name});\n";
            $translatedCode .= "            \$data['{$name}'] = \$translated['original_text'];\n";
            $translatedCode .= "            \$data['{$name}_translated'] = \$translated['translations'];\n";
            $translatedCode .= "            \$data['original_locale'] = \$translated['source_language'];\n";
            $translatedCode .= "        }\n";
        }

        return $translatedCode;
    }

    protected function generateTranslatedWithComparisonCode($attributes)
    {

        $translatedAttributes = array_filter($attributes, function ($attr) {
            return isset($attr['translated']) && $attr['translated'] === true;
        });

        $translatedCode = "";

        foreach ($translatedAttributes as $attribute) {
            $name = $attribute['name'];
            $nameTranslatedVar = "{$name}Translateds";

            // Gerar código dinamicamente para atributos traduzíveis
            $translatedCode .= "\${$nameTranslatedVar} = [];\n";
            $translatedCode .= "        if (!empty(\$request->{$name}) && (\$request->{$name} != \$item->{$name})) {\n";
            $translatedCode .= "            \$translated = TranslatorGoogle::translate(\$request->{$name});\n";
            $translatedCode .= "            \$data['{$name}'] = \$translated['original_text'];\n";
            $translatedCode .= "            \${$nameTranslatedVar} = \$translated['translations'];\n";
            $translatedCode .= "            \$data['original_locale'] = \$translated['source_language'];\n";
            $translatedCode .= "        }\n        ";
        }

        return $translatedCode;
    }

    protected function generateCreateIsActiveApprovedCode($model, $useIsActive, $useApprovedStatus)
    {

        $saveCode = "\$motive = 'Registro Inicial';\n";

        if ($useIsActive) {
            $saveCode .= "            if (\$item->is_active) {\n";
            $saveCode .= "                StatusService::active(\$item, \$motive, Auth::user()->id);\n";
            $saveCode .= "            } else {\n";
            $saveCode .= "                StatusService::deactive(\$item, \$motive, Auth::user()->id);\n";
            $saveCode .= "            }\n";
        }
        if ($useApprovedStatus) {
            $saveCode .= "            StatusService::analisys(\$item, \$motive, Auth::user()->id);\n";
        }
        return $saveCode;
    }

    protected function generateSaveTranslationsCode($attributes)
    {

        $translatedAttributes = array_filter($attributes, function ($attr) {
            return isset($attr['translated']) && $attr['translated'] === true;
        });

        $saveTranslationsCode = "";

        foreach ($translatedAttributes as $attribute) {
            $name = $attribute['name'];
            $nameTranslatedVar = "{$name}Translateds";

            // Gerar código dinamicamente para salvar as traduções
            $saveTranslationsCode .= "if (\${$nameTranslatedVar}) {\n";
            $saveTranslationsCode .= "                \$item->setTranslations('{$name}_translated', \${$nameTranslatedVar});\n";
            $saveTranslationsCode .= "                \$item->save();\n";
            $saveTranslationsCode .= "            }\n            ";
        }

        return $saveTranslationsCode;
    }
}
