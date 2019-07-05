<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Task extends Model
{
    protected $casts = [
        'is_completed' => 'boolean',
        'created_at' => 'datetime:c',
        'updated_at' => 'datetime:c',
        'completed_at' => 'datetime:c'
    ];

    public function items(){
    	return $this->hasMany('App\Models\Item', 'task_id');
    }
}
