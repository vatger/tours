<?php

namespace App\Http\Controllers\Api\V1\Manager\User;

use App\Http\Controllers\ApiBaseController;
use App\Models\User;
use Inertia\Inertia;

use Illuminate\Http\Request;
use App\Services\QueryService;
use App\Services\StatusService;
use App\Services\UploadService;
use App\Http\Requests\UserRequest;
use App\Services\TranslatorGoogle;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\Api\V1\User\UserResource;

class UserController extends ApiBaseController
{

    // Lista de usuários com filtros e ordenação
    public function index(Request $request)
    {

        $filters = $request->only(['search', 'sort', 'per_page', 'only_trashed']);
        Log::debug("filters", $filters);
        $onlyTrashed = false;
        if (!empty($request->filter['OnlyTrashed'])) {
            $onlyTrashed = true;
        }
        $finalFilters = [
            'search' => $request->filter['Search'] ?? null,
            'sort' => $request->sort,
            'per_page' => (int)$request->per_page,
            'only_trashed' => $onlyTrashed,

        ];
        $users = QueryService::query(
            User::class,
            User::ALLOWEDFILTERS(),
            User::ALLOWEDINCLUDES(),
            User::ALLOWEDSORTS(),
            User::DEFAULTSORT(),
            user::DEFAULTINCLUDES()
        )->paginate(request('per_page', 10))->withQueryString();
        // Log::debug("message", ['users' => $users]);
        return Inertia::render('users/Index', [
            'users' => UserResource::collection($users),
            'filters' => $finalFilters
        ]);
    }

    // Salvar novo usuário
    public function store(UserRequest $request)
    {
        $data = [];
        foreach ($request->validated() as $key => $value) {
            if (!is_null($value)) {
                $data[$key] = $value;
            }
        }

        // UPLOAD de avatar
        $storageAvatar = User::AVATAR_STORAGE;
        $savedNameAvatar = '';
        $originalFileName = '';
        $fileSize = 0;
        $fileExtension = '';
        $uploadData = [];
        try {
            if ($request->hasFile('avatar')) {
                $uploadData = UploadService::putFile($request->file('avatar'), $storageAvatar);
            } else {
                if (!empty($request->avatar) && strpos($request->avatar, ';base64')) {
                    $uploadData = UploadService::putFile($request->avatar, $storageAvatar);
                }
            }
            if ($uploadData) {
                $savedNameAvatar = $uploadData['saved_name'];
                $originalFileName = $uploadData['original_name'];
                $fileSize =  $uploadData['size'];
                $fileExtension = $uploadData['extension'];
            }
        } catch (\Throwable $th) {
            throw $th;
        }
        if ($savedNameAvatar) {
            $data['avatar'] = $savedNameAvatar;
            $data['original_file_name'] = $originalFileName;
            // $data['avatar_file_size'] = $fileSize;
            // $data['avatar_file_extension'] = $fileExtension;
        }
        $user = User::create($data);
        $motives = [];
        $translateds = TranslatorGoogle::translate('Cadastro inicial');
        $motives = $translateds['translations'];

        $user->approvedMotives()->create([
            'approved_status' => '1',
            'causer_type' => Auth::user()::class, // Tipo do usuário que ativou
            'causer_id' => Auth::user()->id, // ID do usuário que ativou
            'motive' => $motives,
            // 'motive' => $motive,
        ]);
        $user->activeMotives()->create([
            'is_active' => false, // Usuário criado não está ativo por padrão
            'causer_type' => Auth::user()::class, // Tipo do usuário que ativou
            'causer_id' => Auth::user()->id, // ID do usuário que ativou
            'motive' => $motives,
            // 'motive' => $motive,
        ]);
        return back()->with('success',  __('The action was executed successfully.'));
    }

    // Atualizar usuário
    public function update(UserRequest $request, User $user)
    {
        $item = QueryService::getOrFailById(User::class, $user->id);
        $data = [];
        foreach ($request->validated() as $key => $value) {
            if (!is_null($value) && $item->{$key} !== $value) {
                $data[$key] = $value;
            }
        }
        // UPLOAD de avatar
        $oldSavedNameAvatar = $item->avatar;

        $storageAvatar = User::AVATAR_STORAGE;
        $savedNameAvatar = '';
        $originalFileName = '';
        $fileSize = 0;
        $fileExtension = '';
        $uploadData = [];
        Log::debug("user", ['user' => $user]);
        Log::debug("request", ['request' => $request->all()]);
        Log::debug("data", $data);


        if ($request->has('remove_avatar') && $request->remove_avatar) {
            // Remover avatar existente
            if ($oldSavedNameAvatar) {
                UploadService::removeFile($oldSavedNameAvatar, $storageAvatar);
                $data['avatar'] = '';
                $data['original_file_name'] = '';
                // $data['avatar_file_size'] = 0;
                // $data['avatar_file_extension'] = '';
            }
        } else {
            try {
                if ($request->hasFile('avatar')) {
                    $uploadData = UploadService::putFile($request->file('avatar'), $storageAvatar);
                } else {
                    if (!empty($request->avatar) && strpos($request->avatar, ';base64')) {
                        $uploadData = UploadService::putFile($request->avatar, $storageAvatar);
                    }
                }
                if ($uploadData) {
                    $savedNameAvatar = $uploadData['saved_name'];
                    $originalFileName = $uploadData['original_name'];
                    $fileSize =  $uploadData['size'];
                    $fileExtension = $uploadData['extension'];
                }
            } catch (\Throwable $th) {
                throw $th;
            }
            if ($savedNameAvatar) {
                $data['avatar'] = $savedNameAvatar;
                $data['original_file_name'] = $originalFileName;
                // $data['avatar_file_size'] = $fileSize;
                // $data['avatar_file_extension'] = $fileExtension;
            }
        }

        DB::beginTransaction();
        try {
            if (!empty($data)) {
                $item->update($data);
            }
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            if ($savedNameAvatar) {
                UploadService::removeFile($savedNameAvatar, $storageAvatar);
            }
            throw $th;
        }

        if ($savedNameAvatar && $oldSavedNameAvatar) {
            UploadService::removeFile($oldSavedNameAvatar, $storageAvatar);
        }


        return back()->with('success',  __('The action was executed successfully.'));
    }

    // Excluir usuário
    public function destroy(User $user)
    {
        $user->delete();
        return back()->with('success',  __('The action was executed successfully.'));
    }

    //TRASHED
    /**
     * Show Project TRASHED by ID.
     * @urlParam user_id int required No-example
     * @queryParam include Allowed: activities Relacionamentos que podem ser incluídos na resposta No-example
     */
    public function showTrashed(string $userId)
    {
        $item = QueryService::getOrFailTrashedById(User::class, $userId, User::ALLOWEDINCLUDES(), User::DEFAULTINCLUDES());

        // Carrega as roles e permissions do usuário
        $userRoles = $item->getRoleNames();
        $userPermissions = $item->getAllPermissions()->pluck('name');

        if (request()->wantsJson()) {
            return $this->sendResponse(new UserResource($item));
        } else {
            return Inertia::render('users/Show', [
                'user' => new UserResource($item),
                'userRoles' => $userRoles,
                'userPermissions' => $userPermissions,
            ])->with('success', __('The action was executed successfully.'));
        }
    }
    /**
     * Restore User.
     * @urlParam user_id int required No-example
     */
    public function restore($userId)
    {
        $item = QueryService::getOrFailTrashedById(User::class, $userId);
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
            return back()->with('success', __('The action was executed successfully.'));
        }
    }

    /**
     * Force Delete User.
     * @urlParam user_id int required No-example
     */
    public function forceDelete($userId)
    {
        $item = QueryService::getOrFailTrashedById(User::class, $userId);
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
            return back()->with('success', __('The action was executed successfully.'));
        }
    }

    /**
     * Active User.
     * @urlParam user_id int required No-example
     */
    public function active(ChangePlatformParamIsActiveRequest $request, string $userId)
    {
        $item = QueryService::getOrFailById(User::class,  $userId);
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
            return  $this->sendSuccess();
        } else {
            return back()->with('success', __('The action was executed successfully.'));
        }
    }

    /**
     * Deactive User.
     * @urlParam user_id int required No-example
     */
    public function deactive(ChangePlatformParamIsActiveRequest $request, string $userId)
    {
        $item = QueryService::getOrFailById(User::class, $userId);
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
            return  $this->sendSuccess();
        } else {
            return back()->with('success', __('The action was executed successfully.'));
        }
    }

    /**
     * Toggle IsActive.
     * @urlParam user_id int required No-example
     */
    public function toggleIsActive(string $userId)
    {
        $item = QueryService::getOrFailById(User::class, $userId);

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
            return  $this->sendSuccess();
        } else {
            return back()->with('success', __('The action was executed successfully.'));
        }
    }

    /**
     * Toggle approvedStatus.
     * @urlParam user_id int required No-example
     * @urlParam approved_status int required No-example
     */
    public function toggleApproveStatus(string $userId, string $status = '2')
    {
        request()->validate([
            'reason' => ['required', 'string', 'max:300'],
            'status' => ['required', 'in:1,2,3,4,5'],
        ], [
            'reason.required' => __('O motivo é obrigatório.'),
            'reason.max' => __('O motivo deve ter no máximo 300 caracteres.'),
            'status.in' => __('Status inválido.'),
        ]);

        $reason = request('reason');
        $status = request('status', $status);
        // if (!in_array($status, User::APPROVED_STATUS)) {
        //     return $this->sendError(__('Status inválido.'), 422);
        // }

        $item = QueryService::getOrFailById(User::class, $userId);
        if ($item->approved_status !== $status) {

            $motives = [];
            $translateds = TranslatorGoogle::translate($reason);
            $motives = $translateds['translations'];
            DB::beginTransaction();
            try {
                if ($item->approved_status !== $status) {
                    $item->update([
                        'approved_status' => $status,
                        // Salve o motivo se desejar, ex: 'approve_reason' => $reason,
                    ]);
                    $item->approvedMotives()->create([
                        'approved_status' => $status,
                        'causer_type' => Auth::user()::class, // Tipo do usuário que ativou
                        'causer_id' => Auth::user()->id, // ID do usuário que ativou
                        'motive' => $motives,
                        // 'motive' => $motive,
                    ]);
                }
                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();
                throw $th;
            }
        }
        if (request()->wantsJson()) {
            return  $this->sendSuccess();
        } else {
            return back()->with('success', __('The action was executed successfully.'));
        }
    }
    public function toggleApproveStatusxxx(string $userId, string $status = '2')
    {
        $item = QueryService::getOrFailById(User::class, $userId);
        DB::beginTransaction();
        try {
            if ($item->approved_status !== $status) {
                $item->update([
                    'approved_status' => $status,
                ]);
            }
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
        if (request()->wantsJson()) {
            return  $this->sendSuccess();
        } else {
            return back()->with('success', __('The action was executed successfully.'));
        }
    }
}
