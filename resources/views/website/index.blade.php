@extends('layouts.app')

@section('content')
    <div class="panel panel-default">
        <div class="panel-heading">Websites <a href="/websites/create" class="btn btn-xs btn-primary pull-right">Add website</a></div>

        <div class="panel-body">

            @if (count($websites) >= 1)
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th colspan="2">CMS Version</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($websites as $k => $website)
                            <tr>
                                <td><a href="{{ url('/websites/'. $website->id) }}">{{ $website->name }}</a></td>
                                <td>WordPress <span class="label label-{{ ($website->wp_version == $current_wp_version) ? 'success' : 'danger' }}">{{ $website->wp_version }}</span></td>
                                <td>
                                    <div class="dropdown pull-right">
                                        <button class="btn btn-xs btn-default dropdown-toggle" type="button" id="dropdownMenu{{ $k }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                        actions
                                        <span class="caret"></span>
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenu{{ $k }}">
                                            <li><a href="{{ url('/websites/'. $website->id.'/edit') }}">Edit</a></li>
                                            <li><a href="{{ url('/websites/'. $website->id.'/sync') }}">Sync</a></li>
                                            <li><a href="{{ url('/websites/'. $website->id.'/audit') }}">Audit</a></li>
                                            <li><a href="{{ url('/websites/'. $website->id.'/scores') }}">Scores</a></li>
                                            @if ($website->hasExtension('Sphinge Interceptor'))
                                                <li><a href="{{ url('/websites/'. $website->id.'/logs') }}">Logs</a></li>
                                            @endif
                                            <li role="separator" class="divider"></li>
                                            <li><a style="color:#a94442" href="{{ url('/websites/'. $website->id.'/destroy') }}">Trash</a></li>
                                        </ul>
                                    </div>

                                </td>
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
