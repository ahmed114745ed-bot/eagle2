@extends('admin::index')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">{{ __('Salary Report') }} - {{ $month }}/{{ $year }}</h3>
                <div class="box-tools">
                    <form action="" method="GET" class="form-inline">
                        <select name="month" class="form-control">
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ $m }}</option>
                            @endfor
                        </select>
                        <select name="year" class="form-control">
                            @for($y = 2020; $y <= date('Y') + 1; $y++)
                                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                        <button type="submit" class="btn btn-primary btn-sm">{{ __('Filter') }}</button>
                    </form>
                </div>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="info-box bg-aqua">
                            <span class="info-box-icon"><i class="fa fa-money"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">{{ __('Total Salary') }}</span>
                                <span class="info-box-number">{{ number_format($data->sum('sallary'), 2) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-red">
                            <span class="info-box-icon"><i class="fa fa-minus"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">{{ __('Total Cut Amount') }}</span>
                                <span class="info-box-number">{{ number_format($data->sum('cut_amount'), 2) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-green">
                            <span class="info-box-icon"><i class="fa fa-dollar"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">{{ __('Net Salary') }}</span>
                                <span class="info-box-number">{{ number_format($data->sum('sallary') - $data->sum('cut_amount'), 2) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-yellow">
                            <span class="info-box-icon"><i class="fa fa-building"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">{{ __('Agencies Count') }}</span>
                                <span class="info-box-number">{{ $data->count() }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>{{ __('Agency ID') }}</th>
                            <th>{{ __('Agency Name') }}</th>
                            <th>{{ __('Salary') }}</th>
                            <th>{{ __('Cut Amount') }}</th>
                            <th>{{ __('Net Salary') }}</th>
                            <th>{{ __('Paid') }}</th>
                            <th>{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $salary)
                        <tr>
                            <td>{{ $salary->agency_id }}</td>
                            <td>{{ $salary->agency?->name }}</td>
                            <td>{{ number_format($salary->sallary, 2) }}</td>
                            <td>{{ number_format($salary->cut_amount, 2) }}</td>
                            <td>{{ number_format($salary->sallary - $salary->cut_amount, 2) }}</td>
                            <td>
                                @if($salary->is_paid)
                                    <span class="label label-success">{{ __('Paid') }}</span>
                                @else
                                    <span class="label label-warning">{{ __('Unpaid') }}</span>
                                @endif
                            </td>
                            <td>
                                @if(!$salary->is_paid)
                                    <form action="{{ route('admin.agency-salaries.mark-paid', $salary->id) }}" method="POST" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-xs" onclick="return confirm('{{ __('Are you sure?') }}')">
                                            {{ __('Mark as Paid') }}
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                
                {{ $data->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
