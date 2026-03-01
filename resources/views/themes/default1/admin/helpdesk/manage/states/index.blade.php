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
@if(Session::has('success'))
<div class="alert alert-success alert-dismissable">
  <i class="fa  fa-check-circle"></i>
  <b>Success!</b>
  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
  {!! Session::get('success') !!}
</div>
@endif
@if(Session::has('fails'))
<div class="alert alert-danger alert-dismissable">
  <i class="fa fa-ban"></i>
  <b>Fail!</b>
  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
  {!! Session::get('fails') !!}
</div>
@endif

<div class="card card-light">

	<div class="card-header">
		<h3 class="card-title">Senarai Negeri</h3>
		<div class="card-tools">
			<a href="{{route('states.create')}}" class="btn btn-default btn-tool">
				<span class="fas fa-plus"></span>&nbsp;Tambah Negeri
			</a>
		</div>
	</div>

	<div class="card-body">
		<table class="table table-bordered dataTable" style="overflow:scroll;">
			<tr>
				<th width="80px">Kod</th>
				<th>Nama</th>
				<th width="120px">Bil. Daerah</th>
				<th width="200px">Tindakan</th>
			</tr>
			@foreach($states as $state)
			<tr>
				<td>{{ $state->code }}</td>
				<td><a href="{{route('states.edit',$state->id)}}">{{ $state->name }}</a></td>
				<td>{{ $state->districts_count }}</td>
				<td>
					{!! Form::open(['route'=>['states.destroy', $state->id],'method'=>'DELETE']) !!}
					<a href="{{route('states.edit',$state->id)}}" class="btn btn-primary btn-xs"><i class="fas fa-edit"> </i> Kemaskini</a>
					{!! Form::button('<i class="fas fa-trash"> </i> Padam',
					['type' => 'submit',
					'class'=> 'btn btn-danger btn-xs',
					'onclick'=>'return confirm("Adakah anda pasti?")'])
					!!}
					{!! Form::close() !!}
				</td>
			</tr>
			@endforeach
		</table>
	</div>
</div>
@stop
