<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Template extends Model
{

 	protected $casts = [
        'checklist' => 'array',
        'items' => 'array',
        'created_at' => 'datetime:c',
        'updated_at' => 'datetime:c'
    ];

    protected $hidden = ['user_id'];
}
