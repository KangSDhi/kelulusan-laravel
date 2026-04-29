<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'nisn', 'birth_date', 'major'])]
class Graduation extends Model
{

}
