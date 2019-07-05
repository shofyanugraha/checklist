<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;


// model
use App\Models\Task;
use App\Models\Item;

// filter
use App\Rules\Filter;
use App\Rules\ISODateValidator;

// trasnformer
use App\Transformers\Json;
use App\Transformers\TaskTransformer;
use App\Transformers\ItemTransformer;

// use package
use Carbon\Carbon;
use League\Fractal\Resource\Collection;


class ItemController extends Controller
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


  // do completing an items
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
          return Json::exception('item_id '. $dt['item_id'].' has been completed before', null, 400);  
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

  // do incompletig an items
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
          return Json::exception('item_id '. $dt['item_id'].' has been incompleted before', null, 400);  
        }
      } catch (\Illuminate\Database\QueryException $e){
        return Json::exception($e->getMessage(), env('APP_ENV', 'local') == 'local' ? $e : null, 500);
      } catch (\Exception $e){
        return Json::exception($e->getMessage(), env('APP_ENV', 'local') == 'local' ? $e : null, 400);
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
  

  // list items
  public function index(Request $request, $id){
    $this->validate($request, [
    ]);

    try{
      $items = Item::where('task_id', $id)->paginate($request->input('offset', 10));
      $items->getCollection()->transform(function($item, $key){
        return (new ItemTransformer)->transform($item);
      });
      // $task = (new TaskTransformer)->single($tasks, ['relations'=>'items']);
      
      return Json::response($items);
    } catch (\Illuminate\Database\QueryException $e){
      return Json::exception($e->getMessage(), env('APP_ENV', 'local') == 'local' ? $e : null, 500);
    } catch (\Exception $e){
      return Json::exception($e->getMessage(), env('APP_ENV', 'local') == 'local' ? $e : null, 400);
    }
  }

  public function store(Request $request, $id){
    $this->validate($request, [
      'data.attributes.description'=>'required',
      'data.attributes.due'=>'required',
      'data.attributes.urgency'=>'numeric',
      'data.attributes.assignee_id'=>'required',
    ]);

    $due = Carbon::parse($request->data['attributes']['due'])->toDateTimeString();

    try{
      $tasks = Task::findOrFail($id);
      $dataItem = new Item;
      $dataItem->user_id = $request->userid;
      $dataItem->assignee_id = $request->userid;
      $dataItem->urgency = $request->data['attributes']['urgency'];
      $dataItem->description = $request->data['attributes']['description'];
      $dataItem->due = $due;
      $dataItem->is_completed = false;
      if($tasks->items()->save($dataItem)){
        $task = (new TaskTransformer)->single($tasks, ['relations'=>'items']);
      }
      

      return Json::response($task);
    } catch (\Illuminate\Database\QueryException $e){
      return Json::exception($e->getMessage(), env('APP_ENV', 'local') == 'local' ? $e : null, 500);
    } catch (\Exception $e){
      return Json::exception($e->getMessage(), env('APP_ENV', 'local') == 'local' ? $e : null, 400);
    }
  }

  // update item
  public function update(Request $request, $id, $itemID){
    $this->validate($request, [
      'data.attributes.description'=>'required',
      'data.attributes.due'=>'required',
      'data.attributes.urgency'=>'numeric',
      'data.attributes.assignee_id'=>'required',
    ]);

    $due = Carbon::parse($request->data['attributes']['due'])->toDateTimeString();

    try{
      $tasks = Task::findOrFail($id);
      try{
        $dataItem = Item::findOrFail($itemID);
        if($dataItem->task_id == $id){
          $dataItem->updated_by = $request->userid;
          $dataItem->assignee_id = $request->userid;
          $dataItem->urgency = $request->data['attributes']['urgency'];
          $dataItem->description = $request->data['attributes']['description'];
          $dataItem->due = $due;
          if($dataItem->save()){
            $task = (new TaskTransformer)->single($tasks, ['relations'=>'items']);
            return Json::response($task);
          } else {
            return Json::exception('Cannot udpate' ,null, 400);  
          }
        } else {
          return Json::exception('Item Doesnt Match Checklist' ,null, 404);  
        }
      } catch (\Illuminate\Database\QueryException $e){
        return Json::exception($e->getMessage(), env('APP_ENV', 'local') == 'local' ? $e : null, 500);
      } catch (\Exception $e){
        return Json::exception($e->getMessage(), env('APP_ENV', 'local') == 'local' ? $e : null, 400);
      }
    } catch(\Exception $e){
      return Json::exception($e->getMessage(), env('APP_ENV', 'local') == 'local' ? $e : null, 400);
    }
  }

  public function updateBulk(Request $request, $id){
    $this->validate($request, [
      'data'=>'required|array',
      'data.*.id'=>'required|exists:items,id',
      'data.*.action'=>'required|in:update,delete',
      'data.*.attributes'=>'required|array',
    ]);

    $return = [];
    try{
      $tasks = Task::findOrFail($id);
      foreach($request->data as $data){
        $due = Carbon::parse($data['attributes']['due'])->toDateTimeString();
        $dtReturn = [
          'id'      =>  $data['id'],
          'action'  =>  $data['action'],
          'status'  =>  404
        ];
        try{
          $dataItem = Item::findOrFail($data['id']);
          if($dataItem->task_id == $id) {
            if($data['action'] == 'update'){
              $dataItem->updated_by = $request->userid;
              $dataItem->assignee_id = $request->assignee_id;
              $dataItem->urgency = $request->data['attributes']['urgency'];
              $dataItem->description = $request->data['attributes']['description'];
              $dataItem->due = $due;
              if($dataItem->save()){
                $dtReturn['status'] = 200;
              } else {
                $dtReturn['status'] = 400;
                return Json::exception('Cannot udpate' ,null, 400);  
              }
            } elseif($data['action'] == 'delete') {

              if($dataItem->delete()){
                $dtReturn['status'] = 204;
              }
            } else {
              $dtReturn['status'] = 400;
            }
          } else {
            $dtReturn['status'] = 404;
          }
        } catch (\Illuminate\Database\QueryException $e){
          $dtReturn['status'] = 500;
        } catch (\Exception $e){
          $dtReturn['status'] = 404;
        }
        $return[] = $dtReturn;
      }
      return Json::response($return, null);
    } catch (\Illuminate\Database\QueryException $e){
      return Json::exception($e->getMessage(), env('APP_ENV', 'local') == 'local' ? $e : null, 500);
    } catch (\Exception $e){
      return Json::exception($e->getMessage(), env('APP_ENV', 'local') == 'local' ? $e : null, 400);
    }
  }

  public function delete(Request $request, $id, $itemID){
    $this->validate($request, [
    ]);

    try{
      $dataItem = Item::findOrFail($itemID);
      if($dataItem->delete()){
        return Json::response(null, null, 204);
      } else {
        return Json::exception('Cannot udpate' ,null, 400);  
      }
    } catch (\Illuminate\Database\QueryException $e){
      return Json::exception($e->getMessage(), env('APP_ENV', 'local') == 'local' ? $e : null, 500);
    } catch (\Exception $e){
      return Json::exception($e->getMessage(), env('APP_ENV', 'local') == 'local' ? $e : null, 400);
    }
  }

  public function summary(Request $request){
    $this->validate($request, [
      'date'=>'required',
      'tz'=>'string',
      'object_domain'=>'string'
    ]);

    $date = Carbon::parse($request->data)->toDateString();
    $thisWeek = Carbon::parse($request->data)->subWeek(1)->toDateString();
    $thisMonth = Carbon::parse($request->data)->subMonth(1)->toDateString();
    
    $data = [
      'total' => Item::whereDate('completed_at', '<=' ,$date)->count(),
      'today' => Item::whereDate('completed_at', $date)->count(),
      'past_due' => Item::whereDate('due', '<', $date)->where('is_completed', 0)->count(),
      'this_week' => Item::whereBetween('completed_at', [$thisWeek. ' 00:00:00', $date.' 23:59:59'])->where('is_completed',1)->count(),
      'past_week' => Item::whereBetween('due', [$thisWeek. ' 00:00:00', $date.' 23:59:59'])->where('is_completed', 0)->count(),
      'past_month' => Item::whereBetween('due', [$thisMonth. ' 00:00:00', $date.' 23:59:59'])->where('is_completed', 0)->count(),
      'this_month' => Item::whereBetween('completed_at', [$thisMonth. ' 00:00:00', $date.' 23:59:59'])->where('is_completed', 1)->count(),
      
    ];

    return Json::response($data, null, 200);
    
  }

}
