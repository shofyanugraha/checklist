<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use App\Models\Template;
use App\Models\User;
class ItemTest extends TestCase
{

  /*
  * 1. Create
  */

  // Test Checklist Create
  public function testItemCreateSuccess()
  {
    $paramsText ='{"data":{"attributes":{"description":"Need to verify this guy house.","due":"2019-01-19 18:34:51","urgency":"3","assignee_id":123}}}';
    $params = json_decode($paramsText, true);

    $this->post('/checklists/1/items', $params, [
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
  public function testItemCreateValidationFailed()
  {
    $paramsText ='{"data":{"attributes":{"description":"Need to verify this guy house.","due":null,"urgency":"3","assignee_id":123}}}';
    $params = json_decode($paramsText, true);

    $this->post('/checklists/1/items', $params, [
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
  public function testItemListingSucess()
  {
    $params = [];
    $this->get('/checklists/1/items', [
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


  /*
  * 3. Update
  */
  
  // Test Update Template Success
  public function testItemUpdateSuccess()
  {
    $paramsText = '{"data":{"attributes":{"description":"Hallo bro","due":"2019-05-19 18:34:51","urgency":"2","assignee_id":123}}}';
    $params = json_decode($paramsText, true);
    $this->patch('/checklists/51/items/1', $params, [
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
  public function testItemUpdateValidationFailed()
  {
    $paramsText = '{"data":{"attributes":{"description":"Hallo bro","due":null,"urgency":"2","assignee_id":123}}}';
    $params = json_decode($paramsText, true);
    $this->patch('/checklists/51/items/1', $params, [
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
  public function testItemUpdateWithoutAuthHeaderFailed()
  {
    $paramsText = '{"data":{"type":"checklists","id":1,"attributes":{"object_domain":"contact","object_id":"1","description":"Need to verify this guy house.","is_completed":"a","completed_at":"2018-01-25T07:50:14+00:00","created_at":"2018-01-25T07:50:14+00:00"}}}';
    $params = json_decode($paramsText, true);
    $this->patch('/checklists/51/items/1', $params, [
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
  * 5. Delete
  */

  public function testItemDeleteSuccess()
  {
    $this->delete('/checklists/5/items', [
      'Authorization' => 'Bearer '. User::find(1)->auth_key,
    ]);

     $this->seeStatusCode(204);
  }


  /*
  * 6. Complete / Incomplete
  */


  public function testItemComplete()
  {
    $paramsText = '{"data":[{"item_id":1},{"item_id":2},{"item_id":3},{"item_id":4}]}';
    $params = json_decode($paramsText, true);
    
    $this->post('/checklists/complete', $params, [
      'Authorization' => 'Bearer '. User::find(1)->auth_key,
    ]);

     $this->seeStatusCode(200);
     $this->seeJsonStructure(
            [
              'data'=>[
                '*'=>[
                  'id',
                  'item_id',
                  'is_completed',
                  'checklist_id',
                ]
              ]
            ]    
        );
  }



  public function testItemInComplete()
  {
    $paramsText = '{"data":[{"item_id":1},{"item_id":2},{"item_id":3},{"item_id":4}]}';
    $params = json_decode($paramsText, true);

    
    $this->post('/checklists/incomplete', $params, [
      'Authorization' => 'Bearer '. User::find(1)->auth_key,
    ]);

     $this->seeStatusCode(200);
     $this->seeJsonStructure(
            [
              'data'=>[
                '*'=>[
                  'id',
                  'item_id',
                  'is_completed',
                  'checklist_id',
                ]
              ]
            ]    
        );
  }




}
