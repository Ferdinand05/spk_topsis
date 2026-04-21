<?php

namespace App\Models;

use App\Models\Calculation;
use App\Models\Result;
use App\Models\Score;
use Illuminate\Database\Eloquent\Model;

class Alternative extends Model
{
    protected $fillable = [
        'calculation_id',
        'code',
        'name',
    ];


    public function calculation()
    {
        return $this->belongsTo(Calculation::class);
    }

    public function scores()
    {
        return $this->hasMany(Score::class);
    }

    public function result()
    {
        return $this->hasOne(Result::class);
    }
}
