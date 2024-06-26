<?php

namespace Tests\Feature;

use App\Models\Contact;
use Database\Seeders\ContactSeeder;
use Database\Seeders\SearchSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class ContactTest extends TestCase
{
    /**`
     * A basic feature test example.
     */
    public function testCreateSuccess(): void
    {
        $this->seed(UserSeeder::class);

        $this->post('/api/contacts', [
            'first_name' => 'test',
            'last_name' => 'test',
            'email' => 'test@gmail.com',
            'phone' => 'test'
        ], [
            'Authorization' => 'test'
        ])->assertStatus(201)->assertJson([
            'data' => [
                'first_name' => 'test',
                'last_name' => 'test',
                'email' => 'test@gmail.com',
                'phone' => 'test',
            ]
        ]);
    }

    public function testCreateFailed(): void
    {
        $this->seed(UserSeeder::class);
        $this->post('/api/contacts', [
            'first_name' => '',
            'last_name' => '',
            'email' => 'salah',
            'phone' => ''
        ], [
            'Authorization' => 'test'
        ])->assertStatus(400)->assertJson([
            'errors' => [
                'first_name' => ['The first name field is required.'],
                'email' => ['The email field must be a valid email address.'],
                'phone' => ['The phone field is required.'],
            ]
        ]);
    }

    public function testGetSuccess()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);
        $contact = Contact::query()->first();

        $this->get("/api/contacts/$contact->id", [
            'Authorization' => 'test'
        ])->assertStatus(200)->assertJson([
            'data' => [
                'first_name' => $contact->first_name,
                'last_name' => $contact->last_name,
                'email' => $contact->email,
                'phone' => $contact->phone
            ]
        ]);
    }

    public function testGetNotFound()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);
        $contact = Contact::query()->first();

        $this->get("/api/contacts/$contact->id" . +1, [
            'Authorization' => 'test'
        ])->assertStatus(404)->assertJson([
            'errors' => [
                'message' => [
                    'not found'
                ]
            ]
        ]);
    }

    public function testUpdateSuccess()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);
        $contact = Contact::query()->limit(1)->first();

        $this->put("/api/contacts/$contact->id", [
            'first_name' => 'update',
            'last_name' => 'update',
            'email' => 'update@gmail.com',
            'phone' => 'update'
        ], [
            'Authorization' => 'test'
        ])->assertStatus(200)->assertJson([
            'data' => [
                'first_name' => 'update',
                'last_name' => 'update',
                'email' => 'update@gmail.com',
                'phone' => 'update',
            ]
        ]);
    }

    public function testUpdateFailed()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);
        $contact = Contact::query()->limit(1)->first();

        $this->put("/api/contacts/$contact->id" . +1, [
            'first_name' => 'update',
            'last_name' => 'update',
            'email' => 'update@gmail.com',
            'phone' => 'update'
        ], [
            'Authorization' => 'test'
        ])->assertStatus(404)->assertJson([
            'errors' => [
                'message' => [
                    'not found'
                ]
            ]
        ]);
    }

    public function testDeleteSuccess()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);
        $contact = Contact::query()->limit(1)->first();

        $this->delete("/api/contacts/$contact->id", [], [
            'Authorization' => 'test'
        ])->assertStatus(200)->assertJson([
            'data' => true
        ]);
    }

    public function testDeleteNotFound()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);
        $contact = Contact::query()->limit(1)->first();

        $this->delete("/api/contacts/$contact->id" . +1, [], [
            'Authorization' => 'test'
        ])->assertStatus(404)->assertJson([
            "errors" => [
                "message" => [
                    "not found"
                ]
            ]
        ]);
    }

    public function testSearchByFirstName()
    {
        $this->seed([UserSeeder::class, SearchSeeder::class]);

        $response = $this->get('/api/contacts?name=first',[
            'Authorization' => 'test'
        ])
        ->assertStatus(200)
        ->json();

        Log::info(json_encode($response, JSON_PRETTY_PRINT));

        self::assertEquals(10, count($response['data']));
    }

    public function testSearchByLastName()
    {
        $this->seed([UserSeeder::class, SearchSeeder::class]);

        $response = $this->get('/api/contacts?name=last', [
            'Authorization' => 'test'
        ])
            ->assertStatus(200)
            ->json();

        Log::info(json_encode($response, JSON_PRETTY_PRINT));

        self::assertEquals(10, count($response['data']));
        self::assertEquals(20, $response['meta']['total']);
    }

    public function testSearchByEmail()
    {
        $this->seed([UserSeeder::class, SearchSeeder::class]);

        $response = $this->get('/api/contacts?email=test', [
            'Authorization' => 'test'
        ])
            ->assertStatus(200)
            ->json();

        Log::info(json_encode($response, JSON_PRETTY_PRINT));

        self::assertEquals(10, count($response['data']));
        self::assertEquals(20, $response['meta']['total']);
    }

    public function testSearchByPhone()
    {
        $this->seed([UserSeeder::class, SearchSeeder::class]);

        $response = $this->get('/api/contacts?phone=11111', [
            'Authorization' => 'test'
        ])
            ->assertStatus(200)
            ->json();

        Log::info(json_encode($response, JSON_PRETTY_PRINT));

        self::assertEquals(10, count($response['data']));
        self::assertEquals(20, $response['meta']['total']);
    }

    public function testSearchNotFound()
    {
        $this->seed([UserSeeder::class, SearchSeeder::class]);

        $response = $this->get('/api/contacts?name=tidakada', [
            'Authorization' => 'test'
        ])
            ->assertStatus(200)
            ->json();

        Log::info(json_encode($response, JSON_PRETTY_PRINT));

        self::assertEquals(0, count($response['data']));
        self::assertEquals(0, $response['meta']['total']);
    }

    public function testSearchWithPage()
    {
        $this->seed([UserSeeder::class, SearchSeeder::class]);

        $response = $this->get('/api/contacts?size=5&page=2', [
            'Authorization' => 'test'
        ])
            ->assertStatus(200)
            ->json();

        Log::info(json_encode($response, JSON_PRETTY_PRINT));

        self::assertEquals(5, count($response['data']));
        self::assertEquals(20, $response['meta']['total']);
        self::assertEquals(2, $response['meta']['current_page']);
    }

}
