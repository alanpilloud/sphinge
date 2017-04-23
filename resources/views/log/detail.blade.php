@extends('layouts.app')

@section('content')
    <h1 class="page-header"><a href="{{ url('/websites/'. $website->id) }}">{{ $website->name }}</a> <small>Log detail</small></h1>

    <div class="panel panel-default">
        <div class="panel-body">
            <dl class="dl-horizontal">
                <dt>Type</dt>
                <dd>{{ $log->type }}</dd>
                <dt>Message</dt>
                <dd>{{ $log->message }}</dd>
                <dt>File</dt>
                <dd>{{ $log->file }}</dd>
                <dt>Line</dt>
                <dd>{{ $log->line }}</dd>
                <dt>Occurences</dt>
                <dd>{{ $log->occurences }}</dd>
                <dt>Last occurence</dt>
                <dd>{{ $log->last_occurence }}</dd>
            </dl>
        </div>
    </div>

@endsection
