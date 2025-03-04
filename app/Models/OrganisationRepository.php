<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganisationRepository extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $casts = [
        'topics' => 'array',
    ];
}
