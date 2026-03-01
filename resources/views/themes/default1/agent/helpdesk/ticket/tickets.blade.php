@extends('themes.default1.agent.layout.agent')

@section('Tickets')
class="nav-link active"
@stop

@section('ticket-bar')
active
@stop

@section('dept-ticket-bar')
class="nav-link active"
@stop

@section('ticket')
class="active"
@stop

<?php
$inputs     = Request::get('show');
$activepage = $inputs[0];
if (Request::has('assigned'))
{
    $activepage = Request::get('assigned')[0];
} elseif (Request::has('last-response-by')){
    $activepage = Request::get('last-response-by')[0];
}
?>

@if($activepage == 'trash')
    @section('trash')
        class="nav-link active"
    @stop
@elseif ($activepage == 'mytickets')
    @section('myticket')
        class="nav-link active"
    @stop
@elseif ($activepage == 'followup')
    @section('followup')
        class="nav-link active"
    @stop
@elseif($activepage == 'inbox')
    @section('inbox')
        class="nav-link active"
    @stop
@elseif($activepage == 'overdue')
    @section('overdue')
        class="nav-link active"
    @stop
@elseif($activepage == 'closed')
    @section('closed')
        class="nav-link active"
    @stop
@elseif($activepage == 'approval')
    @section('approval')
        class="nav-link active"
    @stop
@elseif($activepage == 'Agent')
    @section('answered')
        class="nav-link active"
    @stop
@elseif($activepage == 'Client')
    @section('open')
        class="nav-link active"
    @stop
@elseif($activepage == 0)
    @section('unassigned')
        class="nav-link active"
    @stop
@else
    @section('assigned')
        class="nav-link active"
    @stop
@endif

@section('PageHeader')
<h1>{{Lang::get('lang.tickets')}}</h1>
<style>
    .tooltip1 {
        position: relative;
    }

    .tooltip1 .tooltiptext {
        visibility: hidden;
        width: 100%;
        background-color: black;
        color: #fff;
        text-align: center;
        border-radius: 6px;
        padding: 5px 0;
        position: absolute;
        z-index: 1;
    }

    .tooltip1:hover .tooltiptext {
        visibility: visible;
    }

    /* Filter Panel Styles */
    .filter-panel {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        margin-bottom: 15px;
    }
    .filter-panel .card-header {
        background-color: #e9ecef;
        cursor: pointer;
        padding: 10px 15px;
    }
    .filter-panel .card-header:hover {
        background-color: #dee2e6;
    }
    .filter-panel .card-body {
        padding: 15px;
    }
    .filter-row {
        margin-bottom: 10px;
    }
    .filter-row label {
        font-weight: 600;
        font-size: 12px;
        color: #495057;
        margin-bottom: 3px;
    }
    .filter-row .select2-container {
        width: 100% !important;
    }
    .filter-actions {
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid #dee2e6;
    }
</style>
@stop
@section('content')
<!-- Main content -->
<div class="card card-light">
    <div class="card-header">
        <h3 class="card-title">
            @if($activepage == 'trash')
            {{Lang::get('lang.trash')}}
            @elseif ($activepage == 'mytickets')
            {{Lang::get('lang.my_tickets')}}
            @elseif ($activepage == 'followup')
            {{Lang::get('lang.followup')}}
            @elseif($activepage == 'inbox')
            {{Lang::get('lang.inbox')}}
            @elseif($activepage == 'overdue')
            {{Lang::get('lang.overdue')}}
            @elseif($activepage == 'closed')
{{--            {{Lang::get('lang.closed')}}--}}
            @elseif($activepage == 'approval')
            {{Lang::get('lang.approval')}}
            @elseif($activepage == 0)
            {{Lang::get('lang.unassigned')}}
            @else
            {{Lang::get('lang.inbox')}}
            @endif 
            @if(count(Request::all()) > 2 && $activepage != '0')
            / {{Lang::get('lang.filtered-results')}}
            @else()
            @if(count(Request::get('departments')) == 1 && Request::get('departments')[0] != 'All')
            / {{Lang::get('lang.filtered-results')}}
            @elseif (count(Request::get('departments')) > 1)
            / {{Lang::get('lang.filtered-results')}}
            @endif
            @endif
        </h3>
    </div><!-- /.box-header -->

    <div class="card-body ">
        <!-- Filter Panel -->
        <?php
        $departments = \App\Model\helpdesk\Agent\Department::orderBy('name')->get();
        $statuses = \App\Model\helpdesk\Ticket\Ticket_Status::orderBy('name')->get();
        $priorities = \App\Model\helpdesk\Ticket\Ticket_Priority::orderBy('priority')->get();
        $sources = \App\Model\helpdesk\Ticket\Ticket_source::orderBy('name')->get();
        $agents = \App\User::whereIn('role', ['admin', 'agent'])->where('active', 1)->orderBy('first_name')->get();

        // Labels table may not exist in all installations
        try {
            $labels = \App\Model\helpdesk\Filters\Label::where('status', 1)->orderBy('title')->get();
        } catch (\Exception $e) {
            $labels = collect([]);
        }

        $sla_plans = \App\Model\helpdesk\Manage\Sla_plan::where('status', 1)->orderBy('name')->get();
        $help_topics = \App\Model\helpdesk\Manage\Help_topic::where('status', 1)->orderBy('topic')->get();

        // Get current filter values from request
        $current_filters = Request::all();
        ?>
        <div class="filter-panel card mb-3">
            <div class="card-header" data-toggle="collapse" data-target="#filterCollapse" aria-expanded="false">
                <i class="fas fa-filter"></i> <strong>{{Lang::get('lang.filters')}}</strong>
                <i class="fas fa-chevron-down float-right" id="filter-chevron"></i>
                @if(count($current_filters) > 1)
                <span class="badge badge-info ml-2">{{ count($current_filters) - 1 }} {{Lang::get('lang.active')}}</span>
                @endif
            </div>
            <div class="collapse" id="filterCollapse">
                <div class="card-body">
                    <form id="filter-form" method="GET" action="{{ url('tickets') }}">
                        <input type="hidden" name="show[]" value="{{ isset($current_filters['show']) ? $current_filters['show'][0] : 'inbox' }}">

                        <div class="row">
                            <!-- Department Filter -->
                            <div class="col-md-3 filter-row">
                                <label>{{Lang::get('lang.department')}}</label>
                                <select name="departments[]" id="departments-filter" class="form-control select2-filter" multiple>
                                    @foreach($departments as $dept)
                                    <option value="{{ $dept->name }}" {{ (isset($current_filters['departments']) && in_array($dept->name, $current_filters['departments'])) ? 'selected' : '' }}>{{ $dept->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Status Filter -->
                            <div class="col-md-3 filter-row">
                                <label>{{Lang::get('lang.status')}}</label>
                                <select name="status[]" id="status-filter" class="form-control select2-filter" multiple>
                                    @foreach($statuses as $status)
                                    <option value="{{ $status->name }}" {{ (isset($current_filters['status']) && in_array($status->name, $current_filters['status'])) ? 'selected' : '' }}>{{ $status->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Priority Filter -->
                            <div class="col-md-3 filter-row">
                                <label>{{Lang::get('lang.priority')}}</label>
                                <select name="priority[]" id="priority-filter" class="form-control select2-filter" multiple>
                                    @foreach($priorities as $priority)
                                    <option value="{{ $priority->priority }}" {{ (isset($current_filters['priority']) && in_array($priority->priority, $current_filters['priority'])) ? 'selected' : '' }}>{{ $priority->priority }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Source Filter -->
                            <div class="col-md-3 filter-row">
                                <label>{{Lang::get('lang.source')}}</label>
                                <select name="source[]" id="source-filter" class="form-control select2-filter" multiple>
                                    @foreach($sources as $source)
                                    <option value="{{ $source->name }}" {{ (isset($current_filters['source']) && in_array($source->name, $current_filters['source'])) ? 'selected' : '' }}>{{ $source->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row mt-2">
                            <!-- Assigned To Filter -->
                            <div class="col-md-3 filter-row">
                                <label>{{Lang::get('lang.assigned_to')}}</label>
                                <select name="assigned-to[]" id="assigned-to-filter" class="form-control select2-filter" multiple>
                                    @foreach($agents as $agent)
                                    <option value="{{ $agent->first_name }} {{ $agent->last_name }}" {{ (isset($current_filters['assigned-to']) && in_array($agent->first_name.' '.$agent->last_name, $current_filters['assigned-to'])) ? 'selected' : '' }}>{{ $agent->first_name }} {{ $agent->last_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- SLA Filter -->
                            <div class="col-md-3 filter-row">
                                <label>{{Lang::get('lang.sla_plan')}}</label>
                                <select name="sla[]" id="sla-filter" class="form-control select2-filter" multiple>
                                    @foreach($sla_plans as $sla)
                                    <option value="{{ $sla->name }}" {{ (isset($current_filters['sla']) && in_array($sla->name, $current_filters['sla'])) ? 'selected' : '' }}>{{ $sla->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Help Topic Filter -->
                            <div class="col-md-3 filter-row">
                                <label>{{Lang::get('lang.help_topic')}}</label>
                                <select name="help-topic[]" id="help-topic-filter" class="form-control select2-filter" multiple>
                                    @foreach($help_topics as $topic)
                                    <option value="{{ $topic->topic }}" {{ (isset($current_filters['help-topic']) && in_array($topic->topic, $current_filters['help-topic'])) ? 'selected' : '' }}>{{ $topic->topic }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Labels Filter (only if labels exist) -->
                            @if($labels->count() > 0)
                            <div class="col-md-3 filter-row">
                                <label>{{Lang::get('lang.labels')}}</label>
                                <select name="labels[]" id="labels-filter" class="form-control select2-filter" multiple>
                                    @foreach($labels as $label)
                                    <option value="{{ $label->title }}" {{ (isset($current_filters['labels']) && in_array($label->title, $current_filters['labels'])) ? 'selected' : '' }}>{{ $label->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif
                        </div>

                        <div class="row mt-2">
                            <!-- Created Date Filter -->
                            <div class="col-md-3 filter-row">
                                <label>{{Lang::get('lang.created')}}</label>
                                <select name="created[]" id="created-filter" class="form-control select2-filter">
                                    <option value="">-- {{Lang::get('lang.any-time')}} --</option>
                                    <option value="today" {{ (isset($current_filters['created']) && in_array('today', $current_filters['created'])) ? 'selected' : '' }}>{{Lang::get('lang.today')}}</option>
                                    <option value="yesterday" {{ (isset($current_filters['created']) && in_array('yesterday', $current_filters['created'])) ? 'selected' : '' }}>{{Lang::get('lang.yesterday')}}</option>
                                    <option value="this-week" {{ (isset($current_filters['created']) && in_array('this-week', $current_filters['created'])) ? 'selected' : '' }}>{{Lang::get('lang.this-week')}}</option>
                                    <option value="last-week" {{ (isset($current_filters['created']) && in_array('last-week', $current_filters['created'])) ? 'selected' : '' }}>{{Lang::get('lang.last-week')}}</option>
                                    <option value="this-month" {{ (isset($current_filters['created']) && in_array('this-month', $current_filters['created'])) ? 'selected' : '' }}>{{Lang::get('lang.this-month')}}</option>
                                    <option value="last-month" {{ (isset($current_filters['created']) && in_array('last-month', $current_filters['created'])) ? 'selected' : '' }}>{{Lang::get('lang.last-month')}}</option>
                                    <option value="last-3-months" {{ (isset($current_filters['created']) && in_array('last-3-months', $current_filters['created'])) ? 'selected' : '' }}>{{Lang::get('lang.last-3-months')}}</option>
                                    <option value="last-6-months" {{ (isset($current_filters['created']) && in_array('last-6-months', $current_filters['created'])) ? 'selected' : '' }}>{{Lang::get('lang.last-6-months')}}</option>
                                    <option value="last-year" {{ (isset($current_filters['created']) && in_array('last-year', $current_filters['created'])) ? 'selected' : '' }}>{{Lang::get('lang.last-year')}}</option>
                                </select>
                            </div>

                            <!-- Updated Date Filter -->
                            <div class="col-md-3 filter-row">
                                <label>{{Lang::get('lang.updated')}}</label>
                                <select name="updated[]" id="updated-filter" class="form-control select2-filter">
                                    <option value="">-- {{Lang::get('lang.any-time')}} --</option>
                                    <option value="today" {{ (isset($current_filters['updated']) && in_array('today', $current_filters['updated'])) ? 'selected' : '' }}>{{Lang::get('lang.today')}}</option>
                                    <option value="yesterday" {{ (isset($current_filters['updated']) && in_array('yesterday', $current_filters['updated'])) ? 'selected' : '' }}>{{Lang::get('lang.yesterday')}}</option>
                                    <option value="this-week" {{ (isset($current_filters['updated']) && in_array('this-week', $current_filters['updated'])) ? 'selected' : '' }}>{{Lang::get('lang.this-week')}}</option>
                                    <option value="last-week" {{ (isset($current_filters['updated']) && in_array('last-week', $current_filters['updated'])) ? 'selected' : '' }}>{{Lang::get('lang.last-week')}}</option>
                                    <option value="this-month" {{ (isset($current_filters['updated']) && in_array('this-month', $current_filters['updated'])) ? 'selected' : '' }}>{{Lang::get('lang.this-month')}}</option>
                                    <option value="last-month" {{ (isset($current_filters['updated']) && in_array('last-month', $current_filters['updated'])) ? 'selected' : '' }}>{{Lang::get('lang.last-month')}}</option>
                                </select>
                            </div>

                            <!-- Assigned Status Filter -->
                            <div class="col-md-3 filter-row">
                                <label>{{Lang::get('lang.assigned')}}</label>
                                <select name="assigned[]" id="assigned-filter" class="form-control select2-filter">
                                    <option value="">-- {{Lang::get('lang.all')}} --</option>
                                    <option value="1" {{ (isset($current_filters['assigned']) && in_array('1', $current_filters['assigned'])) ? 'selected' : '' }}>{{Lang::get('lang.assigned')}}</option>
                                    <option value="0" {{ (isset($current_filters['assigned']) && in_array('0', $current_filters['assigned'])) ? 'selected' : '' }}>{{Lang::get('lang.unassigned')}}</option>
                                </select>
                            </div>

                            <!-- Last Response By Filter -->
                            <div class="col-md-3 filter-row">
                                <label>{{Lang::get('lang.last_response')}}</label>
                                <select name="last-response-by[]" id="response-filter" class="form-control select2-filter">
                                    <option value="">-- {{Lang::get('lang.all')}} --</option>
                                    <option value="Agent" {{ (isset($current_filters['last-response-by']) && in_array('Agent', $current_filters['last-response-by'])) ? 'selected' : '' }}>{{Lang::get('lang.agent')}}</option>
                                    <option value="Client" {{ (isset($current_filters['last-response-by']) && in_array('Client', $current_filters['last-response-by'])) ? 'selected' : '' }}>{{Lang::get('lang.client')}}</option>
                                </select>
                            </div>
                        </div>

                        <!-- Filter Actions -->
                        <div class="filter-actions">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-search"></i> {{Lang::get('lang.apply_filter')}}
                            </button>
                            <a href="{{ url('tickets') }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-times"></i> {{Lang::get('lang.reset')}}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- End Filter Panel -->

        @if(Session::has('success'))
        <div class="alert alert-success alert-dismissable">
            <i class="fas fa-check-circle"> </i>
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            {{Session::get('success')}}
        </div>
        @endif
        <!-- failure message -->
        @if(Session::has('fails'))
        <div class="alert alert-danger alert-dismissable">
            <i class="fas fa-ban"> </i> <b> {!! Lang::get('lang.alert') !!}! </b>
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            {{Session::get('fails')}}
        </div>
        @endif

        <div class="alert alert-success alert-dismissable" style="display: none;">
            <i class="fas fa-check-circle"> </i> <span class="success-message"></span>
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            {{Session::get('success')}}
        </div>
        <div class="alert alert-danger alert-dismissable" style="display: none;">
            <i class="fas fa-ban"> </i> <b> {!! Lang::get('lang.alert') !!}!</b> <span class="error-message"></span>
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            {{Session::get('fails')}}
        </div>
        <!--<div class="mailbox-controls">-->
        <!-- Check all button -->

        <button type="button" class="btn btn-sm btn-default text-green" id="Edit_Ticket" data-toggle="modal" data-target="#MergeTickets">
            <i class="fas fa-cogs"> </i> {!! Lang::get('lang.merge') !!}
        </button>
        
        <?php $inputs   = Request::all(); ?>
        
        <div class="btn-group">
        <?php $statuses = Finder::getCustomedStatus(); ?>
            <button type="button" class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown" id="d1">
                <i class="fas fa-exchange-alt" style="color:teal;" id="hidespin"> </i>
                <i class="fas fa-spinner fa-spin" style="color:teal; display:none;" id="spin"></i>
                {!! Lang::get('lang.change_status') !!} <span class="caret"></span>
            </button>

            <div class="dropdown-menu">
                @foreach($statuses as $ticket_status)    
                <a href="javascript:;"  class="dropdown-item" onclick="changeStatus({!! $ticket_status -> id !!}, '{!! $ticket_status->name !!}')" 
                    data-toggle="modal" data-target="#myModal">
                    {{trans('lang.'.strtolower($ticket_status->name)) }}
                </a>
                @endforeach
            </div>
        </div>

        <button type="button" class="btn btn-sm btn-default" id="assign_Ticket" data-toggle="modal" data-target="#AssignTickets" style="display: none;">
            <i class="fas fa-hand-point-right"> </i> {!! Lang::get('lang.assign') !!}
        </button>

        @if($activepage == 'trash')
        <button form="modalpopup" class="btn btn-sm btn-danger" id="hard-delete" name="submit" type="submit">
            <i class="fas fa-trash"></i>&nbsp;{{Lang::get('lang.clean-up')}}
        </button>
        @endif
        <p><p/>
        
        <div class="row">
            <div class="col-md-5">
            </div>
            <div class="col-md-6" id="loader1" style="display:none;">
                <img src="{{asset("lb-faveo/media/images/gifloader.gif")}}"><br/><br/><br/>
            </div>
        </div>
        <div class="mailbox-messages" id="refresh">

            <!--datatable-->
            {!! Form::open(['id'=>'modalpopup', 'route'=>'select_all','method'=>'post']) !!}
            {!!$table->render('vendor.Chumper.template')!!}
            {!! Form::close() !!} 

            <!-- /.datatable -->
        </div><!-- /.mail-box-messages -->
    </div><!-- /.box-body -->
</div><!-- /. box -->


<!-- Modal -->   
@include('themes.default1.agent.helpdesk.ticket.more.tickets-model')

{!! $table->script('vendor.Chumper.tickets-javascript') !!}
@include('themes.default1.agent.helpdesk.ticket.more.tickets-options-script')
<script>
    $(document).ready(function () {
        // Initialize Select2 for all filter dropdowns
        $('.select2-filter').select2({
            placeholder: '-- {{Lang::get("lang.select")}} --',
            allowClear: true,
            width: '100%'
        });

        // Toggle chevron icon on collapse
        $('#filterCollapse').on('show.bs.collapse', function () {
            $('#filter-chevron').removeClass('fa-chevron-down').addClass('fa-chevron-up');
        });
        $('#filterCollapse').on('hide.bs.collapse', function () {
            $('#filter-chevron').removeClass('fa-chevron-up').addClass('fa-chevron-down');
        });

        // Auto-expand filter panel if filters are active
        @if(count($current_filters) > 1)
        $('#filterCollapse').collapse('show');
        @endif

        // Remove empty values before form submission
        $('#filter-form').on('submit', function() {
            $(this).find('select').each(function() {
                if ($(this).val() === '' || $(this).val() === null || (Array.isArray($(this).val()) && $(this).val().length === 0)) {
                    $(this).prop('disabled', true);
                }
            });
        });
    });
</script>
@stop