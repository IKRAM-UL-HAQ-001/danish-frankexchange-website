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
                            <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#addLoanModal">Loan Form</button>
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
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Add Loan Modal -->
    <div class="modal fade" id="addLoanModal" tabindex="-1" aria-labelledby="addLoanModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header d-flex justify-content-between align-items-center">
                    <h5 class="modal-title" id="addLoanModalLabel" style="color:white">Add Loan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-validation">
                        <div class="alert alert-success text-white" id='success' style="display:none;">
                            {{ session('success') }}
                        </div>
                        <div class="alert alert-danger text-white" id='error' style="display:none;">
                            {{ session('error') }}
                        </div>
                        <form id="LoanForm" method="post">
                            @csrf
                            <div id="ExchangeDropdown">
                                <label for="receiver_exchange" class="form-label">Receiver Exchange<span class="text-danger">*</span></label>
                                <div class="input-group input-group-outline mb-3">
                                    <select class="form-control" id="receiver_exchange" name="receiver_exchange">
                                        <option value="" disabled selected>Select an Exchange</option>
                                        @foreach($exchangeRecords as $exchange)
                                        <option value="{{ $exchange->id ?? 'N/A' }}">{{ $exchange->name ?? 'N/A' }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div id="ExchangeDropdown">
                                <label for="cash_type" class="form-label">Cash Type<span class="text-danger">*</span></label>
                                <div class="input-group input-group-outline mb-3">
                                    <select class="form-control" id="cash_type" name="cash_type">
                                        <option value="" disabled selected>Select Cash Type</option>
                                        <option value="send">Send</option>
                                        <option value="return">Return</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="cash_amount" class="form-label">Cash Amount<span class="text-danger">*</span></label>
                                <input type="text" class="form-control border" id="cash_amount" name="cash_amount" placeholder="Enter Cash Amount" value="{{ old('cash_amount') }}" required>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="remarks" class="form-label">Remarks<span class="text-danger">*</span></label>
                                <input type="text" class="form-control border" id="remarks" name="remarks" placeholder="Enter Remarks" required>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="closeModalButton">Close</button>
                                <button type="button" class="btn btn-warning" id="submitLoan">Submit</button>
                            </div>
                        </form>
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
    

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function() {
        const userTable = $('#LoanTable').DataTable({
            // pagingType: "full_numbers",
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

        // Handle form submission
        $('#submitLoan').click(function(event) {
            event.preventDefault(); // Prevent default form submission

            // Capture form values
            const receiverExchange = $('#receiver_exchange').val();
            const cashType = $('#cash_type').val();
            const cashAmount = $('#cash_amount').val();
            const remarks = $('#remarks').val();

            $.ajax({
                url: "{{ route('exchange.loan.store') }}",
                method: "POST",
                data: {
                    receiver_exchange: receiverExchange,
                    cash_type: cashType,
                    cash_amount: cashAmount,
                    remarks: remarks,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.message) {
                        $('#error').hide();
                        $('#success')
                            .text(response.message)
                            .removeClass('alert-danger')
                            .addClass('alert-success') // Ensure success styling
                            .show();

                        // Close the modal and refresh the page after a delay
                        setTimeout(() => {
                            $('#addLoanModal').modal('hide'); // Close the modal
                            location.reload(); // Refresh the page
                        }, 2000);
                    } else {
                        $('#success').hide();
                        $('#error')
                            .text(response.message)
                            .removeClass('alert-success')
                            .addClass('alert-danger') // Ensure error styling
                            .show();

                        setTimeout(() => {
                            $('#error').hide();
                        }, 2000);
                    }
                },
                error: function(xhr) {
                    $('#success').hide();
                    $('#error')
                        .text('Please fill in all the fields')
                        .removeClass('alert-success')
                        .addClass('alert-danger') // Ensure error styling
                        .show();

                    setTimeout(() => {
                        $('#error').hide();
                    }, 2000);
                }
            });
        });

        // Reset form on close modal button click
        $('#closeModalButton').on('click', function() {
            $('#LoanForm')[0].reset();
            $('#error').hide();
            $('#success').hide();
        });
    });
</script>
@endsection
