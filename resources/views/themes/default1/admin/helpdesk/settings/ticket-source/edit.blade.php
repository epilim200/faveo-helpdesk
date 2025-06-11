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
<h1>Social media settings</h1>
@stop
<!-- /header -->
<!-- breadcrumbs -->
@section('breadcrumbs')
<ol class="breadcrumb">

</ol>
@stop
@section('content')

{!! Form::model($ticketSource, ['route' => ['ticket-source.update', $ticketSource->id], 'method' => 'PUT']) !!}

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
@if(Session::has('warn'))
<div class="alert alert-warning alert-dismissable">
    <i class="fas fa-check-circle"></i>
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    {!!Session::get('warn')!!}
</div>
@endif
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
        <h3 class="card-title">{!! trans('lang.ticket-source.title-update') !!}</h3>
    </div>

    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                    {!! Form::label('name', Lang::get('lang.ticket-source.name')) !!}<spam class="help-block"> *</spam>
                    {!! Form::text('name', $ticketSource->name,['class' => 'form-control']) !!}
                    {!! $errors->first('name', '<spam class="help-block">:message</spam>') !!}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group {{ $errors->has('value') ? 'has-error' : '' }}">
                    {!! Form::label('clientvalue_secret',Lang::get('lang.ticket-source.value')) !!}<spam class="help-block"> *</spam>
                    {!! Form::text('value',$ticketSource->value,['class' => 'form-control']) !!}
                     {!! $errors->first('value', '<spam class="help-block">:message</spam>') !!}
                </div>
            </div>
        </div> 
        <div class="row">
            <div class="col-md-6">
                <div class="form-group {{ $errors->has('css_class') ? 'has-error' : '' }}">
                    {!! Form::label('css_class',Lang::get('lang.ticket-source.css_class')) !!}
                    {!! Form::text('css_class',$ticketSource->css_class,['class' => 'form-control']) !!}
                    {!! $errors->first('css_class', '<spam class="help-block">:message</spam>') !!}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group {{ $errors->has('status') ? 'has-error' : '' }}">
                    <div class="row">
                        <div class="col-md-12">
                            {!! Form::label('status',Lang::get('lang.ticket-source.status')) !!} 
                        </div>
                        <div class="col-md-6">
                            <p>{!! Form::radio('status',1,$ticketSource->status) .Lang::get('lang.active')!!}</p>
                        </div>
                        <div class="col-md-6">
                            <p>{!! Form::radio('status',0,$ticketSource->status) .Lang::get('lang.inactive')!!} </p>
                        </div>
                        {{-- <div class="col-md-12">
                            <i>Activate login via {{ucfirst($provider)}}</i>
                        </div> --}}
                         {!! $errors->first('status', '<spam class="help-block">:message</spam>') !!}
                    </div>
                </div>
            </div>
        </div>

    </div>
    <div class="card-footer">
        {!! Form::submit(Lang::get('lang.submit'),['class'=>'btn btn-primary'])!!}
    </div>
</div>
{!! Form::close() !!}
@stop
