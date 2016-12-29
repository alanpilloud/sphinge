@extends('layouts.app')

@section('content')
    <div class="panel panel-default">
        <div class="panel-heading">Add new website</div>

        <div class="panel-body">
            <form method="POST" action="/websites/store" accept-charset="UTF-8">

                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" class="form-control" name="name" id="name">
                </div>

                <div class="form-group">
                    <label for="url">URL</label>
                    <input type="text" class="form-control" name="url" id="url">
                </div>

                <div class="form-group">
                    <label for="secret_key">Secret Key</label>
                    <input type="text" class="form-control" name="secret_key" id="secret_key">
                </div>

                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <button type="submit" class="btn btn-success">Save</button>
            </form>
        </div>
    </div>
@endsection
