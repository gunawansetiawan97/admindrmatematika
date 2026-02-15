<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\UserSubscription;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function index()
    {
        $subscriptions = Subscription::withCount('userSubscriptions')->get();

        return view('admin.subscriptions.index', compact('subscriptions'));
    }

    public function create()
    {
        return view('admin.subscriptions.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
            'days' => 'nullable|array',
            'days.*' => 'string|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'meetings_count' => 'nullable|integer|min:1',
            'features' => 'nullable|array',
            'features.*' => 'string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['features'] = $request->features ?? [];
        $validated['days'] = $request->days ?? [];

        Subscription::create($validated);

        return redirect()->route('admin.subscriptions.index')
            ->with('success', 'Paket kelas berhasil dibuat.');
    }

    public function edit(Subscription $subscription)
    {
        return view('admin.subscriptions.edit', compact('subscription'));
    }

    public function update(Request $request, Subscription $subscription)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
            'days' => 'nullable|array',
            'days.*' => 'string|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'meetings_count' => 'nullable|integer|min:1',
            'features' => 'nullable|array',
            'features.*' => 'string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['features'] = $request->features ?? [];
        $validated['days'] = $request->days ?? [];

        $subscription->update($validated);

        return redirect()->route('admin.subscriptions.index')
            ->with('success', 'Paket kelas berhasil diperbarui.');
    }

    public function destroy(Subscription $subscription)
    {
        // Check if there are active subscriptions
        if ($subscription->userSubscriptions()->where('status', 'active')->exists()) {
            return back()->with('error', 'Tidak dapat menghapus paket kelas yang masih memiliki pelanggan aktif.');
        }

        $subscription->delete();

        return redirect()->route('admin.subscriptions.index')
            ->with('success', 'Paket kelas berhasil dihapus.');
    }

    public function subscribers()
    {
        $userSubscriptions = UserSubscription::with(['user', 'subscription'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.subscriptions.subscribers', compact('userSubscriptions'));
    }
}
