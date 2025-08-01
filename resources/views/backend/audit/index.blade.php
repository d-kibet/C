@extends('admin_master')
@section('admin')

<div class="page-content">
    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">System</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">Audit Trail</li>
                </ol>
            </nav>
        </div>
    </div>
    <!--end breadcrumb-->

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Audit Trail</h5>
                        <div class="d-flex gap-2">
                            <a href="{{ route('audit.stats') }}" class="btn btn-info btn-sm">
                                <i class="bx bx-stats"></i> Statistics
                            </a>
                            <button type="button" class="btn btn-success btn-sm" onclick="exportAudit()">
                                <i class="bx bx-export"></i> Export
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <form method="GET" action="{{ route('audit.index') }}" class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">User</label>
                                <select name="user_id" class="form-select">
                                    <option value="">All Users</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Event</label>
                                <select name="event" class="form-select">
                                    <option value="">All Events</option>
                                    @foreach($events as $event)
                                        <option value="{{ $event }}" {{ request('event') == $event ? 'selected' : '' }}>
                                            {{ ucfirst($event) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Model Type</label>
                                <select name="model_type" class="form-select">
                                    <option value="">All Models</option>
                                    @foreach($modelTypes as $type)
                                        <option value="{{ $type }}" {{ request('model_type') == $type ? 'selected' : '' }}>
                                            {{ class_basename($type) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Date From</label>
                                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Date To</label>
                                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                            </div>
                            <div class="col-md-1">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary w-100" style="margin-top: 0;">
                                    <i class="bx bx-search"></i> Filter
                                </button>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-4">
                                <label class="form-label">Search</label>
                                <input type="text" name="search" class="form-control" placeholder="Search by user, IP, or record ID..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-success">
                                        <i class="bx bx-search"></i> Search
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-grid">
                                    <a href="{{ route('audit.index') }}" class="btn btn-secondary">
                                        <i class="bx bx-refresh"></i> Clear All
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Results -->
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Date/Time</th>
                                    <th>User</th>
                                    <th>Event</th>
                                    <th>Model</th>
                                    <th>ID</th>
                                    <th>IP Address</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($audits as $audit)
                                    <tr>
                                        <td>
                                            <small>{{ $audit->created_at->format('Y-m-d H:i:s') }}</small>
                                            <br>
                                            <small class="text-muted">{{ $audit->created_at->diffForHumans() }}</small>
                                        </td>
                                        <td>
                                            @if($audit->user)
                                                <strong>{{ $audit->user->name }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $audit->user->email }}</small>
                                            @else
                                                <span class="text-muted">System</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ 
                                                $audit->event === 'created' ? 'success' : 
                                                ($audit->event === 'updated' ? 'warning' : 
                                                ($audit->event === 'deleted' ? 'danger' : 'info')) 
                                            }}">
                                                {{ $audit->event_display }}
                                            </span>
                                        </td>
                                        <td>{{ $audit->model_display }}</td>
                                        <td>{{ $audit->display_id }}</td>
                                        <td><small>{{ $audit->ip_address }}</small></td>
                                        <td>
                                            <a href="{{ route('audit.show', $audit) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="bx bx-show"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No audit records found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $audits->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function exportAudit() {
    const params = new URLSearchParams(window.location.search);
    window.location.href = "{{ route('audit.export') }}?" + params.toString();
}
</script>

@endsection