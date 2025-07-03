<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin\Size;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;

class SizeController extends Controller
{
    public function index() { return Size::latest()->get(); }

    public function store(Request $request) {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:sizes',
            'code' => 'nullable|string|max:10',
            'description' => 'nullable|string',
        ]);
        return Size::create($validated);
    }

    public function show(Size $size) { return $size; }

    public function update(Request $request, Size $size) {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('sizes')->ignore($size->id)],
            'code' => 'nullable|string|max:10',
            'description' => 'nullable|string',
        ]);
        $size->update($validated);
        return $size;
    }

    public function destroy(Size $size) {
        $size->delete();
        return response()->noContent();
    }
}
