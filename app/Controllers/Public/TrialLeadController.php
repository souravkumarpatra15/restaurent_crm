<?php

namespace App\Controllers\Public;

use App\Controllers\BaseController;
use App\Models\TrialLeadModel;

class TrialLeadController extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $plans = $db->table('saas_plans')
            ->where('is_active', 1)
            ->orderBy('sort_order', 'ASC')
            ->get()
            ->getResultArray();

        $selectedPlan = (int) $this->request->getGet('plan');

        return view('public/trial_request', [
            'pageTitle'     => 'Start Free Trial',
            'plans'         => $plans,
            'selectedPlan'  => $selectedPlan,
        ]);
    }

    public function store()
    {
        $rules = [
            'name'            => 'required|min_length[2]|max_length[120]',
            'restaurant_name' => 'required|min_length[2]|max_length[180]',
            'phone'           => 'required|min_length[8]|max_length[30]',
            'email'           => 'required|valid_email|max_length[180]',
            'city'            => 'permit_empty|max_length[100]',
            'branches'        => 'required|integer|greater_than[0]|less_than_equal_to[9999]',
            'plan_id'         => 'permit_empty|integer',
            'message'         => 'permit_empty|max_length[3000]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $db = \Config\Database::connect();
        $planId = (int) $this->request->getPost('plan_id');

        if ($planId > 0) {
            $planExists = $db->table('saas_plans')
                ->where('id', $planId)
                ->where('is_active', 1)
                ->countAllResults();
            if (! $planExists) {
                $planId = null;
            }
        } else {
            $planId = null;
        }

        $lead = new TrialLeadModel();
        $lead->insert([
            'name'            => trim((string) $this->request->getPost('name')),
            'restaurant_name' => trim((string) $this->request->getPost('restaurant_name')),
            'phone'           => trim((string) $this->request->getPost('phone')),
            'email'           => trim((string) $this->request->getPost('email')),
            'city'            => trim((string) $this->request->getPost('city')) ?: null,
            'branches'        => (int) $this->request->getPost('branches'),
            'plan_id'         => $planId,
            'message'         => trim((string) $this->request->getPost('message')) ?: null,
            'source'          => 'website',
            'status'          => 'new',
        ]);

        return redirect()->to(base_url('start-free-trial?submitted=1'))->with('success', 'Thanks! Your free-trial request has been received. Our team will contact you shortly.');
    }
}
