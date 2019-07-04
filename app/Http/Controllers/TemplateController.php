<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;


// model
use App\Models\Template;

// filter
use App\Rules\Filter;

// trasnformer
use App\Transformers\Json;


class TemplateController extends Controller
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
      $templates = Template::query();

      if($request->has('sort')){
          $sorts = explode(',', $request->sort);
          foreach ($sorts as $sort) {
              $field = preg_replace('/[-]/', '', $sort);
              if (preg_match('/^[-]/', $sort)) {
                  $templates->orderBy($field, 'desc');
              } else {
                  $templates->orderBy($field, 'asc');
              }
          }
      }
      if($request->has('filter')) {
        foreach ($request->filter as $key => $filter) {
          foreach ($filter as $k => $value) {
            $value = explode(',',$value);
            if($k == 'is') {
              $templates->where($key,$value);
            } elseif ($k == '!is') {
              $templates->where($key,'!=',$value);
            } elseif ($k == 'in') {
              $templates->whereIn($key,$value);
            } elseif ($k == '!in') {
              $templates->whereNotIn($key,$value);
            } elseif ($k == 'like') {
              $templates->whereLike($key,preg_replace('/[*]/', '%', $value));
            } elseif ($k == '!like') {
              $templates->whereNotLike($key, preg_replace('/[*]/', '%', $value));
            }
          }
        }
      }
      if($request->has('field')) {
        $fields = $request->field;
        $arrayField = explode(',', $fields);
        $templates->select($arrayField);
      }

      $templates = $templates->paginate($request->input('page_limit', 10))->appends($request->all());
      // dd($templates);
      return Json::response($templates);
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
      $templates = Template::findOrFail($id);
      
      return Json::response($templates);
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
}
