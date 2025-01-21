<?php

namespace App\Http\Controllers;

use App\Models\StoreData;
use Illuminate\Http\Request;
use Storage;

class StoreDataController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = StoreData::first();
        // dd($data);
        return view('dataStore.index', [
            "data" => $data
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('dataStore.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = new StoreData();
        $data->name = $request->name;
        $data->address = $request->address;
        $data->contact_person = $request->contact_person;
        $data->email = $request->email;
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            // Simpan file gambar di folder public/images
            $logoPath = $file->storeAs('images', uniqid('logo_') . '.' . $file->getClientOriginalExtension(), 'public');
            $data->logo = $logoPath; // Simpan path ke database
        }
        $data->phone_number = $request->phone_number;
        $data->save();
        return redirect()->route('dataStore.index')->with('success', 'Data toko baru telah disimpan');
    }

    /**
     * Display the specified resource.
     */
    public function show(StoreData $data)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit()
    {
        // dd($data);
        $data = StoreData::first(); // Ambil data pertama dari tabel
        return view('dataStore.edit', compact("data"));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $data = StoreData::find($request->id_store); // Gunakan find() untuk tidak langsung melempar exception
        if (!$data) {
            \Log::error('Store data not found with ID: ' . $request->id_store);
        }
        $data->name = $request->name;
        $data->address = $request->address;
        $data->email = $request->email;
        $data->contact_person = $request->contact_person;
        // Cek apakah ada file logo yang diupload
        if ($request->hasFile('logo')) {
            // Hapus file logo lama jika ada
            if ($data->logo) {
                Storage::disk('public')->delete($data->logo);
            }

            // Simpan file logo baru
            $file = $request->file('logo');
            $logoPath = $file->store('images', 'public');
            $data->logo = $logoPath;
        }
        $data->phone_number = $request->phone_number;
        $data->save();
        return redirect()->route('dataStore.index')->with('status', 'Data Toko Berhasil Diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
