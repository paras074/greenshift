/* delete form from the list function submit form */
window.DeleteLead = function(lead_id) {
    var formId = 'DeleteLeadForm-' + lead_id;
    var form = document.getElementById(formId);
    if (!form) { console.warn('Form with id ' + formId + ' not found.'); return; }

    window.miniConfirm(
        'Are you sure you want to delete this lead? This action cannot be undone.',
        'Delete',
        function() { form.submit(); }
    );
};

window.DeleteTask = function(task_id) {
    var formId = 'DeleteTaskForm-' + task_id;
    var form = document.getElementById(formId);
    if (!form) { console.warn('Form with id ' + formId + ' not found.'); return; }

    window.miniConfirm(
        'Are you sure you want to delete this Task? This action cannot be undone.',
        'Delete',
        function() { form.submit(); }
    );
};

window.SetTableState = function(tableId, type, message = "") {
    const $table = $(tableId);
    const $tbody = $table.find('tbody');
    
    const colCount = $table.find('thead th').length || 1;

    let content = '';

    if (type === 'loader') {
        content = `
			<tr>
				<td colspan="8" class="text-center py-5">
					<div class="spinner-border text-primary" role="status"></div>
					<div class="mt-2">${message || 'Loading leads...'}</div>
				</td>
			</tr>`;
    } 
    else if (type === 'error') {
        content = `
            <tr>
                <td colspan="${colCount}" class="text-center py-5">
                    <i class="bi bi-exclamation-triangle text-danger" style="font-size: 2rem;"></i>
                    <div class="mt-2 text-danger">${message || 'Something went wrong. Please try again.'}</div>
                </td>
            </tr>`;
    }

    $tbody.html(content);
};

window.SaveTaskSt = function(taskId) {
    const statusSelect = document.getElementById('status');
    const msgArea = document.querySelector('.status-message-area');
    msgArea.innerHTML = '<span class="text-muted small">Saving...</span>';

    msgArea.innerHTML = `<div class="gs-alert gs-alert--info" style="margin-bottom:6px; display:flex;">
		<i class="bi bi-info-circle-fill"></i> Saving...
	</div>`;

    if(!statusSelect){
        console.error('Status select not found');
        return;
    }
    const statusValue = statusSelect.value;
    if(!statusValue){
        console.error('No status selected');    
        return;
    }

    statusSelect.disabled = true;

    let url = window.appConfig.routes.update_task_status.replace(':id', taskId);

    fetch(url, {
        method: 'POST', // You can use PATCH if you prefer
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': window.appConfig.csrfToken
        },
        body: JSON.stringify({
            status: statusValue
        })
    })
    .then(res => {
        if (!res.ok) throw new Error('Update failed');
        return res.json();
    })
    .then(data => {
        console.log('Status updated:', data.message);
        msgArea.innerHTML = `<div class="gs-alert gs-alert--success" style="margin-bottom:6px; display:flex;">
            <i class="bi bi-check-circle-fill"></i> ${data.message}
        </div>`;
        setTimeout(() => { msgArea.innerHTML = ''; }, 3000);
    })
    .catch(err => {
        console.error(err);
        msgArea.innerHTML = `<div class="gs-alert gs-alert--error" style="margin-bottom:6px; display:flex;">
            <i class="bi bi-exclamation-circle-fill"></i> Failed to update status.
        </div>`;
    })
    .finally(() => {
        statusSelect.disabled = false;
    });
};

// Helper function to build and append the HTML you provided
window.showAlert = function(message, type) {
    let schdbutton = document.getElementById('schedule-btn');
    const icon = {
        success: 'bi-check-circle-fill',
        error: 'bi-exclamation-circle-fill',
        info: 'bi-info-circle-fill'
    }[type];

    const alertHtml = `
        <div class="gs-alert gs-alert--${type}" style="display:flex;">
            <i class="bi ${icon}"></i> ${message}
        </div>
    `;
    
    schdbutton.insertAdjacentHTML('afterend', alertHtml);
}

window.ScheduleFollowUp = function(leadid) {
    const btn = document.getElementById('schedule-btn');
    const dateInput = document.querySelector('input[name="follow_up_date"]');
    const reminderInput = document.querySelector('.vl-switch input[type="checkbox"]');
    
    // Clear any existing alerts before starting
    document.querySelectorAll('.gs-alert').forEach(el => el.remove());

    const dateValue = dateInput.value;
    if (!dateValue) {
        showAlert('Please select date', 'error');
        return;
    }

    // Date Validation
    const selectedDate = new Date(dateValue).setHours(0,0,0,0);
    const today = new Date().setHours(0,0,0,0);
    if (selectedDate < today) {
        showAlert('Date cannot be in the past', 'error');
        return;
    }

    // Show Loading/Info Alert
    showAlert('Scheduling...', 'info');
    btn.disabled = true;

    fetch(window.appConfig.routes.create_schedule_task, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': window.appConfig.csrfToken
        },
        body: JSON.stringify({
            lead_id: leadid,
            end_date: dateValue,
            reminder: reminderInput.checked ? 1 : 0
        })
    })
    .then(response => response.json())
    .then(data => {
        document.querySelectorAll('.gs-alert').forEach(el => el.remove());
        
        if(data.success) {
            showAlert('Follow-up scheduled successfully!', 'success');
            dateInput.value = ''; // Reset input
            
            // Remove success message after 500ms
            setTimeout(() => {
                document.querySelectorAll('.gs-alert--success').forEach(el => el.remove());
            }, 5000);
        } else {
            showAlert(data.message || 'Something went wrong', 'error');
        }
    })
    .catch(error => {
        document.querySelectorAll('.gs-alert').forEach(el => el.remove());
        showAlert('Server error occurred', 'error');
    })
    .finally(() => {
        btn.disabled = false;
    });
};


window.FetchTask = function(taskId) {
    const container = document.getElementById('tasks-body');
    if(container){
        container.innerHTML = `
            <div class='text-center py-5'>
                <div class='spinner-border text-primary' role='status' style='width: 2.5rem; height: 2.5rem;'>
                    <span class='visually-hidden'>Loading...</span>
                </div>
            </div>`;

        let url = window.appConfig.routes.load_task.replace(':id', taskId);
        fetch(url, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': window.appConfig.csrfToken
            }
        })
        .then(res => res.json())
        .then(data => {
            
            container.innerHTML = '';
            container.innerHTML = data.html;
        })
        .catch(err => console.error(err));
    }
};

document.addEventListener('DOMContentLoaded', function () {
    initMultiselectSearch('sale_managers_search', '.sales-manager');
    initMultiselectSearch('sale_members_search', '.sales-member');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const notifBtn = document.querySelector('[data-bs-target="#notificationPanel"]');
    const notifBody = document.getElementById('notifications-body');

    if (notifBtn && notifBody) {

        let loaded = false;

        notifBtn.addEventListener('click', function () {

            // show loader
            notifBody.innerHTML = `
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status" style="width: 2.5rem; height: 2.5rem;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `;

            fetch(window.appConfig?.routes?.notifications, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": csrfToken || "",
                    "X-Requested-With": "XMLHttpRequest"
                }
            })
            .then(response => response.json())
            .then(data => {

                notifBody.innerHTML = data.html;
                let el = document.getElementById('unread_count');
                if (el) {
                    el.textContent = data.count > 0 ? `(${data.count})` : '(0)';
                }
                loaded = true;
            })
            .catch(error => {
                console.error(error);
                notifBody.innerHTML = `<div class="text-danger text-center py-3">Failed to load notifications</div>`;
            });

        });

        document.body.addEventListener('click', function (e) {

            const item = e.target.closest('.gs-notif-item');

            if (!item) return;

            const id = item.dataset.id;
            const url = item.getAttribute('href');

            if (!id) return;

            // UI update immediately
            item.classList.remove('gs-notif-item--unread');

            fetch(`${window.appConfig.routes.notifications_markread}/${id}`, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": csrfToken,
                    "X-Requested-With": "XMLHttpRequest"
                }
            })
            .then(res => res.json())
            .then(data => {
                console.log('Marked as read');
            })
            .catch(err => {
                console.error(err);
            });

            // redirect
            if (url && url !== '#') {
                window.location.href = url;
            }

        });
    }

    const taskPanel = document.getElementById('TaskPanel');
    if(taskPanel){
        taskPanel.addEventListener('show.bs.offcanvas', function (event) {
            const button = event.relatedTarget;
            const taskId = button.getAttribute('data-id');
            const title = button.getAttribute('title');
            const panelTitle = document.getElementById('TaskPanelLabel');
            if(panelTitle){
                panelTitle.textContent = title;
                FetchTask(taskId);
            }           
        });
    }

});

window.initMultiselectSearch = function(inputId, listClass) {
    const searchInput = document.getElementById(inputId);
    const container = document.querySelector(listClass);

    if (!searchInput || !container) return;

    const items = container.querySelectorAll('.item-group');
    const noResults = container.querySelector('.no-results');

    searchInput.addEventListener('input', function () {
        const query = this.value.toLowerCase().trim();
        let hasMatches = false;

        items.forEach(item => {
            const text = item.textContent.toLowerCase();
            
            if (query.length === 0 || text.includes(query)) {
                item.style.display = 'block';
                hasMatches = true;
            } else {
                item.style.display = 'none';
            }
        });
		
        if (noResults) {
            noResults.style.display = (!hasMatches && query.length > 0) ? 'block' : 'none';
        }
    });
};

(function () {
    const overlay = document.getElementById('miniConfirm');
    const textEl  = document.getElementById('miniConfirmText');
    const okBtn   = document.getElementById('miniConfirmOk');
    const cancel  = document.getElementById('miniConfirmCancel');
    let action = null;
    if (!overlay) return;

    function open(msg, okLabel, onOk, alertMode) {
        textEl.textContent = msg || 'Are you sure?';
        okBtn.textContent = okLabel || (alertMode ? 'OK' : 'Confirm');
        if (cancel) cancel.style.display = alertMode ? 'none' : '';
        action = onOk;
        overlay.classList.add('show');
    }
    // Confirm dialog: OK + Cancel. onOk fires only on OK.
    window.miniConfirm = function (msg, okLabel, onOk) { open(msg, okLabel, onOk, false); };
    // Alert dialog: single OK button (message only). onOk optional (fires on OK).
    window.miniAlert = function (msg, okLabel, onOk) { open(msg, okLabel || 'OK', onOk || null, true); };
    function close() { overlay.classList.remove('show'); action = null; }

    okBtn.addEventListener('click', () => { if (action) action(); close(); });
    cancel.addEventListener('click', close);
    overlay.addEventListener('click', e => { if (e.target === overlay) close(); });

    // Forms with data-confirm
    document.addEventListener('submit', function (e) {
        const form = e.target.closest('form[data-confirm]');
        if (!form || form.dataset.confirmed === 'true') return;
        e.preventDefault();
        open(form.dataset.confirm, form.dataset.confirmBtn, () => {
            form.dataset.confirmed = 'true';
            form.submit();
        });
    }, true);

    // Links/buttons with .js-confirm
    document.addEventListener('click', function (e) {
        const el = e.target.closest('.js-confirm');
        if (!el) return;
        e.preventDefault();
        open(el.dataset.confirm, el.dataset.confirmBtn, () => window.location = el.href);
    }, true);
})();