@extends('layouts.app')

@section('content')
    <h1 class="page-header"><a href="{{ url('/websites/'. $website->id) }}">{{ $website->name }}</a> <small>Scores</small></h1>

    <div class="panel panel-default">
        <div class="panel-heading">Scores</div>

        <div class="panel-body">
            @if (count($scores) >= 1)
                <table class="table">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Since Day 1</th>
                            <th>Since last flush</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($scores as $score)
                            <tr>
                                <td>{{ $score->score_name }}</td>
                                <td>{{ $score->score_value_since_day1 }}</td>
                                <td>{{ $score->score_value }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p>No score entries for the moment.</p>
            @endif
        </div>
    </div>

@endsection
