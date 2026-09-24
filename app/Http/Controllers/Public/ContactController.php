<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContactMessageRequest;
use App\Models\ContactMessage;

class ContactController extends Controller
{
    public function index()
    {
        return view('public.contact.index')->with('title', 'Kontak');
    }

    public function send(StoreContactMessageRequest $request)
    {
        try {
            ContactMessage::create($request->validated());

            return back()->with('success', 'Pesan Anda berhasil dikirim. Terima kasih!');
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'Terjadi kesalahan saat mengirim pesan. Silakan coba lagi.');
        }
    }
}
