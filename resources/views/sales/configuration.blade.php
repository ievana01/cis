@extends('layouts.btemplate')

@section('content')
    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <form action="{{ route('sales.configuration.save') }}" method="POST">
        @csrf
        <button class="btn btn-danger" type="button">Cancel</button>
        <button class="btn btn-success" type="submit">Save</button>

        @foreach ($configuration as $c)
            <div class="card-body bg-white mt-2 mb-2">
                <h5 class="card-title">{{ $c->name }}</h5>
                <ul>
                    @foreach ($c->details as $detail)
                        @if ($c->id_configuration == 1 || $c->id_configuration == 2)
                            <div class="form-check">
                                <input type="radio" class="form-check-input"
                                    id="radio{{ $detail->id_detail_configuration }}"
                                    name="configurations[{{ $c->id_configuration }}]"
                                    value="{{ $detail->id_detail_configuration }}"
                                    {{ $detail->status_active == 1 ? 'checked' : '' }}>
                                <label class="form-check-label" for="radio{{ $detail->id_detail_configuration }}">
                                    {{ $detail->name }}
                                </label>
                            </div>
                        @else
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input"
                                    id="checkbox{{ $detail->id_detail_configuration }}"
                                    name="configurations[{{ $c->id_configuration }}][]"
                                    value="{{ $detail->id_detail_configuration }}"
                                    {{ $detail->status_active == 1 ? 'checked' : '' }}
                                    {{ $detail->type == 'mandatory' ? 'disabled' : '' }}>
                                <label class="form-check-label" for="checkbox{{ $detail->id_detail_configuration }}">
                                    {{ $detail->name }}
                                </label>
                                <p>{{ $detail->description }}</p>
                                @if (str_contains($detail->name, 'Discount'))
                                    <div class="pb-2">
                                        <input type="number" class="form-control" id="value"
                                            name="discount_values[{{ $detail->id_detail_configuration }}]"
                                            placeholder="Insert discount value" value="{{ $detail->value }}">
                                    </div>
                                @endif
                            </div>
                        @endif
                    @endforeach
                </ul>
            </div>
        @endforeach
    </form>
@endsection
