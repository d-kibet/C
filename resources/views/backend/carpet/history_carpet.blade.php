
@extends('admin_master')
@section('admin')

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>


<div class="container">

    <a href="{{ route('all.carpet') }}" class="btn btn-primary mb-3 mt-5" style="margin-top: 100px;">Back to All Carpets</a>

    <h3 class="mt-4">Client History</h3>
    <p><strong>Phone Number:</strong> {{ $phone }}</p>

     <!-- Date Filter Form -->
     <form method="GET" action="{{ route('history.client', $phone) }}" class="mb-3">
        <div class="row">
            <!-- Start Date Input -->
            <div class="col-md-5">
                <input type="date" name="start_date" class="form-control" placeholder="Start Date"
                    value="{{ request('start_date') }}" required>
            </div>
            <!-- End Date Input -->
            <div class="col-md-5">
                <input type="date" name="end_date" class="form-control" placeholder="End Date"
                    value="{{ request('end_date') }}" required>
            </div>
            <!-- Submit Button -->
            <div class="col-md-2">
                <button type="submit" class="btn btn-secondary w-100">Filter</button>
            </div>
        </div>
    </form>

    <h3>Carpet Records</h3>
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Unique ID</th>
                <th>Size</th>
                <th>Price</th>
                <th>Location</th>
                <th>Payment Status</th>
                <th>Delivered</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($client as $client)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $record->uniqueid }}</td>
                <td>{{ $record->size }}</td>
                <td>{{ $record->price }}</td>
                <td>{{ $record->location }}</td>
                <td>{{ $record->payment_status }}</td>
                <td>{{ $record->delivered }}</td>
                <td>{{ $record->created_at->format('d-m-Y') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center">No records found for this client.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <p><strong>Total Visits:</strong> {{ $client->count() }}</p>

     <!-- Pagination Links -->
     <div class="d-flex justify-content-center">
        {{ $client->appends(request()->query())->links() }}
    </div>

</div>



<script type="text/javascript">

	$(document).ready(function(){
		$('#image').change(function(e){
			var reader = new FileReader();
			reader.onload =  function(e){
				$('#showImage').attr('src',e.target.result);
			}
			reader.readAsDataURL(e.target.files['0']);
		});
	});
</script>

@endsection

