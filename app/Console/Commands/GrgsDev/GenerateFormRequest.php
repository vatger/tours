<?php

namespace App\Console\Commands\GrgsDev;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateFormRequest extends Command
{
    protected $signature = 'grgsdev:formrequest {model}';
    protected $description = 'Generate Form Requests based on a JSON file';

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
        $version = $jsonContent['version'] ?? 'V1';
        $softDeletes = $jsonContent['softDeletes'] ?? false; // Verifica se softDeletes está no JSON
        $useIsActive = $jsonContent['useIsActive'] ?? false;
        $useApprovedStatus = $jsonContent['useApprovedStatus'] ?? false;

        // Gerar Form Requests
        $this->generateFormRequests($modelName, $attributes, $relations, $version, $softDeletes, $useIsActive, $useApprovedStatus);


        $this->info("Form Requests for {$modelName} generated successfully.");
    }


    protected function generateFormRequests($modelName, $attributes, $relations, $version, $softDeletes, $useIsActive, $useApprovedStatus)
    {
        $hasFile = false;
        $hasImage = false;
        $hasVideo = false;
        if ($useIsActive) {
            $this->generateChangeIsActiveRequest($modelName, $version);
        }

        if ($useApprovedStatus) {
            $this->generateChangeApprovedStatusRequest($modelName, $version);
        }
        foreach ($attributes as $attribute) {
            if ($attribute['type'] === 'file') {
                $hasFile = true;
                $this->generateChangeRequest($modelName, $attribute, $version);
            }
            if ($attribute['type'] === 'image' || $attribute['type'] === 'video') {
                $hasImage = true;
                $this->generateChangeRequest($modelName, $attribute, $version);
            }
            if ($attribute['type'] === 'video') {
                $hasVideo = true;
                $this->generateChangeRequest($modelName, $attribute, $version);
            }
        }
        $imageGet = '';
        $videoGet = '';
        $fileGet = '';
        if ($hasImage) {
            $imageGet = "\$supportedImageMimes = implode(',', config('image-file.supported_images'));";
        }
        if ($hasVideo) {
            $videoGet = "\$supportedVideoMimes = implode(',', config('image-file.supported_videos'));";
        }
        if ($hasFile) {
            $fileGet = "\$supportedFileMimes = implode(',', config('image-file.supported_files'));";
        }
        // Gerar Store Request
        $this->generateRequest('Store', $modelName, $attributes, $relations, $version, $softDeletes, $imageGet, $videoGet, $fileGet, $useIsActive, $useApprovedStatus);
        // Gerar Update Request
        $this->generateRequest('Update', $modelName, $attributes, $relations, $version, $softDeletes, $imageGet, $videoGet, $fileGet, $useIsActive, $useApprovedStatus);
    }

    protected function generateRequest($type, $modelName, $attributes, $relations, $version, $softDeletes, $imageGet, $videoGet, $fileGet, $useIsActive, $useApprovedStatus)
    {
        $className = "{$type}{$modelName}Request";
        $namespace = "App\\Http\\Requests\\Api\\{$version}\\{$modelName}";

        // Caminho do arquivo
        $filePath = app_path("Http/Requests/Api/{$version}/{$modelName}/{$className}.php");

        // Definir as regras baseadas nos atributos e relações
        $rules = $this->generateRules($modelName, $attributes, $relations, $type, $softDeletes);
        $bodyParameters = $this->generateBodyParameters($attributes, $relations);
        $prepareForValidationBoolean = $this->generatePrepareForValidationBoolean($type, $attributes, $relations, $useIsActive, $useApprovedStatus);
        $prepareForValidationDate = $this->generatePrepareForValidationDate($attributes, $relations);
        // Template da classe do Form Request
        $classTemplate = <<<EOD
<?php

namespace {$namespace};

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\\$modelName;
use App\Services\DateService;
/**
 * @version {$version}
 */
class {$className} extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        {$imageGet}
        {$videoGet}
        {$fileGet}
        return {$rules};
    }

    protected function prepareForValidation()
    {
        \$this->merge([
            {$prepareForValidationBoolean}
        ]);

        {$prepareForValidationDate}
    }

    public function bodyParameters()
    {
        return [
            {$bodyParameters}
        ];
    }
}
EOD;

        // Criar diretório, se não existir
        if (!File::isDirectory(dirname($filePath))) {
            File::makeDirectory(dirname($filePath), 0755, true);
        }

        // Escrever o arquivo
        File::put($filePath, $classTemplate);
        $this->info("Form Request {$className} gerado com sucesso.");
    }


    protected function generateChangeRequest($modelName, $attribute, $version)
    {
        if ($attribute['type'] === 'image') {
            $supported = "\$supportedImageMimes = implode(',', config('image-file.supported_images'));";
            $rule = "'required|image|mimes:' . \$supportedImageMimes . '|max:2048',";
        }
        if ($attribute['type'] === 'video') {
            $supported = "\$supportedVideoMimes = implode(',', config('image-file.supported_videos'));";
            $rule = "'required|file|mimes:' . \$supportedVideoMimes . '|max:2048',";
        }

        if ($attribute['type'] === 'file') {
            $supported = "\$supportedFileMimes = implode(',', config('image-file.supported_files'));";
            $rule = "'required|file|mimes:' . \$supportedFileMimes . '|max:25600',";
        }
        $description = $attribute['description'] ?? 'No description provided.';

        $attributeCamel = Str::ucfirst(Str::camel($attribute['name']));
        $className = "Change{$modelName}{$attributeCamel}Request";
        $namespace = "App\\Http\\Requests\\Api\\{$version}\\{$modelName}";

        // Caminho do arquivo
        $filePath = app_path("Http/Requests/Api/{$version}/{$modelName}/{$className}.php");
        // Template da classe do Form Request
        $classTemplate = <<<EOD
<?php

namespace {$namespace};

use Illuminate\Foundation\Http\FormRequest;

/**
 * @version {$version} generated
 */
class {$className} extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        {$supported}

        return [
            '{$attribute['name']}' => {$rule}
        ];
    }

    public function bodyParameters()
    {
        return [
            '{$attribute['name']}' => [
                'description' => '{$description}',
            ],
        ];
    }
}
EOD;

        // Criar diretório, se não existir
        if (!File::isDirectory(dirname($filePath))) {
            File::makeDirectory(dirname($filePath), 0755, true);
        }

        // Escrever o arquivo
        File::put($filePath, $classTemplate);
        $this->info("Form Request {$className} gerado com sucesso.");
    }

    protected function generateChangeIsActiveRequest($modelName, $version)
    {
        $attributeCamel = Str::ucfirst(Str::camel('is_active'));
        $className = "Change{$modelName}{$attributeCamel}Request";
        $namespace = "App\\Http\\Requests\\Api\\{$version}\\{$modelName}";

        // Caminho do arquivo
        $filePath = app_path("Http/Requests/Api/{$version}/{$modelName}/{$className}.php");
        // Template da classe do Form Request
        $classTemplate = <<<EOD
<?php

namespace  {$namespace};

use Illuminate\Foundation\Http\FormRequest;

class {$className} extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'motive' => 'nullable|string',
        ];
    }

    public function bodyParameters()
    {
        return [
            'motive' => [
                'description' => 'Descrição do motivo.',
                'example' => 'Não apresentou a documentação',
            ],
        ];
    }
}
EOD;

        // Criar diretório, se não existir
        if (!File::isDirectory(dirname($filePath))) {
            File::makeDirectory(dirname($filePath), 0755, true);
        }

        // Escrever o arquivo
        File::put($filePath, $classTemplate);
        $this->info("Form Request {$className} gerado com sucesso.");
    }

    protected function generateChangeApprovedStatusRequest($modelName, $version)
    {
        $attributeCamel = Str::ucfirst(Str::camel('approved_status'));
        $className = "Change{$modelName}{$attributeCamel}Request";
        $namespace = "App\\Http\\Requests\\Api\\{$version}\\{$modelName}";

        // Caminho do arquivo
        $filePath = app_path("Http/Requests/Api/{$version}/{$modelName}/{$className}.php");
        // Template da classe do Form Request
        $classTemplate = <<<EOD
<?php

namespace  {$namespace};

use Illuminate\Foundation\Http\FormRequest;

class {$className} extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'motive' => 'nullable|string',
        ];
    }

    public function bodyParameters()
    {
        return [
            'motive' => [
                'description' => 'Descrição do motivo.',
                'example' => 'Não apresentou a documentação',
            ],
        ];
    }
}
EOD;

        // Criar diretório, se não existir
        if (!File::isDirectory(dirname($filePath))) {
            File::makeDirectory(dirname($filePath), 0755, true);
        }

        // Escrever o arquivo
        File::put($filePath, $classTemplate);
        $this->info("Form Request {$className} gerado com sucesso.");
    }

    protected function generateBodyParameters($attributes, $relations)
    {
        $bodyParams = [];
        $body = '';
        foreach ($attributes as $attribute) {
            if (isset($attribute['validate']) && !$attribute['validate']) {
                continue;
            }

            $description = $attribute['description'] ?? 'No description provided.';
            $example = $attribute['example'] ?? 'No example provided.';
            $name = $attribute['name'];
            $texts = [];
            if (!in_array($attribute['type'], ['morphs', 'nullableMorphs', 'numericMorphs', 'nullableNumericMorphs', 'uuidMorphs', 'nullableUuidMorphs', 'ulidMorphs', 'nullableUlidMorphs'])) {
                if ($attribute['type'] === 'image' || $attribute['type'] === 'video' || $attribute['type'] === 'file') {
                    $body .= "'{$name}' => [
                    'description' => '{$description}',
                ],\n            ";
                } else {
                    $body .= "'{$name}' => [
                        'description' => '{$description}',
                        'example' => '{$example}'
                ],\n            ";
                }
            }
        }

        // Adicionar as regras das relações
        foreach ($relations as $relation) {
            if ($relation['type'] === 'belongsTo') {
                $description = $relation['description'] ?? 'No description provided.';
                $example = $relation['example'] ?? 'No example provided.';
                $nameRelation = $relation['name'] . '_id';
                $body .= "'{$nameRelation}' => [
                    'description' => '{$description}',
                    'example' => '{$example}'
            ],\n            ";
            } elseif ($relation['type'] === 'belongsToMany') {
                $description = $relation['description'] ?? 'No description provided.';
                $example = $relation['example'] ?? 'No example provided.';
                $nameRelation = $relation['name'] . '.*';
                $body .= "'{$nameRelation}' => [
                    'description' => '{$description}',
                    'example' => '{$example}'
            ],\n            ";
            }
        }

        return $body;
    }

    protected function generatePrepareForValidationBoolean($type, $attributes, $relations, $useIsActive, $useApprovedStatus)
    {
        $forValidation = '';
        foreach ($attributes as $attribute) {
            if (isset($attribute['validate']) && !$attribute['validate']) {
                continue;
            }

            if ($attribute['type'] === 'boolean') {
                $name = $attribute['name'];
                $forValidation .= "'$name' => \$this->boolean('$name'),\n            ";
            }
        }
        if ($type === 'Store') {
            if ($useIsActive) {
                $name = 'is_active';
                $forValidation .= "'$name' => 0,\n            ";
            }
            if ($useApprovedStatus) {
                $name = 'approved_status';
                $forValidation .= "'$name' => '1',\n            ";
            }
        }

        return $forValidation;
    }

    protected function generatePrepareForValidationDate($attributes, $relations)
    {
        $forValidation = '';
        foreach ($attributes as $attribute) {
            if (isset($attribute['validate']) && !$attribute['validate']) {
                continue;
            }

            $name = $attribute['name'];

            // Controle para atributos do tipo date, datetime, timestamp ou time
            if (in_array($attribute['type'], ['date', 'timestamp', 'timeStamp', 'datetime', 'dateTime'])) {
                $parsedFunction = $attribute['type'] === 'time' ? 'parseTime' : 'parseDate'; // Determina qual função usar
                $camelName = Str::ucfirst(Str::camel($name));
                // Geração do código para o atributo
                $forValidation .= "if (\$this->input('$name')) {\n";
                $forValidation .= "            \$parsed{$camelName} = DateService::{$parsedFunction}(\$this->input('$name'));\n";
                $forValidation .= "            if (\$parsed{$camelName}) {\n";
                $forValidation .= "                \$this->merge([\n";
                $forValidation .= "                    '$name' => \$parsed{$camelName}->setTimezone('UTC')->toISOString(),\n";
                $forValidation .= "                ]);\n";
                $forValidation .= "            } else {\n";
                $forValidation .= "                \$this->merge([\n";
                $forValidation .= "                    '$name' => 'error date', // handle error case appropriately\n";
                $forValidation .= "                ]);\n";
                $forValidation .= "            }\n";
                $forValidation .= "        }\n\n        ";
            }
        }

        return $forValidation;
    }

    protected function generateRules($modelName, $attributes, $relations, $type, $softDeletes)
    {
        $rules = [];

        foreach ($attributes as $attribute) {
            if (isset($attribute['validate']) && !$attribute['validate']) {
                continue;
            }
            $name = $attribute['name'];
            $attributeType = $attribute['type'];
            if (!in_array($attributeType, ['morphs', 'nullableMorphs', 'numericMorphs', 'nullableNumericMorphs', 'uuidMorphs', 'nullableUuidMorphs', 'ulidMorphs', 'nullableUlidMorphs'])) {
                if ($type === 'Update') {
                    $required = 'sometimes|nullable';
                } else {
                    $required = $attribute['required'] ? 'required' : 'nullable';
                }

                $unique = $attribute['unique'] ?? false;
                $nullable = $attribute['nullable'] ? 'nullable' : 'required';
                $rule = $required . '|';
                switch ($attributeType) {
                    case 'integer':
                    case 'bigInteger':
                    case 'tinyInteger':
                    case 'smallInteger':
                    case 'mediumInteger':
                    case 'unsignedInteger':
                    case 'unsignedBigInteger':
                    case 'unsignedTinyInteger':
                    case 'unsignedSmallInteger':
                    case 'unsignedMediumInteger':
                        $rule .= 'integer';
                        if (!empty($attribute['min'])) {
                            $rule .= "|min:{$attribute['min']}";
                        }
                        if (!empty($attribute['max'])) {
                            $rule .= "|max:{$attribute['max']}";
                        }
                        break;

                    case 'boolean':
                        $rule .= 'boolean';
                        break;

                    case 'decimal':
                    case 'float':
                    case 'double':
                        $precision = $attribute['precision'] ?? 8;
                        $scale = $attribute['scale'] ?? 2;
                        $rule .= "numeric";
                        $rule .= "|digits_between:0,{$precision}";
                        if (!empty($scale)) {
                            $rule .= "regex:/^\d+(\.\d{1,{$scale}})?$/";
                        }
                        break;

                    case 'string':
                    case 'char':
                        $length = $attribute['length'] ?? 255;
                        $values = $attribute['values'] ?? '';
                        $rule .= "string";
                        $rule .= "|max:$length";
                        if ($values) {
                            $rule .= '|in:' . implode(',', $values);
                        }


                        break;

                    case 'email':
                        $length = $attribute['length'] ?? 255;
                        $rule .= "email";
                        $rule .= "|max:$length";
                        break;

                    case 'text':
                    case 'mediumText':
                    case 'longText':
                        $rule .= 'string';
                        break;

                    case 'enum':
                        $rule .= 'in:' . implode(',', $attribute['values']);
                        break;

                    case 'date':
                    case 'datetime':
                    case 'timestamp':
                    case 'dateTime':
                    case 'timeStamp':

                        $rule .= 'date';
                        break;

                    case 'time':
                        $rule .= 'date';
                        break;

                    case 'json':
                    case 'jsonb':
                        $rule .= 'json';
                        break;

                    case 'uuid':
                        $rule .= 'uuid';
                        break;

                    case 'binary':
                        $rule .= 'string'; // Laravel não tem regra explícita para binary, trata como string.
                        break;

                    case 'file':
                        $length = $attribute['length'] ?? 25600;
                        $rule .= 'file';
                        $rule .= "|mimes:' . \$supportedFileMimes . ";
                        $rule .= "'|max:$length";
                        break;

                    case 'image':
                        $length = $attribute['length'] ?? 2048;
                        $rule .= 'image';
                        $rule .= "|mimes:' . \$supportedImageMimes . ";

                        // $rule .= '|mimes:jpg,jpeg,png,svg,ifc,dwg,dxf';
                        $rule .= "'|max:$length";
                        break;
                    case 'video':
                        $length = $attribute['length'] ?? 2048;
                        $rule .= 'file';
                        $rule .= "|mimes:' . \$supportedVideoMimes . ";

                        // $rule .= '|mimes:jpg,jpeg,png,svg,ifc,dwg,dxf';
                        $rule .= "'|max:$length";
                        break;

                    case 'ipAddress':
                        $rule .= 'ip';
                        break;

                    case 'macAddress':
                        $rule .= 'mac_address';
                        break;

                    case 'geometry':
                    case 'point':
                    case 'linestring':
                    case 'polygon':
                    case 'geometryCollection':
                    case 'multipoint':
                    case 'multilinestring':
                    case 'multipolygon':
                        // Não há regras de validação específicas para tipos geométricos no Laravel.
                        break;


                    default:
                        $rule = "{$nullable}";
                        break;
                }
                //|' . Rule::unique(Company::class)->whereNull('deleted_at')->ignore($this->company_id)
                if ($unique) {
                    if ($type === 'Update') {
                        $modelSnakedId = Str::snake($modelName) . '_id';
                        if ($softDeletes) {
                            $rule .= "|' . Rule::unique({$modelName}::class)->whereNull('deleted_at')->ignore(\$this->{$modelSnakedId})";
                        } else {
                            $rule .= "|' . Rule::unique({$modelName}::class)->ignore(\$this->{$modelSnakedId})";
                        }
                    } else {
                        $rule .= "|' . Rule::unique({$modelName}::class,'{$name}')";
                    }

                    // $rule .= "|unique:{$modelName}::class,{$name}";
                } else {
                    $rule .= "'";
                }

                $rules[] = "'{$name}' => '{$rule}";
            }
        }

        // Adicionar as regras das relações
        foreach ($relations as $relation) {
            $tableName = Str::snake(Str::pluralStudly($relation['related']));
            if ($relation['type'] === 'belongsTo') {
                $rules[] = "'{$relation['name']}_id' => 'required|exists:{$tableName},id'";
            } elseif ($relation['type'] === 'belongsToMany') {
                $rules[] = "'{$relation['name']}' => 'nullable|array'";
                $rules[] = "'{$relation['name']}.*' => 'exists:{$tableName},id'";
            }
        }

        return "[\n            " . implode(",\n            ", $rules) . "\n        ]";
    }
}
