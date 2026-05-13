<?php

namespace App\Controllers\Employe;

use App\Controllers\BaseController;
use App\Models\CongeModel;
use App\Models\EmployeModel;
use App\Models\SoldeModel;
use App\Models\TypeCongeModel;
use DateTimeImmutable;

class CongeController extends BaseController
{
    public function index()
    {
        if (!session()->has('user_id')) {
            return redirect()->to('/');
        }

        $employeId = (int) session('user_id');
        $statut = (string) $this->request->getGet('statut');
        $statut = $statut !== '' ? $statut : null;

        $congeModel = new CongeModel();
        $conges = $congeModel->getByEmploye($employeId, $statut);

        $employe = $this->getEmployeHeader($employeId);
        $congesAff = $this->mapConges($conges);

        return view('employe/conge_list', [
            'employe' => $employe,
            'conges' => $congesAff,
            'statutActif' => $statut,
        ]);
    }

    public function create()
    {
        if (!session()->has('user_id')) {
            return redirect()->to('/');
        }

        $employeId = (int) session('user_id');
        $annee = (int) date('Y');

        $typeModel = new TypeCongeModel();
        $soldeModel = new SoldeModel();
        $congeModel = new CongeModel();

        $types = $typeModel->orderBy('libelle', 'ASC')->findAll();
        $soldes = $soldeModel->getDetailByEmploye($employeId, $annee);
        $soldesMap = [];
        foreach ($soldes as $solde) {
            $soldesMap[(int) $solde['type_conge_id']] = $solde;
        }

        $typesAff = [];
        foreach ($types as $type) {
            $typeId = (int) $type['id'];
            $restant = isset($soldesMap[$typeId]) ? (int) $soldesMap[$typeId]['jours_restant'] : 0;
            $typesAff[] = [
                'id' => $typeId,
                'libelle' => $type['libelle'],
                'restant' => $restant,
                'deductible' => (int) $type['deductible'] === 1,
            ];
        }

        $computed = $this->computeFormPreview($congeModel);

        return view('employe/conge_form', [
            'employe' => $this->getEmployeHeader($employeId),
            'types' => $typesAff,
            'soldes' => $this->mapSoldes($soldes),
            'errors' => session('errors') ?? [],
            'computed' => $computed,
        ]);
    }

    public function store()
    {
        if (!session()->has('user_id')) {
            return redirect()->to('/');
        }

        $rules = [
            'type_conge_id' => 'required|is_natural_no_zero',
            'date_debut' => 'required|valid_date[Y-m-d]',
            'date_fin' => 'required|valid_date[Y-m-d]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $employeId = (int) session('user_id');
        $typeId = (int) $this->request->getPost('type_conge_id');
        $dateDebut = (string) $this->request->getPost('date_debut');
        $dateFin = (string) $this->request->getPost('date_fin');
        $motif = (string) $this->request->getPost('motif');

        $congeModel = new CongeModel();
        $typeModel = new TypeCongeModel();
        $soldeModel = new SoldeModel();

        $type = $typeModel->find($typeId);
        if (!$type) {
            return redirect()->back()->withInput()->with('error', 'Type de congé invalide.');
        }

        if ($dateFin < $dateDebut) {
            return redirect()->back()->withInput()->with('error', 'La date de fin doit être après la date de début.');
        }

        if ($congeModel->hasOverlap($employeId, $dateDebut, $dateFin)) {
            return redirect()->back()->withInput()->with('error', 'Chevauchement détecté avec une demande existante.');
        }

        $nbJours = $congeModel->calculerJoursOuvrables($dateDebut, $dateFin);
        if ($nbJours <= 0) {
            return redirect()->back()->withInput()->with('error', 'Nombre de jours invalide.');
        }

        $annee = (int) (new DateTimeImmutable($dateDebut))->format('Y');
        if ((int) $type['deductible'] === 1) {
            $restant = $soldeModel->getRestant($employeId, $typeId, $annee);
            if ($restant < $nbJours) {
                return redirect()->back()->withInput()->with('error', 'Solde insuffisant pour ce type de congé.');
            }
        }

        $congeModel->insert([
            'employe_id' => $employeId,
            'type_conge_id' => $typeId,
            'date_debut' => $dateDebut,
            'date_fin' => $dateFin,
            'nb_jours' => $nbJours,
            'motif' => $motif,
            'statut' => 'en_attente',
        ]);

        return redirect()->to('/employe/dashboard')->with('success', 'Votre demande de congé a bien été soumise. Elle est en attente de validation.');
    }

    public function cancel(int $id)
    {
        if (!session()->has('user_id')) {
            return redirect()->to('/');
        }

        if ($this->request->getMethod() !== 'post') {
            return redirect()->to('/employe/conges');
        }

        $employeId = (int) session('user_id');
        $congeModel = new CongeModel();

        if (!$congeModel->annuler($id, $employeId)) {
            return redirect()->to('/employe/conges')->with('error', 'Impossible d\'annuler cette demande.');
        }

        return redirect()->to('/employe/conges')->with('success', 'La demande a été annulée.');
    }

    private function getEmployeHeader(int $employeId): array
    {
        $employeModel = new EmployeModel();
        $detail = $employeModel->getDetail($employeId);

        $prenom = $detail['prenom'] ?? (string) session('prenom');
        $nom = $detail['nom'] ?? (string) session('nom');
        $dept = $detail['departement_nom'] ?? '';

        $initials = strtoupper(substr($prenom, 0, 1) . substr($nom, 0, 1));
        $initials = $initials !== '' ? $initials : '??';

        return [
            'prenom' => $prenom,
            'nom' => $nom,
            'dept' => $dept,
            'initials' => $initials,
        ];
    }

    private function mapSoldes(array $soldes): array
    {
        $mapped = [];
        foreach ($soldes as $solde) {
            $attribues = (int) $solde['jours_attribues'];
            $restant = (int) $solde['jours_restant'];
            $pris = (int) $solde['jours_pris'];
            $percent = $attribues > 0 ? (int) round(($restant / $attribues) * 100) : 0;
            $class = $percent <= 25 ? 'danger' : ($percent <= 50 ? 'warn' : '');

            $mapped[] = [
                'type' => $solde['type_conge_libelle'],
                'attribues' => $attribues,
                'restant' => $restant,
                'pris' => $pris,
                'percent' => $percent,
                'class' => $class,
            ];
        }

        return $mapped;
    }

    private function mapConges(array $conges): array
    {
        $mapped = [];
        foreach ($conges as $conge) {
            $commentaire = $conge['commentaire_rh'] ?? '';
            $mapped[] = [
                'id' => (int) $conge['id'],
                'type' => $conge['type_conge_libelle'],
                'typeBadge' => $this->mapTypeBadge($conge['type_conge_libelle']),
                'dateDebut' => $this->formatDateFr($conge['date_debut']),
                'dateFin' => $this->formatDateFr($conge['date_fin']),
                'nbJours' => (int) $conge['nb_jours'],
                'statut' => $conge['statut'],
                'statutBadge' => $this->mapStatutBadge($conge['statut']),
                'statutLabel' => $this->mapStatutLabel($conge['statut']),
                'commentaire' => $commentaire !== '' ? $commentaire : '—',
            ];
        }

        return $mapped;
    }

    private function mapTypeBadge(string $libelle): string
    {
        $key = mb_strtolower($libelle);
        if (str_contains($key, 'annuel')) {
            return 't-annuel';
        }
        if (str_contains($key, 'maladie')) {
            return 't-maladie';
        }
        if (str_contains($key, 'spécial') || str_contains($key, 'special')) {
            return 't-special';
        }

        return 't-sans-solde';
    }

    private function mapStatutBadge(string $statut): string
    {
        return match ($statut) {
            'en_attente' => 's-attente',
            'approuvee' => 's-approuvee',
            'refusee' => 's-refusee',
            'annulee' => 's-annulee',
            default => 's-attente',
        };
    }

    private function mapStatutLabel(string $statut): string
    {
        return match ($statut) {
            'en_attente' => 'en attente',
            'approuvee' => 'approuvée',
            'refusee' => 'refusée',
            'annulee' => 'annulée',
            default => $statut,
        };
    }

    private function formatDateFr(string $date): string
    {
        $months = [
            1 => 'janv.',
            2 => 'févr.',
            3 => 'mars',
            4 => 'avr.',
            5 => 'mai',
            6 => 'juin',
            7 => 'juil.',
            8 => 'août',
            9 => 'sept.',
            10 => 'oct.',
            11 => 'nov.',
            12 => 'déc.',
        ];

        $dt = new DateTimeImmutable($date);
        $month = (int) $dt->format('n');

        return $dt->format('j ') . ($months[$month] ?? $dt->format('m')) . $dt->format(' Y');
    }

    private function computeFormPreview(CongeModel $congeModel): ?array
    {
        $dateDebut = (string) (old('date_debut') ?? '');
        $dateFin = (string) (old('date_fin') ?? '');

        if ($dateDebut === '' || $dateFin === '') {
            return null;
        }

        $nbJours = $congeModel->calculerJoursOuvrables($dateDebut, $dateFin);
        if ($nbJours <= 0) {
            return null;
        }

        return [
            'jours' => $nbJours,
            'label' => $this->formatDateRangeLabel($dateDebut, $dateFin),
        ];
    }

    private function formatDateRangeLabel(string $debut, string $fin): string
    {
        $start = $this->formatDateFr($debut);
        $end = $this->formatDateFr($fin);

        return 'du ' . $start . ' au ' . $end;
    }
}
