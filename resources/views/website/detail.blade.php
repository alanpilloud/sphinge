@extends('layouts.app')

@section('content')
    <h1 class="page-header">{{ $website->name }} <small>Details</small>
        <div class="pull-right">
            <a class="btn btn-xs btn-primary" href="{{ url('/websites/'. $website->id.'/sync') }}">Synchronize</a>
            <a class="btn btn-xs btn-primary" href="{{ url('/websites/'. $website->id.'/edit') }}">Edit</a>
            <a class="btn btn-xs btn-primary" href="{{ url('/websites/'. $website->id.'/audit') }}">Audit</a>
            <a class="btn btn-xs btn-primary" href="{{ url('/websites/'. $website->id.'/scores') }}">Scores</a>
        </div>
    </h1>
    <div class="panel panel-default">
        <div class="panel-heading">
            Main informations
        </div>

        <div class="panel-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Version</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td>Sphinge Version</td><td>{{ $sphinge_version }}</td></tr>
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
                <p>No extensions for the moment.</p>
            @endif
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading">Users</div>

        <div class="panel-body">
            @if (count($users) >= 1)
                <table class="table">
                    <thead>
                        <tr>
                            <th>Login</th>
                            <th>Email</th>
                            <th>Registration date<br/><small>Remote website time</small></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td>{{ $user->login }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->registered }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p>No users for the moment.</p>
            @endif
        </div>
    </div>
@endsection
