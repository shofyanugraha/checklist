<?php 
namespace App\Transformers;

use App\Models\Task;
use League\Fractal\TransformerAbstract;


class TaskTransformer extends TransformerAbstract{
	
	public function single(Task $task){
		return [
			'type'	=> 'checklist',
			'id'		=> $task->id,
			'attributes' => [
				'object_domain' => $task->object_domain,
				'object_id' => $task->object_id,
				'description' => $task->description,
				'is_completed' => $task->is_completed == 1 ? true : false,
				'due' => $task->due,
				'urgency' => $task->urgency,
				'completed_at' => $task->completed_at,
				'last_update_by' => $task->updated_by,
				'created_at' => $task->created_at,
				'updated_at' => $task->updated_at
			],
			'links'=>['self'=>url('/checklists/'.$task->id)]
		];

	}
}


