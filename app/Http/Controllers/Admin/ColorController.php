<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin\Color;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;

class ColorController extends Controller
{
    public function index() { return Color::latest()->get(); }

    public function store(Request $request) {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:colors',
            'hex_code' => 'nullable|string|max:7',
        ]);
        return Color::create($validated);
    }

    public function show(Color $color) { return $color; }

    public function update(Request $request, Color $color) {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('colors')->ignore($color->id)],
            'hex_code' => 'nullable|string|max:7',
        ]);
        $color->update($validated);
        return $color;
    }

    public function destroy(Color $color) {
        $color->delete();
        return response()->noContent();
    }
}
