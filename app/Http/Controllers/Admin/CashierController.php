<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class CashierController extends Controller
{
    public function index(): View
    {
        $cashiers = User::query()
            ->where('role', User::ROLE_KASIR)
            ->orderBy('name')
            ->paginate(15);

        return view('admin.cashiers.index', [
            'cashiers' => $cashiers,
        ]);
    }

    public function create(): View
    {
        return view('admin.cashiers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => User::ROLE_KASIR,
            'email_verified_at' => now(),
        ]);

        return redirect()->route('admin.cashiers.index')->with('success', 'Berhasil menyimpan kasir.');
    }

    public function edit(User $cashier): View
    {
        $this->ensureCashier($cashier);

        return view('admin.cashiers.edit', [
            'cashier' => $cashier,
        ]);
    }

    public function update(Request $request, User $cashier): RedirectResponse
    {
        $this->ensureCashier($cashier);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$cashier->id],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
        ]);

        $cashier->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if (! empty($validated['password'])) {
            $cashier->update([
                'password' => Hash::make($validated['password']),
            ]);
        }

        return redirect()->route('admin.cashiers.index')->with('success', 'Berhasil menyimpan kasir.');
    }

    public function destroy(User $cashier): RedirectResponse
    {
        $this->ensureCashier($cashier);

        $cashier->delete();

        return redirect()->route('admin.cashiers.index')->with('success', 'Kasir berhasil dihapus.');
    }

    private function ensureCashier(User $user): void
    {
        abort_unless($user->role === User::ROLE_KASIR, 404);
    }
}
