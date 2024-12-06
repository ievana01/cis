{{-- @extends('layouts.btemplate')
@section('content') --}}
    <form method="POST" action="{{ route('warehouse.update', $warehouse->id_warehouse) }}">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="id_warehouse">ID Gudang</label>
            <input type="text" class="form-control" name="id_warehouse" id="id_warehouse" value="{{$warehouse->id_warehouse}}" disabled>
        </div>
        <div class="form-group">
            <label for="name">Nama Gudang</label>
            <input type="text" class="form-control" id="name" name="name" aria-describedby="name"
                placeholder="Masukkan nama gudang" value="{{$warehouse->name}}">
        </div>
        <div class="form-group">
            <label for="address">Alamat</label>
            <input type="text" class="form-control" id="address" name="address" aria-describedby="address"
                placeholder="Masukkan alamat" value="{{$warehouse->address}}">
        </div>
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
{{-- @endsection --}}
