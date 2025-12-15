<?php
/**
 * Shluchim Zoom Farbrengens - Public Events Page
 */
$pageSettings = [];
try {
    require_once 'config.php';
    $pdo = getDbConnection();
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $pageSettings[$row['setting_key']] = $row['setting_value'];
    }
} catch (Exception $e) {
    error_log("Failed to load settings: " . $e->getMessage());
}

function metaContent($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

$socialImageUrl = '';
if (!empty($pageSettings['social_image'])) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $socialImageUrl = $protocol . '://' . $_SERVER['HTTP_HOST'] . '/' . ltrim($pageSettings['social_image'], '/');
}
$currentUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= metaContent($pageSettings['site_title'] ?? 'Shluchim Zoom Farbrengens') ?></title>
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= metaContent($currentUrl) ?>">
    <meta property="og:title" content="<?= metaContent($pageSettings['site_title'] ?? 'Shluchim Zoom Farbrengens') ?>">
    <meta property="og:description" content="<?= metaContent($pageSettings['header_description'] ?? 'Join live Farbrengens with Shluchim from around the world') ?>">
    <?php if ($socialImageUrl): ?><meta property="og:image" content="<?= metaContent($socialImageUrl) ?>"><?php endif; ?>
    <meta name="twitter:card" content="summary_large_image">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <?php if (!empty($pageSettings['analytics_code'])): ?><?= $pageSettings['analytics_code'] ?><?php endif; ?>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Nunito', sans-serif; background: linear-gradient(135deg, #f5f7fa 0%, #e8ecf1 100%); min-height: 100vh; padding: 2rem; }
        .events-container { max-width: 1000px; margin: 0 auto; }
        .header-banner { width: 100%; max-height: 300px; object-fit: cover; display: none; margin-bottom: 2rem; border-radius: 16px; }
        .header-banner.visible { display: block; }
        .events-header { text-align: center; margin-bottom: 2rem; padding: 2rem 1rem; }
        .events-header h1 { font-size: clamp(2.5rem, 6vw, 4rem); font-weight: 800; background: linear-gradient(135deg, #6B2C3E 0%, #8B3A4E 30%, #E67E22 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; margin-bottom: 0.75rem; letter-spacing: -1px; line-height: 1.1; }
        .events-header h1#headerLine2 { margin-top: -0.5rem; }
        .events-header p { color: #5a6a7a; font-size: clamp(1.1rem, 2.5vw, 1.4rem); font-weight: 500; }
        .filters { display: flex; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 2rem; justify-content: center; padding: 0 1rem; }
        .filter-btn { padding: 0.5rem 1.25rem; border: 2px solid #6B2C3E; border-radius: 25px; background: white; color: #6B2C3E; font-family: 'Nunito', sans-serif; font-weight: 600; font-size: 0.95rem; cursor: pointer; transition: all 0.2s ease; }
        .filter-btn:hover { background: #f8f0f2; }
        .filter-btn.active { background: #6B2C3E; color: white; }
        .events-list { display: flex; flex-direction: column; gap: 1.5rem; }
        .event-card { background: linear-gradient(145deg, #ffffff 0%, #faf8f9 100%); border-radius: 16px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08); overflow: hidden; transition: transform 0.3s ease, box-shadow 0.3s ease; display: flex; flex-direction: column; border: 1px solid rgba(107, 44, 62, 0.12); }
        .event-card:hover { transform: translateY(-4px); box-shadow: 0 12px 32px rgba(107, 44, 62, 0.12); border-color: rgba(107, 44, 62, 0.25); }
        .event-card.hidden { display: none; }
        .event-card-content { padding: 1.75rem; flex: 1; display: flex; flex-direction: column; }
        .event-header-row { display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; margin-bottom: 1rem; }
        .event-title-section { flex: 1; }
        .event-title { font-size: 1.5rem; font-weight: 700; color: #2D2D2D; margin-bottom: 0.25rem; line-height: 1.3; }
        .event-farbrenger { font-size: 1rem; color: #6B2C3E; font-weight: 600; }
        .event-occasion { display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.5rem 1rem; background: linear-gradient(135deg, #E67E22 0%, #D35400 100%); border-radius: 20px; font-size: 0.9rem; color: white; font-weight: 700; white-space: nowrap; }
        .event-occasion svg { width: 16px; height: 16px; fill: white; }
        .event-description { display: flex; align-items: flex-start; gap: 0.75rem; margin-bottom: 1rem; padding: 1rem; background: linear-gradient(135deg, #faf8f9 0%, #f5f0f2 100%); border-radius: 10px; border-left: 3px solid #6B2C3E; }
        .event-description svg { flex-shrink: 0; width: 18px; height: 18px; margin-top: 2px; color: #6B2C3E; }
        .event-description p { color: #3d4a5c; font-size: 0.95rem; line-height: 1.6; }
        .event-details { display: flex; flex-wrap: wrap; gap: 1.5rem; margin-bottom: 1rem; }
        .event-detail { display: flex; align-items: center; gap: 0.5rem; }
        .event-detail svg { flex-shrink: 0; width: 18px; height: 18px; color: #6B2C3E; }
        .event-detail span { color: #2d3a4a; font-size: 0.95rem; font-weight: 500; }
        .event-cta { display: flex; align-items: center; justify-content: center; gap: 0.75rem; width: 100%; padding: 1rem 1.5rem; background: linear-gradient(135deg, #2D8CFF 0%, #0B5CFF 100%); color: white; text-align: center; text-decoration: none; font-weight: 700; font-size: 1.1rem; border: none; cursor: pointer; transition: all 0.3s ease; position: relative; overflow: hidden; border-radius: 0 0 14px 14px; }
        .event-cta:hover { background: linear-gradient(135deg, #1a7ae9 0%, #0047cc 100%); box-shadow: 0 4px 15px rgba(45, 140, 255, 0.4); }
        .event-cta svg { width: 24px; height: 24px; }
        .no-events { text-align: center; padding: 3rem; color: #5a6a7a; background: rgba(255, 255, 255, 0.9); border-radius: 12px; font-size: 1.1rem; }
        .past-events-divider { display: flex; align-items: center; gap: 1rem; margin: 2rem 0 1rem; padding: 0 1rem; }
        .past-events-divider::before, .past-events-divider::after { content: ''; flex: 1; height: 2px; background: linear-gradient(90deg, transparent, #6B2C3E, transparent); }
        .past-events-divider span { color: #6B2C3E; font-weight: 700; font-size: 1rem; text-transform: uppercase; letter-spacing: 1px; white-space: nowrap; }
        .event-card.past-event { opacity: 0.65; }
        @media (max-width: 768px) { body { padding: 1rem; } .events-header h1 { font-size: 1.8rem; } .event-header-row { flex-direction: column; gap: 0.75rem; } .event-card-content { padding: 1.25rem; } .event-details { flex-direction: column; gap: 0.75rem; } }
        .custom-footer { width: 100%; background: #f8f9fa; border-top: 1px solid #e0e0e0; padding: 2rem 1rem; margin-top: 3rem; text-align: center; color: #5a6a7a; border-radius: 12px; }
        .custom-footer a { color: #6B2C3E; text-decoration: none; transition: color 0.2s ease; }
        .custom-footer a:hover { color: #E67E22; }
    </style>
</head>
<body>
    <div class="events-container">
        <img id="headerBanner" class="header-banner" src="" alt="Header Banner">
        <header class="events-header">
            <h1 id="headerLine1"></h1>
            <h1 id="headerLine2" style="display: none;"></h1>
            <p id="headerDescription"></p>
        </header>
        <div id="filters" class="filters"></div>
        <div id="eventsList" class="events-list"><div class="no-events">Loading Farbrengens...</div></div>
    </div>
    <script>
        const icons = {
            star: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>',
            info: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>',
            calendar: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>',
            clock: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>',
            globe: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>',
            zoom: '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M4 8v8a2 2 0 002 2h8a2 2 0 002-2v-2l4 3V7l-4 3V8a2 2 0 00-2-2H6a2 2 0 00-2 2z"/></svg>'
        };

        const tzAbbrs = {'America/New_York':'ET','America/Chicago':'CT','America/Denver':'MT','America/Los_Angeles':'PT','America/Anchorage':'AKT','Pacific/Honolulu':'HT','Asia/Jerusalem':'IST','Asia/Dubai':'GST','Europe/London':'GMT','Europe/Paris':'CET','Europe/Moscow':'MSK','Australia/Sydney':'AEST','Asia/Shanghai':'CST','Asia/Tokyo':'JST'};
        function getTzAbbr(tz) { return tzAbbrs[tz] || (tz ? tz.split('/').pop() : 'ET'); }

        function formatDate(d) { if (!d) return ''; const [y,m,day] = d.split(' ')[0].split('-').map(Number); return new Date(y, m-1, day).toLocaleDateString('en-US', {weekday:'long',month:'long',day:'numeric'}); }
        function formatTime(d) { if (!d || !d.includes(' ')) return ''; const [h,m] = d.split(' ')[1].split(':').map(Number); return `${h%12||12}:${m.toString().padStart(2,'0')} ${h>=12?'PM':'AM'}`; }
        function formatTimeWithTz(d, tz) { const time = formatTime(d); return time ? `${time} ${getTzAbbr(tz)}` : ''; }
        function getLocalTime(d, tz) {
            if (!d || !d.includes(' ')) return '';
            try {
                const [datePart, timePart] = d.split(' ');
                const [y, m, day] = datePart.split('-').map(Number);
                const [h, mi] = timePart.split(':').map(Number);
                const eventDate = new Date(Date.UTC(y, m-1, day, h, mi));
                const formatter = new Intl.DateTimeFormat('en-US', { hour: 'numeric', minute: '2-digit', timeZone: tz || 'America/New_York', hour12: true });
                const origParts = formatter.formatToParts(eventDate);
                const origH = parseInt(origParts.find(p => p.type === 'hour').value);
                const origM = parseInt(origParts.find(p => p.type === 'minute').value);
                const origP = origParts.find(p => p.type === 'dayPeriod').value;
                const offset = (h * 60 + mi) - (origH * 60 + origM + (origP === 'PM' && origH !== 12 ? 720 : 0) + (origP === 'AM' && origH === 12 ? -720 : 0));
                const localDate = new Date(eventDate.getTime() + offset * 60000);
                const localFormatter = new Intl.DateTimeFormat('en-US', { hour: 'numeric', minute: '2-digit', timeZoneName: 'short', hour12: true });
                return localFormatter.format(localDate);
            } catch (e) { return ''; }
        }
        function isPast(d, tz) { if (!d) return false; const [y,m,day] = d.split(' ')[0].split('-').map(Number); let dt = new Date(y, m-1, day, 23, 59); if (d.includes(' ')) { const [h,mi] = d.split(' ')[1].split(':').map(Number); dt.setHours(h, mi); } return dt < new Date(); }

        async function loadCustomization() {
            try {
                const r = await (await fetch('api.php?action=get_settings')).json();
                if (r.success && r.data) {
                    const s = r.data;
                    document.getElementById('headerLine1').textContent = s.header_line1 || 'Shluchim Zoom';
                    const h2 = document.getElementById('headerLine2');
                    h2.textContent = s.header_line2 || 'Farbrengens';
                    h2.style.display = 'block';
                    if (s.header_description) document.getElementById('headerDescription').textContent = s.header_description;
                    if (s.header_banner) { const b = document.getElementById('headerBanner'); b.src = s.header_banner; b.classList.add('visible'); }
                }
            } catch (e) { document.getElementById('headerLine1').textContent = 'Shluchim Zoom'; document.getElementById('headerLine2').textContent = 'Farbrengens'; document.getElementById('headerLine2').style.display = 'block'; }
        }

        async function loadEvents() {
            try {
                const r = await (await fetch('api.php?action=get_events')).json();
                if (r.success && r.data) { renderEvents(r.data); setupFilters(r.data); }
                else document.getElementById('eventsList').innerHTML = '<div class="no-events">No Farbrengens scheduled.</div>';
            } catch (e) { document.getElementById('eventsList').innerHTML = '<div class="no-events">Failed to load events.</div>'; }
        }

        function setupFilters(events) {
            const c = document.getElementById('filters');
            const occasions = [...new Set(events.map(e => e.occasion).filter(Boolean))];
            if (occasions.length <= 1) { c.style.display = 'none'; return; }
            let html = '<button class="filter-btn active" data-occasion="all">All</button>';
            occasions.forEach(o => html += `<button class="filter-btn" data-occasion="${o}">${o}</button>`);
            c.innerHTML = html;
            c.querySelectorAll('.filter-btn').forEach(btn => btn.addEventListener('click', function() {
                c.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                document.querySelectorAll('.event-card').forEach(card => card.classList.toggle('hidden', this.dataset.occasion !== 'all' && card.dataset.occasion !== this.dataset.occasion));
            }));
        }

        function renderEvents(events) {
            const c = document.getElementById('eventsList');
            if (!events.length) { c.innerHTML = '<div class="no-events">No Farbrengens scheduled.</div>'; return; }
            const upcoming = events.filter(e => !isPast(e.date, e.timezone)), past = events.filter(e => isPast(e.date, e.timezone));
            let html = '';
            upcoming.forEach(e => html += card(e, false));
            if (past.length) { if (upcoming.length) html += '<div class="past-events-divider"><span>Past Farbrengens</span></div>'; past.forEach(e => html += card(e, true)); }
            c.innerHTML = html || '<div class="no-events">No Farbrengens scheduled.</div>';
        }

        function card(e, past) {
            return `<div class="event-card ${past?'past-event':''}" data-occasion="${e.occasion||''}">
                <div class="event-card-content">
                    <div class="event-header-row">
                        <div class="event-title-section">
                            <h3 class="event-title">${e.title}</h3>
                            ${e.farbrenger?`<div class="event-farbrenger">with ${e.farbrenger}</div>`:''}
                        </div>
                        ${e.occasion?`<div class="event-occasion">${icons.star}${e.occasion}</div>`:''}
                    </div>
                    ${e.description?`<div class="event-description">${icons.info}<p>${e.description}</p></div>`:''}
                    <div class="event-details">
                        ${formatDate(e.date)?`<div class="event-detail">${icons.calendar}<span>${formatDate(e.date)}</span></div>`:''}
                        ${formatTimeWithTz(e.date, e.timezone)?`<div class="event-detail">${icons.clock}<span>${formatTimeWithTz(e.date, e.timezone)}</span></div>`:''}
                        ${getLocalTime(e.date, e.timezone)?`<div class="event-detail" style="background:#e8f4e8;padding:4px 8px;border-radius:6px;">${icons.globe}<span style="color:#2d6a2d;">Your time: ${getLocalTime(e.date, e.timezone)}</span></div>`:''}
                    </div>
                </div>
                ${e.zoomLink?`<a href="${e.zoomLink}" target="_blank" rel="noopener" class="event-cta">${icons.zoom}Join Zoom Farbrengen</a>`:''}
            </div>`;
        }

        document.addEventListener('DOMContentLoaded', () => { loadCustomization(); loadEvents(); });
    </script>
</body>
</html>
