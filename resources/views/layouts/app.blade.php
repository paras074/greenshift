<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>@yield('title', config('app.name', 'Laravel'))</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="icon" type="image/x-icon" href="{{ asset('images/fav-gsec.png') }}">
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
  <link href="https://fonts.googleapis.com/css2?family=Anton&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}"/>
  <link rel="stylesheet" href="{{ asset('css/dashboard-responsive.css') }}"/>
  <link rel="stylesheet" href="https://cdn.datatables.net/rowreorder/1.4.1/css/rowReorder.dataTables.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.css">
  @stack('styles')
</head>
<body>
  @include('layouts.partials.sidebar')
  <div class="dashboard-overlay" id="dashboardOverlay"></div>
  <div class="dashboard-main-wrapper">
    @include('layouts.partials.navbar')
    @include('layouts.partials.mobile-search')
    <main class="dashboard-content">
	@include('layouts.partials.notifications')
	@yield('content')
    </main>
  </div>
  <div class="mini-confirm-overlay" id="miniConfirm">
    <div class="mini-confirm-box">
        <p id="miniConfirmText">Are you sure?</p>
        <div class="mini-confirm-actions">
            <button class="mini-confirm-cancel" id="miniConfirmCancel">Cancel</button>
            <button class="mini-confirm-ok" id="miniConfirmOk">Confirm</button>
        </div>
    </div>
</div>
  
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
  <script src="{{ asset('js/dashboard.js') }}"></script>
  <script>
  window.appConfig = {
      csrfToken: "{{ csrf_token() }}",
      routes: {
          notifications: "{{ route('notifications.index') }}",
          notifications_markread: "{{ url('/notifications/mark-read') }}",
          update_task_status: "{{ route('tasks.update-status', ':id') }}",
          create_schedule_task: "{{ route('tasks.schedulecreate') }}",
          load_task: "{{ route('loadtask.per', ':id') }}"
      }
  };
  </script>
  <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/laravel-echo/1.16.1/echo.iife.js"></script>
  @if(auth()->check())

  <script>
      const authUserId = "{{ auth()->id() }}";
      window.Pusher = Pusher;
      window.Echo = new Echo({
          broadcaster: 'pusher',
          key: "{{ env('PUSHER_APP_KEY') }}",
          cluster: "{{ env('PUSHER_APP_CLUSTER') }}",
          forceTLS: true
      });

      window.Echo.channel('notifications')
          .listen('.notification.count.updated', (e) => {
              console.log(e);
              if (parseInt(e.data.user_id) === parseInt(authUserId)) {
                  let countBox = $('#notification-count');
                  if (e.data.unread_count > 0) {
                     countBox.text(e.data.unread_count > 9 ? '9+' : e.data.unread_count).show();
                } else {
                    countBox.hide();
                }
              }
          });

  </script>

  @endif
  <script src="{{ asset('js/init.js') }}?v={{ filemtime(public_path('js/init.js')) }}"></script>
  <script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.js"></script>
  @livewireScripts
  <script src="https://cdn.jsdelivr.net/gh/livewire/sortable@v1.x.x/dist/livewire-sortable.js"></script>
  @stack('scripts')
  <script>
    toastr.options = { closeButton: true, progressBar: true, positionClass: 'toast-top-right', timeOut: 3500 };
    @if (session('status')) toastr.success(@json(session('status'))); @endif
    @if (session('error'))  toastr.error(@json(session('error')));   @endif
    @if ($errors->any())    toastr.error(@json($errors->first()));   @endif
  </script>
</body>
</html>
