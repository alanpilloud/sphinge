@extends('layouts.app')

@section('content')
    <h1 class="page-header"><a href="{{ url('/websites/'. $website->id) }}">{{ $website->name }}</a> <small>Logs</small>
        <div class="pull-right">
            <a class="btn btn-xs btn-primary pull-right" href="{{ url('/websites/'.$website->id.'/logs/destroy-all') }}">Delete All Logs</a>
        </div>
    </h1>

    <div class="panel panel-default">
        <div class="panel-heading">Logs</div>

        <div class="panel-body">
            @if (count($logs) >= 1)
                <table class="table">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Message</th>
                            <th>File</th>
                            <th>Line</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($logs as $log)
                            <tr>
                                <td>{{ $log->type }}</td>
                                <td>{{ $log->message }}</td>
                                <td>.../{{ basename($log->file) }}</td>
                                <td>{{ $log->line }}</td>
                                <td><a href="{{ url('/log/'.$log->id) }}">Details</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p>No log entries for the moment.</p>
            @endif
        </div>
    </div>

@endsection
