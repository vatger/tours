<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;

class ModelExporter
{
    private array $model;

    public function __construct(array $model)
    {
        $this->model = $model;
    }

    public function export(string $format, string $modelName)
    {
        return match ($format) {
            'typescript' => $this->exportTypeScript($modelName),
            'pdf' => $this->exportPdf($modelName),
            'json' => $this->exportRawJson($modelName),
            default => $this->exportRawJson($modelName),
            // 'prisma' => $this->exportPrisma($modelName),
            // 'eloquent' => $this->exportEloquent($modelName),
            // 'openapi' => $this->exportOpenAPI($modelName),
            // default => $this->exportRawJson($modelName),
        };
    }

    private function exportTypeScript(string $modelName)
    {
        $tsCode = "interface {$modelName} {\n";

        foreach ($this->model['attributes'] as $attr) {
            $tsType = $this->mapToTypeScriptType($attr['type']);
            $optional = ($attr['nullable'] ?? false) ? '?' : '';
            $tsCode .= "  {$attr['name']}{$optional}: {$tsType};\n";
        }

        $tsCode .= "}\n";

        return response($tsCode, 200, [
            'Content-Type' => 'text/typescript',
            'Content-Disposition' => "attachment; filename=\"{$modelName}.ts\""
        ]);
    }

    private function mapToTypeScriptType(string $type): string
    {
        return match ($type) {
            'integer', 'unsignedBigInteger', 'decimal', 'float' => 'number',
            'boolean' => 'boolean',
            'date', 'datetime', 'timestamp' => 'Date | string',
            default => 'string',
        };
    }

    private function exportPdf(string $modelName)
    {
        $html = $this->generatePdfHtml($modelName);

        $pdf = Pdf::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->setOption('isRemoteEnabled', true);

        return $pdf->download("{$modelName}_model.pdf");
    }

    private function generatePdfHtml(string $modelName): string
    {
        $model = $this->model;
        $features = $this->getEnabledFeatures();
        $createdAt = $model['created_at'] ?? now()->toDateTimeString();
        $updatedAt = $model['updated_at'] ?? now()->toDateTimeString();

        $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{$modelName} Model Documentation</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h1 { color: #1a365d; font-size: 24px; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px; }
        h2 { color: #2d3748; font-size: 18px; margin-top: 20px; border-bottom: 1px solid #e2e8f0; padding-bottom: 6px; }
        .header { margin-bottom: 20px; }
        .model-info { margin-bottom: 15px; }
        .feature-badge {
            display: inline-block;
            background: #e2e8f0;
            padding: 2px 8px;
            border-radius: 4px;
            margin-right: 5px;
            margin-bottom: 5px;
        }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #f7fafc; text-align: left; padding: 8px; border: 1px solid #e2e8f0; }
        td { padding: 8px; border: 1px solid #e2e8f0; }
        .metadata { margin-top: 20px; font-size: 11px; color: #718096; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{$modelName} Model Documentation</h1>
        <div class="model-info">
            <p><strong>Version:</strong> {$model['version']}</p>
            <div>
                <strong>Features:</strong><br>
HTML;

        // Adiciona features ativadas
        foreach ($features as $feature) {
            $html .= "<span class=\"feature-badge\">{$feature}</span>";
        }

        $html .= <<<HTML
            </div>
        </div>
    </div>

    <h2>Attributes</h2>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Type</th>
                <th>Validation</th>
                <th>Options</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
HTML;

        // Adiciona atributos
        foreach ($model['attributes'] as $attr) {
            $validation = $this->formatValidation($attr);
            $options = $this->formatOptions($attr);

            $html .= <<<HTML
            <tr>
                <td>{$attr['name']}</td>
                <td>{$attr['type']}</td>
                <td>{$validation}</td>
                <td>{$options}</td>
                <td>
HTML;
            $html .= isset($attr['description']) ? $attr['description'] : '';
            $html .= <<<HTML
                </td>
            </tr>
HTML;
        }

        $html .= <<<HTML
        </tbody>
    </table>
HTML;

        // Adiciona relacionamentos se existirem
        if (!empty($model['relations'])) {
            $html .= <<<HTML
    <h2>Relationships</h2>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Type</th>
                <th>Related Model</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
HTML;

            foreach ($model['relations'] as $rel) {
                $description = isset($rel['description']) ? $rel['description'] : '';
                $html .= <<<HTML
            <tr>
                <td>{$rel['name']}</td>
                <td>{$this->formatRelationType($rel['type'])}</td>
                <td>{$rel['related']}</td>
                <td>{$description}</td>
            </tr>
HTML;
            }

            $html .= <<<HTML
        </tbody>
    </table>
HTML;
        }

        // Adiciona metadados
        $html .= <<<HTML
    <div class="metadata">
        <p><strong>Created:</strong> {$createdAt}</p>
        <p><strong>Last Updated:</strong> {$updatedAt}</p>
    </div>
</body>
</html>
HTML;

        return $html;
    }

    private function getEnabledFeatures(): array
    {
        $features = [];
        $featureMap = [
            'softDeletes' => 'Soft Deletes',
            'timestamps' => 'Timestamps',
            'useIsActive' => 'Is Active',
            'useApprovedStatus' => 'Approved Status',
            'useScribe' => 'Scribe Docs',
            'authorize' => 'Authorization',
            'logsActivity' => 'Activity Logs',
            'clearsResponseCache' => 'Clears Cache'
        ];

        foreach ($featureMap as $key => $label) {
            if (!empty($this->model[$key]) && $this->model[$key]) {
                $features[] = $label;
            }
        }

        return $features;
    }

    private function formatValidation(array $attr): string
    {
        $validations = [];
        if ($attr['required'] ?? false) $validations[] = 'Required';
        if ($attr['nullable'] ?? false) $validations[] = 'Nullable';
        if ($attr['unique'] ?? false) $validations[] = 'Unique';
        if ($attr['validate'] ?? false) $validations[] = 'Validated';

        return implode(', ', $validations) ?: '-';
    }

    private function formatOptions(array $attr): string
    {
        $options = [];
        if ($attr['sortAble'] ?? false) $options[] = 'Sortable';
        if ($attr['filterAble'] ?? false) $options[] = 'Filterable';
        if ($attr['searchAble'] ?? false) $options[] = 'Searchable';
        if ($attr['translated'] ?? false) $options[] = 'Translated';

        return implode(', ', $options) ?: '-';
    }

    private function formatRelationType(string $type): string
    {
        return match ($type) {
            'belongsTo' => 'Belongs To',
            'hasOne' => 'Has One',
            'hasMany' => 'Has Many',
            'belongsToMany' => 'Belongs To Many',
            default => $type
        };
    }

    private function exportRawJson(string $modelName)
    {
        return response()->json($this->model, 200, [
            'Content-Disposition' => "attachment; filename=\"{$modelName}.json\""
        ]);
    }
    // ... outros métodos de exportação (prisma, eloquent, openapi)
}
