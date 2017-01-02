@extends('layouts.email')

@section('content')
<h1 style="font-size:20px">Notifications</h1>
<table style="width:100%;border-collapse: collapse;">
    @foreach ($notifications as $notification)
        <tr
            @if ($notification->status == 'danger')
                style="background-color:#f2dede"
            @endif
        >
            <td>{{ $notification->website_name }}</td><td>{{ $notification->message }}</td>
        </tr>
    @endforeach
</table>
@endsection
