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
                                        <button class="btn btn-danger btn-sm" aria-label="Delete Client Balance" onclick="deleteClientBalance(this, {{ $clientBalance->id }})">Delete</button>
                                        <button class="btn btn-warning btn-sm" aria-label="Edit Client Balance" onclick="openEditModal({{ json_encode($clientBalance) }})">Edit</button>
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

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel" style="color:white">Edit Client Balance</h5>
                <button type="button" class="close" aria-label="Close" onclick="resetEditFormAndCloseModal()">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="alertMessage" class="alert d-none" role="alert"></div>
                <form id="editForm">
                    <input type="hidden" id="editId" name="id">
                    <div class="form-group">
                        <label for="editClientBalance">Client Balance</label>
                        <input type="text" class="form-control" id="editClientBalance" name="client_balance" required>
                    </div>
                    <div class="form-group">
                        <label for="editRemarks">Remarks</label>
                        <input type="text" class="form-control" id="editRemarks" name="remarks" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="resetEditForm()">Close</button>
                <button type="button" class="btn btn-warning" onclick="updateClientBalance()">Save changes</button>
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

    function deleteClientBalance(button, id) {
        if (!confirm('Are you sure you want to delete this Client Balance?')) {
            return;
        }

        $.ajax({
            url: "{{ route('admin.client_balance.destroy') }}",
            method: "POST",
            data: {
                id: id,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    // alert(response.message); // Show success message
                    setTimeout(() => {
                        location.reload(); // Reload page after short delay
                    }, 100);
                } else {
                    alert(response.message || 'Failed to delete the Client Balance.');
                }
            },
            error: function(xhr) {
                console.error(xhr.responseText);
                alert('Error: ' + xhr.status + ' - ' + xhr.statusText);
            }
        });
    }



    function openEditModal(clientBalance) {
        $('#editId').val(clientBalance.id);
        $('#editClientBalance').val(clientBalance.client_balance);
        $('#editRemarks').val(clientBalance.remarks);
        $('#editModal').modal('show');
    }

    function updateClientBalance() {
        const data = {
            id: $('#editId').val(),
            client_balance: $('#editClientBalance').val(),
            remarks: $('#editRemarks').val(),
            _token: '{{ csrf_token() }}'
        };

        $.ajax({
            url: "{{ route('admin.client_balance.update') }}",
            method: "POST",
            data: data,
            success: function(response) {
                $('#alertMessage').removeClass('d-none').removeClass('alert-danger').addClass('alert-success');
                $('#alertMessage').text(response.message).show();

                if (response.success) {
                    setTimeout(() => {
                        $('#editModal').modal('hide');
                        location.reload(); // Reload to reflect changes
                    }, 3000);
                } else {
                    // Show error message
                    $('#alertMessage').removeClass('alert-success').addClass('alert-danger');
                }
            },
            error: function(xhr) {
                $('#alertMessage').removeClass('d-none').removeClass('alert-success').addClass('alert-danger');
                $('#alertMessage').text('Error: ' + (xhr.responseJSON?.message || 'Please fill in all fields.')).show();
                setTimeout(() => {
                    $('#alertMessage').addClass('d-none');
                }, 3000);
            }
        });
    }

    function resetEditForm() {
        $('#editForm')[0].reset(); // Reset the form
        $('#alertMessage').addClass('d-none'); // Hide alert message
        $('#editModal').modal('hide'); // Close the modal
    }
    function resetEditFormAndCloseModal() {
        resetEditForm();
        $('#editModal').modal('hide');
    }
</script>

@endsection
