<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Item extends Model
{
	protected $casts = [
        'is_completed' => 'boolean',
        'due' => 'datetime:c',
        'created_at' => 'datetime:c',
        'updated_at' => 'datetime:c',
        'completed_at' => 'datetime:c'
    ];

    //relation to task
    public function task(){
    	return $this->belongsTo('App\Models\Task', 'task_id');
    }
}
