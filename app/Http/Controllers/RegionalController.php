<?php

namespace App\Http\Controllers;

use App\Models\Regional;
use Illuminate\Http\Request;

class RegionalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ], [
            'name.required' => 'Nama regional harus diisi',
            'name.string' => 'Nama regional harus berupa teks',
            'name.max' => 'Maksimal 255 karakter',
        ]);

        Regional::create($request->only('name'));
        return back()->with('success', 'Regional berhasil dibuat.');
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Regional $regional)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ], [
            'name.required' => 'Nama regional harus diisi',
            'name.string' => 'Nama regional harus berupa teks',
            'name.max' => 'Maksimal 255 karakter',
        ]);

        $regional->update($request->only('name'));
        return back()->with('success', 'Regional berhasil diubah.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Regional $regional)
    {
        //
    }
}
