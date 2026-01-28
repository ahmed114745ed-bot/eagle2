@extends('admin::index')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">{{ __('Agency Profile') }}: {{ $agency->name }}</h3>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="text-center">
                            @if($agency->img)
                                <img src="{{ asset('storage/' . $agency->img) }}" alt="{{ $agency->name }}" class="img-circle" style="width: 150px; height: 150px; object-fit: cover;">
                            @else
                                <img src="{{ asset('vendor/laravel-admin/AdminLTE/dist/img/avatar.png') }}" alt="{{ $agency->name }}" class="img-circle" style="width: 150px; height: 150px;">
                            @endif
                            <h4>{{ $agency->name }}</h4>
                            <p class="text-muted">ID: {{ $agency->id }}</p>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <table class="table table-bordered">
                            <tr>
                                <th>{{ __('Status') }}</th>
                                <td>
                                    @if($agency->status == 1)
                                        <span class="label label-success">{{ __('Active') }}</span>
                                    @else
                                        <span class="label label-warning">{{ __('Inactive') }}</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>{{ __('Owner') }}</th>
                                <td>{{ $agency->owner?->name }} ({{ $agency->owner?->uuid }})</td>
                            </tr>
                            <tr>
                                <th>{{ __('Phone') }}</th>
                                <td>{{ $agency->phone }}</td>
                            </tr>
                            <tr>
                                <th>{{ __('Members Count') }}</th>
                                <td>{{ $agency->mempers()->count() }}</td>
                            </tr>
                            <tr>
                                <th>{{ __('Current Month Salary') }}</th>
                                <td>{{ number_format($agency->getSalaryAgency(), 2) }}</td>
                            </tr>
                            <tr>
                                <th>{{ __('Created At') }}</th>
                                <td>{{ $agency->created_at?->format('Y-m-d H:i:s') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">{{ __('Salary Summary') }}</h3>
            </div>
            <div class="box-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>{{ __('Month') }}</th>
                            <th>{{ __('Salary') }}</th>
                            <th>{{ __('Cut Amount') }}</th>
                            <th>{{ __('Net') }}</th>
                            <th>{{ __('Paid') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($agency->agencySalaries()->take(12)->get() as $salary)
                        <tr>
                            <td>{{ $salary->month }}/{{ $salary->year }}</td>
                            <td>{{ number_format($salary->sallary, 2) }}</td>
                            <td>{{ number_format($salary->cut_amount, 2) }}</td>
                            <td>{{ number_format($salary->sallary - $salary->cut_amount, 2) }}</td>
                            <td>
                                @if($salary->is_paid)
                                    <span class="label label-success">{{ __('Yes') }}</span>
                                @else
                                    <span class="label label-warning">{{ __('No') }}</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="box box-success">
            <div class="box-header with-border">
                <h3 class="box-title">{{ __('Top Members') }}</h3>
            </div>
            <div class="box-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>{{ __('Name') }}</th>
                            <th>{{ __('UUID') }}</th>
                            <th>{{ __('Is Admin') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($agency->mempers()->take(10)->get() as $member)
                        <tr>
                            <td>{{ $member->name }}</td>
                            <td>{{ $member->uuid }}</td>
                            <td>
                                @if($member->agencyAdmins()->exists())
                                    <span class="label label-info">{{ __('Admin') }}</span>
                                @else
                                    <span class="label label-default">{{ __('Member') }}</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
