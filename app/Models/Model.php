<?php

namespace App\Models;

use App\Traits\HasSystemTimezoneDates;
use Illuminate\Database\Eloquent\Model as EloquentModel;

abstract class Model extends EloquentModel
{
    use HasSystemTimezoneDates;
}
