<!DOCTYPE html>
<html>
<head>
    <title>PHP - Simple  To Do List App</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.2/css/bootstrap.min.css" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        body{
            background-color:#F5F5F5;
        }
        .table td, .table th{
            text-align:center;
        }
        #save-task{
            margin-top:10%;
        }
        #task-form{
            padding:0 25%;
        }

    </style>
</head>
    <body>
        <div class="container">
            <h1>PHP - Simple  To Do List App</h1>
            <form id="task-form" class="form-inline">
                <div class="form-group mx-sm-3 mb-2">
                    <input type="text" class="form-control" id="title" name="title">
                </div>
                <div class="form-group mx-sm-3 mb-2">
                    <button type="submit" class="btn btn-primary mb-2" id="save-task">Add Task</button>
                </div>
                <button type="button" class="btn btn-primary mb-2" id="show-all" data-status="0">ShowAll Task</button>

            </form>
                <table class="table">
                    <thead>
                        <tr>
                        <th scope="col">#</th>
                        <th scope="col">Task</th>
                        <th scope="col">Status</th>
                        <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody id="task-list">
                        @if(!empty($tasks))
                        @foreach($tasks as $i => $task)
                            <tr data-completed="{{$task->is_completed}}">
                                <th scope="row">{{++$i}}</th>
                                <td>{{$task->title}}</td>
                                <td>{{($task->is_completed == 1)?'Done':''}}</td>
                                <td>
                                    <button class="btn btn-success change-status" data-id="{{$task->id}}"><i class="fa fa-check-square-o"></i></button>
                                    <span> | </span>
                                    <button class="btn btn-danger delete-task" data-id="{{$task->id}}"><i class="fa fa-close"></i></button>
                                </td>
                            </tr>
                            @endforeach
                        @endif
                        
                    </tbody>
                </table>
                
            </div>
        </div>

        <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.2/js/bootstrap.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            $(document).ready(function() {
                $("#task-form").submit(function(e) {
                    e.preventDefault();
                    var title = $('#title').val();
                    if(title == ''){
                        Swal.fire({
                            text: "Task title can not be empty!",
                            icon: "error"
                        });
                        return false;
                    }
                    
                    $.ajax({
                        url: '/tasks',
                        method: 'POST',
                        data: {
                            title: title,
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if(response.success == false){
                                Swal.fire({
                                    text: response.message,
                                    icon: "error"
                                });
                                $('#title').val('');
                                return false;
                            }else{
                                loadTasks($('#show-all').data("status"));
                            }
                        }
                    });
                });

                function loadTasks(status=0) {
                    $.ajax({
                        url: '/allTask',
                        method: 'POST',
                        data: {
                            status: status,
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            $('#title').val('');
                            $('#task-list').html('');
                            var html = '';
                            $.each(response, function(index, task) {
                                var status = (task.is_completed == 1)?"Done":"";

                                html += '<tr data-completed="'+task.is_completed+'">';
                                html += '<th scope="row">'+ ++index +'</th>';
                                html += '<td>' + task.title + '</td>';
                                html += '<td>' + status + '</td>';
                                html += '<td>';
                                if(task.is_completed == 0){
                                    html += '<button class="btn btn-success change-status" data-id="' + task.id + '"><i class="fa fa-check-square-o"></i></button>';
                                    html += '<span> | </span>';
                                }
                                html += '<button class="btn btn-danger delete-task" data-id="' + task.id + '"><i class="fa fa-close"></i></button>';
                                html += '</td>';
                                html += '</tr>';

                            });
                            $('#task-list').html(html);
                        }
                    });
                }

                $(document).on('click', '.delete-task', function() {
                    var id = $(this).data('id');
                    Swal.fire({
                        showDenyButton: true,
                        title: "Are u sure to delete this task ?",
                        confirmButtonText: "Delete",
                        denyButtonText: `Cancle`
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: '/tasks/' + id,
                                method: 'DELETE',
                                data: {
                                    _token: $('meta[name="csrf-token"]').attr('content')
                                },
                                success: function(response) {
                                    if(response.success == true){
                                        Swal.fire({
                                            text: response.message,
                                            icon: "success"
                                        });
                                        loadTasks($('#show-all').data("status"));
                                    }
                                }
                            });
                        }
                    });
                    
                });
                $(document).on('click', '.change-status', function() {
                    var id = $(this).data('id');
                    $.ajax({
                        url: '/tasks/' + id,
                        method: 'PUT',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            Swal.fire({
                                text: "Task marked as done!",
                                icon: "success"
                            });
                            loadTasks($('#show-all').data("status"));
                        }
                    });
                });
                $(document).on('click', '#show-hide-task', function() {
                    $('#task-list tr').filter(function() {
                        var rowValue = $(this).find("td:nth-child(3)").text().toLowerCase(); // Change to the desired column
                        var isMatch = rowValue.indexOf('done') > -1;
                        if (isMatch) {
                            $(this).toggle(isMatch);
                        }else{
                            $(this).toggle(rowValue.indexOf('done') > 0);
                        }
                        
                    });
                });
                $('#show-all').click(function() {
                    $('#show-all').data('status', '1');
                    loadTasks(1);
                });
            });
        </script>
    </body>
</html>
