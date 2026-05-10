@extends("layout.main")
@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card my-4">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-warning shadow-primary border-radius-lg pt-4 d-flex justify-content-between align-items-center px-3">
                        <p style="color: black;"><strong>Client Balance Table (Yearly Bases)</strong></p>
                        <div>
                            <a href="{{ route('export.clientBalanceListWeekly') }}" class="btn btn-dark">Weekly Client Balance Excel</a>
                            <a href="{{ route('export.clientBalanceListMonthly') }}" class="btn btn-dark">Monthly Client Balance Excel</a>
                            <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#addClientBalanceModel">Client Balance Form</button>
                        </div>
                    </div>
                </div>
                <div class="card-body px-0 pb-2 px-3">
                    <div class="table-responsive p-0">
                        <table id="clientBalanceTable" class="table align-items-center mb-0 table-striped table-hover px-2">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder  ">User</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder  ">Exchange</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder  ">Client Balance</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder  ">Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($clientBalanceRecords as $clientBalance)
                                <tr>
                                    <td>{{ $clientBalance->user->name }}</td>
                                    <td>{{ $clientBalance->exchange->name }}</td>
                                    <td>{{ $clientBalance->client_balance }}</td>
                                    <td>{{ $clientBalance->remarks }}</td>
                                    {{-- <td>{{ $clientBalance->settling_point * $clientBalance->price }}</td> --}}
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addClientBalanceModel" tabindex="-1" aria-labelledby="addClientBalanceModelLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header d-flex justify-content-between align-items-center">
                <h5 class="modal-title" id="addClientBalanceModelLabel" style="color:white">Add Client Balance Form</h5>
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
                    <form id="clientBalanceForm" method="post">
                        @csrf
                        <div class="col-md-12 mb-3">
                            <label for="client_balance" class="form-label">Client Balance<span class="text-danger">*</span></label>
                            <input type="text" class="form-control border" id="client_balance" name="client_balance" placeholder="Enter client balance" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="remarks" class="form-label">Remarks<span class="text-danger">*</span></label>
                            <input type="text" class="form-control border" id="remarks" name="remarks" placeholder="Enter remarks" required>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="closeModalButton">Close</button>
                            <button type="button" class="btn btn-warning" id="submitClientBalanceEntry">Submit</button>

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
        const userTable = $('#clientBalanceTable').DataTable({
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

    $('#closeModalButton').on('click', function() {
     $('#clientBalanceForm')[0].reset();
    location.reload();
});
$(document).ready(function() {
    $('#submitClientBalanceEntry').click(function(event) {
        event.preventDefault(); 
        const clientBalance = $('#client_balance').val();
        const remarks = $('#remarks').val();

        $.ajax({
            url: "{{ route('exchange.client_balance.store') }}",
            method: "POST",
            data: {
                client_balance: clientBalance,
                remarks: remarks,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.message) {
                    $('#error').hide();
                    $('#success').text(response.message).show();
                    $('#clientBalanceForm')[0].reset();
                    setTimeout(() => {
                        $('#success').hide();
                        location.reload();
                    }, 2000);
                } else {
                    $('#success').hide();
                    $('#error').text(response.message).show();
                }
            },
            error: function(xhr) {
                $('#error').text('Please Fill All The Fields').show();
                $('#success').hide();
                setTimeout(() => {
                    $('#error').hide();
                }, 2000);
            }
        });
    });
});
</script>
@endsection
