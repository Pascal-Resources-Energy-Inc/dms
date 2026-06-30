@extends('layouts.dashboard')

@section('title', 'Edit User')

@section('content')
    @include('dms.partials.user-form-assets')

    <div class="module-header">
        <div>
            <h1>Edit User</h1>
            <p>Update account credentials, role access, business profile, and delivery details.</p>
        </div>
        <a class="btn btn-primary" href="{{ route('dms.users') }}">Back to Users</a>
    </div>

    @if (session('success'))
        <div class="alert alert-success font-weight-bold">{{ session('success') }}</div>
    @endif

    @if (isset($errors) && $errors->any())
        <div class="alert alert-danger">
            <strong>Please check the form.</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <article class="panel card border-0">
        <div class="d-flex justify-content-between align-items-start flex-wrap mb-3">
            <div>
                <h2 class="panel-title mb-1">Account Details</h2>
                <p class="small-muted mb-0">Partner Code: {{ $user->user_reference ?: 'Pending' }}</p>
            </div>
            <span class="status-pill">{{ $user->roleName() }}</span>
        </div>

        <form method="POST" action="{{ route('dms.users.update', $user) }}" enctype="multipart/form-data">
            @csrf
            {{ method_field('PUT') }}

            @include('dms.partials.user-form', [
                'formUser' => $user,
                'awardedAreas' => $awardedAreas,
                'passwordRequired' => false,
                'submitText' => 'Save Changes'
            ])
        </form>
    </article>
@endsection
