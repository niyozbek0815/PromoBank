@extends('admin.layouts.app')

@section('title', 'Messages')

@push('scripts')
    <script src="{{ asset('adminpanel/assets/js/datatables.min.js') }}"></script>
    <script src="{{ asset('adminpanel/assets/js/buttons.min.js') }}"></script>
    <script src="{{ asset('adminpanel/assets/js/datatables_extension_buttons_init.js') }}"></script>
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $(document).ready(function() {
            if ($.fn.DataTable.isDataTable('#messages-table')) {
                $('#messages-table').DataTable().destroy();
            }

            $('#messages-table').DataTable({
                processing: true,
                serverSide: false,
                ajax: '{{ secure_url(route('admin.settings.messages.data', [], false)) }}',
                columns: [{
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'scope_type',
                        name: 'scope_type'
                    },
                    {
                        data: 'type',
                        name: 'type'
                    },
                    {
                        data: 'channel',
                        name: 'channel'
                    },
                    {
                        data: 'status',
                        name: 'status',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'message',
                        name: 'message'
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false
                    }
                ],
                buttons: [{
                        extend: 'copy',
                        exportOptions: {
                            modifier: {
                                page: 'all'
                            }
                        }
                    },
                    {
                        extend: 'excel',
                        filename: "Messages",
                        exportOptions: {
                            modifier: {
                                page: 'all'
                            }
                        }
                    },
                    {
                        extend: 'csv',
                        filename: "Messages",
                        exportOptions: {
                            modifier: {
                                page: 'all'
                            }
                        }
                    },
                    {
                        extend: 'print',
                        exportOptions: {
                            modifier: {
                                page: 'all'
                            }
                        }
                    }
                ]
            });
        });
    </script>
@endpush

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="page-title mb-0">Messages</h4>
        </div>
        <div class="card-body">
            <table id="messages-table" class="table datatable-button-init-basic">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Qo‘llanish sohasi</th>
                        <th>Turi</th>
                        <th>Platforma</th>
                        <th>Status</th>
                        <th>Xabar (UZ)</th>
                        <th>Amallar</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection
