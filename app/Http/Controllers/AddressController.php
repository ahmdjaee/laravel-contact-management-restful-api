<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddressCreateRequest;
use App\Http\Requests\ContactCreateRequest;
use App\Http\Resources\AddressResource;
use App\Models\Address;
use App\Models\Contact;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class AddressController extends Controller
{
    public function create(int $idContact, AddressCreateRequest $request): JsonResponse
    {
        $user = Auth::user();
        $contact = Contact::query()->where('user_id', $user->id)->find($idContact);

        if (!$contact) {
            throw new HttpResponseException(response([
                'errors' => [
                    'message' => [
                        'not found'
                    ]
                ]
            ], 404));
        }

        $data = $request->validated();
        $address = new Address($data);
        $address->contact_id = $contact->id;
        $address->save();

        return (new AddressResource($address))->response()->setStatusCode(201);
    }

    public function get(int $idContact, int $idAddress): JsonResource
    {
        $user = Auth::user();
        $contact = Contact::query()->where('user_id', $user->id)->find($idContact);

        if (!$contact) {
            throw new HttpResponseException(response([
                'errors' => [
                    'message' => [
                        'not found'
                    ]
                ]
            ], 404));
        }

        $address = Address::query()->where('contact_id', $contact->id)->find($idAddress);
        if (!$address) {
            throw new HttpResponseException(response([
                'errors' => [
                    'message' => [
                        'not found'
                    ]
                ]
            ], 404));
        }

        return new AddressResource($address);
    }
}
