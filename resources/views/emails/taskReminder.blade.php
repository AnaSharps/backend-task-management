<div>
    You have {{ $taskCount }} tasks remaining.

    <table style="border: 1px solid black;">
        <tr style="border: 1px solid black;">
            <strong>
                <td style="border: 1px solid black;">Task Name</td>
                <td style="border: 1px solid black;">Status</td>
                <td style="border: 1px solid black;">Due Date</td>
                <td style="border: 1px solid black;">Assignor</td>
            </strong>
        </tr>
        @foreach ($tasks as $task)
        <tr style="border: 1px solid black;">
            <td style="border: 1px solid black;">{{ $task->taskName }}</td>
            <td style="border: 1px solid black;">{{ $task->status }}</td>
            <td style="border: 1px solid black;">{{ $task->dueDate }}</td>
            <td style="border: 1px solid black;">{{ $task->assignor }}</td>
        </tr>
        @endforeach
    </table>
</div>