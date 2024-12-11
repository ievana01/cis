<form method="POST" action="{{ route('warehouse.update', $warehouse->id_warehouse) }}">
    @csrf
    @method('PUT')
    <div class="form-group">
        <label for="id_warehouse">ID Warehouse</label>
        <input type="text" class="form-control" name="id_warehouse" id="id_warehouse"
            value="{{ $warehouse->id_warehouse }}" disabled>
    </div>
    <div class="form-group">
        <label for="name">Warehouse Name</label>
        <input type="text" class="form-control" id="name" name="name" aria-describedby="name"
            placeholder="Masukkan nama gudang" value="{{ $warehouse->name }}">
    </div>
    <div class="form-group">
        <label for="address">Address</label>
        <input type="text" class="form-control" id="address" name="address" aria-describedby="address"
            placeholder="Masukkan alamat" value="{{ $warehouse->address }}">
    </div>
    <button type="submit" class="btn btn-success">Save Changes</button>
</form>
