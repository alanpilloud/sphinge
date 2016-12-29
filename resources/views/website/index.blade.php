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
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($websites as $website)
                            <tr>
                                <td><a href="{{ url('/websites/'. $website->id) }}">{{ $website->name }}</a></td>
                                <td>
                                    <a class="btn btn-xs btn-danger pull-right" href="{{ url('/websites/'. $website->id.'/destroy') }}">Trash</a>
                                    <a class="btn btn-xs btn-primary pull-right" href="{{ url('/websites/'. $website->id.'/edit') }}">Edit</a>
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
