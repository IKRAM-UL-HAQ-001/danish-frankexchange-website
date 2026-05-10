@extends("layout.main")
@section('content')
</style>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card-header p-0 position-relative mb-3">
                <div class="bg-gradient-warning shadow-primary border-radius-lg pt-4 d-flex justify-content-between align-items-center px-3">
                    <p style="color: black;"><strong>Daily Report</strong></p>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        @php
            $dailyColorClasses = [
                'bg-gradient-warning',
            ];
        @endphp

        <div class="col-xl-3 col-sm-6 mb-4">
            <div class="card">
                <div class="test1 card-header p-3">
                    <div class="d-flex align-items-center">
                        <div class="icon icon-lg icon-shape bg-gradient-warning  text-center border-radius-xl position-relative">
                            <i class="material-icons"style="color:white">arrow_circle_up</i>
                        </div>
                        <div class=" text-end ms-3 text-center flex-grow-1"> <!-- Center alignment -->
                            <p class="text-sm mb-0 text-capitalize">Today Deposit</p>
                            <h4 class="smb-0"style="color:white">{{ e( $totalDepositDaily) }}</h4>
                        </div>
                    </div>
                </div>
                <hr class="dark horizontal my-0">
            </div>
        </div>

        @foreach ([ 
            // ['Today Deposit', $totalDepositDaily, 'arrow_circle_up'],
            ['Today Withdrawal', $totalWithdrawalDaily, 'arrow_circle_down'],
            ['Today Margin', $totalBalanceDaily, 'trending_up'],
            ['Total Bank Balance', $totalBankBalance, 'account_balance_wallet'],

            ['Total Customer Balance', $totalClientBalance, 'arrow_circle_down'],
               
            ['Today Open Close Balance', $totalOpenCloseBalanceDaily, 'monetization_on'],
            ['Today Freez Amount', $totalFreezAmountDaily, 'arrow_downward'],
            ['Today Bonus', $totalBonusDaily, 'star_border'],
            ['Today Settling Points', $totalMasterSettlingDaily, 'point_of_sale'],
        
            ['Today Profit', $totalOwnerProfitDaily, 'attach_money'],
            ['Today Paid Vendor Amount', $totalPaidAmountDaily, 'attach_money'],
        
            ['Total Exchanges', $totalExchanges, 'swap_vert'],
            ['Today Expense', $totalExpenseDaily, 'payment'],
            ['Total Users', $totalUsers, 'group'],
            ['Customers', $totalOldCustomersDaily, 'person_outline'],
            ['Today New Customer', $totalCustomersDaily, 'person_add'],
        ] as $index => $card)
            <div class="col-xl-3 col-sm-6 mb-4">
                <div class="card">
                    <div class="test1 card-header p-3">
                        <div class="d-flex align-items-center">
                            <div class=" icon icon-lg icon-shape {{ e( $dailyColorClasses[$index % count($dailyColorClasses)]) }} shadow-{{ e( strtolower($dailyColorClasses[$index % count($dailyColorClasses)])) }} text-center )border-radius-xl position-relative">
                                <i class=" material-icons" style="color:white">{{ e( $card[2]) }}</i>
                            </div>
                            <div class="text-end ms-3 text-center flex-grow-1"> <!-- Center alignment -->
                                <p class=" text-sm mb-0 text-capitalize">{{ e( $card[0] )}}</p>
                                <h4 class=" mb-0" style="color:white">{{ e( $card[1] )}}</h4>
                            </div>
                        </div>
                    </div>
                    <hr class="dark horizontal my-0">
                </div>
            </div>
        @endforeach
    </div>

    <!-- Weekly Report Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card-header p-0 position-relative mb-3">
                <div class="bg-gradient-warning  border-radius-lg pt-4 d-flex justify-content-between align-items-center px-3">
                    <p style="color: black;"><strong>Weekly Report</strong></p>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-xl-3 col-sm-6 mb-4">
            <div class="card">
                <div class="test1 card-header p-3">
                    <div class="d-flex align-items-center">
                        <!-- Icon Section -->
                        <div class="icon icon-lg icon-shape bg-gradient-warning text-center border-radius-xl position-relative" 
                            style="width: 60px; height: 60px;">
                            <i class="material-icons opacity-10" style="color: white;">arrow_upward</i>
                        </div>
                        <!-- Text Section -->
                        <div class="text-center flex-grow-1 ms-3">
                            <p class="text-sm mb-0 text-capitalize">Weekly Deposit</p>
                            <h4 class="mb-0" style="color:white;">{{  $totalDepositWeekly }}</h4>
                        </div>
                    </div>
                </div>
                <hr class="dark horizontal my-0">
            </div>
        </div>

        @foreach ([ 

            // ['Total Deposit', $totalDepositWeekly, 'arrow_upward'],
            ['Total Withdrawal', $totalWithdrawalWeekly, 'arrow_downward'],
            ['Total Margin', $totalBalanceWeekly, 'account_balance_wallet'],

            ['Weekly Freez Amount', $totalFreezAmountWeekly, 'arrow_downward'],
            ['Total Bonus', $totalBonusWeekly, 'star'],

            ['Total Settling Points', $totalMasterSettlingWeekly, 'point_of_sale'],
            ['Total Expense', $totalExpenseWeekly, 'money_off'],
            ['Weekly Profit', $totalOwnerProfitWeekly, 'attach_money'],

            ['Total Users', $totalUsers, 'group'],
            ['Customers', $totalOldCustomersWeekly, 'person'],        
            ['Total New Customers', $totalCustomersWeekly, 'group_add'],
        ] as $index => $card)
            <div class="col-xl-3 col-sm-6 mb-4">
                <div class="card">
                    <div class="test1 card-header p-3">
                        <div class="d-flex align-items-center">
                            <!-- Icon Section -->
                            <div class="icon icon-lg icon-shape bg-gradient-warning  text-center border-radius-xl position-relative" 
                                style="width: 60px; height: 60px;">
                                <i class="material-icons opacity-10" style="color: white;">{{ $card[2] }}</i>
                            </div>
                            <div class="text-center flex-grow-1 ms-3">
                                <p class="text-sm mb-0 text-capitalize">{{ $card[0] }}</p>
                                <h4 class="mb-0" style="color:white;">{{ $card[1] }}</h4>
                            </div>
                        </div>
                    </div>
                    <hr class="dark horizontal my-0">
                </div>
            </div>
        @endforeach
    </div>

    <!-- Monthly Report Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card-header p-0 position-relative mb-3">
                <div class="bg-gradient-warning shadow-primary border-radius-lg pt-4 d-flex justify-content-between align-items-center px-3">
                    <p style="color: black;"><strong>Monthly Report</strong></p>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        @php
            $monthlyColorClasses = [
                'bg-gradient-warning',
            ];
        @endphp

        <div class="col-xl-3 col-sm-6 mb-4">
            <div class="card">
                <div class="test1 card-header p-3">
                    <div class="d-flex align-items-center">
                        <div class=" icon icon-lg icon-shape bg-gradient-warning  text-center border-radius-xl position-relative">
                            <i class="material-icons" style="color:white">arrow_circle_up</i> <!-- Monthly Profit -->
                        </div>
                        <div class=" text-end ms-3 text-center flex-grow-1"> <!-- Center alignment -->
                            <p class=" text-sm mb-0 text-capitalize">Monthly Deposit</p>
                            <h4 class="mb-0" style="color:white">{{ e(  $totalDepositMonthly )}}</h4>
                        </div>
                    </div>
                </div>
                <hr class="dark horizontal my-0">
            </div>
        </div>

        @foreach ([ 
            // ['Total Deposit', $totalDepositMonthly, 'arrow_circle_up'],
            ['Total Withdrawal', $totalWithdrawalMonthly, 'arrow_circle_down'],
            ['Total Margin', $totalBalanceMonthly, 'account_balance_wallet'],
            ['Total Freez Amount', $totalFreezAmountMonthly, 'arrow_downward'],

            ['Total Bonus', $totalBonusMonthly, 'star_border'],
            ['Total Settling Points', $totalMasterSettlingMonthly, 'account_balance'],
            ['Total Paid Vendor Amount', $totalPaidAmountMonthly, 'attach_money'],
            ['Total Expense', $totalExpenseMonthly, 'money_off'],
            
            ['Monthly Profit', $totalOwnerProfitMonthly, 'attach_money'],
            ['Total Exchanges', $totalExchanges, 'swap_vert'],
            ['Total Users', $totalUsers, 'group'],
            ['Customers', $totalOldCustomersMonthly, 'person_outline'],
            ['Total New Customer', $totalCustomersMonthly, 'person_add'],

        ] as $index => $card)
            <div class="col-xl-3 col-sm-6 mb-4">
                <div class="card">
                    <div class="test1 card-header p-3">
                        <div class="d-flex align-items-center">
                            <div class=" icon icon-lg icon-shape {{ e( $monthlyColorClasses[$index % count($monthlyColorClasses)]) }} shadow-{{ e( strtolower($monthlyColorClasses[$index % count($monthlyColorClasses)])) }} text-center border-radius-xl position-relative">
                                <i class="material-icons" style="color:white">{{ e( $card[2]) }}</i>
                            </div>
                            <div class="text-end ms-3 text-center flex-grow-1"> <!-- Center alignment -->
                                <p class=" text-sm mb-0 text-capitalize">{{ e( $card[0]) }}</p>
                                <h4 class=" mb-0" style="color:white">{{ e( $card[1] )}}</h4>
                            </div>
                        </div>
                    </div>
                    <hr class="dark horizontal my-0">
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
