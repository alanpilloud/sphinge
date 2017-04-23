@extends('layouts.app')

@section('content')
    <h1 class="page-header"><a href="{{ url('/websites/'. $website->id) }}">{{ $website->name }}</a> <small>Audit</small>
        <div class="pull-right">
            <a class="btn btn-xs btn-primary pull-right" href="{{ url('/websites/'. $website->id.'/audit') }}">Run audit</a>
        </div>
    </h1>
    <div class="panel panel-default">
        <div class="panel-heading">Security Rules</div>

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
