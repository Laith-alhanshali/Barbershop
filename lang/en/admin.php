<?php

return [

    // Navigation groups
    'groups' => [
        'bookings'  => 'Operations',
        'employees' => 'Employees',
        'basic'     => 'Basic Management',
    ],

    // General filters
    'filters' => [
        'all' => 'all',
        'from' => 'From date',
        'to'   => 'To date',
    ],

    // =========================
    // Appointments
    // =========================
    'appointments' => [
        'navigation' => 'Appointments',
        'singular'   => 'Appointment',
        'plural'     => 'Appointments',

        'sections' => [
            'details' => 'Appointment Details',
        ],

        // Form fields + table columns
        'fields' => [
            'customer'        => 'Customer',
            'barber'          => 'Barber',
            'services'        => 'Services',
            'start_at'        => 'Start Time',
            'end_at'          => 'End Time',
            'status'          => 'Status',
            'notes'           => 'Notes',
            'services_count'  => 'Services',
            'total_duration'  => 'Total Duration',
            'total_price'     => 'Total Price',
            'created_by'      => 'Created By',
        ],

        // Status values
        'status' => [
            'pending'   => 'Pending',
            'confirmed' => 'Confirmed',
            'done'      => 'Done',
            'cancelled' => 'Cancelled',
            'no_show'   => 'No Show',
        ],

        // Validation messages
        'validation' => [
            'overlap'             => 'The selected time slot overlaps with another appointment for this barber. Please choose a different time.',
            'services_required'   => 'At least one service must be selected.',
            'too_old_for_booking' => 'You cannot select a time more than one hour in the past.',
            'past_date_not_allowed' => 'You cannot select a time more than one hour in the past.',
        ],

        // Meta text under form
        'meta' => [
            'created' => 'Created by :by at :at',
            'updated' => 'Updated by :by at :at',
        ],

        // Actions
        'actions' => [
            'generate_invoice' => 'Generate Invoice',
            'generate_invoice_description' => 'This will create an invoice for this appointment with all selected services.',
            'open_invoice' => 'Open Invoice',
            'print_invoice' => 'Print Invoice',
        ],

        // Messages
        'messages' => [
            'invoice_generated' => 'Invoice generated successfully!',
            'locked_done_cancelled' => 'This appointment is locked. Only super admin can modify it.',
        ],
    ],

    // =========================
    // Barbers
    // =========================
    'barbers' => [
        'navigation' => 'Barbers',
        'singular'   => 'Barber',
        'plural'     => 'Barbers',

        'sections' => [
            'details' => 'Barber Details',
        ],

        'fields'=> [
            'user_account'=> 'User Account',
            'name'=> 'Name',
            'phone'=> 'Phone',
            'bio'=> 'Bio',
            'active'=> 'Active',
            'user_account_helper'=> 'Optional - Link barber to user account',
            'inactive'=> 'Inactive',
            'all'=> 'All',
        ],
    ],

    // =========================
    // Services
    // =========================
    'services' => [
        'navigation' => 'Services',
        'singular'   => 'Service',
        'plural'     => 'Services',

        'sections' => [
            'details' => 'Service Details',
        ],
        'fields'=> [
            'name'=> 'Name',
            'duration_min'=> 'Duration (in minutes)',
            'price'=> 'Price',
            'active'=> 'Active',
            'inactive'=> 'Inactive',
            'all'=> 'All',
        ],
    ],

        // =========================
    // Customers
    // =========================
    'customers' => [
        'navigation' => 'Customers',
        'singular'   => 'Customer',
        'plural'     => 'Customers',

        'sections' => [
            'details' => 'Customer Details',
        ],

        'fields'=> [
            'name'=> 'Name',
            'phone'=> 'Phone',
            'notes'=> 'Notes',
            'created_at'=> 'Created At',
            'updated_at'=> 'Updated At',
            'created_by'=> 'Created By',
            'updated_by'=> 'Updated By',
        ],
    ],

    // =========================
    // Invoices
    // =========================
    'invoices' => [
        'navigation' => 'Invoices',
        'singular'   => 'Invoice',
        'plural'     => 'Invoices',

        'sections' => [
            'details' => 'Invoice Details',
            'totals'  => 'Totals',
            'payment' => 'Payment Information',
            'notes'   => 'Notes',
        ],

        'fields' => [
            'number'         => 'Invoice Number',
            'appointment'    => 'Appointment',
            'customer'       => 'Customer',
            'barber'         => 'Barber',
            'status'         => 'Status',
            'subtotal'       => 'Subtotal',
            'discount'       => 'Discount',
            'tax'            => 'Tax',
            'total'          => 'Total',
            'payment_method' => 'Payment Method',
            'paid_at'        => 'Paid At',
            'notes'          => 'Notes',
            'created_at'     => 'Created At',
        ],

        'status' => [
            'unpaid' => 'Unpaid',
            'paid'   => 'Paid',
            'void'   => 'Void',
        ],

        'payment_methods' => [
            'cash'     => 'Cash',
            'card'     => 'Card',
            'transfer' => 'Bank Transfer',
        ],

        'actions' => [
            'mark_as_paid'             => 'Mark as Paid',
            'mark_as_paid_description' => 'Select the payment method to mark this invoice as paid.',
            'mark_as_unpaid'           => 'Mark as Unpaid',
            'mark_as_unpaid_description' => 'This will revert the invoice status to unpaid.',
            'print'                    => 'Print Invoice',
            'open_appointment'         => 'Open Appointment',
        ],

        'messages' => [
            'marked_as_paid'   => 'Invoice marked as paid successfully!',
            'marked_as_unpaid' => 'Invoice marked as unpaid successfully!',
        ],

        'meta' => [
            'created' => 'Created by :by at :at',
        ],

        'items' => [
            'title' => 'Invoice Items',
            'fields' => [
                'name'         => 'Service Name',
                'qty'          => 'Qty',
                'duration_min' => 'Duration',
                'unit_price'   => 'Unit Price',
                'line_total'   => 'Line Total',
            ],
        ],

        'print' => [
            'title' => 'Invoice Print',
            'print_button' => 'Print',
            'close_button' => 'Close',
            'appointment_date' => 'Appointment Date',
        ],
    ],

];
