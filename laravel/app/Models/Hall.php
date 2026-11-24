<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Hall extends Model
{
    protected $fillable = ['name', 'capacity'];

    public function screenings(): HasMany
    {
        return $this->hasMany(Screening::class);
    }
}