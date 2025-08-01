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
                    <li class="breadcrumb-item"><a href="{{ route('audit.index') }}">Audit Trail</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Details</li>
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
                        <h5 class="mb-0">Audit Record #{{ $audit->id }}</h5>
                        <a href="{{ route('audit.index') }}" class="btn btn-secondary btn-sm">
                            <i class="bx bx-arrow-back"></i> Back to List
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Basic Information</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Date/Time:</strong></td>
                                    <td>{{ $audit->created_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>User:</strong></td>
                                    <td>
                                        @if($audit->user)
                                            {{ $audit->user->name }} ({{ $audit->user->email }})
                                        @else
                                            System
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Event:</strong></td>
                                    <td>
                                        <span class="badge bg-{{ 
                                            $audit->event === 'created' ? 'success' : 
                                            ($audit->event === 'updated' ? 'warning' : 
                                            ($audit->event === 'deleted' ? 'danger' : 'info')) 
                                        }}">
                                            {{ $audit->event_display }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Model:</strong></td>
                                    <td>{{ $audit->model_display }} {{ $audit->display_id }}</td>
                                </tr>
                                <tr>
                                    <td><strong>IP Address:</strong></td>
                                    <td>{{ $audit->ip_address }}</td>
                                </tr>
                                <tr>
                                    <td><strong>URL:</strong></td>
                                    <td><small>{{ $audit->url }}</small></td>
                                </tr>
                            </table>
                        </div>
                        
                        <div class="col-md-6">
                            <h6>User Agent</h6>
                            <p class="small">{{ $audit->user_agent }}</p>
                            
                            @if($audit->tags)
                                <h6>Tags</h6>
                                @foreach($audit->tags as $key => $value)
                                    <span class="badge bg-info me-1">{{ $key }}: {{ $value }}</span>
                                @endforeach
                            @endif
                        </div>
                    </div>

                    @if($audit->old_values || $audit->new_values)
                        <hr>
                        <div class="row">
                            @if($audit->old_values)
                                <div class="col-md-6">
                                    <h6>Old Values</h6>
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <pre><code>{{ json_encode($audit->old_values, JSON_PRETTY_PRINT) }}</code></pre>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if($audit->new_values)
                                <div class="col-md-6">
                                    <h6>New Values</h6>
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <pre><code>{{ json_encode($audit->new_values, JSON_PRETTY_PRINT) }}</code></pre>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        @if($audit->old_values && $audit->new_values)
                            <hr>
                            <h6>Changes Summary</h6>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Field</th>
                                            <th>From</th>
                                            <th>To</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($audit->new_values as $key => $newValue)
                                            @if(isset($audit->old_values[$key]) && $audit->old_values[$key] != $newValue)
                                                <tr>
                                                    <td><strong>{{ $key }}</strong></td>
                                                    <td>
                                                        <span class="text-danger">
                                                            {{ is_array($audit->old_values[$key]) ? json_encode($audit->old_values[$key]) : $audit->old_values[$key] }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="text-success">
                                                            {{ is_array($newValue) ? json_encode($newValue) : $newValue }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    @endif

                    @if($audit->auditable)
                        <hr>
                        <h6>Related Record</h6>
                        <p>
                            @if($audit->auditable_type === 'App\Models\Carpet')
                                <a href="{{ route('details.carpet', $audit->auditable_id) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="bx bx-show"></i> View Carpet Record
                                </a>
                            @elseif($audit->auditable_type === 'App\Models\Laundry')
                                <a href="{{ route('details.laundry', $audit->auditable_id) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="bx bx-show"></i> View Laundry Record
                                </a>
                            @endif
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection