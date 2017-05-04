@extends('main.main')
@section('bodyContent')

  <div class="wrapper-md" style="padding-bottom:0px;">
    <a href="/supportdash/no-billing"><button class="btn btn-default btn-ticket-sort" type="button">Non-Billing Tickets</button></a>
    <a href="/supportdash/Billing"><button class="btn btn-default btn-ticket-sort" type="button">Billing Tickets</button></a>
    <a href="/supportdash/full"><button class="btn btn-default btn-ticket-sort" type="button">All Tickets</button></a>
  </div>

  <div class="wrapper-md">
    <div class="panel panel-default">
      <div class="table-responsive">
        <table ui-jq="dataTable" class="table table-striped b-t b-b" ui-options="{aaSorting:[[9,'desc']]}">
          <thead>
          <tr>
            <th style="width:10%">Ticket</th>
            <th style="width:10%">Status</th>
            <th style="width:10%">Name</th>
            <th style="width:10%">Address</th>
            <th style="width:10%">Tel</th>
            <th style="width:10%">Email</th>
            <th style="width:10%">Issue</th>
            <th style="width:10%">Comment</th>
            <th style="width:10%">Assigned To</th>
            <th style="width:10%">LastUpdate</th>
          </tr>
          </thead>
          <tbody>
          @foreach($tickets  as $ticket)
            <tr>
              @foreach( $ticket as $index => $tInfo)
{{--{{$ticket['Assigned']}}--}}
{{--<br />--}}
                @if($index == 'Comment')
                  <td class="special-td">{!! $tInfo !!} </td>
                @elseif($index == 'CID' || $index == 'TID' || $index == 'DateCreated' || $index == 'History' || $index == 'Reasons' || $index == 'Admin')
                  @break
                @elseif($index == 'name')
                  <td class="a-tag"><a href="/customers/{{ $ticket['CID'] }}" >{{ $tInfo }}</a></td>
                @elseif($index == 'TicketNumber')
                  {{--<td class="a-tag fancybox-effects-d" style="display: none;">{{ $tInfo }}</td>--}}
                  <td class="a-tag display-ticket" ticket-id="{{ $ticket['TID'] }}" typeoff="open">{{ $tInfo }}</td>
                @elseif($index == 'LastUpdate')
                  <td class="sorting_desc">{{ $tInfo }}</td>
                @elseif($index == 'assigned')
                  <td class="">-------------</td>
                @else
                  <td >{{ $tInfo }}</td>
                @endif
              @endforeach
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>


  <div class="col-lg-12 ticket-pos">
    @foreach($tickets  as $ticket)
      <div ticket-id="{{ $ticket['TID'] }}" class="bg-box-black display-ticket" id="bgblack-{{  $ticket['TID']  }}"style="display: none;"typeoff="close"></div>
      <div ticket="{{ $ticket['CID'] }}" id="fancy-{{ $ticket['TID'] }}" style="display: none;" class="fancy-box ticket">
        <p class="close-btn-box display-ticket"  title="Close" ticket-id="{{ $ticket['TID'] }}" typeoff="close"></p>
        <div class="col-lg-12 bg-white">
          <div class="bg-light lter b-b wrapper-md">
            <h2 class="m-n font-thin h3">Ticket Information</h2>
          </div>
          <br />
          <div class="col-lg-12 gral">
            <div class="panel panel-default">
              <div class="panel-heading ticket">
                <div class="clearfix">
                  <div class="clear">

                    <div class="col-lg-3">
                      <div class="h3 m-t-xs m-b-xs">
                        <i class="fa fa-circle text-success pull-right text-xs m-t-sm"></i>
                        <label id="bloque-{{ $ticket['TID'] }}-CID-c">{{ $ticket['name'] }}</label>
                      </div>
                    </div>


                    <div class="col-lg-9 tlb-sc">

                      <div class="col-lg-12">
                        <form action="updateTicketDetails" dbtable="supportTicketsID" id="c-form-{{ $ticket['TID'] }}">


                          <input type="hidden" name="_token" value="{{ csrf_token() }}">
                          <input type="hidden" name="CID" value="" class="block-{{ $ticket['CID'] }}-hidden">
                          <input type="text" class="form-control block-{{ $ticket['CID'] }}-edit block-{{ $ticket['TID'] }}-getName-c block-{{ $ticket['CID'] }}-input ticket-detail-update dis-input editclass"  name='id' readonly>


                          <div class="botones-details-ticket row botones-details-ticket-n" >
                            <button class="btn m-b-xs w-xs btn-info btn-edit block-{{ $ticket['TID'] }}-c" id="block-{{ $ticket['CID'] }}" onclick="return false; ">Edit</button>
                            <button class="btn m-b-xs w-xs btn-success save-btn ticket-save-dupdate" id="save-block-{{ $ticket['CID'] }}" onclick="return false;" TID="{{ $ticket['TID'] }}" idType="TID" bloque="c">Update</button>
                          </div>
                        </form>
                      </div>

                      <div class="panel-body tlb-s block-{{ $ticket['CID'] }}-edit editclass">
                        <form class="form-horizontal" method="get" id="complexSearch">
                          <div class="form-group">
                            <label class="col-sm-1 control-label">Code</label>
                            <div class="col-md-5"><input  index="4" id="id-customers-searchAddress" type="text" name="ShortName" placeholder="Search Address ..." class="input-search id-search form-control"></div>
                            <label class="col-sm-1 control-label">Unit</label>
                            <div class="col-md-5"><input  index="3" id="id-customers-searchUnit" type="text" name=Unit" placeholder="Search Unit ..." class="input-search id-search form-control"></div>
                            {{--<label class="col-sm-1 control-label">Contact</label>--}}
                            {{--<div class="col-md-2"><input index="1" id="id-customers-searchTel" type="text" name="Tel" placeholder="Search Contact Number ..." class="input-search id-search form-control"></div>--}}
                            {{--<label class="col-sm-1 control-label">Email</label>--}}
                            {{--<div class="col-md-2"><input index="2"  id="id-customers-searchEmail" type="text" name="Email" placeholder="Search Email Adress..." class="input-search id-search form-control"></div>--}}
                          </div>
                        </form>
                      </div>
                     <div id="id-customers-search-result" class="search-result anim resultadosComplex tlb-r"></div>
                  </div>


                    <div class="col-lg-3 ticket">
                      <div>Address</div>
                      <small class="text-muted">{{ $ticket['Address'] }} #{{ $ticket['ShortName'] }}</small>
                    </div>
                    <div class="col-lg-3 ticket">
                      <div>Tel</div>
                      <small class="text-muted">{{ $ticket['Tel'] }}</small>
                    </div>
                    <div class="col-lg-3 ticket">
                      <div>Email</div>
                      <small class="text-muted">{{ $ticket['Email'] }}</small>
                    </div>
                    <div class="col-lg-3 ticket">
                      <div>Status</div>
                      <small class="text-muted">{{ $ticket['Status'] }}</small>
                    </div>
                  </div>
                </div>
              </div>

              <form action="updateTicketDetails" method="POST" id="a-form-{{ $ticket['TID'] }}" dbtable="supportTickets">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <div class="list-group no-radius alt">
                  <div class="panel-heading">
                    Details
                    <div class="botones-details-ticket">
                      <button class="btn m-b-xs w-xs btn-info btn-edit" id="block-{{ $ticket['TID'] }}" onclick="return false; ">Edit</button>
                      <button class="btn m-b-xs w-xs btn-success save-btn ticket-save-dupdate" id="save-block-{{ $ticket['TID'] }}" onclick="return false;" TID="{{ $ticket['TID'] }}" idType="TID" bloque="a">Update</button>
                    </div>
                  </div>
                </div>

                <div class="panel ticket">
                  <div class="col-lg-3 ticket"><div>Ticket ID:             <small class="text-muted m-l-xs">{{ $ticket['TicketNumber'] }}</small></div></div>

                  <div class="col-lg-3 ticket">
                    <div>
                      Issue:
                      <small class="text-muted m-l-xs block-{{ $ticket['TID'] }}-label display-inline" id="RID-{{ $ticket['TID'] }}">{{ $ticket['ReasonShortDesc'] }}</small>
                      <select class="form-control block-{{ $ticket['TID'] }}-edit ticket-detail-update editclass" name="RID" >
                        <option value="0">Select Opction...</option>
                        @foreach($ticket['Reasons'] as $reasonInfo)
                          <option value="{{ $reasonInfo['RID'] }}">{{ $reasonInfo['ReasonShortDesc'] }}</option>
                        @endforeach
                      </select>
                    </div>
                  </div>

                  <div class="col-lg-3 ticket">
                    <div>
                      Status:
                      <small class="text-muted m-l-xs block-{{ $ticket['TID'] }}-label display-inline"id="Status-{{ $ticket['TID'] }}">{{ $ticket['Status'] }}</small>
                      <select class="form-control block-{{ $ticket['TID'] }}-edit ticket-detail-update editclass" name="Status" >
                        <option value="escalated">Escalate</option>
                        <option value="closed">Close</option>
                      </select>
                    </div>
                  </div>

                  <div class="col-lg-3 ticket">
                    <div>
                      Assigned to:
                      <small class="text-muted m-l-xs block-{{ $ticket['TID'] }}-label display-inline" id="StaffID-{{ $ticket['TID'] }}">{{ $ticket['Assigned'] }}</small>
                      <select class="form-control block-{{ $ticket['TID'] }}-edit ticket-detail-update editclass" name="StaffID" >
                        <option value="err">Select Opction...</option>
                        @foreach($ticket['Admin'] as $reasonInfo)
                          <option value="{{ $reasonInfo['ID'] }}">{{ $reasonInfo['Name'] }}</option>
                        @endforeach
                      </select>
                    </div>
                  </div>
                </div>
              </form>

              <div class="panel  comm">
                <small class="text-muted m-l-xs">Details:</small>
                <div class="comment-content">
                  {!! $ticket['Comment'] !!}
                </div>
              </div>

              <div class="panel ticket">
                <div class="col-lg-4"><div>Created By:                   <small class="text-muted m-l-xs">{{ $ticket['TicketNumber'] }}</small></div></div>
                <div class="col-lg-4 datesize"><div>Created On:          <small class="text-muted m-l-xs">{{ $ticket['DateCreated'] }}</small></div></div>
                <div class="col-lg-4 datesize"><div>Last Update:         <small class="text-muted m-l-xs">{{ $ticket['LastUpdate'] }}</small></div></div>
              </div>

              <div class="list-group no-radius alt"><div class="panel-heading">New Details</div></div>

              <form action="updateTicketHistory" method="POST" id="b-form-{{ $ticket['TID'] }}" dbtable="supportTicketHistory">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <div class="panel comm">
                  <div class="comment-content-add">

                    <div class="form-group editorgrande">

                      <div ui-jq="wysiwyg" class="form-control ticket ticketadd-info-textbox" >
                        <textarea name="Comment" id="b-comment-{{ $ticket['TID'] }}" placeholder="Go ahead..." class="textarea-main-size" value="err"></textarea>
                      </div>
                    </div>

                  </div>
                </div>

                <div class="form-group col-lg-12 suhs">
                  <label class="col-sm-2 control-label">Status</label>
                  <div class="col-sm-10">
                    <label class="suhs-label">Escalate</label><label class="i-switch bg-dark m-t-xs m-r suhs-radio"><input type="radio" name="Status" checked="" value="escalated"><i></i></label>
                    <label class="suhs-label">Close</label><label class="i-switch bg-dark m-t-xs m-r suhs-radio"><input type="radio"  name="Status" value="closed"><i></i></label>
                  </div>
                </div>

                <div class="col-lg-12 display-content-main-box update-btn-ticket">
                  <button class="btn m-b-xs w-xs btn-primary save-btn display-inline" id="" idType="TID" TID="{{ $ticket['TID'] }}" bloque="b" onclick="return false;">UPDATE</button>
                </div>
              </form>


              <div class="list-group no-radius alt"><div class="panel-heading">History</div></div>
              @if($ticket['History'])
                <div class="col-lg-12 m-b-xxl">
                  <div class="table-responsive">
                    <table  ui-jq="dataTable" class="table table-striped b-t b-b">
                      <thead>
                      <tr>
                        <th class="sorting_desc">Timestamp</th>
                        <th>Details</th>
                        <th>Status</th>
                        <th>Staff</th>
                      </tr>
                      </thead>
                      <tbody id="b-tbody-{{ $ticket['TID'] }}">
                      @foreach($ticket['History'] as $historyData)
                        <tr>
                          <td >{{ $historyData->TimeStamp }}</td>
                          <td class="special-td">{!! $historyData->Comment !!}</td>
                          <td >{{ $historyData->Status }}</td>
                          <td >{{ $historyData->Name }}</td>
                        </tr>
                      @endforeach
                      </tbody>
                    </table>
                  </div>
                </div>
              @endif

            </div>
          </div>
        </div>
      </div>
    @endforeach
  </div>

@stop