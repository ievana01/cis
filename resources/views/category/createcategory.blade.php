@extends('layouts.btemplate')
@section('content')
    <form method="POST" action="{{ route('category.store') }}">
        @csrf
        <div class="form-group">
            <label for="name">Category Name</label>
            <input type="text" class="form-control" id="name" name="name" aria-describedby="name" placeholder="Insert category name">
        </div>
        <div class="form-group">
            <label for="code_category">Code Category</label>
            <input type="text" class="form-control" id="code_category" name="code_category" placeholder="Insert code category">
        </div>
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
@endsection
