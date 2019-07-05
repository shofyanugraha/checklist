<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use App\Models\Template;
use App\Models\User;
class ChecklistTest extends TestCase
{

  /*
  * 1. Create
  */

  // Test Checklist Create
  public function testChecklistCreateSuccess()
  {
    $paramsText ='{"data":{"attributes":{"object_domain":"contact","object_id":"1","due":"2019-01-25T07:50:14+00:00","urgency":1,"description":"Need to verify this guy house.","items":["Visit his house","Capture a photo","Meet him on the house"]}}}';
    $params = json_decode($paramsText, true);

    $this->post('/checklists', $params, [
      'Authorization' => 'Bearer '. User::find(1)->auth_key,
    ]);
    $this->seeStatusCode(200);

    $this->seeJsonStructure([
            'data'=>[
              'type',
              'id',
              'attributes',
              'links'
            ]
          ]   
        );
  }

  // Test Checklist Create Validation Failed
  public function testChecklistCreateValidationFailed()
  {
    $paramsText ='{"data":{"attributes":{"object_domain":1,"object_id":"a","due":"2019-01-25T07:50:14+00:00","urgency":1,"description":"Need to verify this guy house.","items":["Visit his house","Capture a photo","Meet him on the house"]}}}';
    $params = json_decode($paramsText, true);

    $this->post('/checklists', $params, [
      'Authorization' => 'Bearer '. User::find(1)->auth_key,
    ]);
    $this->seeStatusCode(400);

    $this->seeJsonStructure(
            [
              'message',
              'status',
              'error'
            ]    
        );
  }

  /*
  * 2. Listing
  */

  // Test Listing Checklist
  public function testChecklistListingSucess()
  {
    $params = [];
    $this->get('/checklists', [
      'Authorization' => 'Bearer '. User::find(1)->auth_key,
    ]);
    $this->seeStatusCode(200);

    $this->seeJsonStructure([
            'meta'=>['total','count'],
            'links'=>['first', 'last', 'next', 'prev'],
            'data'
          ]   
        );
  }

  // Test Listing Template Filter Success
  public function testChecklistListFilterSuccess()
  {
    $this->get('/checklists?sorts=-object_id&page_limit=2&field=object_id,object_domain&filter[object_domain][like]=de', [
      'Authorization' => 'Bearer '. User::find(1)->auth_key,
    ]);

    $this->seeStatusCode(200);

    $this->seeJsonStructure([
            'meta'=>['total','count'],
            'links'=>['first', 'last', 'next', 'prev'],
            'data'
          ]   
        );
  }

  // Test Listing Template Filter Failed
  public function testChecklistListFilterFailed()
  {
    $this->get('/checklists?sorts=-object_id&page_limit=a&field=object_id,object_domain&filter[object_domain][like]=de', [
      'Authorization' => 'Bearer '. User::find(1)->auth_key,
    ]);

      $this->seeStatusCode(400);

    $this->seeJsonStructure(
            [
              'message',
              'status',
              'error'
            ]    
        );
  }

  /*
  * 3. Show
  */

  // Test Show Template Success
  public function testChecklistShowSuccess()
  {
    $params = [];
    $this->get('/checklist/'.Task::orderBy('id','desc')->first()->id, [
      'Authorization' => 'Bearer '. User::find(1)->auth_key,
    ]);

    $this->seeStatusCode(200);

    $this->seeJsonStructure([
            'data'=>[
              'type',
              'id',
              'attributes',
              'links'
            ]
          ]   
        );
  }

  // Test Show Template Not Found
  public function testChecklistShowNotFound()
  {
    $params = [];
    $this->get('/checklist/9999', [
      'Authorization' => 'Bearer '. User::find(1)->auth_key,
    ]);

    $this->seeStatusCode(404);
  }

  // Test Show Template Whithout Header
  public function testChecklistShowWithoutHeader()
  {
    $params = [];
    $this->get('/checklist/9999', [
    ]);

    $this->seeStatusCode(401);

    $this->seeJsonStructure(
            [
              'message',
              'status'
            ]    
        );
  }


  /*
  * 4. Update
  */
  
  // Test Update Template Success
  public function testChecklistUpdateSuccess()
  {
    $paramsText = '{"data":{"type":"checklists","id":1,"attributes":{"object_domain":"contact","object_id":"1","description":"Need to verify this guy house.","is_completed":false,"completed_at":"2018-01-25T07:50:14+00:00","created_at":"2018-01-25T07:50:14+00:00"}}}';
    $params = json_decode($paramsText, true);
    $this->patch('/checklists/1', $params, [
      'Authorization' => 'Bearer '. User::find(1)->auth_key,
    ]);

    $this->seeStatusCode(200);

    $this->seeJsonStructure([
            'data'=>[
              'type',
              'id',
              'attributes',
              'links'
            ]
          ]   
        );
  }

  // Test Update Template Validation Failed
  public function testChecklistUpdateValidationFailed()
  {
    $paramsText = '{"data":{"type":"checklists","id":1,"attributes":{"object_domain":"contact","object_id":"1","description":"Need to verify this guy house.","is_completed":"a","completed_at":"2018-01-25T07:50:14+00:00","created_at":"2018-01-25T07:50:14+00:00"}}}';
    $params = json_decode($paramsText, true);
    $this->patch('/checklists/1', $params, [
      'Authorization' => 'Bearer '. User::find(1)->auth_key,
    ]);

    $this->seeStatusCode(400);

    $this->seeJsonStructure(
            [
              'message',
              'status',
              'error'
            ]    
        );
  }

  // Test Update Template Without Header
  public function testChecklistUpdateWithoutAuthHeaderFailed()
  {
    $paramsText = '{"data":{"type":"checklists","id":1,"attributes":{"object_domain":"contact","object_id":"1","description":"Need to verify this guy house.","is_completed":"a","completed_at":"2018-01-25T07:50:14+00:00","created_at":"2018-01-25T07:50:14+00:00"}}}';
    $params = json_decode($paramsText, true);
    $this->patch('/checklists/1', $params, [
    ]);

    $this->seeStatusCode(401);

    $this->seeJsonStructure(
            [
              'message',
              'status'
            ]    
        );
  }

  /*
  * 5. Assign
  */

  // Test Update Template Without Header
  public function testAssignToObjectSuccess()
  {
    $paramsText = '{"data":[{"attributes":{"object_id":1,"object_domain":"deals"}},{"attributes":{"object_id":2,"object_domain":"deals"}},{"attributes":{"object_id":3,"object_domain":"deals"}}]}';
    $params = json_decode($paramsText, true);
    $this->post('/checklist/1/assigns', $params, [
      'Authorization' => 'Bearer '. User::find(1)->auth_key,
    ]);

    $this->seeStatusCode(200);

    $this->seeJsonStructure([
            'data'=>[
              '*'=>[
                'user_id',
                'object_domain',
                'description',
                'due',
                'is_completed',
                'type',
                'updated_at',
                'created_at',
                'id',
                'links'=>['self'],
                'relationship'=>[
                  'items'=> [
                    'data'=>[
                      '*'=>[
                        'type',
                        'id'
                      ],
                    ],
                    'links'=>['self', 'related']
                  ],
                ]
              ],  
            ],
            'included'=>[
              '*'=>[
                'id',
                'user_id',
                'description',
                'is_completed',
                'completed_at',
                'due',
                'urgency',
                'updated_by',
                'assignee_id',
                'task_id',
                'created_at',
                'updated_at'
              ]
            ]
          ]   
        );
  }

  // Teset Validation Failed

  public function testAssignTemplateToObjectValidationFailed()
  {
    $paramsText = '{"data":[{"attributes":{"object_id":"a","object_domain":"deals"}},{"attributes":{"object_id":2,"object_domain":"deals"}},{"attributes":{"object_id":3,"object_domain":"deals"}}]}';
    $params = json_decode($paramsText, true);
    $this->post('/checklist/1/assigns', $params, [
      'Authorization' => 'Bearer '. User::find(1)->auth_key,
    ]);
    // dd($paramsText);

     $this->seeStatusCode(500);

    $this->seeJsonStructure(
            [
              'message',
              'status'
            ]    
        );
  }

  /*
  * 6. Delete
  */

  public function testDeleteTemplateSuccess()
  {
    $this->delete('/checklists/5', [
      'Authorization' => 'Bearer '. User::find(1)->auth_key,
    ]);

     $this->seeStatusCode(204);
  }

}
