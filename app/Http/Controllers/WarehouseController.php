<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use DB;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $warehouse = Warehouse::all();
        $multiWh = DB::table('detail_configurations')
            ->where('status_active', 1)
            ->where('configuration_id', 9)
            ->first();
            // dd($multiWh);
        return view('warehouse.index', ["warehouse" => $warehouse, "multiWh" => $multiWh]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('warehouse.createwarehouse');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = new Warehouse();
        $data->name = $request->get('name');
        $data->address = $request->get('address');
        $data->save();
        return redirect()->route('warehouse.index')->with('status', 'Data Berhasil Disimpan');  
    }

    /**
     * Display the specified resource.
     */
    public function show(Warehouse $warehouse)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Warehouse $warehouse)
    {
        return view('warehouse.getEditForm', compact('warehouse'));
    }

    public function getEditForm(Request $request)
    {
        $id = $request->id;
        $warehouse = Warehouse::find($id);
        return response()->json(array(
            'status' => 'oke',
            'msg' => view('warehouse.getEditForm', compact('warehouse'))->render()
        ), 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Warehouse $warehouse)
    {
        $warehouse->name = $request->name;
        $warehouse->address = $request->address;
        $warehouse->save();
        return redirect()->route('warehouse.index')->with('status', 'Data Berhasil Diubah');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Warehouse $warehouse)
    {
        try {
            $warehouse->delete();
            return redirect()->route('warehouse.index')->with('status', 'Data Berhasil Dihapus');
        } catch (\PDOException) {
            $msg = "Failed to deleted data because there are related data with " . $warehouse->name;
            return redirect()->route('warehouse.index')->with('status', $msg);
        }
    }
}
