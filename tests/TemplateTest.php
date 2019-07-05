<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use App\Models\Template;
use App\Models\User;
class TemplateTest extends TestCase
{

  /*
  * 1. Create
  */

  // Test Template Create
  public function testTemplateCreateSuccess()
  {
    $paramsText ='{"data":{"attributes":{"name":"foo template","checklist":{"description":"my checklist","due_interval":3,"due_unit":"hour"},"items":[{"description":"my foo item","urgency":2,"due_interval":40,"due_unit":"minute"},{"description":"my bar item","urgency":3,"due_interval":30,"due_unit":"minute"}]}}}';
    $params = json_decode($paramsText, true);
    // dd($params);

    $this->post('/template', $params, [
      'Authorization' => 'Bearer '. User::find(1)->auth_key,
    ]);
    $this->seeStatusCode(200);

    $this->seeJsonStructure([
            'data'=>[
              'name',
              'checklist'=>[
                'description',
                'due_interval',
                'due_unit'
              ],
              'items'=> [
                '*' => [
                  'description',
                  'urgency',
                  'due_interval',
                  'due_unit'
                ] 
              ],
              'updated_at',
              'created_at',
              'id'
            ]
          ]   
        );
  }

  // Test Template Create Validation Failed
  public function testTemplateCreateValidationFailed()
  {
    $paramsText ='{"data":{"attributes":{"name":null,"checklist":{"description":"my checklist","due_interval":3,"due_unit":"hour"},"items":null}}}';
    $params = json_decode($paramsText, true);
    // dd($params);

    $this->post('/template', $params, [
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

  // Test Listing Template
  public function testTemplateListingSucess()
  {
    $params = [];
    $this->get('/template', [
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
  public function testTemplateListFilterSuccess()
  {
    $this->get('/template?sorts=-id&amp;page_limit=2&amp;field=name,checklist,items&amp;filter[name][like]=foo', [
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
  public function testTemplateListFilterFailed()
  {
    $this->get('/template?sorts=-id&amp;page_limit=a&amp;field=namee,checklist,items&amp;filter[name][andu]=foo', [
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
  public function testTemplateShowSuccess()
  {
    $params = [];
    $this->get('/template/1', [
      'Authorization' => 'Bearer '. User::find(1)->auth_key,
    ]);

    $this->seeStatusCode(200);

    $this->seeJsonStructure([
            'data'=>[
              'name',
              'checklist'=>[
                'description',
                'due_interval',
                'due_unit'
              ],
              'items'=> [
                '*' => [
                  'description',
                  'urgency',
                  'due_interval',
                  'due_unit'
                ] 
              ],
              'updated_at',
              'created_at',
              'id'
            ]
          ]   
        );
  }

  // Test Show Template Not Found
  public function testTemplateShowNotFound()
  {
    $params = [];
    $this->get('/template/9999', [
      'Authorization' => 'Bearer '. User::find(1)->auth_key,
    ]);

    $this->seeStatusCode(404);
  }

  // Test Show Template Whithout Header
  public function testTemplateShowWithoutHeader()
  {
    $params = [];
    $this->get('/template/9999', [
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
  public function testTemplateUpdateSuccess()
  {
    $paramsText = '{"data":{"name":"foo template","checklist":{"description":"my checklist","due_interval":3,"due_unit":"hour"},"items":[{"description":"my foo item","urgency":2,"due_interval":40,"due_unit":"minute"},{"description":"my bar item","urgency":3,"due_interval":30,"due_unit":"minute"}]}}';
    $params = json_decode($paramsText, true);
    $this->patch('/template/1', $params, [
      'Authorization' => 'Bearer '. User::find(1)->auth_key,
    ]);

    $this->seeStatusCode(200);

    $this->seeJsonStructure([
            'data'=>[
              'name',
              'checklist'=>[
                'description',
                'due_interval',
                'due_unit'
              ],
              'items'=> [
                '*' => [
                  'description',
                  'urgency',
                  'due_interval',
                  'due_unit'
                ] 
              ],
              'updated_at',
              'created_at',
              'id'
            ]
          ]   
        );
  }

  // Test Update Template Validation Failed
  public function testTemplateUpdateValidationFailed()
  {
    $paramsText = '{"data":{"name":"foo larva","checklist":{"description":null,"due_interval":3,"due_unit":-1},"items":null}}';
    $params = json_decode($paramsText, true);
    $this->patch('/template/1', $params, [
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
  public function testTemplateUpdateWithoutAuthHeaderFailed()
  {
    $paramsText = '{"data":{"name":"foo larva","checklist":{"description":null,"due_interval":3,"due_unit":-1},"items":null}}';
    $params = json_decode($paramsText, true);
    $this->patch('/template/1', $params, [
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
    $this->post('/template/1/assigns', $params, [
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

  public function testAssignToObjectValidationFailed()
  {
    $paramsText = '{"data":[{"attributes":{"object_id":"a","object_domain":"deals"}},{"attributes":{"object_id":2,"object_domain":"deals"}},{"attributes":{"object_id":3,"object_domain":"deals"}}]}';
    $params = json_decode($paramsText, true);
    $this->post('/template/1/assigns', $params, [
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

}
