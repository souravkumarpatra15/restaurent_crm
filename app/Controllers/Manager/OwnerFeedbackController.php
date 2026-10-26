<?php

namespace App\Controllers\Manager;

use App\Controllers\BaseController;

/**
 * Tenant side of the super-admin feedback programme:
 * restaurant admins see active feedback forms they haven't answered,
 * rate DinoviX, write an optional comment, and optionally allow the
 * answer to appear on the public homepage ("What restaurant owners say").
 *
 * (Customer order feedback lives in Manager\FeedbackController —
 *  this controller is only for the programme the Super Admin runs.)
 */
class OwnerFeedbackController extends BaseController
{
    public function index()
    {
        $db  = \Config\Database::connect();
        $rid = (int)(session('restaurant_id') ?? 0);
        $uid = (int)(session('user_id') ?? 0);

        if ($rid <= 0) {
            return view('admin/owner_feedback/review', [
                'pageTitle' => 'Owner Feedback',
                'forms'     => [], 'responded' => [],
            ]);
        }

        $forms = $db->query(
            "SELECT f.id, f.title, f.message, f.created_at
             FROM feedback_forms f
             WHERE f.is_active = 1 AND f.id NOT IN (
                   SELECT form_id FROM feedback_responses WHERE restaurant_id = ?
             )
             ORDER BY f.created_at DESC",
            [$rid]
        )->getResultArray();

        // my answers (including on closed forms, for history)
        $responded = $db->query(
            "SELECT fr.*, f.title as form_title
             FROM feedback_responses fr
             JOIN feedback_forms f ON f.id = fr.form_id
             WHERE fr.restaurant_id = ?
             ORDER BY fr.responded_at DESC",
            [$rid]
        )->getResultArray();

        return view('admin/owner_feedback/review', [
            'pageTitle' => 'Owner Feedback',
            'forms'     => $forms,
            'responded' => $responded,
        ]);
    }

    public function submit()
    {
        $db      = \Config\Database::connect();
        $rid     = (int)(session('restaurant_id') ?? 0);
        $uid     = (int)(session('user_id') ?? 0);
        $formId  = (int)$this->request->getPost('form_id');
        $rating  = (int)$this->request->getPost('rating');
        $comment = trim((string)$this->request->getPost('comment'));
        $consent = $this->request->getPost('can_publish') ? 1 : 0;

        if ($rid <= 0) {
            return redirect()->to(base_url('admin/dashboard'))->with('error', 'No restaurant context');
        }

        $form = $db->table('feedback_forms')->where('id', $formId)->where('is_active', 1)->get()->getRowArray();
        if (!$form) {
            return redirect()->to(base_url('admin/owner-feedback'))->with('error', 'This feedback form is no longer active.');
        }

        if ($rating < 1 || $rating > 5) {
            return redirect()->back()->withInput()->with('error', 'Please pick a rating between 1 and 5 stars.');
        }

        // duplicate protection (unique form_id + restaurant_id)
        $exists = $db->table('feedback_responses')
            ->where('form_id', $formId)->where('restaurant_id', $rid)->countAllResults();
        if ($exists > 0) {
            return redirect()->to(base_url('admin/owner-feedback'))->with('error', 'You have already responded to this feedback form.');
        }

        $db->table('feedback_responses')->insert([
            'form_id'         => $formId,
            'restaurant_id'   => $rid,
            'user_id'         => $uid,
            'rating'          => $rating,
            'comment'         => mb_substr($comment, 0, 1000) ?: null,
            'can_publish'     => $consent,
            'is_published'    => 0, // only the super admin publishes
            'display_name'    => session('user_name') ?: null,
            'restaurant_name' => session('restaurant_name') ?: null,
            'responded_at'    => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to(base_url('admin/owner-feedback'))->with('success',
            $consent
                ? 'Thank you! Your feedback was sent — we may show it (with your restaurant name) on our homepage.'
                : 'Thank you! Your feedback was sent.');
    }
}
