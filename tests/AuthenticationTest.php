<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use App\Models\User;
class AuthenticationTest extends TestCase
{
  /**
   * A basic test example.
   *
   * @return void
   */
  public function testRegisterSuccess()
  {
    $params = [
        'email' => 'rizkyshofyan@gmail.com',
        'password' => 'bismillah',
        'name' => 'rizky'
    ];
    $this->post('/register', $params, []);
    $this->seeStatusCode(200);

    $this->seeJsonStructure(
            ['data' =>
              [
                'name',
                'email',
                'created_at',
                'updated_at',
              ]
            ]    
        );
    User::where('email', 'rizkyshofyan@gmail.com')->delete();
  }

  public function testRegisterEmailExists()
  {
    $params = [
        'email' => 'rizkyshofyan@gmail.com',
        'password' => 'bismillah',
        'name' => 'rizky'
    ];
    $this->post('/register', $params, []);
    $this->seeStatusCode(200);

    $this->seeJsonStructure(
            ['data' =>
              [
                'name',
                'email',
                'created_at',
                'updated_at',
              ]
            ]    
        );
    $this->post('/register', $params, []);
    $this->seeStatusCode(400);

    $this->seeJsonStructure(
      [
        'message',
        'status',
        'error'
      ]    
    );

    $this->seeJson(['status'=>false]);

    User::where('email', 'rizkyshofyan@gmail.com')->delete();
  }

  public function testRegisterValidation()
  {
    $params = [
        'email' => 'rizkyshofyangmail.com',
        'password' => null,
        'name' => -1
    ];
    $this->post('/register', $params, []);
    $this->seeStatusCode(400);

    $this->seeJsonStructure(
            [
              'message',
              'status',
              'error'
            ]    
        );

    $this->seeJson(['status'=>false]);
  }

  public function testLoginSuccess()
  {
    $params = [
        'email' => 'user@example.com',
        'password' => 'password',
    ];
    $this->post('/login', $params, []);
    $this->seeStatusCode(200);

    $this->seeJsonStructure(
        [
          'status',
          'key',
        ]    
    );

    $this->seeJson(['status'=>true]);
  }

  public function testLoginValidation()
  {
    $params = [
        'email' => 'rizkyshofyan@gmail.com',
        'password' => null,
    ];
    $this->post('/login', $params, []);
    $this->seeStatusCode(400);

    $this->seeJsonStructure(
       [
          'message',
          'status',
          'error'
        ]  
    );

    $this->seeJson(['status'=>false]);
  }
}
