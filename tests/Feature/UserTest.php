<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UserSeeder;
use Tests\TestCase;

class UserTest extends TestCase
{
    public function testRegisterSuccess()
    {
        $this->post('/api/users', [
            'username' => 'test',
            'password' => 'test123',
            'name' => 'test'
        ])->assertStatus(201)->assertJson([
            'data' => [
                'username' => 'test',
                'name' => 'test',
            ]
        ]);
    }
    public function testRegisterFailed()
    {
        $this->post('/api/users', [
            'username' => '',
            'password' => '',
            'name' => ''
        ])->assertStatus(400)->assertJson([
            'errors' => [
                'username' => ['The username field is required.'],
                'password' => ['The password field is required.'],
                'name' => ['The name field is required.'],
            ]
        ]);
    }
    public function testRegisterUsernameAlreadyExists()
    {
        $this->testRegisterSuccess();

        $this->post('/api/users', [
            'username' => 'test',
            'password' => 'test123',
            'name' => 'test'
        ])->assertStatus(400)->assertJson([
            'errors' => [
                'username' => ['Username already exists'],
            ]
        ]);
    }

    public function testLoginSuccess()
    {
        $this->seed(UserSeeder::class);

        $this->post('/api/users/login', [
            'username' => 'admin',
            'password' => 'admin123',
        ])->assertStatus(200)->assertJson([
            'data' => [
                'username' => 'admin',
                'name' => 'Admin',
            ]
        ]);

        $user = User::where('username', '=', 'admin')->first();

        self::assertNotNull($user->token);
    }

    public function testLoginFailedUsernameNotFound()
    {
        $this->post('/api/users/login', [
            'username' => 'gakada',
            'password' => 'gakada',
        ])->assertStatus(400)->assertJson([
            'errors' => [
                'message' => [
                    'username or password wrong'
                ],
            ]
        ]);
    }

    public function testGetUserSuccess()
    {
        $this->seed(UserSeeder::class);

        $this->get('/api/users/current', [
            'Authorization' => 'test'
        ])->assertStatus(200)->assertJson([
            'data' => [
                'username' => 'admin',
                'name' => 'Admin',
            ]
        ]);
    }

    public function testGetUnauthorized()
    {
        $this->seed([UserSeeder::class]);

        $this->get('/api/users/current')
            ->assertStatus(401)
            ->assertJson([
                'errors' => [
                    'message' => [
                        'Unauthorized'
                    ]
                ]
            ]);
    }

    public function testGetInvalidToken()
    {
        $this->get('/api/users/current', [
            'Authorization' => 'salah'
        ])->assertStatus(401)->assertJson([
            'errors' => [
                'message' => [
                    'Unauthorized'
                ]
            ]
        ]);
    }

    public function testUpdateNameSuccess()
    {

        $this->seed(UserSeeder::class);

        $oldUser = User::where('username', '=', 'admin')->first();

        $this->patch(
            '/api/users/current',
            [
                'name' => 'newAdmin',
            ],
            [
                'Authorization' => 'test'
            ]
        )->assertStatus(200)->assertJson([
            'data' => [
                'username' => 'admin',
                'name' => 'newAdmin',
            ]
        ]);

        $newUser = User::where('username', '=', 'admin')->first();

        $this->assertNotEquals($oldUser->name, $newUser->name);
    }


    public function testUpdateFailed()
    {

        $this->seed(UserSeeder::class);

        $this->patch(
            '/api/users/current',
            [
                'name' => 'newAdminnewAdminnewAdminnewAdminnewAdminnewAdminnewAdminnewAdminnewAdminnewAdminnewAdminnewAdminnewAdminnewAdminnewAdminnewAdminnewAdminnewAdminnewAdminnewAdminnewAdminnewAdminnewAdminnewAdminnewAdminnewAdminnewAdminnewAdminnewAdminnewAdminnewAdminnewAdminnewAdminnewAdminnewAdminnewAdminnewAdminnewAdminnewAdminnewAdminnewAdminnewAdmin',
            ],
            [
                'Authorization' => 'test'
            ]
        )->assertStatus(400)->assertJson([
            'errors' => [
                'name' => ['The name field must not be greater than 100 characters.'],
            ]
        ]);
    }

    public function testLogoutSuccess()
    {
        $this->seed(UserSeeder::class);

        $this->delete(uri: '/api/users/logout', headers: [
            'Authorization' => 'test'
        ])->assertStatus(200)->assertJson([
            'data' => true
        ]);

        self::assertNull(User::where('username', '=', 'admin')->first()->token);
    }

    public function testLogoutFailed()
    {

        $this->delete(uri: '/api/users/logout', headers: [
            'Authorization' => 'salah'
        ])->assertStatus(401)->assertJson([
            'errors' => [
                'message' => [
                    'Unauthorized'
                ]
            ]
        ]);
    }
}
