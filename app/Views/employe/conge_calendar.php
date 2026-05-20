<?= $this->extend('layouts/app') ?>

<?= $this->section('sidebar') ?>
<div class="sidebar-brand">
  <div class="sidebar-logo-icon"><i class="bi bi-briefcase"></i></div>
  <div class="sidebar-brand-name">TechMada RH<span>Espace employé</span></div>
</div>
<ul class="sidebar-nav sidebar-nav-spaced">
  <li><a href="<?= site_url('employe/dashboard') ?>"><i class="bi bi-grid-1x2"></i> Tableau de bord</a></li>
  <li><a href="<?= site_url('employe/conges/create') ?>"><i class="bi bi-plus-circle"></i> Nouvelle demande</a></li>
  <li><a href="<?= site_url('employe/conges') ?>"><i class="bi bi-calendar3"></i> Mes demandes</a></li>
  <li><a href="<?= site_url('employe/calendrier') ?>" class="active"><i class="bi bi-calendar2-week"></i> Calendrier</a></li>
  <li><a href="<?= site_url('employe/profil') ?>"><i class="bi bi-person"></i> Mon profil</a></li>
</ul>
<div class="sidebar-user">
  <div class="s-user-row">
    <div class="avatar av-green"><?= esc($employe['initials']) ?></div>
    <div><div class="user-name"><?= esc(trim($employe['prenom'] . ' ' . $employe['nom'])) ?></div><div class="user-role">Employé<?= $employe['dept'] ? ' · ' . esc($employe['dept']) : '' ?></div></div>
    <a href="<?= site_url('logout') ?>" class="sidebar-logout" title="Déconnexion"><i class="bi bi-box-arrow-right"></i></a>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('topbar') ?>
<div>
  <div class="topbar-title">Calendrier des congés</div>
  <div class="topbar-breadcrumb"><a href="<?= site_url('employe/dashboard') ?>">Accueil</a> <i class="bi bi-chevron-right breadcrumb-sep"></i> Calendrier</div>
</div>
<div class="topbar-actions">
  <a href="<?= site_url('employe/conges/create') ?>" class="btn-forest btn-compact"><i class="bi bi-plus-lg"></i> Nouvelle demande</a>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="data-card">
  <div class="data-card-head">
    <h3>Mes congés</h3>
  </div>
  <div style="padding: 0 1.25rem 1.5rem;">
    <style>
      .fc .fc-daygrid-event { padding: 2px 6px; min-height: 18px; }
      .fc .fc-daygrid-event .fc-event-main { padding: 2px 0; }
      .fc .fc-timegrid-event { min-height: 26px; }
      .fc .fc-event { border-width: 2px; }
    </style>
    <div style="display:flex;gap:24px;align-items:flex-start;flex-wrap:wrap;">
      <div style="flex:1;min-width:520px;">
        <div id="calendar"></div>
      </div>
      <div style="width:280px;flex:0 0 280px;">
        <div class="data-card" style="margin:0;">
          <div class="data-card-head">
            <h3>Comprendre vos périodes</h3>
          </div>
          <div style="padding:12px 16px;display:flex;flex-direction:column;gap:12px;">
            <div class="td-muted" style="font-size:.78rem;">
              Cliquez sur un congé pour voir sa période exacte et son statut.
            </div>

            <div>
              <div style="font-weight:600;margin-bottom:8px;">Légende</div>
              <div id="congeLegend" style="display:flex;flex-direction:column;gap:8px;"></div>
            </div>

            <div>
              <div style="font-weight:600;margin-bottom:6px;">Détails</div>
              <div id="congeDetailTitle" style="font-weight:600;">Sélectionnez un congé</div>
              <div id="congeDetailDates" class="td-muted" style="font-size:.82rem;"></div>
              <div id="congeDetailStatus" class="td-muted" style="font-size:.82rem;"></div>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="<?= base_url('assets/lib/fullcalendar/dist/index.global.min.js') ?>"></script>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('calendar');
    if (!calendarEl || !window.FullCalendar) {
      return;
    }

    const legendEl = document.getElementById('congeLegend');
    const detailTitleEl = document.getElementById('congeDetailTitle');
    const detailDatesEl = document.getElementById('congeDetailDates');
    const detailStatusEl = document.getElementById('congeDetailStatus');

    const formatDate = (date) => date.toLocaleDateString('fr-FR', {
      day: '2-digit',
      month: 'short',
      year: 'numeric'
    });

    const getBadgeColor = (badgeClass) => {
      const rootStyles = getComputedStyle(document.documentElement);
      const fallback = rootStyles.getPropertyValue('--forest2').trim();
      const sample = document.createElement('span');
      sample.className = `type-badge ${badgeClass}`;
      sample.style.position = 'absolute';
      sample.style.left = '-9999px';
      sample.style.top = '-9999px';
      document.body.appendChild(sample);
      const styles = getComputedStyle(sample);
      const color = styles.color || styles.backgroundColor;
      document.body.removeChild(sample);
      return color || fallback;
    };

    const resolveBadgeClass = (label) => {
      const key = String(label || '').toLowerCase();
      if (key.includes('annuel')) {
        return 't-annuel';
      }
      if (key.includes('maladie')) {
        return 't-maladie';
      }
      if (key.includes('spécial') || key.includes('special')) {
        return 't-special';
      }
      return 't-annuel';
    };

    const resolveStatutBadge = (statut) => {
      switch (statut) {
        case 'en_attente':
          return 's-attente';
        case 'approuvee':
          return 's-approuvee';
        case 'refusee':
          return 's-refusee';
        case 'annulee':
          return 's-annulee';
        default:
          return 's-attente';
      }
    };

    const buildLegend = () => {
      if (!legendEl) {
        return;
      }
      const items = [
        { label: 'Congé annuel', badge: 't-annuel' },
        { label: 'Congé maladie', badge: 't-maladie' },
        { label: 'Congé spécial', badge: 't-special' }
      ];
      legendEl.innerHTML = '';
      items.forEach((item) => {
        const row = document.createElement('div');
        row.style.display = 'flex';
        row.style.alignItems = 'center';
        row.style.gap = '8px';

        const dot = document.createElement('span');
        dot.style.width = '10px';
        dot.style.height = '10px';
        dot.style.borderRadius = '50%';
        dot.style.backgroundColor = getBadgeColor(item.badge);
        dot.style.flexShrink = '0';

        const text = document.createElement('span');
        text.textContent = item.label;

        row.appendChild(dot);
        row.appendChild(text);
        legendEl.appendChild(row);
      });
    };

    buildLegend();

    let selectedEventEl = null;
    const highlightSelected = (el) => {
      if (selectedEventEl) {
        selectedEventEl.style.boxShadow = '';
        selectedEventEl.style.outline = '';
      }
      if (el) {
        el.style.boxShadow = '0 0 0 2px rgba(0,0,0,0.2)';
        el.style.outline = '2px solid rgba(0,0,0,0.15)';
      }
      selectedEventEl = el;
    };

    const calendar = new FullCalendar.Calendar(calendarEl, {
      initialView: 'dayGridMonth',
      locale: 'fr',
      headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,timeGridDay'
      },
      height: 'auto',
      events: '<?= site_url('employe/calendrier/evenements') ?>',
      eventDisplay: 'block',
      eventContent: function () {
        return { html: '' };
      },
      eventDidMount: function (info) {
        const badgeClass = resolveBadgeClass(info.event.title);
        const color = getBadgeColor(badgeClass);
        const rootStyles = getComputedStyle(document.documentElement);
        info.el.style.backgroundColor = color;
        info.el.style.borderColor = color;
        info.el.style.color = rootStyles.getPropertyValue('--white').trim() || rootStyles.getPropertyValue('--cream').trim();

        const end = info.event.end ? new Date(info.event.end.getTime() - 24 * 60 * 60 * 1000) : info.event.start;
        const dateLabel = `${formatDate(info.event.start)} → ${formatDate(end)}`;
        const statut = info.event.extendedProps && info.event.extendedProps.statutLabel ? info.event.extendedProps.statutLabel : '';
        info.el.title = `${info.event.title} · ${dateLabel}${statut ? ' · ' + statut : ''}`;
      },
      eventClick: function (info) {
        const end = info.event.end ? new Date(info.event.end.getTime() - 24 * 60 * 60 * 1000) : info.event.start;
        const dateLabel = `${formatDate(info.event.start)} → ${formatDate(end)}`;
        const statut = info.event.extendedProps && info.event.extendedProps.statutLabel ? info.event.extendedProps.statutLabel : '';
        const statutRaw = info.event.extendedProps && info.event.extendedProps.statut ? info.event.extendedProps.statut : '';

        if (detailTitleEl) {
          detailTitleEl.textContent = info.event.title;
        }
        if (detailDatesEl) {
          detailDatesEl.textContent = dateLabel;
        }
        if (detailStatusEl) {
          if (statut) {
            const badge = resolveStatutBadge(statutRaw);
            detailStatusEl.innerHTML = `Statut: <span class="statut ${badge}">${statut}</span>`;
          } else {
            detailStatusEl.textContent = '';
          }
        }

        highlightSelected(info.el);
      }
    });

    calendar.render();

  });
</script>
<?= $this->endSection() ?>
