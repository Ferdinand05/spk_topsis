<?php

namespace App\Models;

use App\Models\Alternative;
use App\Models\Criteria;
use App\Models\Result;
use App\Models\Score;
use Illuminate\Database\Eloquent\Model;

class Calculation extends Model
{
    protected $fillable = ['name'];

    public function criteria()
    {
        return $this->hasMany(Criteria::class);
    }

    public function alternatives()
    {
        return $this->hasMany(Alternative::class);
    }

    public function scores()
    {
        return $this->hasMany(Score::class);
    }

    public function results()
    {
        return $this->hasMany(Result::class);
    }
}
