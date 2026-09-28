<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Services\AuditService;
use Illuminate\Http\Request;

class ContactMessageController extends Controller
{
    public function index(Request $request)
    {
        $messages = ContactMessage::query()
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', "%{$request->string('search')}%")->orWhere('email', 'like', "%{$request->string('search')}%")->orWhere('subject', 'like', "%{$request->string('search')}%"))
            ->when($request->filled('status'), function ($q) use ($request) {
                if ($request->string('status') === 'read') {
                    $q->where('is_read', true);
                } elseif ($request->string('status') === 'unread') {
                    $q->where('is_read', false);
                } elseif ($request->string('status') === 'archived') {
                    $q->where('is_archived', true);
                } elseif (in_array($request->string('status')->toString(), ['new', 'in_progress', 'resolved'], true)) {
                    $q->where('handling_status', $request->string('status'));
                }
            })
            ->latest()->paginate(20)->withQueryString();

        $unreadCount = ContactMessage::where('is_read', false)->count();
        $trashedCount = ContactMessage::onlyTrashed()->count();

        return view('admin.contact-messages.index', compact('messages', 'unreadCount', 'trashedCount'));
    }

    public function show(ContactMessage $contactMessage)
    {
        if (! $contactMessage->is_read) {
            $contactMessage->update(['is_read' => true, 'read_at' => now()]);
        }

        return view('admin.contact-messages.show', ['message' => $contactMessage]);
    }

    public function markRead(ContactMessage $contactMessage)
    {
        $contactMessage->update(['is_read' => true, 'read_at' => now()]);

        return back()->with('success', 'Pesan ditandai sudah dibaca.');
    }

    public function markUnread(ContactMessage $contactMessage)
    {
        $contactMessage->update(['is_read' => false, 'read_at' => null]);

        return back()->with('success', 'Pesan ditandai belum dibaca.');
    }

    public function archive(ContactMessage $contactMessage)
    {
        $contactMessage->update(['is_archived' => true]);

        return back()->with('success', 'Pesan diarsipkan.');
    }

    public function unarchive(ContactMessage $contactMessage)
    {
        $contactMessage->update(['is_archived' => false]);

        return back()->with('success', 'Pesan dikeluarkan dari arsip.');
    }

    public function updateHandling(Request $request, ContactMessage $contactMessage)
    {
        $data = $request->validate([
            'handling_status' => ['required', 'in:new,in_progress,resolved'],
            'response_channel' => ['nullable', 'in:email,whatsapp,phone,in_person,other'],
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ]);
        $resolved = $data['handling_status'] === 'resolved';
        $contactMessage->update($data + [
            'resolved_by' => $resolved ? auth()->id() : null,
            'resolved_at' => $resolved ? now() : null,
            'is_read' => true,
            'read_at' => $contactMessage->read_at ?? now(),
        ]);
        AuditService::log('contact_handling_update', $contactMessage, null, $data);

        return back()->with('success', 'Status penanganan pesan diperbarui.');
    }

    public function destroy(ContactMessage $contactMessage)
    {
        $contactMessage->delete();
        AuditService::log('contact_delete', $contactMessage);

        return redirect()->route('admin.contact-messages.index')->with('success', 'Pesan dipindahkan ke Trash.');
    }

    public function trash(Request $request)
    {
        $messages = ContactMessage::onlyTrashed()->latest()->paginate(20)->withQueryString();

        return view('admin.contact-messages.trash', compact('messages'));
    }

    public function restore(int $id)
    {
        $m = ContactMessage::onlyTrashed()->findOrFail($id);
        $m->restore();
        AuditService::log('contact_restore', $m);

        return back()->with('success', 'Pesan dipulihkan.');
    }

    public function forceDelete(int $id)
    {
        $m = ContactMessage::onlyTrashed()->findOrFail($id);
        $label = $m->subject;
        $m->forceDelete();
        AuditService::log('contact_force_delete', null, null, ['subject' => $label]);

        return back()->with('success','Pesan dihapus permanen.');
    }
}
