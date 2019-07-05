<?php 
namespace App\Transformers;

use App\Models\Item;
use League\Fractal\TransformerAbstract;


class ItemTransformer extends TransformerAbstract{
	
	public function transform(Item $data, $option = []){
		$return = [
			'type'	=> 'items',
			'id'		=> $data->id,
			'attributes' => [
				'description' => $data->description,
				'is_completed' => $data->is_completed == 1 ? true : false,
				'due' => $data->due,
				'urgency' => $data->urgency,
				'completed_at' => $data->completed_at,
				'last_update_by' => $data->updated_by,
				'created_at' => $data->created_at,
				'updated_at' => $data->updated_at,
				'checklist_id' => $data->task_id,
			],
			'links'=>['self'=>url('/checklists/'.$data->task_id.'/items/'.$data->id)]
		];

		if(isset($option['relations'])){
			$relations = explode(',', $option['relations']);
			foreach($relations as $relation){
				if($data->{$relation}()->exists()){
					$return['attributes'][$relation] = $data->{$relation};
				}
			}
		}

		return $return;
	}
}


