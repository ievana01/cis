@extends('layouts.btemplate')
@section('content')
    @if (@session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    <form action="{{ route('purchase.configuration.save') }}" method="POST">
        @csrf
        <a href="/" class="btn btn-danger">Batal</a>
        <button class="btn btn-success" type="submit">Simpan</button>

        @foreach ($configuration as $c)
            <div class="card-body bg-white mt-2 mb-2">
                <h5 class="card-title">{{ $c->name }}</h5>
                <p>{{$c->description}}</p>
                <ul>
                    @foreach ($c->details as $detail)
                        <div class="form-check">
                            <input type="radio" class="form-check-input" id="radio{{ $detail->id_detail_configuration }}"
                                name="configurations[{{ $c->id_configuration }}]"
                                value="{{ $detail->id_detail_configuration }}"
                                {{ $detail->status_active == 1 ? 'checked' : '' }}>
                            <label class="form-check-label" for="radio{{ $detail->id_detail_configuration }}">
                                {{ $detail->name }}
                            </label>
                            <p>{{$detail->description}}</p>
                        </div>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </form>
@endsection
