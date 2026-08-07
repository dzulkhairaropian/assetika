@extends('layouts.app')
@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Asset Requests" />
    @if(session('success')) <x-ui.alert variant="success" message="{{ session('success') }}" /> @endif

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <table class="w-full">
            <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                    <th class="px-6 py-3 text-xs text-left text-gray-500">User</th>
                    <th class="px-6 py-3 text-xs text-left text-gray-500">Item Requested</th>
                    <th class="px-6 py-3 text-xs text-left text-gray-500">Status</th>
                    <th class="px-6 py-3 text-xs text-left text-gray-500">Date</th>
                    <th class="px-6 py-3 text-xs text-right text-gray-500">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-white/[0.05]">
                @forelse($requests as $r)
                    <tr>
                        <td class="px-6 py-3.5 text-sm font-medium">{{ $r->user?->name }}</td>
                        <td class="px-6 py-3.5 text-sm">{{ $r->requestable?->name ?? 'Item' }} ({{ class_basename($r->requestable_type) }})</td>
                        <td class="px-6 py-3.5 text-sm">
                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ $r->status === 'approved' ? 'bg-green-100 text-green-800' : ($r->status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-warning-100 text-warning-800') }}">
                                {{ ucfirst($r->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-3.5 text-xs text-gray-500">{{ $r->created_at->format('d M Y H:i') }}</td>
                        <td class="px-6 py-3.5 text-right">
                            @if($r->status === 'pending')
                                <form action="{{ route('asset-requests.process', $r) }}" method="POST" class="inline-flex gap-2">
                                    @csrf
                                    <button name="action" value="approve" class="rounded bg-green-100 px-3 py-1 text-xs font-medium text-green-700 hover:bg-green-200">Approve</button>
                                    <button name="action" value="reject" class="rounded bg-red-100 px-3 py-1 text-xs font-medium text-red-700 hover:bg-red-200">Reject</button>
                                </form>
                            @else
                                <span class="text-xs text-gray-400">Processed by {{ $r->processedBy?->name ?? 'System' }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-6 py-12 text-center text-sm text-gray-500">No asset requests found.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-6 border-t">{{ $requests->links() }}</div>
    </div>
</div>
@endsection
