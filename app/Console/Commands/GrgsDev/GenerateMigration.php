<?php

namespace App\Console\Commands\GrgsDev;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class GenerateMigration extends Command
{
    protected $signature = 'grgsdev:migration {model}';
    protected $description = 'Generate Migration based on a JSON file';

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
        // Gerar a Migration
        $this->generateMigration($jsonContent, $modelName, $version);
    }


    protected function generateMigration(array $modelData, string $modelName, $version)
    {
        $tableName = Str::snake(Str::pluralStudly($modelName));

        $attributes = $modelData['attributes'];
        $relations = $modelData['relations'] ?? [];
        $softDeletes = $modelData['softDeletes'] ?? false;
        $timestamps = $modelData['timestamps'] ?? false;
        $useIsActive = $modelData['useIsActive'] ?? false;
        $useApprovedStatus = $modelData['useApprovedStatus'] ?? false;

        $migrationContent = $this->generateMigrationContent($attributes, $relations, $modelName, $tableName, $softDeletes, $timestamps, $version, $useIsActive, $useApprovedStatus);

        $timestamp = now()->format('Y_m_d_His');
        $migrationFileName = "{$timestamp}_create_{$tableName}_table.php";

        File::put(database_path("migrations/{$migrationFileName}"), $migrationContent);
        $this->info("Migration {$migrationFileName} gerado com sucesso.");
    }

    protected function generateMigrationContent(array $attributes, array $relations, string $ModelName, string $tableName, bool $softDeletes, bool $timestamps, $version, bool $useIsActive, bool $useApprovedStatus)
    {
        $fields = $this->generateFields($attributes);
        $isActiveStr = $useIsActive ? "\$table->boolean('is_active')->nullable()->default(0);" : '';
        $approvedStatusStr = $useApprovedStatus ? "\$table->string('approved_status')->nullable()->default('1');" : '';
        $relationsFields = $this->generateRelationFields($relations);
        $softDeletesStr = $softDeletes ? "\$table->softDeletes();" : '';
        $softDeletesParent = $softDeletes ? "\$table->boolean('deleted_by_parent')->default(0);" : "\$table->boolean('deleted_by_parent')->default(0);";
        $timestampsStr = $timestamps ? "\$table->timestamps();" : '';
        $translatedSchema = $this->generateTranslationsSchema($ModelName, $attributes);
        $dropTranslatedSchema = '';
        $useModel = '';
        if ($translatedSchema) {
            $table = Str::snake($ModelName) . '_translations';
            $dropTranslatedSchema = "Schema::dropIfExists('{$table}');";
            $useModel = "use App\Models\\{$ModelName};";
        }
        $migrationTemplate = <<<EOT
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
{$useModel}

return new class extends Migration
{
    /**
     * Run the migrations.
     * @version {$version}
     * @return void
     */
    public function up()
    {
        Schema::create('{$tableName}', function (Blueprint \$table) {
            \$table->id();

            {$fields}
            {$isActiveStr}
            {$approvedStatusStr}
            {$relationsFields}
            {$softDeletesParent}
            {$softDeletesStr}
            {$timestampsStr}
        });

        {$translatedSchema}
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        {$dropTranslatedSchema}
        Schema::dropIfExists('{$tableName}');
    }
};
EOT;

        return $migrationTemplate;
    }

    protected function generateFields(array $attributes)
    {
        $fields = '';
        $hasTranslated = false;
        foreach ($attributes as $attribute) {
            $type = $this->getMigrationType($attribute['type'], $attribute);
            $nullable = $attribute['nullable'] ? '->nullable()' : '';
            $unique = $attribute['unique'] ?? false ? '->unique()' : '';
            $default = isset($attribute['default']) ? '->default(' . $this->formatDefaultValue($attribute['default']) . ')' : '';
            $field = "\$table->{$type}{$nullable}{$unique}{$default};";
            $fields .= "{$field}\n            ";
            if (isset($attribute['translated']) && $attribute['translated']) {
                $hasTranslated = true;
                $attribute['name'] = $attribute['name'] . '_translated';
                $type = $this->getMigrationType($attribute['type'], $attribute);
                $nullable = '->nullable()';
                $field = "\$table->{$type}{$nullable};";
                $fields .= "{$field}\n            ";
            }
            if ($attribute['type'] === 'image' || $attribute['type'] === 'video' || $attribute['type'] === 'file') {
                $text = $attribute['name'] . '_file_name';
                $type = "string('{$text}')";
                $nullable = '->nullable()';
                $field = "\$table->{$type}{$nullable};";
                $fields .= "{$field}\n            ";

                $text = $attribute['name'] . '_file_extension';
                $type = "string('{$text}')";
                $nullable = '->nullable()';
                $field = "\$table->{$type}{$nullable};";
                $fields .= "{$field}\n            ";

                $text = $attribute['name'] . '_file_size';
                $type = "integer('{$text}')";
                $nullable = '->nullable()';
                $field = "\$table->{$type}{$nullable};";
                $fields .= "{$field}\n            ";
            }
        }
        if ($hasTranslated) {
            $field = "\$table->string('original_locale')->nullable();";
            $fields .= "{$field}\n            ";
        }
        return $fields;
    }

    protected function getMigrationType($type, $attribute)
    {
        switch ($type) {
            case 'bigInteger':
            case 'integer':
            case 'tinyInteger':
            case 'smallInteger':
            case 'mediumInteger':
            case 'unsignedBigInteger':
            case 'unsignedInteger':
            case 'unsignedMediumInteger':
            case 'unsignedSmallInteger':
            case 'unsignedTinyInteger':
                return "{$type}('{$attribute['name']}')";

            case 'morphs':
                return "morphs('{$attribute['name']}')";
            case 'nullableMorphs':
                return "nullableMorphs('{$attribute['name']}')";
            case 'numericMorphs':
                return "numericMorphs('{$attribute['name']}')";
            case 'nullableNumericMorphs':
                return "nullableNumericMorphs('{$attribute['name']}')";
            case 'uuidMorphs':
                return "uuidMorphs('{$attribute['name']}')";
            case 'nullableUuidMorphs':
                return "nullableUuidMorphs('{$attribute['name']}')";
            case 'ulidMorphs':
                return "ulidMorphs('{$attribute['name']}')";
            case 'nullableUlidMorphs':
                return "nullableUlidMorphs('{$attribute['name']}')";

            case 'boolean':
                return "boolean('{$attribute['name']}')";

            case 'decimal':
                $precision = $attribute['precision'] ?? 8;
                $scale = $attribute['scale'] ?? 2;
                return "decimal('{$attribute['name']}', $precision, $scale)";

            case 'float':
                return "float('{$attribute['name']}')";

            case 'double':
                return "double('{$attribute['name']}')";

            case 'string':
                $length = $attribute['length'] ?? 255;
                return "string('{$attribute['name']}', $length)";
            case 'email':
                $length = $attribute['length'] ?? 255;
                return "string('{$attribute['name']}', $length)";

            case 'text':
                return "text('{$attribute['name']}')";

            case 'mediumText':
                return "mediumText('{$attribute['name']}')";

            case 'longText':
                return "longText('{$attribute['name']}')";

            case 'binary':
                return "binary('{$attribute['name']}')";

            case 'date':
                return "date('{$attribute['name']}')";

            case 'datetime':
                return "dateTime('{$attribute['name']}')";
            case 'dateTime':
                return "dateTime('{$attribute['name']}')";

            case 'timestamp':
                return "timestamp('{$attribute['name']}')";
            case 'timeStamp':
                return "timestamp('{$attribute['name']}')";

            case 'time':
                return "time('{$attribute['name']}')";

            case 'year':
                return "year('{$attribute['name']}')";

            case 'json':
                return "json('{$attribute['name']}')";

            case 'jsonb':
                return "jsonb('{$attribute['name']}')";

            case 'uuid':
                return "uuid('{$attribute['name']}')";

            case 'char':
                $length = $attribute['length'] ?? 255;
                return "char('{$attribute['name']}', $length)";

            case 'enum':
                $enumValues = implode(", ", array_map(fn($v) => "'$v'", $attribute['values']));
                return "enum('{$attribute['name']}', [$enumValues])";

            case 'set':
                $setValues = implode(", ", array_map(fn($v) => "'$v'", $attribute['values']));
                return "set('{$attribute['name']}', [$setValues])";

            case 'geometry':
                return "geometry('{$attribute['name']}')";

            case 'point':
                return "point('{$attribute['name']}')";

            case 'linestring':
                return "lineString('{$attribute['name']}')";

            case 'polygon':
                return "polygon('{$attribute['name']}')";

            case 'geometryCollection':
                return "geometryCollection('{$attribute['name']}')";

            case 'multipoint':
                return "multiPoint('{$attribute['name']}')";

            case 'multilinestring':
                return "multiLineString('{$attribute['name']}')";

            case 'multipolygon':
                return "multiPolygon('{$attribute['name']}')";

            case 'ipAddress':
                return "ipAddress('{$attribute['name']}')";

            case 'macAddress':
                return "macAddress('{$attribute['name']}')";

            case 'foreignId':
                return "foreignId('{$attribute['name']}')";

            case 'foreignUuid':
                return "foreignUuid('{$attribute['name']}')";

                // Tratamento de tipos 'file' e 'image' como string
            case 'file':
            case 'image':
            case 'video':
                return "string('{$attribute['name']}')";

            default:
                throw new \Exception("Tipo de dado desconhecido: {$type}");
        }
    }

    protected function formatDefaultValue($default)
    {
        // Format the default value for the migration
        if (is_string($default)) {
            return "'{$default}'";
        }
        if (is_null($default)) {
            return 'null';
        }
        return $default;
    }

    protected function generateRelationFields(array $relations)
    {
        $relationsFields = '';
        foreach ($relations as $relation) {
            $relationType = $relation['type'];
            $relatedModel = $relation['related'];
            $relatedTable = Str::snake(Str::pluralStudly($relatedModel));
            $foreignKey = Str::snake($relation['name']) . '_id';

            switch ($relationType) {
                case 'belongsTo':
                    $relationsFields .= "\$table->foreignId('{$foreignKey}')->constrained('{$relatedTable}');\n            ";
                    break;
                case 'belongsToMany':
                    $pivotTable = $this->getPivotTableName($relatedTable, Str::snake(Str::pluralStudly($relation['name'])));
                    $relationsFields .= "\n            Schema::create('{$pivotTable}', function (Blueprint \$table) use (\$table) {
                \$table->id();
                \$table->foreignId('{$foreignKey}')->constrained('{$relatedTable}');
                \$table->foreignId('{$relatedModel}_id')->constrained('{$relatedTable}');
                \$table->timestamps();
            });\n";
                    break;
            }
        }

        return $relationsFields;
    }

    protected function getPivotTableName(string $relatedTable, string $currentTable)
    {
        $tables = [$relatedTable, $currentTable];
        sort($tables);
        return implode('_', $tables);
    }

    protected function generateTranslationsSchema($model, $attributes)
    {
        $relatedTable = Str::snake(Str::pluralStudly($model));
        $table = Str::snake($model);

        $fields = $this->generateFieldsTranslate($attributes);
        $translatedSchema = '';
        if (!$fields) {
            return $translatedSchema;
        }
        $translatedSchema = "Schema::create('{$table}_translations', function (Blueprint \$table) {
            \$table->id();

            \$table->foreignIdFor({$model}::class, 'item_id')
                ->constrained('{$relatedTable}')
                ->cascadeOnDelete();

            \$table->string('locale');

            {$fields}
            \$table->unique(['item_id', 'locale']);
        });";
        return $translatedSchema;
    }

    protected function generateFieldsTranslate(array $attributes)
    {
        $fields = '';
        foreach ($attributes as $attribute) {
            if (isset($attribute['translated']) && $attribute['translated']) {
                $attribute['name'] = $attribute['name'] . '_translated';
                $type = $this->getMigrationType($attribute['type'], $attribute);
                $nullable = '->nullable()';
                $field = "\$table->{$type}{$nullable};";
                $fields .= "{$field}\n            ";
            }
        }
        return $fields;
    }
}
