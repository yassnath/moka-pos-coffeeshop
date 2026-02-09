<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentMethodController extends Controller
{
    public function index(): View
    {
        $paymentMethods = PaymentMethod::query()->orderBy('name')->paginate(15);

        return view('admin.payment-methods.index', [
            'paymentMethods' => $paymentMethods,
        ]);
    }

    public function create(): View
    {
        return view('admin.payment-methods.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:payment_methods,code'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        PaymentMethod::query()->create([
            'name' => $validated['name'],
            'code' => strtolower($validated['code']),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.payment-methods.index')->with('success', 'Berhasil menyimpan add on.');
    }

    public function edit(PaymentMethod $paymentMethod): View
    {
        return view('admin.payment-methods.edit', [
            'paymentMethod' => $paymentMethod,
        ]);
    }

    public function update(Request $request, PaymentMethod $paymentMethod): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:payment_methods,code,'.$paymentMethod->id],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $paymentMethod->update([
            'name' => $validated['name'],
            'code' => strtolower($validated['code']),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.payment-methods.index')->with('success', 'Berhasil menyimpan add on.');
    }

    public function destroy(PaymentMethod $paymentMethod): RedirectResponse
    {
        $paymentMethod->delete();

        return redirect()->route('admin.payment-methods.index')->with('success', 'Metode bayar berhasil dihapus.');
    }
}
