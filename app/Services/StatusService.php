<?php

namespace App\Services;

use App\Services\TranslatorGoogle;
use App\Mail\User\UserApprovedEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use LaravelLang\Translator\Facades\Translate;


class StatusService
{

    public static function active($model, string $motive, int $createdBy, bool $sendEmail = false)
    {
        $motives = [];
        $translateds = TranslatorGoogle::translate($motive);
        $motives = $translateds['translations'];
        try {
            $model->update([
                'is_active' => 1,
            ]);
            $model->activeMotives()->create([
                'is_active' => 1,
                'causer_type' => Auth::user()::class, // Tipo do usuário que ativou
                'causer_id' => Auth::user()->id, // ID do usuário que ativou
                'motive' => $motives,
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
        return  true;
    }

    public static function deactive($model, string $motive, int $createdBy, bool $sendEmail = false)
    {
        $motives = [];
        $translateds = TranslatorGoogle::translate($motive);
        $motives = $translateds['translations'];

        try {

            $model->update([
                'is_active' => 0,
            ]);
            $model->activeMotives()->create([
                'is_active' => 0,
                'causer_type' => Auth::user()::class, // Tipo do usuário que ativou
                'causer_id' => Auth::user()->id, // ID do usuário que ativou
                'motive' => $motives,
                //               'motive' => $motive,
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
        return  true;
    }


    public static function toggleApprovedStatus($model, string $motive, int $createdBy, bool $sendEmail = false)
    {
        $motives = [];
        $translateds = TranslatorGoogle::translate($motive);
        $motives = $translateds['translations'];

        try {
            $model->update([
                'approved_status' => $model::APPROVED_STATUS_APPROVED,
                'is_active' => true,

            ]);
            $model->approvedMotives()->create([
                'approved_status' => $model::APPROVED_STATUS_APPROVED,
                'causer_type' => Auth::user()::class, // Tipo do usuário que ativou
                'causer_id' => Auth::user()->id, // ID do usuário que ativou
                'motive' => $motives,
                // 'motive' => $motive,
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
        return  true;
    }


    public static function approve($model, string $motive, int $createdBy, bool $sendEmail = false)
    {
        $motives = [];
        $translateds = TranslatorGoogle::translate($motive);
        $motives = $translateds['translations'];

        try {
            $model->update([
                'approved_status' => $model::APPROVED_STATUS_APPROVED,
                'is_active' => true,

            ]);
            $model->approvedMotives()->create([
                'approved_status' => $model::APPROVED_STATUS_APPROVED,
                'causer_type' => Auth::user()::class, // Tipo do usuário que ativou
                'causer_id' => Auth::user()->id, // ID do usuário que ativou
                'motive' => $motives,
                // 'motive' => $motive,
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
        return  true;
    }

    public static function unapprove($model, string $motive, int $createdBy, bool $sendEmail = false)
    {
        $motives = [];
        $translateds = TranslatorGoogle::translate($motive);
        $motives = $translateds['translations'];

        try {
            $model->update([
                'approved_status' => $model::APPROVED_STATUS_UNAPPROVED,

            ]);
            $model->approvedMotives()->create([
                'approved_status' => $model::APPROVED_STATUS_UNAPPROVED,
                'causer_type' => Auth::user()::class, // Tipo do usuário que ativou
                'causer_id' => Auth::user()->id, // ID do usuário que ativou
                'motive' => $motives,
                //  'motive' => $motive,
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
        return  true;
    }

    public static function analisys($model, string $motive, int $createdBy, bool $sendEmail = false)
    {
        $motives = [];
        $translateds = TranslatorGoogle::translate($motive);
        $motives = $translateds['translations'];

        try {
            $model->update([
                'approved_status' => $model::APPROVED_STATUS_ANALISYS,

            ]);
            $model->approvedMotives()->create([
                'approved_status' => $model::APPROVED_STATUS_ANALISYS,
                'causer_type' => Auth::user()::class, // Tipo do usuário que ativou
                'causer_id' => Auth::user()->id, // ID do usuário que ativou
                'motive' => $motives,
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
        return  true;
    }

    public static function block($model, string $motive, int $createdBy, bool $sendEmail = false)
    {
        $motives = [];
        $translateds = TranslatorGoogle::translate($motive);
        $motives = $translateds['translations'];

        try {
            $model->update([
                'approved_status' => $model::APPROVED_STATUS_BLOCKED,

            ]);
            $model->approvedMotives()->create([
                'approved_status' => $model::APPROVED_STATUS_BLOCKED,
                'causer_type' => Auth::user()::class, // Tipo do usuário que ativou
                'causer_id' => Auth::user()->id, // ID do usuário que ativou
                'motive' => $motives,
                //   'motive' => $motive,
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
        return  true;
    }

    public static function cancel($model, string $motive, int $createdBy, bool $sendEmail = false)
    {
        $motives = [];
        $translateds = TranslatorGoogle::translate($motive);
        $motives = $translateds['translations'];

        try {
            $model->update([
                'approved_status' => $model::APPROVED_STATUS_CANCELED,

            ]);
            $model->approvedMotives()->create([
                'approved_status' => $model::APPROVED_STATUS_CANCELED,
                'causer_type' => Auth::user()::class, // Tipo do usuário que ativou
                'causer_id' => Auth::user()->id, // ID do usuário que ativou
                'motive' => $motives,
                //  'motive' => $motive,
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
        return  true;
    }
}
