<?php

namespace App\Http\Controllers\Api\App;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\App\ContactRequest;
use App\Http\Traits\ResponseTrait;
use App\Models\ContactMessage;
use Illuminate\Http\JsonResponse;

class ContactController extends Controller
{
    use ResponseTrait;

    public function store(ContactRequest $request): JsonResponse
    {
        $contactMessage = ContactMessage::create([
            'user_id' => $request->user()?->id,
            'title' => $request->string('title'),
            'message' => $request->string('message'),
        ]);

        return $this->successReturn([
            'id' => $contactMessage->id,
        ], 'messages.contact_us_sent');
    }
}
