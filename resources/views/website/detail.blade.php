@extends('layouts.app')

@section('content')
    <div class="panel panel-default">
        <div class="panel-heading">{{$website->name}}<a class="btn btn-xs btn-primary pull-right" href="{{ url('/websites/'. $website->id.'/sync') }}">Synchronize</a></div>

        <div class="panel-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Version</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td>Sphinge Version</td><td>{{ $website->sphinge_version }}</td></tr>
                    <tr><td>WordPress Version</td><td>{{ $website->wp_version }}</td></tr>
                    <tr><td>PHP Version</td><td>{{ $website->php_version }}</td></tr>
                    <tr><td>MySQL Version</td><td>{{ $website->mysql_version }}</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading">Extensions</div>

        <div class="panel-body">
            @if (count($extensions) >= 1)
                <table class="table">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Name</th>
                            <th>Version</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($extensions as $extension)
                            <tr>
                                <td>{{ $extension->type }}</td>
                                <td>{{ $extension->name }}</td>
                                <td>{{ $extension->version }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p>No websites for the moment.</p>
            @endif
        </div>
    </div>
@endsection
