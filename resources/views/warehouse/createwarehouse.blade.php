@extends('layouts.btemplate')
@section('content')
    <form method="POST" action="{{ route('warehouse.store') }}">
        @csrf
        <h4>New Warehouse</h4>
        <div class="form-group">
            <label for="name">Warehouse Name</label>
            <input type="text" class="form-control" id="name" name="name" aria-describedby="name"
                placeholder="Insert warehouse name">
        </div>
        <div class="form-group">
            <label for="address">Address</label>
            <input type="text" class="form-control" id="address" name="address" aria-describedby="address"
                placeholder="Insert warehouse address">
        </div>
        <div style="text-align: right">
            <a href="{{ route('warehouse.index') }}" type="button" class="btn btn-danger">Cancel</a>
            <button type="submit" class="btn btn-primary">Submit</button>
        </div>
    </form>
@endsection
