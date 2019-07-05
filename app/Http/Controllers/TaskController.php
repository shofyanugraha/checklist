<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;


// model
use App\Models\Task;
use App\Models\Item;

// filter
use App\Rules\Filter;

// trasnformer
use App\Transformers\Json;
use App\Transformers\TaskTransformer;

// use package
use Carbon\Carbon;
use League\Fractal\Resource\Collection;


class TaskController extends Controller
{
  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct()
  {
      $this->middleware('auth:api');
  }


  // Process Store Template
  public function store(Request $request){
    $this->validate($request, [
      'data'=>'required',
      'data.attributes'=>'required',
      'data.attributes.object_domain'=>'required|string',
      'data.attributes.object_id'=>'required|numeric',
      'data.attributes.due'=>'required',
      'data.attributes.urgency'=>'numeric',
      'data.attributes.description'=>'string',
      'data.attributes.items'=>'array',
    ]);

    $due = Carbon::parse($request->data['attributes']['due'])->toDateTimeString();
    // trying save template to db
    try {
      $task = new Task;
      $task->user_id = $request->userid;
      $task->object_domain = $request->data['attributes']['object_domain'];
      $task->object_id = $request->data['attributes']['object_id'];
      $task->description = $request->data['attributes']['description'];
      $task->due = $due;
      $task->is_completed = 0;
      $task->type = 'checklist';
      
      if($task->save()){
        if($request->has('data.attributes.items')){
          $itemHolder = [];
          foreach($request->data['attributes']['items'] as $item){
            $dataItem = new Item;
            $dataItem->user_id = $request->userid;
            $dataItem->assignee_id = $request->userid;
            $dataItem->urgency = $request->data['attributes']['urgency'];
            $dataItem->description = $item;
            $dataItem->due = $due;
            $dataItem->is_completed = false;
            $itemHolder[] = $dataItem;
          }
          try{
            $task->items()->saveMany($itemHolder); 
          } catch (\Illuminate\Database\QueryException $e){
            return Json::exception('Server Error', env('APP_ENV', 'local') == 'local' ? $e : null, 500);
          } catch (\Exception $e){
            return Json::exception('Error', env('APP_ENV', 'local') == 'local' ? $e : null, 401);
          }
        }  
        $response = (new TaskTransformer)->single($task);
        return Json::response($response);
      } else {
        return Json::exception('Failed to create task');
      }
    } catch (\Illuminate\Database\QueryException $e){ 
            return Json::exception('Server Error', env('APP_ENV', 'local') == 'local' ? $e : null, 500);
    } catch (\Exception $e) {
        return Json::exception($e->getMessage(), env('APP_ENV', 'local') == 'local' ? $e : null, 401);
    }
  }

  // List Index
  public function index(Request $request){
    $this->validate($request, [
      'sort'=>'string',
      'filter'=>[new Filter],
      'page_limit'=> 'numeric',
      'field'=>'string'
    ]);

    try{
      $tasks = Task::query();

      if($request->has('sort')){
          $sorts = explode(',', $request->sort);
          foreach ($sorts as $sort) {
              $field = preg_replace('/[-]/', '', $sort);
              if (preg_match('/^[-]/', $sort)) {
                  $tasks->orderBy($field, 'desc');
              } else {
                  $tasks->orderBy($field, 'asc');
              }
          }
      }
      if($request->has('filter')) {
        foreach ($request->filter as $key => $filter) {
          foreach ($filter as $k => $value) {
            $value = explode(',',$value);
            if($k == 'is') {
              $tasks->where($key,$value);
            } elseif ($k == '!is') {
              $tasks->where($key,'!=',$value);
            } elseif ($k == 'in') {
              $tasks->whereIn($key,$value);
            } elseif ($k == '!in') {
              $tasks->whereNotIn($key,$value);
            } elseif ($k == 'like') {
              $tasks->whereLike($key,preg_replace('/[*]/', '%', $value));
            } elseif ($k == '!like') {
              $tasks->whereNotLike($key, preg_replace('/[*]/', '%', $value));
            }
          }
        }
      }
      if($request->has('field')) {
        $fields = $request->field;
        $arrayField = explode(',', $fields);
        $tasks->select($arrayField);
      }

      $tasks = $tasks->paginate($request->input('page_limit', 10))->appends($request->all());
      $tasks->getCollection()->transform(function($task, $key){
        return (new TaskTransformer)->single($task);
      });
      
      return Json::response($tasks);
    } catch (\Illuminate\Database\QueryException $e){
      return Json::exception($e->getMessage(), env('APP_ENV', 'local') == 'local' ? $e : null, 500);
    } catch (\Exception $e){
      return Json::exception($e->getMessage(), env('APP_ENV', 'local') == 'local' ? $e : null, 401);
    }
  }

  public function show(Request $request, $id){
    $this->validate($request, [
    ]);

    try{
      $tasks = Task::findOrFail($id);
      $task = (new TaskTransformer)->single($tasks);
      
      return Json::response($task);
    } catch (\Illuminate\Database\QueryException $e){
      return Json::exception($e->getMessage(), env('APP_ENV', 'local') == 'local' ? $e : null, 500);
    } catch (\Exception $e){
      return Json::exception($e->getMessage(), env('APP_ENV', 'local') == 'local' ? $e : null, 401);
    }
  }

  public function update(Request $request, $id){
    $this->validate($request, [
      'data'=>'required',
      'data.attributes'=>'required',
      'data.attributes.object_domain'=>'required|string',
      'data.attributes.object_id'=>'required|numeric',
      'data.attributes.description'=>'string',
      'data.attributes.is_completed'=>'required|boolean',
      'data.attributes.completed_at'=>'string',
    ]);

    // trying update task to db
    try {
      $task = Task::findOrFail($id);
      $task->updated_by = $request->userid;

    

      if($task->save()){
        $task = (new TaskTransformer)->single($task);
      
        return Json::response($task);
      } else {
        return Json::exception('Failed to update task');
      }
    } catch (\Exception $e) {
        return Json::exception($e->getMessage(), env('APP_ENV', 'local') == 'local' ? $e : null, 401);
    }
  }

  // delete data
  public function delete(Request $request, $id){
    $this->validate($request, [
    ]);

    try{
      $task = Task::findOrFail($id);
      
      if($task->delete()){
        return Json::response(null, null, 204);
      } else {
        return Json::exception('Error',null, 400);
      }
    } catch (\Illuminate\Database\QueryException $e){
      return Json::exception($e->getMessage(), env('APP_ENV', 'local') == 'local' ? $e : null, 500);
    } catch (\Exception $e){
      return Json::exception($e->getMessage(), env('APP_ENV', 'local') == 'local' ? $e : null, 404);
    }
  }

  public function complete(Request $request){
    $this->validate($request, [
      'data'=>'required',
      'data.*'=>'required',
      'data.*.item_id'=>'required|numeric|exists:items,id'
    ]);

    //collector checklist id for check items
    $checklist_ids = collect([]);

    //data for return
    $return = [];

    $completed = Carbon::now()->toDateTimeString();
    
    foreach($request->data as $dt){
      try{
        $item = Item::find($dt['item_id']);
        if($item->is_completed != true) {
          $item->is_completed = true;
          $item->completed_at = $completed;
          if($item->save()){
            $return[] = [
              'id' => $item->id,
              'item_id' => $item->id,
              'is_completed' => $item->is_completed == 1 ? true : false,
              'checklist_id' => $item->task_id,
            ];
            $checklist_ids = $checklist_ids->push($item->task_id);
          } else {
            return Json::exception('Cannot bulk update', null, null, 400);
          }  
        } else {
          return Json::exception('item_id '. $dt['item_id'].' has been completed before', null, 401);  
        }
      } catch (\Illuminate\Database\QueryException $e){
        return Json::exception($e->getMessage(), env('APP_ENV', 'local') == 'local' ? $e : null, 500);
      } catch (\Exception $e){
        return Json::exception($e->getMessage(), env('APP_ENV', 'local') == 'local' ? $e : null, 404);
      }      
    }

    // //check all item complete for checklist
    foreach($checklist_ids->unique() as $chk){
      $task = Task::find($chk);
      $chkItem = $task->items; 
      
      if($chkItem->count() == $chkItem->where('is_completed', 1)->count()){
        
        // check if task is_completed = false before complete the task
        if($task->is_completed != true){
          $task->is_completed = true;
          $task->completed_at = $completed;
          $task->save();
        }
      } 
    }


    return Json::response($return, null, 200, null, 'single');
  }

  public function incomplete(Request $request){
    $this->validate($request, [
      'data'=>'required',
      'data.*'=>'required',
      'data.*.item_id'=>'required|numeric|exists:items,id'
    ]);

    //collector checklist id for check items
    $checklist_ids = collect([]);

    //data for return
    $return = [];

    $completed = Carbon::now()->toDateTimeString();
    
    foreach($request->data as $dt){
      try{
        $item = Item::find($dt['item_id']);
        if($item->is_completed != false) {
          $item->is_completed = false;
          $item->completed_at = $completed;
          if($item->save()){
            $return[] = [
              'id' => $item->id,
              'item_id' => $item->id,
              'is_completed' => $item->is_completed == 1 ? true : false,
              'checklist_id' => $item->task_id,
            ];
            $checklist_ids = $checklist_ids->push($item->task_id);
          } else {
            return Json::exception('Cannot bulk update', null, null, 400);
          }  
        } else {
          return Json::exception('item_id '. $dt['item_id'].' has been incompleted before', null, 401);  
        }
      } catch (\Illuminate\Database\QueryException $e){
        return Json::exception($e->getMessage(), env('APP_ENV', 'local') == 'local' ? $e : null, 500);
      } catch (\Exception $e){
        return Json::exception($e->getMessage(), env('APP_ENV', 'local') == 'local' ? $e : null, 404);
      }      
    }

    // //check all item complete for checklist
    foreach($checklist_ids->unique() as $chk){
      $task = Task::find($chk);
      $chkItem = $task->items; 
      
      if($chkItem->count() == $chkItem->where('is_completed', 1)->count()){
        
        // check if task is_completed = false before complete the task
        if($task->is_completed != true){
          $task->is_completed = true;
          $task->completed_at = $completed;
          $task->save();
        }
      } 
    }


    return Json::response($return, null, 200, null, 'single');
  }
}
