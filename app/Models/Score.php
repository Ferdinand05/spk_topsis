<?php

namespace App\Models;

use App\Models\Alternative;
use App\Models\Calculation;
use App\Models\Criteria;
use Illuminate\Database\Eloquent\Model;

class Score extends Model
{
    protected $fillable = [
        'calculation_id',
        'alternative_id',
        'criteria_id',
        'value',
    ];

    protected $casts = [
        'value' => 'float',
    ];

    public function calculation()
    {
        return $this->belongsTo(Calculation::class);
    }

    public function alternative()
    {
        return $this->belongsTo(Alternative::class);
    }

    public function criteria()
    {
        return $this->belongsTo(Criteria::class);
    }
}
