@extends('themes.default1.admin.layout.admin')

@section('Settings')
class="nav-link active"
@stop

@section('settings-menu-parent')
class="nav-item menu-open"
@stop

@section('settings-menu-open')
class="nav nav-treeview menu-open"
@stop

@section('ticket-source')
class="nav-link active"
@stop

@section('HeadInclude')
@stop
<!-- header -->
@section('PageHeader')
    <h1>{{\Illuminate\Support\Facades\Lang::get('lang.ticket-source.singular')}}</h1>
@stop
<!-- /header -->
<!-- breadcrumbs -->
@section('breadcrumbs')
<ol class="breadcrumb">

</ol>
@stop
@section('content')
@if (count($errors) > 0)
<div class="alert alert-danger">
    <strong>{{Lang::get('lang.woops')}}</strong> {{Lang::get('lang.theirisproblem')}}<br><br>
    <ul>
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif
<!-- check whether success or not -->
@if(Session::has('success'))
<div class="alert alert-success alert-dismissable">
    <i class="fas fa-check-circle"></i>
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    {!!Session::get('success')!!}
</div>
@endif
<!-- failure message -->
@if(Session::has('fails'))
<div class="alert alert-danger alert-dismissable">
    <i class="fas fa-ban"></i>
    <b>{!! Lang::get('lang.alert') !!}!</b>
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    {!!Session::get('fails')!!}
</div>
@endif
<div class="card card-light">
    <div class="card-header">
        <h3 class="card-title">{{trans('lang.ticket-source.plural')}}</h3>
    </div>

    <div class="card-body">
        <div class="row">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>{{trans('lang.ticket-source.id')}}</th>
                        <th>{{trans('lang.ticket-source.name')}}</th>
                        <th>{{trans('lang.ticket-source.value')}}</th>
                        <th>{{trans('lang.ticket-source.css_class')}}</th>
                        <th>{{trans('lang.ticket-source.status')}}</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($ticketSources as $ticketSource)
                    <tr>
                        <td>{!! $ticketSource->id !!}</td>
                        <td>{!! $ticketSource->name !!}</td>
                        <td>{!! $ticketSource->value !!}</td>
                        <td>{!! $ticketSource->css_class !!}</td>
                        <td>{!! $ticketSource->status !!}</td>
                        <td>
                            {!! link_to_route('ticket-source.edit',trans('lang.ticket-source.btn-edit'),[$ticketSource->id],['class'=>'btn btn-primary btn-sm']) !!}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@stop
