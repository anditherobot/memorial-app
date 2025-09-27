@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
  <div class="flex items-center justify-between">
    <h1 class="text-2xl font-semibold">Task & Feature Tracker</h1>
    <button id="add-task-btn" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
      + Add Task
    </button>
  </div>

  <!-- Kanban Board -->
  <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
    @foreach(['todo' => 'To Do', 'in_progress' => 'In Progress', 'blocked' => 'Blocked', 'completed' => 'Completed'] as $status => $label)
      <div class="kanban-col bg-white/80 border rounded-xl p-4">
        <h2 class="font-semibold text-gray-700 mb-4 flex items-center justify-between sticky top-0 bg-white/80 backdrop-blur rounded px-1 py-1">
          <span>{{ $label }}</span>
          <span class="count-pill">({{ $tasks->get($status, collect())->count() }})</span>
        </h2>

        <div class="space-y-3 task-column" data-status="{{ $status }}">
          @foreach($tasks->get($status, collect()) as $task)
            @php
              $border = match($task->status) {
                'in_progress' => 'border-l-blue-500',
                'blocked' => 'border-l-red-500',
                'completed' => 'border-l-green-500',
                default => 'border-l-gray-300',
              };
            @endphp
            <div class="task-card task-card-ui bg-white border rounded-lg p-3 shadow-sm cursor-pointer hover:shadow-md transition-shadow border-l-4 {{ $border }}"
                 data-task-id="{{ $task->id }}"
                 data-status="{{ $task->status }}"
                 data-task='@json($task->toArray())'>
              <div class="flex items-start justify-between mb-2">
                <h3 class="font-medium text-sm text-gray-900 leading-tight">{{ $task->title }}</h3>
                <span class="text-xs px-2 py-1 rounded {{ $task->priority_color }} flex-shrink-0 ml-2">
                  {{ ucfirst($task->priority) }}
                </span>
              </div>

              @if($task->description)
                <p class="text-xs text-gray-600 mb-2 line-clamp-2">{{ Str::limit($task->description, 80) }}</p>
              @endif

              <div class="flex items-center justify-between text-xs text-gray-500">
                <div>
                  @if($task->category)
                    <span class="chip bg-gray-100">{{ $task->category }}</span>
                  @endif
                </div>
                <div class="flex items-center space-x-2">
                  @if($task->assignedTo)
                    <span class="chip bg-gray-100">{{ $task->assignedTo->name }}</span>
                  @endif
                  @if($task->due_date)
                    @php $overdue = $task->due_date->isPast() && $task->status !== 'completed'; @endphp
                    <span class="chip {{ $overdue ? 'bg-red-100 text-red-700' : 'bg-gray-100' }}">{{ $task->due_date->format('M j') }}</span>
                  @endif
                </div>
              </div>

              <div class="flex items-center justify-between mt-2">
                <span class="status-badge text-xs px-2 py-1 rounded {{ $task->status_color }}">{{ ucfirst(str_replace('_',' ', $task->status)) }}</span>
                <select class="task-status-select text-xs border rounded px-2 py-1"
                        data-task-id="{{ $task->id }}">
                  <option value="todo" {{ $task->status==='todo' ? 'selected' : '' }}>To Do</option>
                  <option value="in_progress" {{ $task->status==='in_progress' ? 'selected' : '' }}>In Progress</option>
                  <option value="blocked" {{ $task->status==='blocked' ? 'selected' : '' }}>Blocked</option>
                  <option value="completed" {{ $task->status==='completed' ? 'selected' : '' }}>Completed</option>
                </select>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    @endforeach
  </div>
</div>

<!-- Task Modal -->
<div id="task-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
  <div class="flex items-center justify-center min-h-screen p-4">
    <div class="bg-white rounded-lg p-6 w-full max-w-md">
      <h2 id="modal-title" class="text-lg font-semibold mb-4">Add Task</h2>
      <form id="task-form">
        @csrf
        <input type="hidden" id="task-id" name="task_id">

        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
            <input type="text" id="task-title" name="title" required
                   class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea id="task-description" name="description" rows="3"
                      class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
              <select id="task-status" name="status" required
                      class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="todo">To Do</option>
                <option value="in_progress">In Progress</option>
                <option value="blocked">Blocked</option>
                <option value="completed">Completed</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
              <select id="task-priority" name="priority" required
                      class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="low">Low</option>
                <option value="medium">Medium</option>
                <option value="high">High</option>
                <option value="urgent">Urgent</option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
              <input type="text" id="task-category" name="category"
                     class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                     placeholder="e.g., Feature, Bug, Enhancement">
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Assign To</label>
              <select id="task-assigned" name="assigned_to"
                      class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="">Unassigned</option>
                @foreach($users as $user)
                  <option value="{{ $user->id }}">{{ $user->name }}</option>
                @endforeach
              </select>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
            <input type="date" id="task-due-date" name="due_date"
                   class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
            <textarea id="task-notes" name="notes" rows="2"
                      class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
          </div>
        </div>

        <div class="flex justify-between mt-6">
          <button type="button" id="delete-task-btn" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 hidden">
            Delete
          </button>
          <div class="space-x-3">
            <button type="button" id="close-modal-btn" class="px-4 py-2 border border-gray-300 rounded hover:bg-gray-50">
              Cancel
            </button>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
              Save Task
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const modal = document.getElementById('task-modal');
  const form = document.getElementById('task-form');
  const addBtn = document.getElementById('add-task-btn');
  const closeBtn = document.getElementById('close-modal-btn');
  const deleteBtn = document.getElementById('delete-task-btn');
  const modalTitle = document.getElementById('modal-title');

  // Open modal for new task
  addBtn.addEventListener('click', function() {
    resetForm();
    modalTitle.textContent = 'Add Task';
    deleteBtn.classList.add('hidden');
    modal.classList.remove('hidden');
  });

  // Close modal
  closeBtn.addEventListener('click', function() {
    modal.classList.add('hidden');
  });

  // Close modal on background click
  modal.addEventListener('click', function(e) {
    if (e.target === modal) {
      modal.classList.add('hidden');
    }
  });

  // Open modal for editing task
  document.querySelectorAll('.task-card').forEach(card => {
    card.addEventListener('click', function() {
      const taskData = JSON.parse(this.getAttribute('data-task'));
      populateForm(taskData);
      modalTitle.textContent = 'Edit Task';
      deleteBtn.classList.remove('hidden');
      modal.classList.remove('hidden');
    });
  });

  // Submit form
  form.addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(form);
    const taskId = formData.get('task_id');
    const isEdit = taskId && taskId !== '';

    const url = isEdit ? `/admin/tasks/${taskId}` : '/admin/tasks';
    const method = isEdit ? 'PUT' : 'POST';

    // Convert FormData to regular object for JSON
    const data = {};
    formData.forEach((value, key) => {
      if (key !== 'task_id' && key !== '_token') {
        data[key] = value || null;
      }
    });

    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    const csrf = csrfMeta ? csrfMeta.getAttribute('content') : formData.get('_token');
    fetch(url, {
      method: method,
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrf
      },
      body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        location.reload();
      }
    })
    .catch(error => console.error('Error:', error));
  });

  // Delete task
  deleteBtn.addEventListener('click', function() {
    const taskId = document.getElementById('task-id').value;

    if (confirm('Are you sure you want to delete this task?')) {
      const csrfMeta = document.querySelector('meta[name="csrf-token"]');
      const csrf = csrfMeta ? csrfMeta.getAttribute('content') : '';
      fetch(`/admin/tasks/${taskId}`, {
        method: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': csrf
        }
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          location.reload();
        }
      })
      .catch(error => console.error('Error:', error));
    }
  });

  function resetForm() {
    form.reset();
    document.getElementById('task-id').value = '';
  }

  function populateForm(task) {
    document.getElementById('task-id').value = task.id;
    document.getElementById('task-title').value = task.title || '';
    document.getElementById('task-description').value = task.description || '';
    document.getElementById('task-status').value = task.status || 'todo';
    document.getElementById('task-priority').value = task.priority || 'medium';
    document.getElementById('task-category').value = task.category || '';
    document.getElementById('task-assigned').value = task.assigned_to || '';
    document.getElementById('task-due-date').value = task.due_date || '';
    document.getElementById('task-notes').value = task.notes || '';
  }

  // Quick status update dropdowns (in-place move + badge recolor)
  document.querySelectorAll('.task-status-select').forEach(sel => {
    // Prevent opening the edit modal when interacting with the dropdown
    sel.addEventListener('click', e => e.stopPropagation());

    sel.addEventListener('change', function() {
      const taskId = this.getAttribute('data-task-id');
      const status = this.value;
      const csrfMeta = document.querySelector('meta[name="csrf-token"]');
      const csrf = csrfMeta ? csrfMeta.getAttribute('content') : '';

      fetch(`/admin/tasks/${taskId}/status`, {
        method: 'PATCH',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrf
        },
        body: JSON.stringify({ status })
      })
      .then(r => r.json())
      .then(d => {
        if (!d.success) return;
        const card = sel.closest('.task-card');
        const fromColumn = card.closest('.task-column');
        const toColumn = document.querySelector(`.task-column[data-status="${status}"]`);
        if (toColumn) {
          toColumn.appendChild(card);
        }
        // Update card data-status
        card.setAttribute('data-status', status);
        // Update status badge text and color
        const badge = card.querySelector('.status-badge');
        const clsMap = {
          todo: 'bg-gray-100 text-gray-800',
          in_progress: 'bg-blue-100 text-blue-800',
          blocked: 'bg-red-100 text-red-800',
          completed: 'bg-green-100 text-green-800',
        };
        // Remove any known color classes, then add new
        badge.classList.remove('bg-gray-100','text-gray-800','bg-blue-100','text-blue-800','bg-red-100','text-red-800','bg-green-100','text-green-800');
        const parts = clsMap[status].split(' ');
        parts.forEach(c => badge.classList.add(c));
        badge.textContent = status.replace('_',' ').replace(/\b\w/g, c => c.toUpperCase());

        // Update column counts
        function updateCount(col) {
          if (!col) return;
          const count = col.querySelectorAll('.task-card').length;
          const heading = col.previousElementSibling; // the <h2>
          if (heading) {
            const span = heading.querySelector('span');
            if (span) span.textContent = `(${count})`;
          }
        }
        updateCount(fromColumn);
        updateCount(toColumn);
      })
      .catch(err => console.error('Status update failed', err));
    });
  });
});
</script>

<style>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
@endsection
