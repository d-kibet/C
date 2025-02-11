@extends('admin_master')
@section('admin')

<div class="content">
    <div class="container-fluid">
        <h4 class="mb-4">Compare Mpesa Records</h4>

        <!-- Form to select dates for comparison -->
        <form method="GET" action="{{ route('mpesa.compare') }}" class="mb-4">
            <div class="row">
                <div class="col-md-5">
                    <div class="form-group">
                        <label for="first_date">First Date:</label>
                        <input type="date" name="first_date" value="{{ $firstDate }}" class="form-control">
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="form-group">
                        <label for="second_date">Second Date:</label>
                        <input type="date" name="second_date" value="{{ $secondDate }}" class="form-control">
                    </div>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Compare</button>
                </div>
            </div>
        </form>

        <!-- Display the totals and difference -->
        <div class="row">
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h5>First Date ({{ $firstDate }}) Total</h5>
                        <p class="display-6">{{ $firstTotal }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h5>Second Date ({{ $secondDate }}) Total</h5>
                        <p class="display-6">{{ $secondTotal }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h5>Difference</h5>
                        <p class="display-6">{{ $difference }}</p>
                        <small>
                            @if($difference > 0)
                                Second date total is higher.
                            @elseif($difference < 0)
                                First date total is higher.
                            @else
                                Totals are equal.
                            @endif
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
