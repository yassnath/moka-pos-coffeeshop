<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Addon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AddonController extends Controller
{
    public function index(): View
    {
        $addons = Addon::query()->orderBy('name')->paginate(15);

        return view('admin.addons.index', [
            'addons' => $addons,
        ]);
    }

    public function create(): View
    {
        return view('admin.addons.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:addons,name'],
            'price' => ['required', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        Addon::query()->create([
            'name' => $validated['name'],
            'price' => $validated['price'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.addons.index')->with('success', 'Berhasil menyimpan add on.');
    }

    public function edit(Addon $addon): View
    {
        return view('admin.addons.edit', [
            'addon' => $addon,
        ]);
    }

    public function update(Request $request, Addon $addon): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:addons,name,'.$addon->id],
            'price' => ['required', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $addon->update([
            'name' => $validated['name'],
            'price' => $validated['price'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.addons.index')->with('success', 'Berhasil menyimpan add on.');
    }

    public function destroy(Addon $addon): RedirectResponse
    {
        $addon->delete();

        return redirect()->route('admin.addons.index')->with('success', 'Add-on berhasil dihapus.');
    }
}
