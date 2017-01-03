@extends('layouts.app')

@section('content')
    <div class="panel panel-default">
        <div class="panel-heading">{{$website->name}}<a class="btn btn-xs btn-primary pull-right" href="{{ url('/websites/'. $website->id.'/audit') }}">Run audit</a></div>

        <div class="panel-body">
            <div class="list-group">
                @foreach ($rules as $rule)
                    <div class="list-group-item list-group-item-{{ $rule->status }}">
                        <h4 class="list-group-item-heading">{{ $rule->title }}</h4>
                        <p class="list-group-item-text">{{ $rule->info }}</p>
                    </div>

                @endforeach
            </div>
        </div>
    </div>
@endsection
