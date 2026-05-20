<?= $this->extend('layouts/app') ?>

<?= $this->section('sidebar') ?>
<div class="sidebar-brand">
  <div class="sidebar-logo-icon"><i class="bi bi-briefcase"></i></div>
  <div class="sidebar-brand-name">TechMada RH<span>Espace employé</span></div>
</div>
<ul class="sidebar-nav sidebar-nav-spaced">
  <li><a href="<?= site_url('employe/dashboard') ?>"><i class="bi bi-grid-1x2"></i> Tableau de bord</a></li>
  <li><a href="<?= site_url('employe/conges/create') ?>"><i class="bi bi-plus-circle"></i> Nouvelle demande</a></li>
  <li><a href="<?= site_url('employe/conges') ?>" class="active"><i class="bi bi-calendar3"></i> Mes demandes</a></li>
  <li><a href="<?= site_url('employe/calendrier') ?>"><i class="bi bi-calendar2-week"></i> Calendrier</a></li>
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
  <div class="topbar-title">Mes demandes de congé</div>
  <div class="topbar-breadcrumb"><a href="<?= site_url('employe/dashboard') ?>">Accueil</a> <i class="bi bi-chevron-right breadcrumb-sep"></i> Mes demandes</div>
</div>
<div class="topbar-actions">
  <a href="<?= site_url('employe/conges/create') ?>" class="btn-forest btn-compact"><i class="bi bi-plus-lg"></i> Nouvelle demande</a>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="data-card">
<div class="data-card-head">
    <h3>Toutes mes demandes</h3>
    <div class="u-flex u-gap-6">
      <form method="get" action="<?= site_url('employe/conges') ?>">
        <select class="f-select f-compact" name="statut" onchange="this.form.submit()">
          <option value="">Tous les statuts</option>
          <option value="en_attente" <?= $statutActif === 'en_attente' ? 'selected' : '' ?>>En attente</option>
          <option value="approuvee" <?= $statutActif === 'approuvee' ? 'selected' : '' ?>>Approuvée</option>
          <option value="refusee" <?= $statutActif === 'refusee' ? 'selected' : '' ?>>Refusée</option>
          <option value="annulee" <?= $statutActif === 'annulee' ? 'selected' : '' ?>>Annulée</option>
        </select>
      </form>
    </div>
  </div>
  <table class="tbl">
    <thead>
      <tr><th>Type</th><th>Début</th><th>Fin</th><th>Durée</th><th>Statut</th><th>Commentaire RH</th><th>Action</th></tr>
    </thead>
    <tbody>
      <?php if (!$conges): ?>
        <tr>
          <td class="td-muted" colspan="7">Aucune demande trouvée.</td>
        </tr>
      <?php else: ?>
        <?php foreach ($conges as $conge): ?>
          <tr>
            <td><span class="type-badge <?= esc($conge['typeBadge']) ?>"><?= esc($conge['type']) ?></span></td>
            <td class="td-muted"><?= esc($conge['dateDebut']) ?></td>
            <td class="td-muted"><?= esc($conge['dateFin']) ?></td>
            <td class="td-mono"><?= esc((string) $conge['nbJours']) ?> j</td>
            <td><span class="statut <?= esc($conge['statutBadge']) ?>"><?= esc($conge['statutLabel']) ?></span></td>
            <td class="td-muted u-fs-78"><?= esc($conge['commentaire']) ?></td>
            <td>
              <?php if ($conge['statut'] === 'en_attente'): ?>
                <form action="<?= site_url('employe/conges/annuler/' . $conge['id']) ?>" method="post" class="u-inline">
                  <?= csrf_field() ?>
                  <button class="btn-sm btn-cancel"><i class="bi bi-x"></i> Annuler</button>
                </form>
              <?php else: ?>
                <span class="td-muted u-fs-75">—</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<div class="data-card">
  <div class="data-card-head">
    <h3>Répartition des demandes par type</h3>
  </div>
  <div style="padding: 0 1.25rem 1.5rem;">
    <div id="congesTypeChartEmpty" class="td-muted" style="display:none;">Aucune donnée disponible pour le moment.</div>
    <div style="display:flex;gap:24px;align-items:center;flex-wrap:wrap;">
      <div style="flex:0 0 320px;max-width:320px;height:260px;">
        <canvas id="congesTypeChart" style="width:100%;height:100%;"></canvas>
      </div>
      <div style="flex:1;min-width:220px;">
        <div id="congesTypeLegend" class="u-flex" style="flex-direction:column;gap:10px;"></div>
        <div id="congesTypeTotal" class="td-muted" style="margin-top:12px;"></div>
      </div>
    </div>
  </div>
</div>

<script src="<?= base_url('assets/lib/chartjs/dist/chart.umd.min.js') ?>"></script>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('congesTypeChart');
    if (!ctx || !window.Chart) {
      return;
    }

    const emptyEl = document.getElementById('congesTypeChartEmpty');
    const legendEl = document.getElementById('congesTypeLegend');
    const totalEl = document.getElementById('congesTypeTotal');

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
      return 't-sans-solde';
    };

    fetch('<?= site_url('employe/conges/stats') ?>', { headers: { 'Accept': 'application/json' } })
      .then((response) => response.ok ? response.json() : Promise.reject())
      .then((data) => {
        const labels = Array.isArray(data.labels) ? data.labels : [];
        const values = Array.isArray(data.values) ? data.values : [];

        if (!labels.length || !values.length) {
          if (emptyEl) {
            emptyEl.style.display = 'block';
          }
          return;
        }

        const colors = labels.map((label) => getBadgeColor(resolveBadgeClass(label)));
        const total = values.reduce((acc, value) => acc + value, 0);

        if (legendEl) {
          legendEl.innerHTML = '';
          labels.forEach((label, index) => {
            const item = document.createElement('div');
            item.style.display = 'flex';
            item.style.alignItems = 'center';
            item.style.gap = '8px';

            const dot = document.createElement('span');
            dot.style.width = '10px';
            dot.style.height = '10px';
            dot.style.borderRadius = '50%';
            dot.style.backgroundColor = colors[index];
            dot.style.flexShrink = '0';

            const text = document.createElement('span');
            text.textContent = `${label} — ${values[index]}`;

            item.appendChild(dot);
            item.appendChild(text);
            legendEl.appendChild(item);
          });
        }

        if (totalEl) {
          totalEl.innerHTML = `<strong style="font-size:1.05rem;">Total demandes: ${total}</strong>`;
        }

        new Chart(ctx, {
          type: 'pie',
          data: {
            labels: labels,
            datasets: [
              {
                data: values,
                backgroundColor: colors,
                borderWidth: 2,
                borderColor: getComputedStyle(document.documentElement).getPropertyValue('--white').trim()
              }
            ]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                display: false
              }
            }
          }
        });
      })
      .catch(() => {
        if (emptyEl) {
          emptyEl.style.display = 'block';
        }
      });
  });
</script>
<?= $this->endSection() ?>
