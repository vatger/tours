<?php

namespace App\Http\Controllers\SystemDevelopment;

use Inertia\Inertia;

use Illuminate\Http\Request;
use App\Services\ModelExporter;
use Illuminate\Support\Facades\Log;

use App\Http\Controllers\Controller;

use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Requests\SystemDevelopment\StoreModelRequest;
use App\Http\Requests\SystemDevelopment\UpdateModelRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class ModelGenerateController extends Controller
{
    private function getModelDirectory(): string
    {
        return base_path("documentation/models/json");
    }

    private function getModelPath(string $model): string
    {
        $directory = $this->getModelDirectory();

        // Verifica e cria o diretório se não existir
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        return $directory . '/' . $model;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Inertia\Response
     */

    public function index(Request $request)
    {
        // 1. CAPTURAR INPUTS
        $filters = $request->validate([
            'search' => 'nullable|string',
            'sort_field' => 'string|in:name,version,attributes_count,relations_count,updated_at',
            'sort_direction' => 'string|in:asc,desc',
            'per_page' => 'integer|min:5|max:100',
        ]);

        // Usando valores padrão com o helper data_get
        $search = data_get($filters, 'search');
        $sortField = data_get($filters, 'sort_field', 'updated_at');
        $sortDirection = data_get($filters, 'sort_direction', 'desc');
        $perPage = data_get($filters, 'per_page', 10);

        $finalFilters = [
            'search' => $search,
            'sort_field' => $sortField,
            'sort_direction' => $sortDirection,
            'per_page' => (int) $perPage, // Garante que seja um inteiro
        ];
        // Pega todos os modelos (idealmente do cache, como no ponto 1)
        $allModels = $this->getAllModels();

        // 2. APLICAR FILTROS E ORDENAÇÃO COM COLLECTIONS
        $modelsCollection = collect($allModels)
            ->when($search, function ($collection) use ($search) {
                // A busca com `filter` da Collection
                return $collection->filter(function ($model) use ($search) {
                    return stripos($model['name'], $search) !== false;
                });
            })
            ->sortBy($sortField, SORT_REGULAR, $sortDirection === 'desc');

        // 3. PAGINAÇÃO
        $paginatedModels = new LengthAwarePaginator(
            $modelsCollection->forPage(Paginator::resolveCurrentPage('page'), $perPage)->values(),
            $modelsCollection->count(),
            $perPage,
            Paginator::resolveCurrentPage('page'),
            ['path' => $request->url()]
        );
        $paginatedModels->appends($finalFilters);
        // 4. RENDERIZAR RESPOSTA
        return Inertia::render('systemDevelopment/model/Index', [
            'models' => $paginatedModels,
            'filters' => $finalFilters, // Retorna os filtros validados
        ]);
    }

    public function create()
    {
        return Inertia::render('systemDevelopment/model/Create', [
            'defaults' => $this->getDefaultModelStructure()
        ]);
    }
    public function store(StoreModelRequest $request)
    {
        $validated = $request->validated();
        $filename = $validated['name'] . '.json';
        $path = $this->getModelPath($filename);

        try {
            file_put_contents(
                $path,
                json_encode(array_merge($validated, [
                    'created_at' => now()->toDateTimeString(),
                    'updated_at' => now()->toDateTimeString(),
                ]), JSON_PRETTY_PRINT)
            );

            return redirect()->route('models-generate.show', basename($filename, '.json'))
                ->with('success', 'Model created successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to create model: ' . $e->getMessage());
        } finally {
            // Limpa o cache de modelos para garantir que a lista atualizada seja carregada
            Cache::forget('models.all');
        }
    }

    public function show(string $model)
    {
        $path = $this->getModelPath($model . '.json');

        if (!file_exists($path)) {
            abort(404);
        }

        $content = json_decode(file_get_contents($path), true);

        return Inertia::render('systemDevelopment/model/Show', [
            'model' => array_merge($content, ['name' => $model])
        ]);
    }
    public function edit(string $model)
    {
        $path = $this->getModelPath($model . '.json');

        if (!file_exists($path)) {
            abort(404);
        }

        $content = json_decode(file_get_contents($path), true);

        return Inertia::render('systemDevelopment/model/Edit', [
            'model' => array_merge($content, ['name' => $model])
        ]);
    }


    public function update(UpdateModelRequest $request, string $model)
    {
        $path = $this->getModelPath($model . '.json');
        $newFilename = $request->name . '.json';
        $newPath = $this->getModelPath($newFilename);

        // Verifica se o arquivo existe
        if (!file_exists($path)) {
            abort(404);
        }

        try {
            $validated = $request->validated();

            // Carrega o conteúdo atual do arquivo para preservar o created_at
            $currentContent = json_decode(file_get_contents($path), true);
            $createdAt = $currentContent['created_at'] ?? now()->toDateTimeString();

            if ($path !== $newPath && file_exists($newPath)) {
                throw new \Exception('A model with this name already exists');
            }

            file_put_contents(
                $newPath,
                json_encode(array_merge($validated, [
                    'created_at' => $createdAt, // Mantém o created_at original
                    'updated_at' => now()->toDateTimeString(),
                ]), JSON_PRETTY_PRINT)
            );

            if ($path !== $newPath) {
                unlink($path);
            }

            return redirect()->route('models-generate.show', basename($newFilename, '.json'))
                ->with('success', 'Model updated successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update model: ' . $e->getMessage());
        } finally {
            // Limpa o cache de modelos para garantir que a lista atualizada seja carregada
            Cache::forget('models.all');
        }
    }
    public function destroy(string $model)
    {
        Log::debug('Deleting model: ' . $model);
        $path = $this->getModelPath($model . '.json');
        Log::debug('Model path: ' . $path);
        Log::debug('File exists: ' . (file_exists($path) ? 'true' : 'false'));
        if (!file_exists($path)) {
            abort(404);
        }

        try {
            unlink($path);
            return redirect()->back()
                ->with('success', 'Model deleted successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete model: ' . $e->getMessage());
        } finally {
            // Limpa o cache de modelos para garantir que a lista atualizada seja carregada
            Cache::forget('models.all');
        }
    }


    public function export(string $model, string $format)
    {
        $path = $this->getModelPath($model . '.json');

        if (!file_exists($path)) {
            abort(404);
        }

        $content = json_decode(file_get_contents($path), true);
        $exporter = new ModelExporter($content);

        return $exporter->export($format, $model);
    }

    private function getDefaultModelStructure(): array
    {
        return [
            'version' => 'V1',
            'softDeletes' => true,
            'timestamps' => true,
            'useIsActive' => false,
            'useApprovedStatus' => false,
            'useScribe' => false,
            'authorize' => false,
            'logsActivity' => false,
            'clearsResponseCache' => false,
            'attributes' => [
                [
                    'name' => 'id',
                    'type' => 'string',
                    'length' => 255,
                    'max' => 255,
                    'min' => 0,
                    'precision' => 0,
                    'scale' => 0,
                    'validate' => true,
                    'required' => true,
                    'nullable' => false,
                    'unique' => true,
                    'translated' => false,

                    'sortAble' => true,
                    'filterAble' => true,
                    'exactFilter' => false,
                    'serachAble' => false,

                    'description' => 'The unique identifier',
                    'example' => '1234567890',
                ]
            ],
            'relations' => []
        ];
    }

    private function getAllModels()
    {
        // A chave do cache.
        $cacheKey = 'models.all';

        // Cache::rememberForever irá executar a função e salvar o resultado
        // para sempre. Ele só executará a função novamente se o cache for limpo.
        return Cache::rememberForever($cacheKey, function () {
            $directory = $this->getModelDirectory();

            if (!File::exists($directory)) {
                // Se o diretório não existe, retorna um array vazio.
                // A criação pode ser movida para um comando ou outro local.
                return [];
            }

            $allModels = [];
            // Use o facade File para uma melhor abstração
            $files = File::glob($directory . '/*.json');

            foreach ($files as $file) {
                $content = json_decode(File::get($file), true);

                // Adiciona uma verificação para JSONs inválidos
                if (json_last_error() !== JSON_ERROR_NONE) {
                    // Opcional: Logar o erro para saber qual arquivo está corrompido
                    // Log::warning("Arquivo JSON inválido encontrado: " . $file);
                    continue; // Pula para o próximo arquivo
                }

                $allModels[] = [
                    'name' => File::name($file), // Mais limpo que basename($file, '.json')
                    'version' => $content['version'] ?? 'V1',
                    'attributes_count' => count($content['attributes'] ?? []),
                    'relations_count' => count($content['relations'] ?? []),
                    'updated_at' => $content['updated_at'] ?? date('Y-m-d H:i:s', File::lastModified($file)),
                ];
            }

            return $allModels;
        });
    }
}
