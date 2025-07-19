<?php

namespace App\Http\Controllers\Api\V1\AbstractContracts\Log;

use Inertia\Inertia;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use App\Services\QueryService;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\ApiBaseController;
use App\Http\Resources\Api\V1\ActivityLog\ActivityLogResource;


/**
 * @group API-ADMINISTRATIVE
 *
 * @subgroup LOGS
 * @authenticated
 */
abstract class ActivityLogController extends ApiBaseController
{
    /**
     * Listar logs de atividades.
     *
     * Lista todos os logs de atividades gerados, com opção de filtrar por
     * evento, subject (tipo de tabela), e causer (quem realizou a ação).
     *
     * @queryParam page int Indique o número da página desejada No-example
     * @queryParam per_page int Indique quantos registros deve conter cada página[default=50] No-example
     * @queryParam filter[event] string Filtra pelo tipo de evento (created, updated, deleted). No-example
     * @queryParam filter[subject_type] string Filtra pelo tipo de subject (modelo afetado. Exemplo: App\Models\User). No-example
     * @queryParam filter[subject_id] int Filtra pelo ID do subject  (quem sofreu a ação). No-example
     * @queryParam filter[subject_name] string Filtra pelo name/title do subject (quem sofreu a ação). No-example
     * @queryParam filter[causer_id] int Filtra pelo ID do causer (quem realizou a ação). No-example
     * @queryParam filter[causer_name] string Filtra pelo username do causer (quem realizou a ação). No-example
     */
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'sort', 'per_page']);
        Log::debug("filters", $filters);

        $finalFilters = [
            'search' => $request->filter['Search'] ?? null,
            'sort' => $request->sort,
            'per_page' => $request->per_page
        ];

        $query = QueryService::query(
            ActivityLog::class,
            ActivityLog::ALLOWEDFILTERS(),
            ActivityLog::ALLOWEDINCLUDES(),
            ActivityLog::ALLOWEDSORTS(),
            ActivityLog::DEFAULTSORT(),
            ActivityLog::DEFAULTINCLUDES()
        )->paginate(request('per_page'))->withQueryString();



        // $query = QueryBuilder::for(ActivityLog::class)
        //     ->allowedFilters([
        //         AllowedFilter::exact('event'),              // Filtra por evento (created, updated, deleted)
        //         AllowedFilter::exact('subject_type'),       // Filtra pelo tipo de subject (modelo afetado)
        //         AllowedFilter::exact('subject_id'),         // Filtra pelo ID do subject
        //         AllowedFilter::scope('subject_name'),        // Filtra pelo nome do subject
        //         AllowedFilter::exact('causer_id'),          // Filtra pelo ID do causer
        //         AllowedFilter::scope('causer_name'),        // Filtra pelo nome do causer
        //     ])
        //     ->with(['subject', 'causer'])                   // Carrega os relacionamentos 'subject' e 'causer'
        //     ->paginate(request('per_page'))->withQueryString();
        if (request()->wantsJson()) {
            return  $this->sendResponse(new ActivityLogResource($query));
        } else {
            return Inertia::render('logs/activityLogs/Index', [
                'activityLogs' => ActivityLogResource::collection($query),
                'filters' => $finalFilters
            ]);
        }
    }
}
