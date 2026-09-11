<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\TrialLeadModel;

class TrialLeadController extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('trial_leads tl')
            ->select('tl.*, p.name as plan_name')
            ->join('saas_plans p', 'p.id = tl.plan_id', 'left');

        $status = trim((string) $this->request->getGet('status'));
        if ($status !== '' && in_array($status, ['new', 'contacted', 'converted', 'lost'], true)) {
            $builder->where('tl.status', $status);
        }

        $search = trim((string) $this->request->getGet('q'));
        if ($search !== '') {
            $builder->groupStart()
                ->like('tl.name', $search)
                ->orLike('tl.restaurant_name', $search)
                ->orLike('tl.phone', $search)
                ->orLike('tl.email', $search)
                ->groupEnd();
        }

        $leads = $builder->orderBy('tl.created_at', 'DESC')->get()->getResultArray();
        $counts = [];
        foreach (['new', 'contacted', 'converted', 'lost'] as $key) {
            $counts[$key] = $db->table('trial_leads')->where('status', $key)->countAllResults();
        }

        return view('admin/trial_leads/index', [
            'pageTitle' => 'Free Trial Leads',
            'leads'     => $leads,
            'counts'    => $counts,
            'status'    => $status,
            'search'    => $search,
            'userName'  => session('user_name'),
            'userRole'  => session('role_slug'),
        ]);
    }

    public function updateStatus($id)
    {
        $status = (string) $this->request->getPost('status');
        if (! in_array($status, ['new', 'contacted', 'converted', 'lost'], true)) {
            return redirect()->back()->with('error', 'Invalid lead status.');
        }

        $lead = new TrialLeadModel();
        $lead->update((int) $id, ['status' => $status]);

        return redirect()->back()->with('success', 'Lead status updated.');
    }

    public function updateNotes($id)
    {
        $notes = trim((string) $this->request->getPost('notes'));
        if (strlen($notes) > 5000) {
            return redirect()->back()->with('error', 'Notes are too long.');
        }

        $lead = new TrialLeadModel();
        $lead->update((int) $id, ['notes' => $notes ?: null]);

        return redirect()->back()->with('success', 'Lead notes updated.');
    }
}
