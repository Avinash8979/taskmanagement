<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Manager</title>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">

</head>
<style>
/* General Styles */
body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f9;
    color: #333;
    margin: 0;
    padding: 20px;
}

#task-app {
    max-width: 500px;
    margin: 0 auto;
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

 

/* Task List Styles */
#task-list {
    list-style-type: none;
    padding: 0;
    margin: 0;
}

#task-list li {
    display: flex;
    align-items: center;
    padding: 10px;
    border-bottom: 1px solid #ddd;
    transition: background-color 0.3s;
}

#task-list li:last-child {
    border-bottom: none;
}

#task-list li.completed {
    background-color: #d4edda;
    /* text-decoration: line-through; */
    color: #6c757d;
}

#task-list li:hover {
    background-color: #f8f9fa;
}

.task-checkbox {
    margin-right: 10px;
    cursor: pointer;
}

.delete-task-btn {
    margin-left: auto;
    padding: 5px 10px;
    background-color: #dc3545;
    color: #fff;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    transition: background-color 0.3s;
}

.delete-task-btn:hover {
    background-color: #c82333;
}

/* Show All Tasks Button */
#show-all-tasks-btn {
    display: block;
    width: 100%;
    padding: 10px;
    margin-top: 20px;
    background-color: #007bff;
    color: #fff;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
    transition: background-color 0.3s;
}

#show-all-tasks-btn:hover {
    background-color: #0069d9;
}

.check-symbol {
    margin-left: 10px;
    color: green; /* Green color for the check symbol */
    font-size: 18px;
    font-weight: bold;
}

#task-input-container {
    display: flex;
    align-items: center;
    margin-bottom: 15px; /* Optional: Add some space below */
}

#new-task {
    flex: 1; /* This allows the input field to take up available space */
    padding: 8px;
    font-size: 16px;
    border: 1px solid #ccc;
    border-radius: 4px;
    margin-right: 10px; /* Space between input and button */
}

#add-task-btn {
    padding: 8px 12px;
    font-size: 16px;
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

#add-task-btn:hover {
    background-color: #0056b3;
}

</style>
<body>
    <div id="task-app">
        <div id="task-input-container">
            <input type="text" id="new-task" placeholder="Enter new task" autofocus>
            <button id="add-task-btn">Add Task</button>
        </div>
        
        <ol id="task-list">
            @foreach($tasks as $task)
                <li data-id="{{ $task->id }}" class="{{ $task->completed ? 'completed' : '' }}">
                    <input type="checkbox" class="task-checkbox" {{ $task->completed ? 'checked' : '' }}>
                    <span>{{ $task->name }}</span>                     
                     <button class="delete-task-btn">&times;</button>
                     @if($task->completed == 1)
                        <span class="check-symbol" style="margin-left: 10px; color: green;">✔</span>
                    @endif

                </li>
            @endforeach
        </ol>

        <button id="show-all-tasks-btn">Show All Tasks</button>
    </div>

    <script>
        $(document).ready(function() {
            $('#add-task-btn').click(function() {
                let taskName = $('#new-task').val();
                if (taskName === '') return;  
                $.post('/tasks', {
                    name: taskName,
                    _token: $('meta[name="csrf-token"]').attr('content')
                }, function(task) {
                    $('#task-list').append(`
                        <li data-id="${task.id}">
                            <input type="checkbox" class="task-checkbox">
                            <span>${task.name}</span>
                            <button class="delete-task-btn">&times;</button>
                        </li>
                    `);
                    $('#new-task').val('');
                }).fail(function() {
                    alert('Task already exists or another error occurred.');
                });
            });

            

            $(document).on('change', '.task-checkbox', function() {
                let taskElement = $(this).closest('li');
                let taskId = taskElement.data('id');

                $.ajax({
                    url: `/tasks/status/${taskId}`,
                    method: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function() {
                        taskElement.toggleClass('completed');

                        if (taskElement.hasClass('completed')) {
                        // Add the check symbol if it doesn't exist
                        if (!taskElement.find('.check-symbol').length) {
                            taskElement.append(' <span class="check-symbol" style="margin-left: 10px; color: green;">✔</span>');
                        }
                        } else {
                            // Remove the check symbol if the task is marked incomplete
                            taskElement.find('.check-symbol').remove();
                        }
                    },
                    error: function() {
                        alert('An error occurred while updating the task status.');
                    }
                });
            });


            $(document).on('click', '.delete-task-btn', function() {
                if (!confirm('Are you sure to delete this task?')) return;

                let taskElement = $(this).closest('li');
                let taskId = taskElement.data('id');

                $.ajax({
                    url: `/tasks/${taskId}`,
                    method: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function() {
                        taskElement.remove();
                    }
                });
            });

            $('#show-all-tasks-btn').click(function() {
                $.get('/', function(data) {

                    $('#task-list').empty(); 
                    data.tasks.forEach(task => {
                        let checkSymbol = task.completed ? '<span class="check-symbol" style="margin-left: 10px; color: green;">✔</span>' : '';
                        $('#task-list').append(`
                            <li data-id="${task.id}" class="${task.completed ? 'completed' : ''}">
                                <input type="checkbox" class="task-checkbox">
                                <span>${task.name}</span> 
                                <button class="delete-task-btn">&times;</button>
                                ${checkSymbol}
                            </li>
                        `);
                    });
                });
            });
        });
    </script>

    <style>
        .completed span {
            /* text-decoration: line-through; */
        }
    </style>
</body>
</html>
