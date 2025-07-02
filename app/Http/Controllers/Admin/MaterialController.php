<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\Admin\Material;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;

class MaterialController extends Controller
{
    public function index() { return Material::latest()->get(); }

    public function store(Request $request) {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:materials',
            'description' => 'nullable|string',
        ]);
        return Material::create($validated);
    }

    public function show(Material $material) { return $material; }

    public function update(Request $request, Material $material) {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('materials')->ignore($material->id)],
            'description' => 'nullable|string',
        ]);
        $material->update($validated);
        return $material;
    }

    public function destroy(Material $material) {
        $material->delete();
        return response()->noContent();
    }
}
