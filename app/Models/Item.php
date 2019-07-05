<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
class Item extends Model
{
	protected $casts = [
        'is_completed' => 'boolean',
        // 'due' => 'datetime:u',
        // 'created_at' => 'datetime:c',
        // 'updated_at' => 'datetime:Y-m-d\TH:i:s.\0\Z',
        // 'completed_at' => 'datetime:Y-m-d\TH:i:s.\0\Z'
    ];

    //relation to task
    public function task(){
    	return $this->belongsTo('App\Models\Task', 'task_id');
    }

    public function getDueAtAttribute()
    {
        return  Carbon::parse($this->attributes['created_at'])->setTimeZone('UTC')->toIso8601String();
    }
    public function getCreatedAtAttribute()
    {
        return  Carbon::parse($this->attributes['created_at'])->setTimeZone('UTC')->toIso8601String();
    }

    public function getUpdatedAtAttribute()
    {
        return  Carbon::parse($this->attributes['created_at'])->setTimeZone('UTC')->toIso8601String();
    }

    public function getCompletedAtAttribute()
    {
        return  Carbon::parse($this->attributes['created_at'])->setTimeZone('UTC')->toIso8601String();
    }
}
