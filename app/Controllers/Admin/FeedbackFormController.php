<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

/**
 * Super-admin feedback programme:
 * the super admin writes a small form (title + message), sends it to
 * restaurant owners as in-app notifications, collects their answers
 * (rating + comment), and decides which ones appear on the public
 * landing page under "What restaurant owners say".
 */
class FeedbackFormController extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();

        $forms = $db->table('feedback_forms f')
            ->select('f.*, u.name as created_by_name,
                     (SELECT COUNT(*) FROM feedback_responses fr WHERE fr.form_id = f.id)                    AS responses,
                     (SELECT COUNT(*) FROM feedback_responses fr WHERE fr.form_id = f.id AND fr.is_published = 1) AS published')
            ->join('users u', 'u.id = f.created_by', 'left')
            ->orderBy('f.id', 'DESC')
            ->get()->getResultArray();

        // Latest answers across forms, newest tenant feedback first
        $responses = $db->table('feedback_responses fr')
            ->select('fr.*, f.title as form_title')
            ->join('feedback_forms f', 'f.id = fr.form_id', 'left')
            ->orderBy('fr.responded_at', 'DESC')
            ->limit(60)
            ->get()->getResultArray();

        $tenantCount = (int)$db->table('restaurants')->where('is_active', 1)->countAllResults();

        return view('super/feedback/index', [
            'pageTitle'   => 'Owner Feedback',
            'forms'       => $forms,
            'responses'   => $responses,
            'tenantCount' => $tenantCount,
        ]);
    }

    public function store()
    {
        $title   = trim((string)$this->request->getPost('title'));
        $message = trim((string)$this->request->getPost('message'));

        if (mb_strlen($title) < 5 || mb_strlen($title) > 150) {
            return redirect()->back()->withInput()->with('error', 'A clear title (5–150 characters) is required.');
        }

        $db = \Config\Database::connect();
        $db->table('feedback_forms')->insert([
            'title'      => $title,
            'message'    => $message ?: null,
            'is_active'  => 1,
            'created_by' => (int)session('user_id'),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to(base_url('super/feedback'))->with('success', 'Feedback form created. Tap "Send to owners" to notify the restaurants.');
    }

    /** Send (or resend) one form to all active tenants — in-app notification per owner */
    public function send(int $formId)
    {
        $db      = \Config\Database::connect();
        $form    = $db->table('feedback_forms')->where('id', $formId)->get()->getRowArray();
        if (!$form) {
            return redirect()->back()->with('error', 'Feedback form not found');
        }

        $owners = $db->query(
            "SELECT u.id, u.restaurant_id
             FROM users u
             JOIN restaurants r ON r.id = u.restaurant_id
             JOIN roles ro      ON ro.id = u.role_id
             WHERE ro.slug = 'restaurant_admin' AND r.is_active = 1"
        )->getResultArray();

        $count = 0;
        foreach ($owners as $o) {
            $db->table('notifications')->insert([
                'restaurant_id' => (int)$o['restaurant_id'],
                'user_id'       => (int)$o['id'],
                'type'          => 'feedback_request',
                'title'         => 'Owner feedback requested',
                'message'       => $form['message']
                    ?: $form['title'],
                'data_json'     => json_encode(['form_id' => (int)$formId, 'url' => 'admin/owner-feedback']),
                'is_read'       => 0,
                'created_at'    => date('Y-m-d H:i:s'),
            ]);
            $count++;
        }

        return redirect()->to(base_url('super/feedback'))
            ->with('success', "Feedback form sent to $count restaurant owner(s).");
    }

    public function toggle(int $formId)
    {
        $db    = \Config\Database::connect();
        $form  = $db->table('feedback_forms')->where('id', $formId)->get()->getRowArray();
        if (!$form) {
            return redirect()->back()->with('error', 'Form not found');
        }
        $db->table('feedback_forms')->where('id', $formId)->update(['is_active' => (int)!$form['is_active']]);

        return redirect()->to(base_url('super/feedback'))->with('success', $form['is_active'] ? 'Form closed — owners can no longer respond.' : 'Form re-opened.');
    }

    /** Show / hide an individual answer on the public landing page */
    public function publish(int $responseId)
    {
        $db = \Config\Database::connect();
        $r  = $db->table('feedback_responses')->where('id', $responseId)->get()->getRowArray();
        if (!$r) {
            return redirect()->back()->with('error', 'Response not found');
        }
        if (!in_array((int)$r['can_publish'], [0, 1], true)) {
            return redirect()->back()->with('error', 'Invalid response record');
        }
        // Publishing ALWAYS requires the owner's explicit consent
        if (!(int)$r['can_publish']) {
            return redirect()->back()->with('error', 'The owner did not give permission to publish this response.');
        }
        $now = date('Y-m-d H:i:s');
        $db->table('feedback_responses')->where('id', $responseId)->update([
            'is_published' => (int)!$r['is_published'],
            'published_at' => $r['is_published'] ? null : $now,
        ]);

        return redirect()->to(base_url('super/feedback'))->with('success', $r['is_published']
            ? 'Removed from the homepage testimonials.'
            : 'Published to "What restaurant owners say" on the homepage (consent required).');
    }

    public function deleteResponse(int $responseId)
    {
        $db = \Config\Database::connect();
        $db->table('feedback_responses')->where('id', $responseId)->delete();

        return redirect()->to(base_url('super/feedback'))->with('success', 'Response deleted.');
    }
}
