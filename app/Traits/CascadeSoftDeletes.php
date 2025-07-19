<?php

namespace App\Traits;

use LogicException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use App\Exceptions\API\CascadeSoftDeleteException;
use Illuminate\Database\Eloquent\Relations\Relation;

trait CascadeSoftDeletes
{
    //TODO verificar o controle de quando já está deletado, no caso de ser softdeletes, caso contrário fazer backup e excluir
    /**
     * Boot the trait.
     *
     * Listen for the deleting event of a soft deleting model, and run
     * the delete operation for any configured relationship methods.
     *
     * @throws \LogicException
     */
    protected static function bootCascadeSoftDeletes()
    {
        static::deleting(function ($model) {
            $model->validateCascadingSoftDelete();
            $model->backupDeletedRecord();
            $model->runCascadingDeletes();
        });

        static::restoring(function ($model) {
            $model->validateCascadingSoftDelete();
            $model->deleteBackupRecord();
            $model->runCascadingRestores();
        });
    }

    /**
     * Validate that the calling model is correctly setup for cascading soft deletes.
     *
     * @throws \App\Exceptions\API\CascadeSoftDeleteException
     */
    protected function validateCascadingSoftDelete()
    {
        if (!$this->implementsSoftDeletes()) {
            throw CascadeSoftDeleteException::softDeleteNotImplemented(get_called_class());
        }

        if ($invalidCascadingRelationships = $this->hasInvalidCascadingRelationships()) {
            throw CascadeSoftDeleteException::invalidRelationships($invalidCascadingRelationships);
        }
    }

    /**
     * Run the cascading soft delete for this model.
     *
     * @return void
     */
    protected function runCascadingDeletes()
    {
        $relations = [];

        foreach ($this->getActiveCascadingDeletes() as $relationship) {
            $this->cascadeSoftDeletes($relationship);
        }
    }

    /**
     * Cascade delete the given relationship on the given model.
     *
     * @param  string  $relationship
     * @return void
     */
    protected function cascadeSoftDeletes($relationship)
    {
        $delete = $this->forceDeleting ? 'forceDelete' : 'delete';

        foreach ($this->{$relationship}()->get() as $model) {



            if (Schema::hasColumn($model->getTable(), 'deleted_by_parent')) {
                $model->pivot ? $model->pivot->update(['deleted_by_parent' => true]) : $model->update(['deleted_by_parent' => true]);
            }
            // if ($delete === 'forceDelete') {
            //     $model->backupDeletedRecord();
            // }

            $model->pivot ? $model->pivot->{$delete}() : $model->{$delete}();
        }
    }

    /**
     * Run the cascading restore for this model.
     *
     * @return void
     */
    protected function runCascadingRestores()
    {

        foreach ($this->getDeletedCascadingDeletes() as $relationship) {
            $this->cascadeRestore($relationship);
        }
    }

    /**
     * Cascade restore the given relationship on the given model.
     *
     * @param  string  $relationship
     * @return void
     */
    protected function cascadeRestore($relationship)
    {
        foreach ($this->{$relationship}()->withTrashed()->get() as $model) {
            if (Schema::hasColumn($model->getTable(), 'deleted_by_parent')) {
                if ($model->deleted_by_parent) {
                    $model->restore();

                    $model->update(['deleted_by_parent' => false]);
                }
            }

            // if ($model->deleted_by_parent) {
            //     $model->restore();

            //     $model->update(['deleted_by_parent' => false]);
            // }
        }
    }

    /**
     * Determine if the current model implements soft deletes.
     *
     * @return bool
     */
    protected function implementsSoftDeletes()
    {
        return method_exists($this, 'runSoftDelete');
    }

    /**
     * Determine if the current model has any invalid cascading relationships defined.
     *
     * A relationship is considered invalid when the method does not exist, or the relationship
     * method does not return an instance of Illuminate\Database\Eloquent\Relations\Relation.
     *
     * @return array
     */
    protected function hasInvalidCascadingRelationships()
    {
        return array_filter($this->getCascadingDeletes(), function ($relationship) {
            return !method_exists($this, $relationship) || !$this->{$relationship}() instanceof Relation;
        });
    }

    /**
     * Fetch the defined cascading soft deletes for this model.
     *
     * @return array
     */
    protected function getCascadingDeletes()
    {
        return isset($this->cascadeDeletes) ? (array) $this->cascadeDeletes : [];
    }

    /**
     * For the cascading deletes defined on the model, return only those that are not null.
     *
     * @return array
     */
    protected function getActiveCascadingDeletes()
    {
        return array_filter($this->getCascadingDeletes(), function ($relationship) {
            return !is_null($this->{$relationship});
        });
    }

    /**
     * For the cascading deletes defined on the model, return only those that are not null.
     *
     * @return array
     */
    protected function getDeletedCascadingDeletes()
    {
        return array_filter($this->getCascadingDeletes(), function ($relationship) {
            return !is_null($this->{$relationship}()->withTrashed());
        });
    }

    protected function backupDeletedRecord()
    {
        // Verifica se a configuração 'keep_deleted_backup' está definida como true
        if (config('app.keep_deleted_backup')) {
            $user = Auth::user();

            DB::table('deleted_records')->insert([
                'subject_type' => get_class($this),
                'subject_id' => $this->id,
                'subject_data' => json_encode($this->attributesToArray()),
                'deleted_at' => now(),
                'causer_id' => $user ? $user->id : null,
                'causer_type' => $user ? get_class($user) : null,
            ]);
        }
    }

    protected function deleteBackupRecord()
    {
        DB::table('deleted_records')
            ->where('subject_type', get_class($this))
            ->where('subject_id', $this->id)
            ->delete();
    }
}
