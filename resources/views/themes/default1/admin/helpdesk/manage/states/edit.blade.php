@extends('themes.default1.admin.layout.admin')

@section('IPS')
class="nav-link active"
@stop

@section('ips-menu-parent')
class="nav-item menu-open"
@stop

@section('ips-menu-open')
class="nav nav-treeview menu-open"
@stop

@section('states')
class="nav-link active"
@stop

@section('HeadInclude')
@stop
<!-- header -->
@section('PageHeader')
<h1>IPS</h1>
@stop
<!-- /header -->
<!-- breadcrumbs -->
@section('breadcrumbs')
<ol class="breadcrumb">
</ol>
@stop
<!-- /breadcrumbs -->
<!-- content -->
@section('content')
{!! Form::model($state,['url' => 'states/'.$state->id, 'method' => 'PATCH']) !!}
@if(Session::has('errors'))
<div class="alert alert-danger alert-dismissable">
    <i class="fa fa-ban"></i>
    <b>Alert!</b>
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    <br/>
    @if($errors->first('code'))
    <li class="error-message-padding">{!! $errors->first('code', ':message') !!}</li>
    @endif
    @if($errors->first('name'))
    <li class="error-message-padding">{!! $errors->first('name', ':message') !!}</li>
    @endif
</div>
@endif
<div class="card card-light">
    <div class="card-header">
        <h3 class="card-title">Kemaskini Negeri</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <div class="form-group {{ $errors->has('code') ? 'has-error' : '' }}">
                    {!! Form::label('code','Kod') !!} <span class="text-red"> *</span>
                    {!! Form::text('code',null,['class' => 'form-control', 'maxlength' => '2']) !!}
                </div>
            </div>
            <div class="col-md-8">
                <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                    {!! Form::label('name','Nama') !!} <span class="text-red"> *</span>
                    {!! Form::text('name',null,['class' => 'form-control']) !!}
                </div>
            </div>
        </div>
    </div>
    <div class="card-footer">
        {!! Form::submit('Kemaskini',['class'=>'btn btn-primary'])!!}
    </div>
</div>
{!! Form::close() !!}
@stop
