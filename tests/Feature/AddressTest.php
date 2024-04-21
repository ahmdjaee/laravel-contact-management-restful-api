<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Contact;
use Database\Seeders\AddressSeeder;
use Database\Seeders\ContactSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AddressTest extends TestCase
{
    public function testCreateSuccess(): void
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);

        $contact = Contact::query()->limit(1)->first();

        $this->post(
            "/api/contacts/$contact->id/addresses",
            [
                'street' => 'test',
                'city' => 'test',
                'province' => 'test',
                'country' => 'test',
                'postal_code' => 'test'
            ],
            [
                'Authorization' => 'test'
            ]
        )->assertStatus(201)->assertJson([
            'data' => [
                'street' => 'test',
                'city' => 'test',
                'province' => 'test',
                'country' => 'test',
                'postal_code' => 'test'
            ]
        ]);
    }

    public function testCreateFailed(): void
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);

        $contact = Contact::query()->limit(1)->first();

        $this->post(
            "/api/contacts/$contact->id/addresses",
            [
                'street' => 'test',
                'city' => 'test',
                'province' => 'test',
                'country' => '',
                'postal_code' => 'test'
            ],
            [
                'Authorization' => 'test'
            ]
        )->assertStatus(400)->assertJson([
            'errors' => [
                'country' => [
                    'The country field is required.'
                ]
            ]
        ]);
    }

    public function testGetSuccess(): void
    {
        $this->seed([UserSeeder::class, ContactSeeder::class, AddressSeeder::class]);
        $address = Address::query()->limit(1)->first();

        $this->get("/api/contacts/$address->contact_id/addresses/$address->id", [
            'Authorization' => 'test'
        ])->assertStatus(200)->assertJson([
            'data' => [
                'street' => $address->street,
                'city' => $address->city,
                'province' => $address->province,
                'country' => $address->country,
                'postal_code' => $address->postal_code
            ]
        ]);
    }
    public function testGetNotFound(): void
    {
        $this->seed([UserSeeder::class, ContactSeeder::class, AddressSeeder::class]);
        $address = Address::query()->limit(1)->first();

        $this->get("/api/contacts/$address->contact_id/addresses/" .  ($address->id + 1), [
            'Authorization' => 'test'
        ])->assertStatus(404)->assertJson([
            'errors' => [
                'message' => [
                    'not found'
                ]
            ]
        ]);
    }
}
