<?php
/**
 * Shluchim Zoom Farbrengens - Admin Panel
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Shluchim Zoom Farbrengens</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #333; background: linear-gradient(135deg, #6B2C3E 0%, #8B3A4E 50%, #E67E22 100%); min-height: 100vh; padding: 20px; }
        .login-container { max-width: 400px; margin: 100px auto; background: white; padding: 40px; border-radius: 12px; box-shadow: 0 8px 32px rgba(0,0,0,0.2); }
        .admin-container { max-width: 900px; margin: 0 auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 8px 32px rgba(0,0,0,0.2); display: none; }
        .admin-container.visible { display: block; }
        h1 { color: #6B2C3E; margin-bottom: 30px; text-align: center; }
        h2 { color: #6B2C3E; margin: 30px 0 20px; padding-bottom: 10px; border-bottom: 2px solid #6B2C3E; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 600; }
        input[type="text"], input[type="email"], input[type="password"], input[type="datetime-local"], input[type="url"], input[type="file"], textarea, select { width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 1rem; }
        input:focus, textarea:focus, select:focus { outline: none; border-color: #6B2C3E; }
        textarea { resize: vertical; min-height: 100px; }
        .btn { padding: 12px 30px; border: none; border-radius: 25px; font-weight: 600; cursor: pointer; transition: all 0.3s; font-size: 1rem; }
        .btn:disabled { opacity: 0.6; cursor: not-allowed; }
        .btn-primary { background: #6B2C3E; color: white; }
        .btn-primary:hover:not(:disabled) { background: #5a2535; transform: translateY(-2px); }
        .btn-success { background: #48bb78; color: white; }
        .btn-success:hover:not(:disabled) { background: #38a169; }
        .btn-danger { background: #f56565; color: white; }
        .btn-danger:hover:not(:disabled) { background: #e53e3e; }
        .btn-secondary { background: #718096; color: white; }
        .btn-secondary:hover:not(:disabled) { background: #4a5568; }
        .header-actions { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 10px; }
        .user-info { font-size: 0.95rem; color: #666; }
        .user-info strong { color: #6B2C3E; }
        .events-list { margin-top: 30px; }
        .event-item { background: #f8f9fa; padding: 20px; margin-bottom: 15px; border-radius: 8px; border-left: 4px solid #6B2C3E; }
        .event-item-header { display: flex; justify-content: space-between; align-items: start; margin-bottom: 10px; }
        .event-item-title { font-size: 1.2rem; font-weight: 700; color: #6B2C3E; }
        .event-item-actions { display: flex; gap: 10px; }
        .event-item-details { color: #666; font-size: 0.9rem; }
        .admin-header-buttons { position: fixed; top: 20px; right: 20px; z-index: 1000; display: flex; gap: 10px; }
        .section { background: #f8f9fa; padding: 25px; border-radius: 8px; margin-bottom: 30px; }
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .alert-success { background: #c6f6d5; color: #22543d; border-left: 4px solid #48bb78; }
        .alert-error { background: #fed7d7; color: #742a2a; border-left: 4px solid #f56565; }
        .users-list { margin-top: 20px; }
        .user-item { background: white; padding: 15px; margin-bottom: 10px; border-radius: 8px; border-left: 4px solid #6B2C3E; display: flex; justify-content: space-between; align-items: center; }
        .badge { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 0.8rem; font-weight: 600; margin-left: 5px; }
        .badge-super { background: #E67E22; color: white; }
        .badge-admin { background: #6B2C3E; color: white; }
        .tabs { display: flex; gap: 0; margin: 30px 0; border-bottom: 2px solid #e0e0e0; flex-wrap: wrap; }
        .tab { padding: 12px 24px; background: transparent; border: none; border-bottom: 3px solid transparent; cursor: pointer; font-weight: 600; color: #666; font-size: 1rem; }
        .tab:hover { color: #6B2C3E; background: rgba(107, 44, 62, 0.05); }
        .tab.active { color: #6B2C3E; border-bottom-color: #6B2C3E; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        .banner-preview { margin-top: 15px; max-width: 100%; max-height: 200px; border-radius: 8px; display: none; }
        .banner-preview.visible { display: block; }
        @media (max-width: 768px) { .event-item-header, .user-item { flex-direction: column; } .event-item-actions { margin-top: 10px; } .admin-header-buttons { position: relative; top: auto; right: auto; margin-bottom: 20px; justify-content: center; } .tabs { flex-direction: column; } .tab { text-align: left; border-left: 3px solid transparent; } .tab.active { border-left-color: #6B2C3E; border-bottom-color: transparent; } }
    </style>
</head>
<body>
    <div class="login-container" id="loginContainer">
        <h1>🔗 Admin Login</h1>
        <div id="loginError" class="alert alert-error" style="display: none;"></div>
        <form id="loginForm">
            <div class="form-group"><label for="username">Username:</label><input type="text" id="username" required></div>
            <div class="form-group"><label for="password">Password:</label><input type="password" id="password" required></div>
            <button type="submit" class="btn btn-primary" style="width: 100%;">Login</button>
        </form>
        <p style="margin-top: 20px; text-align: center; color: #666; font-size: 0.9rem;">Default: <strong>admin</strong> / <strong>farbrengen2024</strong></p>
    </div>

    <div class="admin-container" id="adminContainer">
        <div class="admin-header-buttons">
            <button class="btn btn-danger" onclick="logout()">Logout</button>
            <a href="/" class="btn btn-secondary">← Back</a>
        </div>
        <h1>🔗 Shluchim Farbrengens Admin</h1>
        <div class="header-actions"><div class="user-info">Welcome, <strong id="userFullName"></strong> <span id="userRoleBadge"></span></div></div>
        <div id="alertContainer"></div>

        <div class="tabs" id="tabsNavigation" style="display: none;">
            <button class="tab active" data-tab="events">📋 Farbrengens</button>
            <button class="tab" data-tab="customizations">🎨 Customizations</button>
            <button class="tab" data-tab="users">👥 Users</button>
            <button class="tab" data-tab="settings">🔒 Settings</button>
        </div>

        <div class="tab-content active" id="events-tab">
            <div class="section">
                <h2>➕ <span id="eventFormTitle">Add New Farbrengen</span></h2>
                <form id="eventForm">
                    <input type="hidden" id="eventId">
                    <div class="form-group"><label for="eventTitle">Title: *</label><input type="text" id="eventTitle" required placeholder="e.g., Yud Tes Kislev Farbrengen"></div>
                    <div class="form-group"><label for="eventFarbrenger">Farbrenger:</label><input type="text" id="eventFarbrenger" placeholder="e.g., Rabbi Yossi Shuchat"></div>
                    <div class="form-group"><label for="eventOccasion">Occasion:</label><input type="text" id="eventOccasion" placeholder="e.g., Yud Tes Kislev, Yud Shevat, Purim"></div>
                    <div class="form-group"><label for="eventDescription">Description:</label><textarea id="eventDescription" placeholder="Describe what this Farbrengen is about..."></textarea></div>
                    <div class="form-group"><label for="eventDateTime">Date & Time:</label><input type="datetime-local" id="eventDateTime"></div>
                    <div class="form-group"><label for="eventZoomLink">Zoom Link:</label><input type="url" id="eventZoomLink" placeholder="https://zoom.us/j/123456789"></div>
                    <button type="submit" class="btn btn-primary" id="submitEventBtn">Add Farbrengen</button>
                    <button type="button" class="btn btn-secondary" onclick="cancelEdit()" style="display: none;" id="cancelEditBtn">Cancel</button>
                </form>
            </div>
            <div class="section"><h2>📋 Manage Farbrengens</h2><div id="eventsList" class="events-list"><div class="loading">Loading...</div></div></div>
        </div>

        <div class="tab-content" id="customizations-tab">
            <div class="section" id="customizationSection" style="display: none;">
                <h2>🎨 Site Customization</h2>
                <div class="form-group"><label for="siteTitle">Site Title:</label><input type="text" id="siteTitle"></div>
                <div class="form-group"><label for="headerLine1">Header Line 1:</label><input type="text" id="headerLine1"></div>
                <div class="form-group"><label for="headerLine2">Header Line 2:</label><input type="text" id="headerLine2"></div>
                <div class="form-group"><label for="headerDescription">Header Description:</label><input type="text" id="headerDescription"></div>
                <div class="form-group"><label for="headerBannerUpload">Banner Image:</label><input type="file" id="headerBannerUpload" accept="image/*"><img id="bannerPreview" class="banner-preview" alt="Banner"><button type="button" class="btn btn-danger" onclick="removeBanner()" style="margin-top:10px;display:none;" id="removeBannerBtn">Remove</button></div>
                <div class="form-group"><label for="socialImageUpload">Social Image:</label><input type="file" id="socialImageUpload" accept="image/*"><img id="socialImagePreview" class="banner-preview" alt="Social Image"><button type="button" class="btn btn-danger" onclick="removeSocialImage()" style="margin-top:10px;display:none;" id="removeSocialImageBtn">Remove</button></div>
                <div class="form-group"><label for="submissionPassword">Submission Password:</label><input type="text" id="submissionPassword"></div>
                <div class="form-group"><label for="analyticsCode">Analytics Code:</label><textarea id="analyticsCode" rows="4"></textarea></div>
                <button class="btn btn-success" onclick="saveCustomization()">Save</button>
            </div>
        </div>

        <div class="tab-content" id="users-tab">
            <div class="section" id="userManagementSection" style="display: none;">
                <h2>👥 User Management</h2>
                <div class="form-group"><button class="btn btn-primary" onclick="showCreateUserForm()">+ Add User</button></div>
                <div id="createUserForm" style="display:none;background:white;padding:20px;border-radius:8px;margin-bottom:20px;">
                    <h3 style="color:#6B2C3E;margin-bottom:15px;">Create New User</h3>
                    <form id="newUserForm">
                        <div class="form-group"><label for="newUsername">Username: *</label><input type="text" id="newUsername" required></div>
                        <div class="form-group"><label for="newEmail">Email: *</label><input type="email" id="newEmail" required></div>
                        <div class="form-group"><label for="newUserPassword">Password: *</label><input type="password" id="newUserPassword" required minlength="6"></div>
                        <div class="form-group"><label for="newFullName">Full Name:</label><input type="text" id="newFullName"></div>
                        <div class="form-group"><label for="newRole">Role:</label><select id="newRole"><option value="admin">Admin</option><option value="super_admin">Super Admin</option></select></div>
                        <button type="submit" class="btn btn-success">Create</button>
                        <button type="button" class="btn btn-secondary" onclick="hideCreateUserForm()">Cancel</button>
                    </form>
                </div>
                <div class="users-list" id="usersList"><div class="loading">Loading...</div></div>
            </div>
        </div>

        <div class="tab-content" id="settings-tab">
            <div class="section">
                <h2>🔒 Change Password</h2>
                <form id="changePasswordForm">
                    <div class="form-group"><label for="newPassword">New Password: *</label><input type="password" id="newPassword" required minlength="6"></div>
                    <div class="form-group"><label for="confirmPassword">Confirm: *</label><input type="password" id="confirmPassword" required minlength="6"></div>
                    <button type="submit" class="btn btn-primary">Update</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        let currentUser = null, editingEventId = null;

        async function checkAuth() {
            try { const r = await (await fetch('api.php?action=check_auth')).json(); if (r.success && r.data) { currentUser = r.data; showAdmin(); } } catch(e) {}
        }

        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const f = new FormData(); f.append('action','login'); f.append('username', document.getElementById('username').value); f.append('password', document.getElementById('password').value);
            try { const r = await(await fetch('api.php',{method:'POST',body:f})).json(); if(r.success) { currentUser = r.data; showAdmin(); } else { document.getElementById('loginError').textContent = r.message; document.getElementById('loginError').style.display='block'; }} catch(e) { document.getElementById('loginError').textContent = 'Error'; document.getElementById('loginError').style.display='block'; }
        });

        function showAdmin() {
            document.getElementById('loginContainer').style.display = 'none';
            document.getElementById('adminContainer').classList.add('visible');
            document.getElementById('tabsNavigation').style.display = 'flex';
            document.getElementById('userFullName').textContent = currentUser.full_name || currentUser.username;
            document.getElementById('userRoleBadge').innerHTML = currentUser.role === 'super_admin' ? '<span class="badge badge-super">Super Admin</span>' : '<span class="badge badge-admin">Admin</span>';
            if (currentUser.role === 'super_admin') { document.getElementById('customizationSection').style.display='block'; document.getElementById('userManagementSection').style.display='block'; }
            loadEvents(); loadSettings(); if(currentUser.role==='super_admin') loadUsers();
            document.querySelectorAll('.tab').forEach(t => t.addEventListener('click', function() { document.querySelectorAll('.tab').forEach(x => x.classList.remove('active')); this.classList.add('active'); document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active')); document.getElementById(this.dataset.tab+'-tab').classList.add('active'); }));
        }

        async function logout() { await fetch('api.php?action=logout'); location.reload(); }
        async function loadEvents() { try { const r = await(await fetch('api.php?action=get_admin_events')).json(); if(r.success) renderEventsList(r.data); } catch(e) {} }
        function renderEventsList(events) { const c = document.getElementById('eventsList'); if(!events.length) { c.innerHTML = '<p style="color:#666;text-align:center;">No farbrengens yet.</p>'; return; } let html = ''; events.forEach(e => { html += `<div class="event-item"><div class="event-item-header"><div class="event-item-title">${esc(e.title)}</div><div class="event-item-actions"><button class="btn btn-secondary" onclick="editEvent(${e.id})">Edit</button><button class="btn btn-danger" onclick="deleteEvent(${e.id})">Delete</button></div></div><div class="event-item-details">${e.farbrenger?'<strong>Farbrenger:</strong> '+esc(e.farbrenger)+'<br>':''}${e.occasion?'<strong>Occasion:</strong> '+esc(e.occasion)+'<br>':''}<strong>Date:</strong> ${fmtDT(e.event_date)}${e.zoom_link?'<br><a href="'+esc(e.zoom_link)+'" target="_blank">Zoom Link</a>':''}</div></div>`; }); c.innerHTML = html; }
        function fmtDT(d) { if(!d) return 'Not set'; const [dt,tm] = d.split(' '); if(!tm) return dt; const [h,m] = tm.split(':').map(Number); return new Date(...dt.split('-').map((v,i)=>i===1?v-1:v), h, m).toLocaleString('en-US',{weekday:'short',month:'short',day:'numeric',hour:'numeric',minute:'2-digit'}); }

        document.getElementById('eventForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const f = new FormData(); f.append('action', editingEventId?'update_event':'create_event'); if(editingEventId) f.append('event_id', editingEventId);
            f.append('title', document.getElementById('eventTitle').value); f.append('farbrenger', document.getElementById('eventFarbrenger').value); f.append('occasion', document.getElementById('eventOccasion').value); f.append('description', document.getElementById('eventDescription').value); f.append('zoom_link', document.getElementById('eventZoomLink').value);
            const dt = document.getElementById('eventDateTime').value; if(dt) f.append('event_date', dt.replace('T',' '));
            try { const r = await(await fetch('api.php',{method:'POST',body:f})).json(); if(r.success) { showAlert(editingEventId?'Updated!':'Added!','success'); resetEventForm(); loadEvents(); } else showAlert(r.message||'Failed','error'); } catch(e) { showAlert('Error','error'); }
        });

        async function editEvent(id) { try { const r = await(await fetch('api.php?action=get_event&id='+id)).json(); if(r.success) { const e = r.data; editingEventId = id; document.getElementById('eventId').value = id; document.getElementById('eventTitle').value = e.title||''; document.getElementById('eventFarbrenger').value = e.farbrenger||''; document.getElementById('eventOccasion').value = e.occasion||''; document.getElementById('eventDescription').value = e.description||''; document.getElementById('eventZoomLink').value = e.zoom_link||''; if(e.event_date) document.getElementById('eventDateTime').value = e.event_date.replace(' ','T'); document.getElementById('eventFormTitle').textContent = 'Edit Farbrengen'; document.getElementById('submitEventBtn').textContent = 'Update'; document.getElementById('cancelEditBtn').style.display = 'inline-block'; document.getElementById('eventForm').scrollIntoView({behavior:'smooth'}); }} catch(e) { showAlert('Failed','error'); }}
        async function deleteEvent(id) { if(!confirm('Delete this farbrengen?')) return; const f = new FormData(); f.append('action','delete_event'); f.append('event_id',id); try { const r = await(await fetch('api.php',{method:'POST',body:f})).json(); if(r.success) { showAlert('Deleted!','success'); loadEvents(); } else showAlert(r.message||'Failed','error'); } catch(e) { showAlert('Error','error'); }}
        function cancelEdit() { resetEventForm(); }
        function resetEventForm() { editingEventId = null; document.getElementById('eventForm').reset(); document.getElementById('eventId').value = ''; document.getElementById('eventFormTitle').textContent = 'Add New Farbrengen'; document.getElementById('submitEventBtn').textContent = 'Add Farbrengen'; document.getElementById('cancelEditBtn').style.display = 'none'; }

        async function loadSettings() { try { const r = await(await fetch('api.php?action=get_settings')).json(); if(r.success) { const s = r.data; document.getElementById('siteTitle').value = s.site_title||''; document.getElementById('headerLine1').value = s.header_line1||''; document.getElementById('headerLine2').value = s.header_line2||''; document.getElementById('headerDescription').value = s.header_description||''; document.getElementById('submissionPassword').value = s.submission_password||''; document.getElementById('analyticsCode').value = s.analytics_code||''; if(s.header_banner) { document.getElementById('bannerPreview').src = s.header_banner; document.getElementById('bannerPreview').classList.add('visible'); document.getElementById('removeBannerBtn').style.display='inline-block'; } if(s.social_image) { document.getElementById('socialImagePreview').src = s.social_image; document.getElementById('socialImagePreview').classList.add('visible'); document.getElementById('removeSocialImageBtn').style.display='inline-block'; }}} catch(e) {}}
        async function saveCustomization() { const f = new FormData(); f.append('action','update_settings'); f.append('site_title',document.getElementById('siteTitle').value); f.append('header_line1',document.getElementById('headerLine1').value); f.append('header_line2',document.getElementById('headerLine2').value); f.append('header_description',document.getElementById('headerDescription').value); f.append('submission_password',document.getElementById('submissionPassword').value); f.append('analytics_code',document.getElementById('analyticsCode').value); try { const r = await(await fetch('api.php',{method:'POST',body:f})).json(); if(r.success) showAlert('Saved!','success'); else showAlert('Failed','error'); } catch(e) { showAlert('Error','error'); } const bf = document.getElementById('headerBannerUpload').files[0]; if(bf) { const fd = new FormData(); fd.append('action','upload_banner'); fd.append('banner_file',bf); await fetch('api.php',{method:'POST',body:fd}); } const sf = document.getElementById('socialImageUpload').files[0]; if(sf) { const fd = new FormData(); fd.append('action','upload_social_image'); fd.append('social_image_file',sf); await fetch('api.php',{method:'POST',body:fd}); }}
        async function removeBanner() { const f = new FormData(); f.append('action','remove_banner'); await fetch('api.php',{method:'POST',body:f}); document.getElementById('bannerPreview').classList.remove('visible'); document.getElementById('removeBannerBtn').style.display='none'; showAlert('Removed','success'); }
        async function removeSocialImage() { const f = new FormData(); f.append('action','remove_social_image'); await fetch('api.php',{method:'POST',body:f}); document.getElementById('socialImagePreview').classList.remove('visible'); document.getElementById('removeSocialImageBtn').style.display='none'; showAlert('Removed','success'); }

        async function loadUsers() { try { const r = await(await fetch('api.php?action=get_users')).json(); if(r.success) { const c = document.getElementById('usersList'); if(!r.data.length) { c.innerHTML = '<p style="color:#666;">No users.</p>'; return; } let html = ''; r.data.forEach(u => { html += `<div class="user-item"><div><div style="font-weight:600;color:#6B2C3E;">${esc(u.full_name||u.username)} ${u.role==='super_admin'?'<span class="badge badge-super">Super</span>':'<span class="badge badge-admin">Admin</span>'}</div><div style="font-size:0.9rem;color:#666;">${esc(u.email)}</div></div><button class="btn btn-danger" onclick="deleteUser(${u.id})">Delete</button></div>`; }); c.innerHTML = html; }} catch(e) {}}
        function showCreateUserForm() { document.getElementById('createUserForm').style.display='block'; }
        function hideCreateUserForm() { document.getElementById('createUserForm').style.display='none'; document.getElementById('newUserForm').reset(); }
        document.getElementById('newUserForm').addEventListener('submit', async function(e) { e.preventDefault(); const f = new FormData(); f.append('action','create_user'); f.append('username',document.getElementById('newUsername').value); f.append('email',document.getElementById('newEmail').value); f.append('password',document.getElementById('newUserPassword').value); f.append('full_name',document.getElementById('newFullName').value); f.append('role',document.getElementById('newRole').value); try { const r = await(await fetch('api.php',{method:'POST',body:f})).json(); if(r.success) { showAlert('User created!','success'); hideCreateUserForm(); loadUsers(); } else showAlert(r.message||'Failed','error'); } catch(e) { showAlert('Error','error'); }});
        async function deleteUser(id) { if(!confirm('Delete this user?')) return; const f = new FormData(); f.append('action','delete_user'); f.append('user_id',id); try { const r = await(await fetch('api.php',{method:'POST',body:f})).json(); if(r.success) { showAlert('Deleted!','success'); loadUsers(); } else showAlert(r.message||'Failed','error'); } catch(e) { showAlert('Error','error'); }}

        document.getElementById('changePasswordForm').addEventListener('submit', async function(e) { e.preventDefault(); if(document.getElementById('newPassword').value !== document.getElementById('confirmPassword').value) { showAlert('Passwords do not match','error'); return; } const f = new FormData(); f.append('action','change_password'); f.append('new_password',document.getElementById('newPassword').value); try { const r = await(await fetch('api.php',{method:'POST',body:f})).json(); if(r.success) { showAlert('Password changed!','success'); document.getElementById('changePasswordForm').reset(); } else showAlert(r.message||'Failed','error'); } catch(e) { showAlert('Error','error'); }});

        function showAlert(msg, type) { const c = document.getElementById('alertContainer'); const a = document.createElement('div'); a.className = 'alert alert-'+type; a.textContent = msg; c.appendChild(a); setTimeout(() => a.remove(), 4000); }
        function esc(t) { if(!t) return ''; const d = document.createElement('div'); d.textContent = t; return d.innerHTML; }

        document.addEventListener('DOMContentLoaded', checkAuth);
    </script>
</body>
</html>
