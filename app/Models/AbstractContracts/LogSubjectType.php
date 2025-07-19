<?php

namespace App\Models\AbstractContracts;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

abstract class LogSubjectType extends Model
{
    use HasFactory;
    protected $fillable = ['type', 'alias', 'what_subject_name'];
}
