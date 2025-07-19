<?php

namespace App\Console\Commands\GrgsDev;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class GenerateModelContract extends Command
{
    protected $signature = 'grgsdev:model {model}';
    protected $description = 'Generate Abstract Model and Concret Model(if not exists) based on a JSON file';

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

        $version = $jsonContent['version'] ?? 'V1';
        $logsActivity = $jsonContent['logsActivity'] ?? false;
        $clearsResponseCache = $jsonContent['clearsResponseCache'] ?? false;
        // Gerar o Model Contract abstract class
        $this->generateModelContract($jsonContent, $modelName, $version, $logsActivity, $clearsResponseCache);
    }

    /////////////////////

    protected function generateModelContract($data, $modelName, $version, $logsActivity, $clearsResponseCache)
    {
        $attributes = $data['attributes'];
        $relations = $data['relations'] ?? [];
        $softDeletes = $data['softDeletes'] ?? false; // Verifica se softDeletes está no JSON
        $timestamps = $data['timestamps'] ?? false;
        $useIsActive = $data['useIsActive'] ?? false;
        $useApprovedStatus = $data['useApprovedStatus'] ?? false;

        $hasImages = false;
        $imageConsts = '';
        $fileConsts = '';

        $fillable = [];
        $casts = [];
        $appends = [];
        $relationMethods = [];
        $hasSearchAble = false;
        $hasTranslated = false;
        $hasBoolean = false;
        // Prepara os atributos
        foreach ($attributes as $attribute) {
            $name = $attribute['name'];
            $type = $attribute['type'];
            if ($type === 'boolean') {
                $hasBoolean = true;
            }
            if (isset($attribute['translated']) && $attribute['translated']) {
                $hasTranslated = true;
            }
            if (isset($attribute['searchAble']) && $attribute['searchAble']) {
                $hasSearchAble = true;
            }
            if (!in_array($type, ['morphs', 'nullableMorphs', 'numericMorphs', 'nullableNumericMorphs', 'uuidMorphs', 'nullableUuidMorphs', 'ulidMorphs', 'nullableUlidMorphs'])) {
                $casts[$name] = $this->getCastType($type, $attribute);
            }

            if ($type === 'image' || $type === 'video' || $type === 'file') {
                $fillable[] = "'$name',";

                $nameImageAux = $name . '_file_name';
                $fillable[] = "'$nameImageAux',";
                $nameImageAux = $name . '_file_size';
                $fillable[] = "'$nameImageAux',";
                $nameImageAux = $name . '_file_extension';
                $fillable[] = "'$nameImageAux',";

                $hasImages = true;
                $snakeModel = Str::of($modelName)->snake(); //snake(strtolower($modelName));
                $upperName = strtoupper($name);
                $appends[] = "'{$name}_url'";
                $imageConsts .= "const {$upperName}_STORAGE = '{$type}/{$snakeModel}_{$name}';\n    ";
            } else {
                if (in_array($type, ['morphs', 'nullableMorphs', 'numericMorphs', 'nullableNumericMorphs', 'uuidMorphs', 'nullableUuidMorphs', 'ulidMorphs', 'nullableUlidMorphs'])) {
                    $nameAux = $name . '_id';
                    $fillable[] = "'$nameAux',";
                    $nameAux = $name . '_type';
                    $fillable[] = "'$nameAux',";
                } else {
                    $fillable[] = "'$name',";
                }
            }
        }
        $useTranslated = '';
        $useHasTranslations = '';
        if ($hasTranslated) {
            $fillable[] = "'original_locale',";
            $casts['original_locale'] = 'string';
            $useTranslated = "use LaravelLang\Models\HasTranslations;";
            $useHasTranslations = "use HasTranslations;";
        }
        if ($useIsActive) {
            $imageConsts .= "const IS_ACTIVE_TRUE = '1';\n    ";
            $imageConsts .= "const IS_ACTIVE_FALSE = '0';\n    ";
            $imageConsts .= "const IS_ACTIVE_TEXT = ['0' => 'INACTIVE', '1' => 'ACTIVE'];\n    ";

            $fillable[] = "'is_active',";
            $casts['is_active'] = 'boolean';
            $appends[] = "'is_active_text'";
        }
        if ($useApprovedStatus) {
            $imageConsts .= "const APPROVED_STATUS_ANALISYS = '1';\n    ";
            $imageConsts .= "const APPROVED_STATUS_APPROVED = '2';\n    ";
            $imageConsts .= "const APPROVED_STATUS_UNAPPROVED = '3';\n    ";
            $imageConsts .= "const APPROVED_STATUS_BLOCKED = '4';\n    ";
            $imageConsts .= "const APPROVED_STATUS_CANCELED = '5';\n    ";
            $imageConsts .= "const APPROVED_STATUS_TEXT = ['1' => 'ANALISYS', '2' => 'APPROVED', '3' => 'UNAPPROVED', '4' => 'BLOCKED', '5' => 'CANCELED'];\n    ";

            $fillable[] = "'approved_status',";
            $casts['approved_status'] = 'string';
            $appends[] = "'approved_status_text'";
        }
        if ($softDeletes) {
            $fillable[] = "'deleted_by_parent',";
            $casts['deleted_by_parent'] = 'boolean';
        } else {
            $fillable[] = "'deleted_by_parent',";
            $casts['deleted_by_parent'] = 'boolean';
        }
        //prepara scopes
        $scopeSearch = '';
        if ($hasSearchAble) {
            $scopeSearch = $this->generateSearchScope($attributes, $timestamps, $softDeletes, $useIsActive, $useApprovedStatus);
        }
        $scopeBoolean = '';
        if ($hasBoolean) {
            $scopeBoolean = $this->generateBooleanScopes($attributes);
        }


        $scopeActiveApproved = $this->generateActiveApprovedScopes($useIsActive, $useApprovedStatus);
        $scopeDate = $this->generateDateScopes($attributes, $timestamps, $softDeletes, $useIsActive, $useApprovedStatus);

        $allowedFilters = $this->generateAllowedFilters($attributes, $timestamps, $softDeletes, $hasSearchAble, $useIsActive, $useApprovedStatus);
        $allowedIncludes = $this->generateAllowedIncludes($relations, $logsActivity, $useIsActive, $useApprovedStatus);
        $defaultIncludes = $this->generateDefaultIncludes($relations, $logsActivity, $useIsActive, $useApprovedStatus);
        $allowedSorts = $this->generateAllowedSorts($attributes, $timestamps, $softDeletes, $useIsActive);
        $defaultSorts = $this->generateDefaultSorts($attributes);

        $useRelations = '';
        // Prepara os relacionamentos
        foreach ($relations as $relation) {
            $relationName = $relation['name'];
            $relationType = $relation['type'];
            $relatedModel = $relation['related'];
            $relationMethods[] = $this->generateRelationMethod($relationName, $relationType, $relatedModel);
            if ($relatedModel) {
                $useRelations .= "use App\Models\\$relatedModel;\n";
            }
        }

        $useRelationsIsActiveApprovedStatus = '';
        $useRelationsIsActive = "";
        $useRelationsApprovedStatus = "";
        if ($useIsActive) {
            $useRelationsIsActive = "use App\Models\ActiveHistoric;\n";
        }
        if ($useApprovedStatus) {
            $useRelationsApprovedStatus = "use App\Models\ApproveHistoric;\n";
        }

        $useRelationsIsActiveApprovedStatus = $this->generateRelationIsActiveApprovedStatus($useIsActive, $useApprovedStatus);

        // Define o uso do SoftDeletes
        $softDeletesTrait = $softDeletes ? 'use SoftDeletes;' : '';
        $softDeletesImport = $softDeletes ? "use Illuminate\\Database\\Eloquent\\SoftDeletes;" : '';
        $cascadeSoftDeletesTrait = $softDeletes ? 'use CascadeSoftDeletes;' : '';
        $cascadeSoftDeletesImport = $softDeletes ? "use App\\Traits\\CascadeSoftDeletes;" : '';

        //Define o uso do logs activity
        $logsActivityOptionsImport = $logsActivity ? "use Spatie\\Activitylog\\LogOptions;" : '';
        $logsActivityImport = $logsActivity ? "use Spatie\\Activitylog\\Traits\LogsActivity;" : '';
        $logsActivityTrait = $logsActivity ? 'use LogsActivity;' : '';

        //Define o uso de clears cache
        $clearsResponseCacheTraitImport = $clearsResponseCache ? "use App\\Traits\\ClearsResponseCache;" : '';
        $clearsResponseCacheTrait = $clearsResponseCache ? 'use ClearsResponseCache;' : '';

        $storageImport = $hasImages ? "use Illuminate\\Support\\Facades\\Storage;" : '';

        // Construa o conteúdo do modelo
        $modelContent = <<<EOT
    <?php

    namespace App\Models\AbstractContracts;

    use App\Services\DateService;
    use Spatie\QueryBuilder\AllowedFilter;
    use Illuminate\Database\Eloquent\Builder;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use App\Traits\HasDateScopes;
    $useTranslated
    $useRelationsIsActive
    $useRelationsApprovedStatus
    $useRelations
    $softDeletesImport
    $cascadeSoftDeletesImport
    $logsActivityImport
    $logsActivityOptionsImport
    $clearsResponseCacheTraitImport
    $storageImport
    /**
     * @version {$version}
     */
    abstract class {$modelName} extends Model
    {
        use HasFactory;
        $useHasTranslations
        $softDeletesTrait
        $cascadeSoftDeletesTrait
        $logsActivityTrait
        $clearsResponseCacheTrait
        $imageConsts
        $fileConsts

        use HasDateScopes;
        protected \$dateFields = ['created_at', 'updated_at'];
        protected \$cascadeDeletes = [];

        \n    protected \$fillable = [
    EOT;
        $modelContent .= "\n        ";
        // Concatenar fillable manualmente

        $modelContent .= implode("\n        ", $fillable);

        $modelContent .= <<<EOT
        \n    ];

        protected \$casts = [
    EOT;

        // Adicionar os casts manualmente
        foreach ($casts as $key => $value) {
            $modelContent .= "\n        '$key' => '$value',";
        }

        $modelContent .= "\n    ];\n\n    // APPENDS\n";

        $modelContent .= "    protected \$appends = [\n        ";

        // Adicionar os appends manualmente
        $modelContent .= implode(",\n        ", $appends);

        $modelContent .= "\n    ];\n\n";
        if ($useIsActive) {
            $modelContent .= $this->generateAppendActiveMethod();
            $modelContent .= "\n\n";
        }
        if ($useApprovedStatus) {
            $modelContent .= $this->generateAppendApprovedMethod();
            $modelContent .= "\n\n";
        }
        // Adicionando a função para image URL
        foreach ($attributes as $attribute) {
            if ($attribute['type'] === 'image' || $attribute['type'] === 'video' || $attribute['type'] === 'file') {
                $name = $attribute['name'];
                $modelContent .= $this->generateImageUrlMethod($name);
                $modelContent .= "\n\n";
            }
        }

        // Adicionando os métodos de relacionamento
        if ($relationMethods || $useIsActive || $useApprovedStatus) {
            $modelContent .= "\n    // INCLUDES\n    ";
        }
        $modelContent .= implode("\n\n    ", $relationMethods);
        $modelContent .= $useRelationsIsActiveApprovedStatus;
        // Adiciona scopes para SoftDeletes se for habilitado
        if ($softDeletes) {
            $modelContent .= "\n        ";
            $modelContent .= <<<EOT

        // SCOPES
        {$scopeSearch}
        {$scopeBoolean}
        {$scopeActiveApproved}
        {$scopeDate}

        public function scopeWithTrashed(\$query)
        {
            return \$query->withTrashed();
        }

        public function scopeOnlyTrashed(\$query)
        {
            return \$query->onlyTrashed();
        }

        // QUERY BUILDER
        {$allowedFilters}
        {$allowedIncludes}
        {$allowedSorts}
        {$defaultSorts}
        {$defaultIncludes}

    EOT;
        } else {
            $modelContent .= "\n        ";
            $modelContent .= <<<EOT

        // SCOPES
        {$scopeSearch}
        {$scopeBoolean}
        {$scopeActiveApproved}
        {$scopeDate}

        // QUERY BUILDER
        {$allowedFilters}
        {$allowedIncludes}
        {$allowedSorts}
        {$defaultSorts}
        {$defaultIncludes}

    EOT;
        }
        // Adiciona implementação para activity logs
        if ($logsActivity) {
            $modelContent .= "\n        ";
            $modelContent .= <<<EOT

        // ACTYVITY LOGS
        public function getActivitylogOptions(): LogOptions
        {
            return LogOptions::defaults()->logFillable()->dontSubmitEmptyLogs();
        }

    EOT;
        }
        $modelContent .= "\n}\n";
        // Garante que o diretório 'Contracts' existe
        $contractsPath = app_path('Models/AbstractContracts');
        File::ensureDirectoryExists($contractsPath);
        $abstractModelPath = "{$contractsPath}/{$modelName}.php";
        File::put($abstractModelPath, $modelContent);

        if ($hasTranslated) {
            $this->generateModelTranslation($attributes, $modelName, $version);
        }
        // Gerar o Model concret class
        $this->generateModel($modelName, $version);
    }

    protected function getCastType($type, $attribute)
    {
        switch ($type) {
            case 'bigIncrements':
            case 'bigInteger':
            case 'increments':
            case 'integer':
            case 'mediumIncrements':
            case 'mediumInteger':
            case 'smallIncrements':
            case 'smallInteger':
            case 'tinyIncrements':
            case 'tinyInteger':
            case 'unsignedBigInteger':
            case 'unsignedInteger':
            case 'unsignedMediumInteger':
            case 'unsignedSmallInteger':
            case 'unsignedTinyInteger':
            case 'year':
                return 'integer';

            case 'decimal':
            case 'unsignedDecimal':
                $precision = $attribute['precision'] ?? 8;
                $scale = $attribute['scale'] ?? 2;

                return "decimal:{$precision},{$scale}";

            case 'float':
            case 'double':
                return 'float'; // 'double' também pode ser usado aqui dependendo do uso

            case 'boolean':
                return 'boolean';

            case 'string':
            case 'char':
            case 'text':
            case 'mediumText':
            case 'longText':
            case 'enum':
            case 'uuid':
                return 'string';

            case 'json':
            case 'jsonb':
                return 'json'; // ou 'array' ou 'object' dependendo do uso

            case 'date':
                return 'date';

            case 'datetime':
            case 'dateTime':
            case 'dateTimeTz':
                return 'datetime';

            case 'timestamp':
            case 'timeStamp':
            case 'timestampTz':
                return 'datetime';

            case 'time':
            case 'timeTz':
                return 'string';

            case 'ipAddress':
                return 'ipAddress';

            case 'macAddress':
                return 'macAddress';

            case 'binary':
            case 'geometry':
            case 'geometryCollection':
            case 'lineString':
            case 'multiLineString':
            case 'multiPoint':
            case 'multiPolygon':
            case 'point':
            case 'polygon':
                return null; // Não há cast nativo para tipos geométricos

            default:
                return 'string'; // Um fallback padrão para tipos não especificados
        }
    }

    protected function generateImageUrlMethod($attribute)
    {
        $capitalLetter = ucwords($attribute);
        $upperName = strtoupper($attribute);

        return <<<EOT
    public function get{$capitalLetter}UrlAttribute()
    {
        \$storage = self::{$upperName}_STORAGE . '/';
        if (!\$this->{$attribute}) {
            return null; // asset('noimage.png');
        }
        return Storage::url(\$storage . \$this->{$attribute});
    }
EOT;
    }

    protected function generateAppendActiveMethod()
    {
        return <<<EOT
    public function getIsActiveTextAttribute()
    {
        return  __(self::IS_ACTIVE_TEXT[\$this->is_active]);
    }
EOT;
    }

    protected function generateAppendApprovedMethod()
    {
        return <<<EOT
    public function getApprovedStatusTextAttribute()
    {
        return  __(self::APPROVED_STATUS_TEXT[\$this->approved_status]);
    }
EOT;
    }

    protected function generateSearchScope($attributes, $timestamps, $softDeletes)
    {
        $scopeSearch = "
    public function scopeSearch(Builder \$builder, string \$search)
    {
        \$parsedDate = DateService::parseDate(\$search);

        if (\$parsedDate) {
            \$builder->where(function (\$query) use (\$parsedDate) {";

        // Adiciona as condições para os atributos do tipo date, timestamp ou datetime
        foreach ($attributes as $attribute) {
            if (isset($attribute['searchAble']) && $attribute['searchAble'] && in_array($attribute['type'], ['date', 'timestamp', 'timeStamp', 'datetime', 'dateTime'])) {
                $name = $attribute['name'];
                $scopeSearch .= "
                \$query->whereDate('$name', \$parsedDate->format('Y-m-d'));";
            }
        }
        if ($timestamps) {
            $name = 'created_at';
            $scopeSearch .= "
                \$query->whereDate('$name', \$parsedDate->format('Y-m-d'));";
            $name = 'updated_at';
            $scopeSearch .= "
                \$query->whereDate('$name', \$parsedDate->format('Y-m-d'));";
        }
        if ($softDeletes) {
            $name = 'deleted_at';
            $scopeSearch .= "
                \$query->whereDate('$name', \$parsedDate->format('Y-m-d'));";
        }
        // Finaliza a parte da busca por datas completas
        $scopeSearch .= "
            });
        } else {
            \$builder->where(function (\$query) use (\$search) {
                // Busca parcial de datas (exemplo: dia/mês ou apenas mês)
                if (preg_match('/^\\d{1,2}\\/\\d{1,2}\$/', \$search)) {
                    // Exemplo: 29/03 (dia/mês)
                    list(\$day, \$month) = explode('/', \$search);";

        // Adiciona as condições para os atributos do tipo date, timestamp ou datetime para busca parcial de dia/mês
        foreach ($attributes as $attribute) {
            if (isset($attribute['searchAble']) && $attribute['searchAble'] && in_array($attribute['type'], ['date', 'timestamp', 'timeStamp', 'datetime', 'dateTime'])) {
                $name = $attribute['name'];
                $scopeSearch .= "
                    \$query->whereMonth('$name', \$month)
                            ->whereDay('$name', \$day);";
            }
        }
        if ($timestamps) {
            $name = 'created_at';
            $scopeSearch .= "
                    \$query->whereMonth('$name', \$month)
                            ->whereDay('$name', \$day);";
            $name = 'updated_at';
            $scopeSearch .= "
                    \$query->whereMonth('$name', \$month)
                            ->whereDay('$name', \$day);";
        }
        if ($softDeletes) {
            $name = 'deleted_at';
            $scopeSearch .= "
                    \$query->whereMonth('$name', \$month)
                            ->whereDay('$name', \$day);";
        }

        // Busca parcial por dia
        $scopeSearch .= "
                } elseif (preg_match('/^\\d{1,2}\\/\\$/', \$search)) {
                    // Exemplo: 29/ (somente dia)
                    \$day = str_replace('/', '', \$search);";

        foreach ($attributes as $attribute) {
            if (isset($attribute['searchAble']) && $attribute['searchAble'] && in_array($attribute['type'], ['date', 'timestamp', 'timeStamp', 'datetime', 'dateTime'])) {
                $name = $attribute['name'];
                $scopeSearch .= "
                    \$query->whereDay('$name', \$day);";
            }
        }
        if ($timestamps) {
            $name = 'created_at';
            $scopeSearch .= "
                    \$query->whereDay('$name', \$day);";

            $name = 'updated_at';
            $scopeSearch .= "
                    \$query->whereDay('$name', \$day);";
        }
        if ($softDeletes) {
            $name = 'deleted_at';
            $scopeSearch .= "
                    \$query->whereDay('$name', \$day);";
        }
        // Busca parcial por mês
        $scopeSearch .= "
                } elseif (preg_match('/^\\/\\d{1,2}\$/', \$search)) {
                    // Exemplo: /05 (somente mês)
                    \$month = str_replace('/', '', \$search);";

        foreach ($attributes as $attribute) {
            if (isset($attribute['searchAble']) && $attribute['searchAble'] && in_array($attribute['type'], ['date', 'timestamp', 'timeStamp', 'datetime', 'dateTime'])) {
                $name = $attribute['name'];
                $scopeSearch .= "
                    \$query->whereMonth('$name', \$month);";
            }
        }
        if ($timestamps) {
            $name = 'created_at';
            $scopeSearch .= "
                    \$query->whereMonth('$name', \$month);";

            $name = 'updated_at';
            $scopeSearch .= "
                    \$query->whereMonth('$name', \$month);";
        }
        if ($softDeletes) {
            $name = 'deleted_at';
            $scopeSearch .= "
                    \$query->whereMonth('$name', \$month);";
        }
        // Busca padrão por strings (como nome, email, etc.)
        $scopeSearch .= "
                } else {";

        foreach ($attributes as $attribute) {
            if (isset($attribute['searchAble']) && $attribute['searchAble'] && !in_array($attribute['type'], ['date', 'timestamp', 'timeStamp', 'datetime', 'dateTime'])) {
                $name = $attribute['name'];
                $scopeSearch .= "
                    \$query->orWhere('$name', 'LIKE', \"%\$search%\");";
                if (isset($attribute['translated']) && $attribute['translated']) {
                    $nameTranslated = $name . '_translated';
                    $scopeSearch .= "
                    \$query->orWhereHas('translations', fn(\$q) => \$q->where('$nameTranslated', 'LIKE', \"%\$search%\"));";
                }
            }
        }

        // Finaliza o método scopeSearch
        $scopeSearch .= "
                }
            });
        }
    }";

        return $scopeSearch;
    }

    protected function generateDateScopes($attributes, $timestamps, $softDeletes)
    {
        $scopes = '';
        if ($timestamps) {
            $attributeUpdatedAt = [
                "name" => "updated_at",
                "type" => "timestamp",
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
            array_unshift($attributes, $attributeUpdatedAt);
            $attributeCreatedAt = [
                "name" => "created_at",
                "type" => "timestamp",
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
            array_unshift($attributes, $attributeCreatedAt);
        }
        if ($softDeletes) {
            $attributeDeleteddAt = [
                "name" => "deleted_at",
                "type" => "timestamp",
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
            array_unshift($attributes, $attributeDeleteddAt);
        }

        foreach ($attributes as $attribute) {
            if (in_array($attribute['type'], ['date', 'timestamp', 'timeStamp', 'datetime', 'dateTime'])) {
                // $name = ucfirst($attribute['name']);
                $name = Str::ucfirst(Str::camel($attribute['name']));

                // Scope for the exact date
                $scopes .= "
    public function scope{$name}(Builder \$builder, \$date)
    {
        return \$this->applyDateScope(\$builder, '{$attribute['name']}', 'At', \$date);
    }

    public function scope{$name}Before(Builder \$builder, \$date)
    {
        return \$this->applyDateScope(\$builder, '{$attribute['name']}', 'AtBefore', \$date);
    }

    public function scope{$name}After(Builder \$builder, \$date)
    {
        return \$this->applyDateScope(\$builder, '{$attribute['name']}', 'AtAfter', \$date);
    }

    public function scope{$name}Between(Builder \$builder, \$startDate, \$endDate)
    {
        return \$this->applyDateScope(\$builder, '{$attribute['name']}', 'AtBetween', \$startDate, \$endDate);
    }

    public function scope{$name}CurrentDay(Builder \$builder)
    {
        return \$this->applyDateScope(\$builder, '{$attribute['name']}', 'AtCurrentDay');
    }

    public function scope{$name}CurrentWeek(Builder \$builder)
    {
        return \$this->applyDateScope(\$builder, '{$attribute['name']}', 'AtCurrentWeek');
    }

    public function scope{$name}CurrentMonth(Builder \$builder)
    {
        return \$this->applyDateScope(\$builder, '{$attribute['name']}', 'AtCurrentMonth');
    }

    public function scope{$name}LastDays(Builder \$builder, int \$days)
    {
        return \$this->applyDateScope(\$builder, '{$attribute['name']}', 'AtLastDays', \$days);
    }

    public function scope{$name}NextDays(Builder \$builder, int \$days)
    {
        return \$this->applyDateScope(\$builder, '{$attribute['name']}', 'AtNextDays', \$days);
    }
        ";
            }
        }

        return $scopes;
    }

    protected function generateBooleanScopes($attributes)
    {
        $scopes = '';
        foreach ($attributes as $attribute) {
            if ($attribute['type'] === 'boolean') {
                $attr = $attribute['name'];
                $name = Str::ucfirst(Str::camel($attr));
                $scopes .= "
    public function scope{$name}(Builder \$query): void
    {
        \$query->where('{$attribute['name']}', 1);
    }

    ";

                $attr = 'not_' . $attribute['name'];
                $name = Str::ucfirst(Str::camel($attr));

                // Scope for is NOT active
                $scopes .= "
    public function scope{$name}(Builder \$query): void
    {
        \$query->where('{$attribute['name']}', 0);
    }
        ";
            }
        }

        return $scopes;
    }


    protected function generateActiveApprovedScopes($useIsActive, $useApprovedStatus)
    {
        $scopes = '';
        if ($useIsActive) {
            $attr = 'is_active';
            $name = Str::ucfirst(Str::camel($attr));

            // Scope for is active
            $scopes .= "
    public function scopeIsActive(Builder \$query): void
    {
        \$query->where('is_active', 1);
    }

    ";

            $attr = 'is_not_active';
            $name = Str::ucfirst(Str::camel($attr));

            // Scope for is NOT active
            $scopes .= "
    public function scopeIsNotActive(Builder \$query): void
    {
        \$query->where('is_active', 0);
    }
        ";
        }

        if ($useApprovedStatus) {
            $attr = 'analisys';
            $name = Str::ucfirst(Str::camel($attr));
            $scopes .= "
    public function scopeAnalisys(Builder \$query): void
    {
        \$query->where('approved_status', \$this::APPROVED_STATUS_ANALISYS);
    }
    ";
            $attr = 'approved';
            $name = Str::ucfirst(Str::camel($attr));
            $scopes .= "
    public function scopeApproved(Builder \$query): void
    {
        \$query->where('approved_status', \$this::APPROVED_STATUS_APPROVED);
    }
        ";
            $attr = 'unapproved';
            $name = Str::ucfirst(Str::camel($attr));
            $scopes .= "
    public function scopeUnapproved(Builder \$query): void
    {
        \$query->where('approved_status', \$this::APPROVED_STATUS_UNAPPROVED);
    }
    ";
            $attr = 'blocked';
            $name = Str::ucfirst(Str::camel($attr));
            $scopes .= "
    public function scopeBlocked(Builder \$query): void
    {
        \$query->where('approved_status', \$this::APPROVED_STATUS_BLOCKED);
    }
    ";
            $attr = 'canceled';
            $name = Str::ucfirst(Str::camel($attr));
            $scopes .= "
    public function scopeCanceled(Builder \$query): void
    {
        \$query->where('approved_status', \$this::APPROVED_STATUS_CANCELED);
    }
    ";
        }

        return $scopes;
    }

    protected function generateAllowedFilters($attributes, $timestamps, $softDelete, $hasSearchAble, $useIsActive, $useApprovedStatus)
    {
        $allowedFilters = "
    /**
     * The allowed filters attributes.
     * ";

        // Lista os atributos filtráveis
        $filterAttributes = [];
        $filterAttributes[] = 'id';
        foreach ($attributes as $attribute) {
            if (isset($attribute['filterAble']) && $attribute['filterAble']) {
                if (isset($attribute['exactFilter']) && $attribute['exactFilter']) {
                    $filterAttributes[] = $attribute['name'];
                    if (isset($attribute['type']) && in_array($attribute['type'], ['boolean'])) {
                        $filterAttributes[] = Str::ucfirst(Str::camel($attribute['name']));
                        $filterAttributes[] = 'Not' . Str::ucfirst(Str::camel($attribute['name']));
                    }
                } elseif (
                    isset($attribute['searchAble']) && $attribute['searchAble'] &&
                    (isset($attribute['type']) && in_array($attribute['type'], ['date', 'timestamp', 'timeStamp', 'datetime', 'dateTime']))
                ) {
                    $filterAttributes[] = Str::ucfirst(Str::camel($attribute['name']));
                    $filterAttributes[] = Str::ucfirst(Str::camel($attribute['name'])) . 'Before';
                    $filterAttributes[] = Str::ucfirst(Str::camel($attribute['name'])) . 'After';
                    $filterAttributes[] = Str::ucfirst(Str::camel($attribute['name'])) . 'Between';
                    $filterAttributes[] = Str::ucfirst(Str::camel($attribute['name'])) . 'CurrentDay';
                    $filterAttributes[] = Str::ucfirst(Str::camel($attribute['name'])) . 'CurrentWeek';
                    $filterAttributes[] = Str::ucfirst(Str::camel($attribute['name'])) . 'CurrentMonth';
                    $filterAttributes[] = Str::ucfirst(Str::camel($attribute['name'])) . 'LastDays';
                    $filterAttributes[] = Str::ucfirst(Str::camel($attribute['name'])) . 'NextDays';
                } else {
                    $filterAttributes[] = $attribute['name'];
                }
            }
        }

        if ($useIsActive) {
            $attr = 'is_active';
            $filterAttributes[] = Str::ucfirst(Str::camel($attr));
            $attr = 'is_not_active';
            $filterAttributes[] = Str::ucfirst(Str::camel($attr));
        }

        if ($useApprovedStatus) {
            $attr = 'analisys';
            $filterAttributes[] = Str::ucfirst(Str::camel($attr));
            $attr = 'approved';
            $filterAttributes[] = Str::ucfirst(Str::camel($attr));
            $attr = 'unapproved';
            $filterAttributes[] = Str::ucfirst(Str::camel($attr));
            $attr = 'blocked';
            $filterAttributes[] = Str::ucfirst(Str::camel($attr));
            $attr = 'canceled';
            $filterAttributes[] = Str::ucfirst(Str::camel($attr));
        }


        if ($timestamps) {
            $attr = 'created_at';
            $filterAttributes[] = Str::ucfirst(Str::camel($attr));
            $filterAttributes[] = Str::ucfirst(Str::camel($attr)) . 'Before';
            $filterAttributes[] = Str::ucfirst(Str::camel($attr)) . 'After';
            $filterAttributes[] = Str::ucfirst(Str::camel($attr)) . 'Between';
            $filterAttributes[] = Str::ucfirst(Str::camel($attr)) . 'CurrentDay';
            $filterAttributes[] = Str::ucfirst(Str::camel($attr)) . 'CurrentWeek';
            $filterAttributes[] = Str::ucfirst(Str::camel($attr)) . 'CurrentMonth';
            $filterAttributes[] = Str::ucfirst(Str::camel($attr)) . 'LastDays';
            $filterAttributes[] = Str::ucfirst(Str::camel($attr)) . 'NextDays';
            $attr = 'updated_at';
            $filterAttributes[] = Str::ucfirst(Str::camel($attr));
            $filterAttributes[] = Str::ucfirst(Str::camel($attr)) . 'Before';
            $filterAttributes[] = Str::ucfirst(Str::camel($attr)) . 'After';
            $filterAttributes[] = Str::ucfirst(Str::camel($attr)) . 'Between';
            $filterAttributes[] = Str::ucfirst(Str::camel($attr)) . 'CurrentDay';
            $filterAttributes[] = Str::ucfirst(Str::camel($attr)) . 'CurrentWeek';
            $filterAttributes[] = Str::ucfirst(Str::camel($attr)) . 'CurrentMonth';
            $filterAttributes[] = Str::ucfirst(Str::camel($attr)) . 'LastDays';
            $filterAttributes[] = Str::ucfirst(Str::camel($attr)) . 'NextDays';
        }
        if ($softDelete) {
            $attr = 'deleted_at';
            $filterAttributes[] = Str::ucfirst(Str::camel($attr));
            $filterAttributes[] = Str::ucfirst(Str::camel($attr)) . 'Before';
            $filterAttributes[] = Str::ucfirst(Str::camel($attr)) . 'After';
            $filterAttributes[] = Str::ucfirst(Str::camel($attr)) . 'Between';
            $filterAttributes[] = Str::ucfirst(Str::camel($attr)) . 'CurrentDay';
            $filterAttributes[] = Str::ucfirst(Str::camel($attr)) . 'CurrentWeek';
            $filterAttributes[] = Str::ucfirst(Str::camel($attr)) . 'CurrentMonth';
            $filterAttributes[] = Str::ucfirst(Str::camel($attr)) . 'LastDays';
            $filterAttributes[] = Str::ucfirst(Str::camel($attr)) . 'NextDays';
            $filterAttributes[] = 'WithTrashed';
            $filterAttributes[] = 'OnlyTrashed';
        }
        if ($hasSearchAble) {
            $filterAttributes[] = 'Search';
        }
        // Adiciona os atributos filtráveis no comentário da função
        if (!empty($filterAttributes)) {
            $allowedFilters .= implode(',', $filterAttributes);
        } else {
            $allowedFilters .= "No filterable attributes found.";
        }

        // Fecha o comentário e define o retorno da função
        $allowedFilters .= "
     */
    public static function ALLOWEDFILTERS()
    {
        return [";
        $nameId = 'id';
        $allowedFilters .= "
            AllowedFilter::exact('{$nameId}'),";
        foreach ($attributes as $attribute) {
            if (isset($attribute['filterAble']) && $attribute['filterAble']) {
                $name = $attribute['name'];
                $type = $attribute['type'];
                $scopeName = Str::ucfirst(Str::camel($attribute['name']));
                // Verifica se o tipo é date, timestamp ou datetime e se tem searchAble
                if (in_array($type, ['date', 'timestamp', 'timeStamp', 'datetime', 'dateTime']) && isset($attribute['searchAble']) && $attribute['searchAble']) {
                    $allowedFilters .= "
            AllowedFilter::scope('{$scopeName}'),
            AllowedFilter::scope('{$scopeName}Before'),
            AllowedFilter::scope('{$scopeName}After'),
            AllowedFilter::scope('{$scopeName}Between'),
            AllowedFilter::scope('{$scopeName}CurrentDay'),
            AllowedFilter::scope('{$scopeName}CurrentWeek'),
            AllowedFilter::scope('{$scopeName}CurrentMonth'),
            AllowedFilter::scope('{$scopeName}LastDays'),
            AllowedFilter::scope('{$scopeName}NextDays'),";
                } elseif (isset($attribute['exactFilter']) && $attribute['exactFilter']) {
                    // Se tem exactFilter true, usa o filtro exato
                    $allowedFilters .= "
            AllowedFilter::exact('{$name}'),";
                    if (in_array($type, ['boolean'])) {
                        $scopeName = Str::ucfirst(Str::camel($attribute['name']));
                        $allowedFilters .= "
            AllowedFilter::scope('{$scopeName}'),
            AllowedFilter::scope('Not{$scopeName}'),";
                    }
                } else {
                    // Caso não tenha exactFilter ou seja false, usa partial
                    $allowedFilters .= "
            AllowedFilter::partial('{$name}'),";
                }
            }
        }

        if ($useIsActive) {
            $attr = 'is_active';
            $scopeName = Str::ucfirst(Str::camel($attr));
            $allowedFilters .= "
            AllowedFilter::scope('{$scopeName}'),";
            $attr = 'is_not_active';
            $scopeName = Str::ucfirst(Str::camel($attr));
            $allowedFilters .= "
            AllowedFilter::scope('{$scopeName}'),";
        }

        if ($useApprovedStatus) {
            $attr = 'analisys';
            $scopeName = Str::ucfirst(Str::camel($attr));
            $allowedFilters .= "
            AllowedFilter::scope('{$scopeName}'),";
            $attr = 'approved';
            $scopeName = Str::ucfirst(Str::camel($attr));
            $allowedFilters .= "
            AllowedFilter::scope('{$scopeName}'),";
            $attr = 'unapproved';
            $scopeName = Str::ucfirst(Str::camel($attr));
            $allowedFilters .= "
            AllowedFilter::scope('{$scopeName}'),";
            $attr = 'blocked';
            $scopeName = Str::ucfirst(Str::camel($attr));
            $allowedFilters .= "
            AllowedFilter::scope('{$scopeName}'),";
            $attr = 'canceled';
            $scopeName = Str::ucfirst(Str::camel($attr));
            $allowedFilters .= "
            AllowedFilter::scope('{$scopeName}'),";
        }


        if ($timestamps) {
            $attr = 'created_at';
            $scopeName = Str::ucfirst(Str::camel($attr));
            $allowedFilters .= "
            AllowedFilter::scope('{$scopeName}'),
            AllowedFilter::scope('{$scopeName}Before'),
            AllowedFilter::scope('{$scopeName}After'),
            AllowedFilter::scope('{$scopeName}Between'),
            AllowedFilter::scope('{$scopeName}CurrentDay'),
            AllowedFilter::scope('{$scopeName}CurrentWeek'),
            AllowedFilter::scope('{$scopeName}CurrentMonth'),
            AllowedFilter::scope('{$scopeName}LastDays'),
            AllowedFilter::scope('{$scopeName}NextDays'),";
            $attr = 'updated_at';
            $scopeName = Str::ucfirst(Str::camel($attr));
            $allowedFilters .= "
            AllowedFilter::scope('{$scopeName}'),
            AllowedFilter::scope('{$scopeName}Before'),
            AllowedFilter::scope('{$scopeName}After'),
            AllowedFilter::scope('{$scopeName}Between'),
            AllowedFilter::scope('{$scopeName}CurrentDay'),
            AllowedFilter::scope('{$scopeName}CurrentWeek'),
            AllowedFilter::scope('{$scopeName}CurrentMonth'),
            AllowedFilter::scope('{$scopeName}LastDays'),
            AllowedFilter::scope('{$scopeName}NextDays'),";
        }
        // Adiciona filtros padrão como Search e OnlyTrashed
        if ($softDelete) {
            $attr = 'deleted_at';
            $scopeName = Str::ucfirst(Str::camel($attr));
            $allowedFilters .= "
            AllowedFilter::scope('{$scopeName}'),
            AllowedFilter::scope('{$scopeName}Before'),
            AllowedFilter::scope('{$scopeName}After'),
            AllowedFilter::scope('{$scopeName}Between'),
            AllowedFilter::scope('{$scopeName}CurrentDay'),
            AllowedFilter::scope('{$scopeName}CurrentWeek'),
            AllowedFilter::scope('{$scopeName}CurrentMonth'),
            AllowedFilter::scope('{$scopeName}LastDays'),
            AllowedFilter::scope('{$scopeName}NextDays'),";
            $allowedFilters .= "
            AllowedFilter::scope('WithTrashed'),
            AllowedFilter::scope('OnlyTrashed'),
    ";
        }
        if ($hasSearchAble) {
            $allowedFilters .= "
            AllowedFilter::scope('Search'),
    ";
        }

        $allowedFilters .= "
        ];
    }";


        return $allowedFilters;
    }

    protected function generateAllowedSorts($attributes, $timestamps, $softDeletes, $useIsActive)
    {
        // Inicializa o comentário com uma explicação e uma lista de atributos
        $allowedSorts = "
    /**
     * The allowed sorts attributes.
     * ";

        // Lista os atributos sortáveis no comentário
        $sortAttributes = [];
        $sortAttributes[] = 'id';


        foreach ($attributes as $attribute) {
            if (isset($attribute['sortAble']) && $attribute['sortAble']) {
                $sortAttributes[] = $attribute['name'];
            }
        }
        if ($useIsActive) {
            $sortAttributes[] = 'is_active';
        }
        if ($timestamps) {
            $sortAttributes[] = 'created_at';
            $sortAttributes[] = 'updated_at';
        }
        if ($softDeletes) {
            $sortAttributes[] = 'deleted_at';
        }

        // Adiciona os atributos sortáveis no comentário da função
        if (!empty($sortAttributes)) {
            $allowedSorts .= implode(',', $sortAttributes);
        } else {
            $allowedSorts .= "No sortable attributes found.";
        }

        // Fecha o comentário e define o retorno da função
        $allowedSorts .= "
     */
    public static function ALLOWEDSORTS()
    {
        return [";

        // Adiciona os atributos sortáveis no retorno da função
        foreach ($sortAttributes as $name) {
            $allowedSorts .= "'{$name}', ";
        }

        // Remove a última vírgula e espaço, e fecha o array da função
        $allowedSorts = rtrim($allowedSorts, ', ') . "];
    }";

        return $allowedSorts;
    }

    protected function generateDefaultSorts($attributes)
    {
        // Inicializa o comentário com uma explicação e uma lista de atributos
        $allowedSorts = "
    /**
     * The Default sorts attributes.
     * ";

        // Lista os atributos sortáveis no comentário
        $sortAttributes = [];
        foreach ($attributes as $attribute) {
            if (isset($attribute['sortAble']) && $attribute['sortAble']) {
                $sortAttributes[] = $attribute['name'];
            }
        }

        // Adiciona os atributos sortáveis no comentário da função
        if (!empty($sortAttributes)) {
            $allowedSorts .= implode(',', $sortAttributes);
        } else {
            $allowedSorts .= "No default sortable attributes found.";
        }

        // Fecha o comentário e define o retorno da função
        $allowedSorts .= "
     */
    public static function DEFAULTSORT()
    {
        return [";

        // Adiciona os atributos sortáveis no retorno da função
        foreach ($sortAttributes as $name) {
            $allowedSorts .= "'{$name}', ";
        }

        // Remove a última vírgula e espaço, e fecha o array da função
        $allowedSorts = rtrim($allowedSorts, ', ') . "];
    }";

        return $allowedSorts;
    }

    protected function generateAllowedIncludes($relations, $logsActivity, $useIsActive, $useApprovedStatus)
    {
        // Inicializa o comentário com uma explicação e uma lista de atributos
        $allowedIncludes = "
    /**
     * The allowed includes relationships.
     * ";

        // Lista os atributos no comentário
        $sortAttributes = [];
        foreach ($relations as $relation) {
            $sortAttributes[] = $relation['name'];
        }
        if ($useIsActive) {
            $sortAttributes[] = 'activeMotives';
        }
        if ($useApprovedStatus) {
            $sortAttributes[] = 'approvedMotives';
        }

        if ($logsActivity) {
            $sortAttributes[] = 'activities';
        }
        // Adiciona os atributos no comentário da função
        if (!empty($sortAttributes)) {
            $allowedIncludes .= implode(',', $sortAttributes);
        } else {
            $allowedIncludes .= "No includes relationships found.";
        }

        // Fecha o comentário e define o retorno da função
        $allowedIncludes .= "
     */
    public static function ALLOWEDINCLUDES()
    {
        return [";

        // Adiciona os atributos sortáveis no retorno da função
        foreach ($sortAttributes as $name) {
            $allowedIncludes .= "'{$name}', ";
        }

        // Remove a última vírgula e espaço, e fecha o array da função
        $allowedIncludes = rtrim($allowedIncludes, ', ') . "];
    }";

        return $allowedIncludes;
    }

    protected function generateDefaultIncludes($relations, $logsActivity, $useIsActive, $useApprovedStatus)
    {
        // Inicializa o comentário com uma explicação e uma lista de atributos
        $allowedIncludes = "
    /**
     * The default includes relationships.
     * ";

        // Lista os atributos no comentário
        $sortAttributes = [];
        foreach ($relations as $relation) {
            if (isset($relation['default']) && $relation['default']) {
                $sortAttributes[] = $relation['name'];
            }
        }

        // Adiciona os atributos no comentário da função
        if (!empty($sortAttributes)) {
            $allowedIncludes .= implode(',', $sortAttributes);
        } else {
            $allowedIncludes .= "No default includes relationships found.";
        }

        // Fecha o comentário e define o retorno da função
        $allowedIncludes .= "
     */
    public static function DEFAULTINCLUDES()
    {
        return [";

        // Adiciona os atributos sortáveis no retorno da função
        foreach ($sortAttributes as $name) {
            $allowedIncludes .= "'{$name}', ";
        }

        // Remove a última vírgula e espaço, e fecha o array da função
        $allowedIncludes = rtrim($allowedIncludes, ', ') . "];
    }";

        return $allowedIncludes;
    }


    protected function generateRelationMethod($name, $type, $related)
    {
        switch ($type) {
            case 'belongsTo':
                return "public function {$name}() { return \$this->belongsTo({$related}::class); }";

            case 'hasOne':
                return "public function {$name}() { return \$this->hasOne({$related}::class); }";

            case 'hasMany':
                return "public function {$name}() { return \$this->hasMany({$related}::class); }";

            case 'belongsToMany':
                return "public function {$name}() { return \$this->belongsToMany({$related}::class); }";

            case 'morphTo':
                return "public function {$name}() { return \$this->morphTo(); }";

            case 'morphMany':
                return "public function {$name}() { return \$this->morphMany({$related}::class); }";

            case 'morphOne':
                return "public function {$name}() { return \$this->morphOne({$related}::class); }";

            case 'morphToMany':
                return "public function {$name}() { return \$this->morphToMany({$related}::class); }";

            default:
                return "// Relação não suportada: $type";
        }
    }

    protected function generateRelationIsActiveApprovedStatus($useIsActive, $useApprovedStatus)
    {
        $include = '';
        if ($useIsActive) {
            $include .= "
    public function activeMotives()
    {
        return \$this->morphMany(ActiveHistoric::class, 'subject');
    }

            ";
        }

        if ($useApprovedStatus) {
            $include .= "
    public function approvedMotives()
    {
        return \$this->morphMany(ApproveHistoric::class, 'subject');
    }
            ";
        }
        return $include;
    }


    ////////////////////

    protected function generateModel($model, $version)
    {
        $modelPath = app_path("Models/{$model}.php");

        // Verifica se o modelo concreto já existe
        if (!File::exists($modelPath)) {
            $modelContent = <<<PHP
    <?php

    namespace App\Models;

    use App\Models\AbstractContracts\\{$model} as AbstractContracts{$model};

    /**
     * @version {$version}
     */
    class {$model} extends AbstractContracts{$model}
    {
        // Outros métodos e propriedades da classe concreta
    }

    PHP;
            // $path = app_path('Models');
            // File::ensureDirectoryExists($path);

            File::put($modelPath, $modelContent);
            $this->info("Model {$model} gerado com sucesso.");
        }
    }

    protected function generateModelTranslation($attributes, $model, $version)
    {
        $modelPath = app_path("Models/{$model}Translation.php");
        $fillable[] = "'locale',";
        foreach ($attributes as $attribute) {
            $name = $attribute['name'] . '_translated';

            if (isset($attribute['translated']) && $attribute['translated']) {
                $fillable[] = "'{$name}',";
                $casts[$name] = "TrimCast::class";
            }
        }
        // Verifica se o modelo já existe
        if (!File::exists($modelPath)) {
            // Construa o conteúdo do modelo
            $modelContent = <<<EOT
    <?php

    namespace App\Models;

    use LaravelLang\Models\Casts\TrimCast;
    use LaravelLang\Models\Eloquent\Translation;

    /**
     * @version {$version}
     */
    class {$model}Translation extends Translation
    {
        \n    protected \$fillable = [
    EOT;
            $modelContent .= "\n        ";
            // Concatenar fillable manualmente

            $modelContent .= implode("\n        ", $fillable);

            $modelContent .= <<<EOT
        \n    ];

        protected \$casts = [
    EOT;
            // Adicionar os casts manualmente
            foreach ($casts as $key => $value) {
                $modelContent .= "\n        '$key' => $value,";
            }

            $modelContent .= "\n    ];";

            $modelContent .= "\n}\n";

            File::put($modelPath, $modelContent);
            $this->info("Model {$model}Translation gerado com sucesso.");
        }
    }
}
