<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $customer = Customer::all();
        return view('customer.index', ["customer" => $customer]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('customer.createcustomer');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $messages = [
            'name.required' => 'Nama pelanggan harus diisi!',
            'name.max' => 'Nama pelanggan tidak boleh lebih dari 45 karakter!',
            'phone_number.required' => 'Nomor telepon wajib diisi!',
            'phone_number.max' => 'Nomor telepon tidak boleh dari 15 karakter!',
            'email.required' => 'Email wajib diisi!',
            'email.email' => 'Format email tidak valid!',
            'address.required' => 'Alamat harus diisi!',
            'address.max' => 'Alamat tidak boleh lebih dari 45 karakter!',
        ];
        $validated = $request->validate([
            'name' => 'required|string|max:45',
            'phone_number' => 'required|max:15',
            'email' => 'required|email',
            'address' => 'required|string|max:45'
        ], $messages);
        $data = new Customer();
        $data->name = $validated['name'];
        $data->phone_number = $validated['phone_number'];
        $data->email = $validated['email'];
        $data->address = $validated['address'];
        $data->save();
        return redirect()->route('customer.index')->with('status', 'Data Berhasil Disimpan');
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer)
    {
        return view('customer.edit', compact('customer'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Customer $customer)
    {
        $messages = [
            'name.max' => 'Nama pelanggan tidak boleh lebih dari 45 karakter!',
            'phone_number.max' => 'Nomor telepon tidak boleh lebih dari 15 karakter!',
            'email.email' => 'Format email tidak valid!',
            'address.max' => 'Alamat tidak boleh lebih dari 45 karakter!',
        ];
        $validated = $request->validate([
            'name' => 'string|max:45',
            'phone_number' => 'max:15',
            'email' => 'email',
            'address' => 'string|max:45'
        ], $messages);
        
        $customer->name = $validated['name'];
        $customer->phone_number = $validated['phone_number'];
        $customer->email = $validated['email'];
        $customer->address = $validated['address'];
        $customer->save();
        return redirect()->route('customer.index')->with('status', 'Data Berhasil Diubah');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        try {
            $customer->delete();
            return redirect()->route('customer.index')->with('status', 'Data Berhasil Dihapus');
        } catch (\PDOException) {
            $msg = "Failed to deleted data because there are related data with " . $customer->name;
            return redirect()->route('customer.index')->with('status', $msg);
        }
    }
}
