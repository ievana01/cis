@extends('layouts.btemplate')
@section('content')
    <form method="POST" action="{{ route('category.store') }}">
        @csrf
        <h4>New Category</h4>
        <div class="form-group">
            <label for="name">Category Name</label>
            <input type="text" class="form-control" id="name" name="name" aria-describedby="name"
                placeholder="Insert category name">
        </div>
        <div class="form-group">
            <label for="code_category">Code Category</label>
            <input type="text" class="form-control" id="code_category" name="code_category"
                placeholder="Insert code category">
        </div>
        <div style="text-align: right">
            <a href="{{ route('category.index') }}" type="button" class="btn btn-danger">Cancel</a>
            <button type="submit" class="btn btn-primary">Submit</button>
        </div>
    </form>
@endsection
