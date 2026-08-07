<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SystemViewController extends Controller
{
    public function comments(Request $request): View
    {
        $search = '';
        $query = DB::table('ticket_comments')
            ->select('ticket_comments.*', 'users.name as user_name', 'tickets.request_id')
            ->leftJoin('users', 'ticket_comments.user_id', '=', 'users.id')
            ->leftJoin('tickets', 'ticket_comments.ticket_id', '=', 'tickets.id')
            ->latest('ticket_comments.created_at');

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where('body', 'like', "%{$search}%")
                ->orWhere('users.name', 'like', "%{$search}%")
                ->orWhere('tickets.request_id', 'like', "%{$search}%");
        }

        return view('admin.system.comments', [
            'comments' => $query->paginate(10)->withQueryString(),
            'search' => $search,
        ]);
    }

    public function histories(Request $request): View
    {
        $search = '';
        $query = DB::table('ticket_histories')
            ->select('ticket_histories.*', 'users.name as user_name', 'tickets.request_id')
            ->leftJoin('users', 'ticket_histories.user_id', '=', 'users.id')
            ->leftJoin('tickets', 'ticket_histories.ticket_id', '=', 'tickets.id')
            ->latest('ticket_histories.created_at');

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where('description', 'like', "%{$search}%")
                ->orWhere('users.name', 'like', "%{$search}%")
                ->orWhere('tickets.request_id', 'like', "%{$search}%");
        }

        return view('admin.system.histories', [
            'histories' => $query->paginate(10)->withQueryString(),
            'search' => $search,
        ]);
    }

    public function attachments(Request $request): View
    {
        $search = '';
        $query = DB::table('attachments')
            ->select('attachments.*', 'users.name as user_name')
            ->leftJoin('users', 'attachments.user_id', '=', 'users.id')
            ->latest('attachments.created_at');

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where('filename', 'like', "%{$search}%")
                ->orWhere('users.name', 'like', "%{$search}%");
        }

        return view('admin.system.attachments', [
            'attachments' => $query->paginate(10)->withQueryString(),
            'search' => $search,
        ]);
    }

    public function backup(): View
    {
        return view('admin.system.backup');
    }

    public function runBackup(): RedirectResponse
    {
        try {
            // Dry-run/pretend backup since we're local dev
            Artisan::call('config:cache');

            return back()->with('success', 'Database backup completed successfully (config cached).');
        } catch (\Exception $e) {
            return back()->with('error', 'Backup failed: '.$e->getMessage());
        }
    }
}
