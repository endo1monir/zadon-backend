<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ContactMessageController extends Controller
{
    public function index(Request $request): View
    {
        $messages = ContactMessage::query()
            ->with('user:id,name,phone')
            ->when($request->filled('search'), fn ($query) => $query->where(function ($query) use ($request): void {
                $term = '%'.$request->string('search').'%';

                $query->where('title', 'like', $term)->orWhere('message', 'like', $term);
            }))
            ->when($request->filled('from_user'), fn ($query) => $query->whereNotNull('user_id'))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.contact-messages.index', [
            'messages' => $messages,
            'filters' => $request->only(['search', 'from_user']),
        ]);
    }

    public function show(ContactMessage $contactMessage): View
    {
        $contactMessage->load('user:id,name,phone,email');

        return view('admin.contact-messages.show', [
            'message' => $contactMessage,
        ]);
    }

    public function destroy(ContactMessage $contactMessage): RedirectResponse
    {
        $contactMessage->delete();

        return back()->with('success', 'Message deleted successfully.');
    }
}
