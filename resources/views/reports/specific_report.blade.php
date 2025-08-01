@extends('admin_master')
@section('admin')
<div class="content">
    <div class="container-fluid">
        <h4 class="mb-3">Reports Landing</h4>
        <form method="POST" action="{{ route('reports.specific_report.handle') }}">
            @csrf
            <div class="row mb-3">
                <!-- Report Type Dropdown -->
                <div class="col-md-4">
                    <label for="type" class="form-label">Select Report Type</label>
                    <select name="type" id="type" class="form-select" required>
                        <option value="carpet">Carpet</option>
                        <option value="laundry">Laundry</option>
                    </select>
                </div>
                <!-- Month Dropdown -->
                <div class="col-md-4">
                    <label for="month" class="form-label">Month</label>
                    <select name="month" id="month" class="form-select" required>
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $m == $currentMonth ? 'selected' : '' }}>
                                {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                            </option>
                        @endfor
                    </select>
                </div>
                <!-- Year Dropdown -->
                <div class="col-md-4">
                    <label for="year" class="form-label">Year</label>
                    <select name="year" id="year" class="form-select" required>
                        @for($y = date('Y') - 5; $y <= date('Y') + 1; $y++)
                            <option value="{{ $y }}" {{ $y == $currentYear ? 'selected' : '' }}>
                                {{ $y }}
                            </option>
                        @endfor
                    </select>
                </div>
            </div>
            <div class="text-end">
                <button type="submit" class="btn btn-primary">View Report</button>
            </div>
        </form>
    </div>
</div>
@endsection
