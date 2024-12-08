@extends('layouts.btemplate')
@section('content')
    @if (@session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    
    <form action="{{ route('inventory.configuration.save') }}" method="POST">
        @csrf
        <button class="btn btn-danger" type="button">Cancel</button>
        <button class="btn btn-success" type="submit">Save</button>

        @foreach ($configuration as $c)
            <div class="card-body bg-white mt-2 mb-2">
                <h5 class="card-title">{{ $c->name }}</h5>
                <ul>
                    @foreach ($c->details as $detail)
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input"
                                id="checkbox{{ $detail->id_detail_configuration }}"
                                name="configurations[{{ $c->id_configuration }}][]"
                                value="{{ $detail->id_detail_configuration }}"
                                {{ $detail->status_active == 1 ? 'checked' : '' }}>
                            <label class="form-check-label" for="checkbox{{ $detail->id_detail_configuration }}">
                                {{ $detail->name }}
                            </label>
                        </div>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </form>
@endsection
