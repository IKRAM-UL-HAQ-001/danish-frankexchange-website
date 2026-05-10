@extends('layout.main')
@section('content')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card my-4">
                    <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                        <div
                            class="bg-gradient-warning border-radius-lg pt-4 d-flex justify-content-between align-items-center px-3">
                            <p style="color: black;"><strong>Freez Bank Balance Table (Weekly Bases)</strong></p>
                        </div>
                    </div>
                    <div class="card-body px-0 pb-2 px-3">
                        <div class="table-responsive p-0">
                            <table id="bank" class="table align-items-center mb-0 table-striped table-hover px-2">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder">Bank Name</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder">Cash Type</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder">Cash Amount</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder">Remarks</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder">Bank Balance</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder">Created At</th>
                                        {{-- <th class="text-uppercase text-secondary text-xxs font-weight-bolder">Action</th> --}}
                                    </tr>
                                </thead>
                                @php
                                    // Create an associative array to store the bank balances and track first occurrences
                                    $bankBalances = [];
                                    $firstEntryFlags = [];
                                @endphp
                                <tbody>
                                    @foreach ($bankEntryRecords as $bankEntry)
                                        @php
                                            if (!isset($bankBalances[$bankEntry->bank_name])) {
                                                $bankBalances[$bankEntry->bank_name] = 0;
                                                $firstEntryFlags[$bankEntry->bank_name] = true; // Mark the first entry
                                            }

                                            // Add or subtract cash based on the cash type
                                            if (strtolower($bankEntry->cash_type) == 'add') {
                                                $bankBalances[$bankEntry->bank_name] += $bankEntry->cash_amount;
                                            } elseif (strtolower($bankEntry->cash_type) == 'minus') {
                                                $bankBalances[$bankEntry->bank_name] -= $bankEntry->cash_amount;
                                            }
                                        @endphp

                                        <tr>
                                            <td>{{ $bankEntry->bank_name }}</td>
                                            <td>{{ $bankEntry->cash_type }}</td>
                                            <td>{{ $bankEntry->cash_amount }}</td>
                                            <td>{{ $bankEntry->remarks }}</td>
                                            <td>
                                                {{-- Show the balance accordingly for the first entry or the updated balance for subsequent entries --}}
                                                @if ($firstEntryFlags[$bankEntry->bank_name])
                                                {{-- For the first occurrence, set the initial balance as the current cash amount --}}
                                                {{ $bankBalances[$bankEntry->bank_name] }}
                                                @php
                                                        $firstEntryFlags[$bankEntry->bank_name] = false; // Mark the first occurrence as processed
                                                        @endphp
                                                @else
                                                {{-- Display the updated bank balance for subsequent entries --}}
                                                {{ $bankBalances[$bankEntry->bank_name] }}
                                                @endif
                                            </td>
                                            <td>{{ $bankEntry->created_at }}</td>
                                            {{-- <td>
                                                <form action="{{ route('admin.bank_freez.unFreez') }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="id" value="{{ $bankEntry->id }}">
                                                    <button type="submit" class="btn btn-danger">UnFreez</button>
                                                </form>
                                            </td> --}}
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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script>
        // $(document).ready(function() {

        //     $('#bank_id').change(function() {
        //         var bankName = $(this).val();

        //         $('#bankForm input, #bankForm select').prop('disabled', true);

        //         $.ajax({
        //             url: '{{ route('exchange.bank.post') }}',
        //             type: 'POST',
        //             data: {
        //                 bank_id: bankName,
        //                 _token: '{{ csrf_token() }}'
        //             },
        //             success: function(response) {
        //                 if (response.balance !== undefined) {
        //                     $('#bank_balance').val(response.balance);
        //                 } else {
        //                     console.warn('Balance not found in response:', response);
        //                 }
        //             },
        //             error: function(xhr) {
        //                 console.error('Error:', xhr);
        //             },
        //             complete: function() {
        //                 $('#bankForm input, #bankForm select').prop('disabled', false);
        //             }
        //         });
        //     });
        // });
    </script>
    <style>
        .form-control.border {
            border: 1px solid #007bff;
            border-radius: 0.25rem;
        }
    </style>
@endsection
