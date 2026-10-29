<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Events workspace — Event Ticketing (migration 4.0.9.221).
 *
 * An event owns one ticket product (db_items, product_type='event') plus any
 * number of dated sessions. The product rides the normal storefront/POS item
 * pipeline, so tickets sell in every online-store template; this controller
 * only manages the event, its sessions, and door check-in.
 */
class Events extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load_info();
        $this->load->model('events_model');
        if (!mp_feature_enabled('event_ticketing')) {
            $this->show_feature_not_activated('event_ticketing',
                'Switch on Event Ticketing from Business Profile to sell tickets to dated sessions.');
        }
    }

    private function _render($page_title, $view, $data = []) {
        $d = $this->data ?? [];
        $d['page_title'] = $page_title;
        $d = array_merge($d, $data);
        $d['content'] = $this->load->view($view, $d, TRUE);
        $this->load->view('mp_layout', $d);
    }

    private function _store_id() {
        return (int)get_current_store_id();
    }

    /* ===================== OVERVIEW ===================== */

    public function index() {
        $this->permission_check('store_view');
        $store_id = $this->_store_id();

        if ($this->input->post('save_event')) {
            $this->permission_check('items_add');
            $this->form_validation->set_rules('event_name', 'Event Name', 'trim|required|max_length[200]');
            $this->form_validation->set_rules('ticket_price', 'Ticket Price', 'trim|required|numeric|decimal-precision-2');
            if ($this->form_validation->run() === TRUE) {
                $edit_id = (int)$this->input->post('edit_id', TRUE);
                $res = $this->_save_event($store_id, $edit_id);
                if (!empty($res['error'])) {
                    $this->session->set_flashdata('error', $res['error']);
                } else {
                    $this->session->set_flashdata('success', $edit_id ? 'Event updated.' : 'Event created — add session dates to start selling.');
                    redirect('events/edit/' . (int)$res['id']);
                    return;
                }
            } else {
                $this->session->set_flashdata('error', validation_errors(' ') ?: 'Check the event details.');
            }
            redirect('events' . ($this->input->post('edit_id') ? '/edit/' . (int)$this->input->post('edit_id', TRUE) : ''));
            return;
        }

        $events = $this->events_model->get_events($store_id, true);
        $item_ids = array_values(array_filter(array_map(function ($ev) { return (int)$ev->item_id; }, $events)));
        $prices = [];
        if ($item_ids) {
            $rows = $this->db->select('id, sales_price')->where('store_id', $store_id)->where_in('id', $item_ids)->get('db_items')->result();
            foreach ($rows as $r) $prices[(int)$r->id] = (float)$r->sales_price;
        }
        $event_rows = [];
        $counts = ['events' => count($events), 'sessions' => 0, 'seats_left' => 0];
        foreach ($events as $ev) {
            $open = $this->events_model->get_sessions($ev->id, $store_id, true);
            $seats = 0;
            foreach ($open as $s) $seats += (int)$s->remaining;
            $counts['sessions'] += count($open);
            $counts['seats_left'] += $seats;
            $event_rows[] = ['event' => $ev, 'open' => $open, 'seats' => $seats, 'price' => $prices[(int)$ev->item_id] ?? 0];
        }
        $tickets_sold = 0;
        if ($this->db->table_exists('db_event_tickets')) {
            $tickets_sold = (int)$this->db->where('store_id', $store_id)
                ->where_in('status', ['sold', 'redeemed'])
                ->count_all_results('db_event_tickets');
        }

        $this->_render('Events', 'events/index', [
            'event_rows' => $event_rows,
            'counts' => $counts + ['tickets_sold' => $tickets_sold],
            'edit_event' => null,
        ]);
    }

    /* ===================== EDIT EVENT + SESSIONS ===================== */

    public function edit($id = null) {
        $this->permission_check('store_view');
        $store_id = $this->_store_id();
        $id = (int)$id;
        $event = $id ? $this->events_model->get_event($id, $store_id) : null;
        if (!$event) {
            $this->session->set_flashdata('error', $id ? 'Event not found.' : 'Create the event first, then add its dates.');
            redirect('events');
            return;
        }

        // Session add
        if ($this->input->post('add_session')) {
            $this->permission_check('items_add');
            $date = $this->input->post('session_date', TRUE);
            $capacity = (int)$this->input->post('capacity', TRUE);
            $d = DateTime::createFromFormat('Y-m-d', (string)$date);
            if (!$d || $d->format('Y-m-d') !== $date || $capacity < 1) {
                $this->session->set_flashdata('error', 'Give a valid future date and a seat count of at least 1.');
            } else {
                $this->events_model->save_session([
                    'store_id' => $store_id,
                    'event_id' => $id,
                    'session_date' => $date,
                    'start_time' => $this->input->post('start_time', TRUE) ?: null,
                    'end_time' => $this->input->post('end_time', TRUE) ?: null,
                    'capacity' => $capacity,
                    'status' => 1,
                ]);
                $this->events_model->recalc_item_stock($event->item_id, $store_id);
                $this->session->set_flashdata('success', 'Date added — now selling.');
            }
            redirect('events/edit/' . $id);
            return;
        }

        // Session status toggle / delete
        $close_id = (int)$this->input->get('close');
        if ($close_id) {
            $this->permission_check('items_add');
            $s = $this->events_model->get_session($close_id, $store_id);
            if ($s && (int)$s->event_id === $id) {
                $this->events_model->save_session([
                    'store_id' => $store_id, 'event_id' => $id,
                    'status' => (int)$s->status === 1 ? 0 : 1,
                ], $close_id);
                $this->events_model->recalc_item_stock($event->item_id, $store_id);
            }
            redirect('events/edit/' . $id);
            return;
        }
        $del_id = (int)$this->input->get('delete_session');
        if ($del_id) {
            $this->permission_check('items_add');
            $s = $this->events_model->get_session($del_id, $store_id);
            if ($s && (int)$s->event_id === $id && $this->events_model->delete_session($del_id, $store_id)) {
                $this->events_model->recalc_item_stock($event->item_id, $store_id);
                $this->session->set_flashdata('success', 'Date removed.');
            } else {
                $this->session->set_flashdata('error', 'That date still has tickets sold — keep it or close it instead.');
            }
            redirect('events/edit/' . $id);
            return;
        }

        $sessions = $event ? $this->events_model->get_sessions($id, $store_id) : [];
        foreach ($sessions as $s) {
            $s->ticket_counts = $this->events_model->session_counts((int)$s->id, $store_id);
        }
        $ticket_price = 0;
        $storefront_url = '';
        if ($event && !empty($event->item_id)) {
            $item = $this->db->select('sales_price')->where('store_id', $store_id)
                ->where('id', (int)$event->item_id)->get('db_items')->row();
            $ticket_price = (float)($item->sales_price ?? 0);
            $sf = $this->db->select('store_slug')->where('store_id', $store_id)->get('db_storefront_settings')->row();
            if (!empty($sf->store_slug)) {
                $storefront_url = base_url('store/' . rawurlencode($sf->store_slug) . '/product/' . (int)$event->item_id);
            }
        }
        $this->_render($event ? $event->event_name : 'New Event', 'events/edit', [
            'edit_event' => $event,
            'sessions' => $sessions,
            'ticket_price' => $ticket_price,
            'storefront_url' => $storefront_url,
        ]);
    }

    /* ===================== CHECK-IN ===================== */

    public function tickets() {
        $this->permission_check('store_view');
        $store_id = $this->_store_id();

        if ($this->input->post('check_code')) {
            $this->permission_check('items_add');
            $back = (int)$this->input->post('session', TRUE);
            $code = strtoupper(trim((string)$this->input->post('ticket_code', TRUE)));
            $ticket = $code !== '' ? $this->events_model->get_ticket_by_code($code, $store_id) : null;
            $when = $ticket && !empty($ticket->session)
                ? ' — ' . date('D, j M', strtotime($ticket->session->session_date))
                : '';
            if ($code === '') {
                $this->session->set_flashdata('error', 'Type or scan the ticket code first.');
            } else if (!$ticket) {
                $this->session->set_flashdata('error', 'Ticket ' . $code . ' is not from this store.');
            } else if ($ticket->status === 'redeemed') {
                $who = $ticket->holder_name ? ' — ' . mb_substr(explode(' ', trim($ticket->holder_name))[0], 0, 40) : '';
                $this->session->set_flashdata('error', 'Already checked in at ' . date('g:ia', strtotime($ticket->redeemed_at)) . $who);
            } else if ($ticket->status === 'void') {
                $this->session->set_flashdata('error', 'That ticket was cancelled.');
            } else if ($this->events_model->redeem_ticket($code, $store_id)) {
                $this->session->set_flashdata('success', 'Ticket ' . $code . ' is good for ' . ($ticket->event->event_name ?? 'the event') . $when . '. Welcome in!');
            } else {
                $this->session->set_flashdata('error', 'Could not check that ticket in — try again.');
            }
            redirect('events/tickets' . ($back ? '?session=' . $back : ''));
            return;
        }

        $session_id = (int)$this->input->get('session');
        $selected = $session_id ? $this->events_model->get_session($session_id, $store_id) : null;
        $holders = [];
        if ($selected) {
            // Door list stays short: the most recent 50 names for that date only.
            foreach (array_slice($this->events_model->tickets_for_session($session_id, $store_id, 'sold'), 0, 50) as $t) {
                $holders[] = $t;
            }
        }

        $sessions = [];
        foreach ($this->events_model->get_events($store_id, true) as $ev) {
            foreach ($this->events_model->get_sessions($ev->id, $store_id, true) as $s) {
                $c = $this->events_model->session_counts((int)$s->id, $store_id);
                $sessions[] = [
                    'event' => $ev,
                    'session' => $s,
                    'counts' => $c,
                    'waiting' => (int)$c['sold'] - (int)$c['redeemed'],
                ];
            }
        }
        usort($sessions, function ($a, $b) {
            return strcmp($a['session']->session_date . $a['session']->start_time, $b['session']->session_date . $b['session']->start_time);
        });

        $this->_render('Ticket Check-in', 'events/tickets', [
            'sessions_rows' => array_slice($sessions, 0, 30),
            'session_total' => count($sessions),
            'selected' => $selected,
            'holders' => $holders,
            'selected_counts' => $selected ? $this->events_model->session_counts($session_id, $store_id) : null,
        ]);
    }

    /* ===================== ARCHIVE ===================== */

    public function archive($id) {
        $this->permission_check('items_add');
        $store_id = $this->_store_id();
        $event = $this->events_model->get_event((int)$id, $store_id);
        if ($event) {
            $this->events_model->save_event([
                'store_id' => $store_id, 'status' => 0,
                'event_name' => $event->event_name,
            ], (int)$id);
            if (!empty($event->item_id)) {
                $this->db->where('id', (int)$event->item_id);
                $this->db->where('store_id', $store_id);
                $this->db->update('db_items', ['publish_online' => 0, 'stock' => 0]);
            }
            $this->session->set_flashdata('success', 'Event archived — tickets stop selling.');
        }
        redirect('events');
    }

    /* ===================== EVENT SAVE (ticket item in sync) ===================== */

    private function _save_event($store_id, $edit_id = 0) {
        $name = $this->input->post('event_name', TRUE);
        $venue = $this->input->post('venue', TRUE);
        $desc = $this->input->post('description', TRUE);
        $price = max(0, (float)str_replace(',', '', (string)$this->input->post('ticket_price', TRUE)));
        $starts = $this->input->post('starts_on', TRUE) ?: null;
        $ends = $this->input->post('ends_on', TRUE) ?: null;

        $event = $edit_id ? $this->events_model->get_event($edit_id, $store_id) : null;
        if ($edit_id && !$event) return ['error' => 'Event not found.'];

        $image = null;
        if (!empty($_FILES['poster']['name'])) {
            $image = $this->_upload_poster();
            if ($image === false) return ['error' => 'Poster upload failed — use a jpg/png/webp under 4 MB.'];
        }

        // Ticket product: created with the event, kept in step on every save.
        $item_data = [
            'item_name' => $name,
            'sales_price' => $price,
            'online_price' => $price,
            'discount_type' => 'Undefined',
            'discount' => 0,
            'description' => $desc,
            'product_type' => 'event',
            'service_bit' => 0,
            'not_for_sale' => 0,
            'publish_online' => 1,
            'status' => 1,
        ];
        if ($image !== null) $item_data['item_image'] = $image;

        $this->db->where('store_id', $store_id);
        $this->db->where('id', (int)($event->item_id ?? 0));
        $existing_item = $event && $event->item_id
            ? $this->db->get('db_items')->row() : null;
        if ($existing_item) {
            $this->db->where('id', (int)$existing_item->id);
            $this->db->where('store_id', $store_id);
            $this->db->update('db_items', $item_data);
            $item_id = (int)$existing_item->id;
        } else {
            $code = 'EVT-' . substr(md5($store_id . '|' . $name . '|' . microtime(true)), 0, 8);
            $this->db->insert('db_items', array_merge($item_data, [
                'store_id' => $store_id,
                'item_code' => $code,
                'stock' => 0,
                'product_type' => 'event',
            ]));
            $item_id = (int)$this->db->insert_id();
            if (!$item_id) return ['error' => 'Could not create the ticket product.'];
        }

        $saved = $this->events_model->save_event(array_merge([
            'store_id' => $store_id,
            'item_id' => $item_id,
            'event_name' => $name,
            'venue' => $venue,
            'description' => $desc,
            'status' => 1,
        ], $starts ? ['starts_on' => $starts] : [], $ends ? ['ends_on' => $ends] : []), $edit_id ?: null);
        if (!$saved) return ['error' => 'Could not save the event.'];
        $new_id = (int)$saved;

        $this->events_model->recalc_item_stock($item_id, $store_id);
        return ['id' => $new_id];
    }

    private function _upload_poster() {
        if (!is_dir('./uploads/items/')) @mkdir('./uploads/items/', 0755, true);
        $this->load->library('upload');
        $config = [
            'upload_path' => './uploads/items/',
            'allowed_types' => 'jpg|jpeg|png|webp',
            'max_size' => 4096,
            'file_name' => 'evt_' . time() . '_' . rand(100, 999),
        ];
        $this->upload->initialize($config);
        if (!$this->upload->do_upload('poster')) return false;
        $data = $this->upload->data();
        return 'uploads/items/' . $data['file_name'];
    }
}
