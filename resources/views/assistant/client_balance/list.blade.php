@extends("layout.main")
@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card my-4">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-warning shadow-primary border-radius-lg pt-4 d-flex justify-content-between align-items-center px-3">
                        <p style="color: black;"><strong>Client Balance Table  (Yearly Bases)</strong></p>
                        <div>
                            <a href="{{ route('export.clientBalanceListWeekly') }}" class="btn btn-dark">Weekly Client Balance Excel</a>
                            <a href="{{ route('export.clientBalanceListMonthly') }}" class="btn btn-dark">Monthly Client Balance Excel</a>
                        </div>
                    </div>
                </div>
                <div class="card-body px-0 pb-2 px-3">
                    <div class="table-responsive p-0" style="overflow-y: hidden;">
                        <table id="clientBalanceTable" class="table align-items-center mb-0 table-striped table-hover">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder">User</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder">Exchange</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder">Client Balance</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder">Remarks</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder">Date and Time</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder ">Action</th>
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
                                    <td>{{ $clientBalance->created_at }}</td>
                                    <td class="text-center">
                                        {{-- <button class="btn btn-danger btn-sm" aria-label="Delete Client Balance" onclick="deleteClientBalance(this, {{ $clientBalance->id }})">Delete</button>
                                        <button class="btn btn-warning btn-sm" aria-label="Edit Client Balance" onclick="openEditModal({{ json_encode($clientBalance) }})">Edit</button> --}}
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
</div>


<style>
</style>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
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
            pageLength: 10,
            order: [[7, 'desc']]
        });
    });
</script>

@endsection
