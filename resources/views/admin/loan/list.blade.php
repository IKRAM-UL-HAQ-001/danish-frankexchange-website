@extends("layout.main")
@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card my-4">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-warning shadow-primary border-radius-lg pt-4 d-flex justify-content-between align-items-center px-3">
                        <p style="color: black;"><strong>Loan Table (Yearly Basis)</strong></p>
                        <div>
                            <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#loanModal">Export Loan List</button>
                        </div>
                    </div>
                </div>
                <div class="card-body px-0 pb-2 px-3">
                    <div class="table-responsive p-0">
                        <table id="LoanTable" class="table align-items-center mb-0 table-striped table-hover px-2">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder">User</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder">Sender Exchange</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder">Receiver Exchange</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder">Type</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder">Amount</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder">Remarks</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder">Date and Time</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($loanRecords as $loan)
                                <tr>
                                    <td>{{ $loan->user->name ?? 'N/A' }}</td>
                                    <td>{{ $loan->exchange->name ?? 'N/A' }}</td>
                                    <td>{{ $loan->receiverExchange->name ?? 'N/A' }}</td>
                                    <td>{{ $loan->cash_type }}</td>
                                    <td>{{ number_format($loan->cash_amount, 2) }}</td>
                                    <td>{{ $loan->remarks }}</td>
                                    <td>{{ $loan->created_at->format('Y-m-d H:i:s') }}</td>
                                    <td>
                                        <button class="btn btn-danger btn-sm" onclick="deleteLoan({{ $loan->id }})">Delete</button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
     <!-- Loan Modal -->
     <div class="modal fade" id="loanModal" tabindex="-1" aria-labelledby="loanModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header d-flex justify-content-between align-items-center bg-warning">
                    <h5 class="modal-title" id="loanModalLabel" style="color:white">Generate Report</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="reportForm" method="post" action="{{ route('export.loan') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="exchange_id" class="form-label">Exchange</label>
                            <select class="form-select px-3" id="exchange_id" name="exchange_id" required>
                                <option value="" disabled selected>Select an exchange</option>
                                @foreach($exchangeRecords as $exchange)
                                    <option value="{{ $exchange->id }}">{{ $exchange->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="sdate" class="form-label">Start Date:</label>
                            <input type="date" class="form-control border px-3" id="sdate" name="start_date" required
                                   value="{{ \Carbon\Carbon::today()->toDateString() }}">
                        </div>
                        <div class="mb-3">
                            <label for="edate" class="form-label">End Date:</label>
                            <input type="date" class="form-control border px-3" id="edate" name="end_date" required
                                   value="{{ \Carbon\Carbon::today()->toDateString() }}">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <input type="submit" class="btn btn-warning" value="Submit">
                        </div>
                    </form>
                </div>                    
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function() {
        const userTable = $('#LoanTable').DataTable({
            pagingType: "full_numbers",
            language: {
                paginate: {
                    first: '«',
                    last: '»',
                    next: '›',
                    previous: '‹'
                }
            },
            lengthMenu: [5, 10, 25, 50],
            pageLength: 10
        });
    });

    function deleteLoan(id) {
    if (!confirm('Are you sure you want to delete this Loan?')) {
        return;
    }

    $.ajax({
        url: "{{ route('admin.loan.destroy') }}",
        method: "POST",
        data: {
            id: id,
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            if (response.success) {
                alert(response.message || 'Loan deleted successfully.');
                location.reload(); // Refresh the page
            } else {
                alert(response.message || 'Failed to delete the Loan.');
            }
        },
        error: function(xhr) {
            console.error(xhr.responseText);
            alert('Error: ' + xhr.status + ' - ' + xhr.statusText);
        }
    });
}

</script>
@endsection
