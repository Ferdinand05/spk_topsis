<?php

namespace App\Models;

use App\Models\Alternative;
use App\Models\Calculation;
use Illuminate\Database\Eloquent\Model;

class Result extends Model
{
    protected $fillable = [
        'calculation_id',
        'alternative_id',
        'score',
        'rank',
    ];

    protected $casts = [
        'score' => 'float',
    ];

    public function calculation()
    {
        return $this->belongsTo(Calculation::class);
    }

    public function alternative()
    {
        return $this->belongsTo(Alternative::class);
    }
}
