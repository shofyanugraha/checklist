<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;


// model
use App\Models\Task;

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
      'data.attributes.name'=>'required|string',
      'data.attributes.checklist'=>'required',
      'data.attributes.checklist.description'=>'required|string',
      'data.attributes.checklist.due_interval'=>'required|numeric',
      'data.attributes.checklist.due_unit'=>'required|string',
      'data.attributes.items'=>'required|array',
      'data.attributes.items.*.description'=>'required|string',
      'data.attributes.items.*.urgency'=>'required|numeric',
      'data.attributes.items.*.due_interval'=>'required|numeric',
      'data.attributes.items.*.due_unit'=>'required|string',
    ]);

    // trying save template to db
    try {
      $template = new Template;
      $template->user_id = $request->userid;
      $template->name = $request->data['attributes']['name'];
      $template->checklist = $request->data['attributes']['checklist'];
      $template->items = $request->data['attributes']['items'];

      if($template->save()){
        return Json::response($template);
      } else {
        return Json::exception('Failed to create template');
      }
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
      
      'data.name'=>'required|string',
      'data.items'=>'required|array',
      'data.items.*.description'=>'required|string',
      'data.items.*.urgency'=>'required|numeric',
      'data.items.*.due_interval'=>'required|numeric',
      'data.items.*.due_unit'=>'required|string',
    ]);

    // trying save template to db
    try {
      $template = Template::findOrFail($id);
      $template->user_id = $request->userid;
      $template->name = $request->data['name'];
      $template->checklist = $request->data['checklist'];
      $template->items = $request->data['items'];

      if($template->save()){
        return Json::response($template);
      } else {
        return Json::exception('Failed to create template');
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
      $template = Template::findOrFail($id);
      
      if($template->delete()){
        return Json::response($template);
      } else {
        return Json::exception('Error',null, 401);
      }
    } catch (\Illuminate\Database\QueryException $e){
      return Json::exception($e->getMessage(), env('APP_ENV', 'local') == 'local' ? $e : null, 500);
    } catch (\Exception $e){
      return Json::exception($e->getMessage(), env('APP_ENV', 'local') == 'local' ? $e : null, 401);
    }
  }

  public function assign(Request $request, $id){
    $this->validate($request, [
      'data'=>'required',
      'data.*'=>'required',
      'data.*.attributes.object_id'=>'required',
      'data.*.attributes.object_domain'=>'required',
    ]);

    $template = Template::findOrFail($id);

    $return = [];
    $included = collect([]);

    foreach($request->data as $data){
      try {
        $task = new Task;

        $task->user_id = $request->userid;
        $task->object_domain = $data['attributes']['object_domain'];
        $task->object_id = $data['attributes']['object_id'];
        $task->description = $template->checklist['description'];
        $task->due = Carbon::now()->add($template->checklist['due_interval'], $template->checklist['due_unit']);
        $task->is_completed = 0;
        
        $task->type = 'checklist';
        if($task->save()){
          $itemHolder = []; 

          foreach ($template->items as $item){
            $dataItem = new Item;
            $dataItem->user_id = $request->userid;
            $dataItem->assignee_id = $request->userid;
            $dataItem->urgency = $item['urgency'];
            $dataItem->description = $item['description'];
            $dataItem->due = Carbon::now()->add($item['due_interval'], $item['due_unit']);
            $dataItem->is_completed = false;
            $itemHolder[] = $dataItem;
          }
          try{
            $task->items()->saveMany($itemHolder); 
          } catch (\Exception $e){
            return Json::exception('Error', env('APP_ENV', 'local') == 'local' ? $e : null, 401);
          }

          $relation = [];
          $relation['items']['data'] =[];
          $relation['items']['links'] = [
            'self'=> url('/checklists/'. $task->id.'/relationships/items'),
            'related'=> url('/checklists/'. $task->id.'/items'),
          ];

          $task->links = [
            'self'=> url('/checklists/'. $task->id),
          ];

          $included = $included->merge($task->items);
          foreach($task->items as $i){
            $relation['items']['data'][] = [
              'type' => 'items',
              'id' => $i->id
            ];
          }
          unset($task->items);



          $task->relationship = $relation;
          $return[] = $task;
        }
      } catch (\Illuminate\Database\QueryException $e){
        return Json::exception('Error', env('APP_ENV', 'local') == 'local' ? $e : null, 500);
      } catch (\Exception $e){
        return Json::exception('Error', env('APP_ENV', 'local') == 'local' ? $e : null, 401);
      }
    }
    return Json::response($return, null, 200, $included );
  }

}
