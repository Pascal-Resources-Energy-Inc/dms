@extends('layouts.dashboard')

@section('title', 'Users')

@section('content')
    @include('dms.partials.user-form-assets')

    <section class="welcome">
        <div class="row">
            <div class="col-12 d-flex align-items-stretch">
                <div class="card w-100 border-0">
                    <div class="card-body users">
                        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                            <div>
                                <h5 class="mb-1 font-weight-bold">Users</h5>
                                <p class="small-muted mb-0">Create, search, edit, and delete DMS users from the application users table.</p>
                            </div>

                            <div class="d-flex flex-wrap align-items-center">
                                @if (auth()->user()->isSuperAdmin())
                                    <button class="btn btn-success font-weight-bold mr-2 mb-2" type="button" data-toggle="modal" data-target="#new_users">
                                        + Add Users
                                    </button>
                                @endif

                                <form class="form-inline mb-2" method="GET" action="{{ route('dms.users') }}">
                                    <input class="form-control mr-2" name="q" value="{{ $q }}" placeholder="Search users">
                                    <button class="btn btn-primary" type="submit">Search</button>
                                </form>
                            </div>
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

                        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                            <div class="mini-grid flex-grow-1 mr-lg-3 mb-3 mb-lg-0">
                                @foreach (\App\User::roles() as $role => $label)
                                    <div class="mini-panel card border-0">
                                        <span class="small-muted">{{ $label }}</span>
                                        <strong>{{ $roleCounts[$role] ?? 0 }}</strong>
                                    </div>
                                @endforeach
                            </div>

                            <div class="form-group mb-0">
                                <label for="roleFilter" class="font-weight-bold mr-2 mb-0">Role Filter:</label>
                                <select id="roleFilter" class="form-control">
                                    <option value="">All Roles</option>
                                    @foreach (\App\User::roles() as $role => $label)
                                        <option value="{{ $role }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="table-wrap">
                            <table id="usersTable" class="table table-sm table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Partner Code</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Territory</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($users as $user)
                                        <tr data-role="{{ $user->roleKey() }}">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img class="table-avatar mr-2" src="{{ $user->avatar_path ? asset($user->avatar_path) : asset('images/gazlite.png') }}" alt="{{ $user->name }}">
                                                    <div>
                                                        <div class="font-weight-bold">{{ $user->name }}</div>
                                                        <small class="text-muted">{{ $user->business_name ?: 'No business profile' }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><span class="status-pill">{{ $user->user_reference ?: 'Pending' }}</span></td>
                                            <td>{{ $user->email }}</td>
                                            <td>{{ $user->roleName() }}</td>
                                            <td>{{ $user->territory ?: 'Unassigned' }}</td>
                                            <td>
                                                <span class="badge {{ strtolower($user->status) === 'active' ? 'badge-success' : 'badge-secondary' }}">
                                                    {{ ucfirst($user->status ?: 'unknown') }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <a class="btn btn-sm btn-outline-primary mr-2" href="{{ route('dms.users.edit', $user) }}">Edit</a>
                                                    <form method="POST" action="{{ route('dms.users.destroy', $user) }}" onsubmit="return confirm('Delete this user account?');">
                                                        @csrf
                                                        {{ method_field('DELETE') }}
                                                        <button class="btn btn-sm btn-outline-danger" type="submit" {{ auth()->id() === $user->id ? 'disabled' : '' }}>Delete</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7">No users found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $users->appends(['q' => $q])->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @if (auth()->user()->isSuperAdmin())
        <div id="new_users" class="modal fade modal-select2" tabindex="-1" role="dialog" aria-labelledby="newUsersTitle" aria-hidden="true">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h4 class="modal-title font-weight-bold" id="newUsersTitle">New Users</h4>
                            <p class="small-muted mb-0">Add a DMS account with distributor profile details.</p>
                        </div>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="newUserForm" method="POST" action="{{ route('dms.users.store') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            @include('dms.partials.user-form', [
                                'formUser' => null,
                                'passwordRequired' => true,
                                'submitText' => 'Submit User',
                                'showSubmit' => false
                            ])
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light font-weight-bold" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary font-weight-bold">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <style>
        .table-avatar {
            width: 42px;
            height: 42px;
            object-fit: contain;
            border-radius: .55rem;
            border: 1px solid #e5eaf2;
            background: #fff;
            padding: .2rem;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var roleFilter = document.getElementById('roleFilter');
            var rows = document.querySelectorAll('#usersTable tbody tr[data-role]');

            if (roleFilter) {
                roleFilter.addEventListener('change', function () {
                    rows.forEach(function (row) {
                        row.style.display = !roleFilter.value || row.dataset.role === roleFilter.value ? '' : 'none';
                    });
                });
            }

            @if (isset($errors) && $errors->any())
                if (window.jQuery && jQuery.fn.modal) {
                    jQuery('#new_users').modal('show');
                }
            @endif
        });
    </script>
@endsection
