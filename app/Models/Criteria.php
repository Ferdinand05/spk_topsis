<?php

namespace App\Models;

use App\Models\Calculation;
use App\Models\Score;
use Illuminate\Database\Eloquent\Model;

class Criteria extends Model
{

    protected $fillable = [
        'calculation_id',
        'code',
        'name',
        'weight',
        'type',
    ];

    protected $casts = [
        'weight' => 'float',
    ];

    public function calculation()
    {
        return $this->belongsTo(Calculation::class);
    }

    public function scores()
    {
        return $this->hasMany(Score::class);
    }
}
