<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
class Task extends Model
{
    protected $casts = [
        'is_completed' => 'boolean',
        'created_at' => 'datetime:c',
        'due' => 'datetime:c',
        'updated_at' => 'datetime:c',
        'completed_at' => 'datetime:c'
    ];

    public function items(){
    	return $this->hasMany('App\Models\Item', 'task_id');
    }

    public function setUpdatedAtAttribute($value){
    	$this->attributes['updated_at'] = Carbon::parse($value)->toDateTimeString();
    }

    public function setCompletedAtAttribute($value){
    	if($value){
    		$this->attributes['completed_at'] = Carbon::parse($value)->toDateTimeString();
    	}
    }
}
