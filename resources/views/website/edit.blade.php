@extends('layouts.app')

@section('content')
    <div class="panel panel-default">
        <div class="panel-heading">{{ $website->name }}</div>

        <div class="panel-body">
            <form method="POST" action="/websites/{{ $website->id }}/update" accept-charset="UTF-8">

                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" value="{{ $website->name }}" class="form-control" name="name" id="name">
                </div>

                <div class="form-group">
                    <label for="url">URL</label>
                    <input type="text" value="{{ $website->url }}" class="form-control" name="url" id="url">
                </div>

                <div class="form-group">
                    <label>Secret Key</label>
                    <input type="text" value="{{ $website->secret_key }}" class="form-control" readonly>
                </div>

                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <button type="submit" class="btn btn-success">Save</button>
            </form>
        </div>
    </div>
@endsection
