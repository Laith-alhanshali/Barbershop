<?php

return [

    // مجموعات التنقل
    'groups' => [
        'bookings'  => 'العمليات',
        'employees' => 'الموظفين',
        'basic'     => 'الإدارة الأساسية',
    ],

    // فلاتر عامة
    'filters' => [
        'all' => 'الكل',
        'from' => 'من تاريخ',
        'to'   => 'إلى تاريخ',
    ],

    // =========================
    // Appointments
    // =========================
    'appointments' => [
        'navigation' => 'المواعيد',
        'singular'   => 'موعد',
        'plural'     => 'المواعيد',

        'sections' => [
            'details' => 'بيانات الموعد',
        ],

        // حقول الفورم + أعمدة الجدول
        'fields' => [
            'customer'        => 'العميل',
            'barber'          => 'الحلاق',
            'services'        => 'الخدمات',
            'start_at'        => 'وقت البدء',
            'end_at'          => 'وقت الانتهاء',
            'status'          => 'الحالة',
            'notes'           => 'ملاحظات',
            'services_count'  => 'الخدمات',
            'total_duration'  => 'المدة الإجمالية',
            'total_price'     => 'السعر الإجمالي',
            'created_by'      => 'أنشئ بواسطة',
        ],

        // حالات الموعد
        'status' => [
            'pending'   => 'قيد الانتظار',
            'confirmed' => 'مؤكد',
            'done'      => 'منجز',
            'cancelled' => 'ملغي',
            'no_show'   => 'لم يحضر',
        ],

        // رسائل التحقق
        'validation' => [
            'overlap'             => 'الفترة الزمنية المختارة تتعارض مع موعد آخر لهذا الحلاق. يرجى اختيار وقت مختلف.',
            'services_required'   => 'يجب اختيار خدمة واحدة على الأقل.',
            'too_old_for_booking' => 'لا يمكن اختيار وقت أقدم من ساعة من الآن.',
            'past_date_not_allowed' => 'لا يمكن اختيار وقت أقدم من ساعة من الآن.',
        ],

        // نصوص الميتا تحت الفورم
        'meta' => [
            'created' => 'تمت الإضافة بواسطة :by بتاريخ :at',
            'updated' => 'تم التعديل بواسطة :by بتاريخ :at',
        ],

        // الإجراءات
        'actions' => [
            'generate_invoice' => 'إنشاء فاتورة',
            'generate_invoice_description' => 'سيتم إنشاء فاتورة لهذا الموعد بجميع الخدمات المحددة.',
            'open_invoice' => 'فتح الفاتورة',
            'print_invoice' => 'طباعة الفاتورة',
        ],

        // الرسائل
        'messages' => [
            'invoice_generated' => 'تم إنشاء الفاتورة بنجاح!',
            'locked_done_cancelled' => 'هذا الموعد مقفل. المسؤول الأعلى فقط يمكنه التعديل.',
        ],
    ],

    // =========================
    // Barbers
    // =========================
    'barbers' => [
        'navigation' => 'الحلاقين',
        'singular'   => 'حلاق',
        'plural'     => 'الحلاقين',

        'sections' => [
            'details' => 'بيانات الحلاق',
        ],
        'fields'=> [
            'user_account'=> 'حساب المستخدم',
            'name'=> 'الاسم',
            'phone'=> 'رقم الهاتف',
            'bio'=> 'نبذة تعريفية',
            'active'=> 'نشط',
            'user_account_helper'=> 'اختياري - ربط الحلاق بحساب مستخدم',
            'inactive'=> 'غير نشط',
            'all'=> 'الكل',
        ],
    ],

    // =========================
    // Services
    // =========================
    'services' => [
        'navigation' => 'الخدمات',
        'singular'   => 'خدمة',
        'plural'     => 'الخدمات',

        'sections' => [
            'details' => 'بيانات الخدمة',
        ],
        'fields'=> [
            'name'=> 'الاسم',
            'duration_min'=> 'المدة (بالدقائق)',
            'price'=> 'السعر',
            'active'=> 'نشط',
            'inactive'=> 'غير نشط',
            'all'=> 'الكل',
        ],
    ],

    
    // =========================
    // Customers
    // =========================
    'customers' => [
        'navigation' => 'العملاء',
        'singular'   => 'عميل', 
        'plural'     => 'العملاء',

        'sections' => [
            'details' => 'بيانات العميل',
        ],
        'fields'=> [
            'name'=> 'الاسم',
            'phone'=> 'رقم الهاتف',
            'notes'=> 'ملاحظات',
            'created_at'=> 'تاريخ الإنشاء',
            'updated_at'=> 'تاريخ التعديل',
            'created_by'=> 'أنشئ بواسطة',
            'updated_by'=> 'عدل بواسطة',
        ],
    ],

    // =========================
    // Invoices (الفواتير)
    // =========================
    'invoices' => [
        'navigation' => 'الفواتير',
        'singular'   => 'فاتورة',
        'plural'     => 'الفواتير',

        'sections' => [
            'details' => 'بيانات الفاتورة',
            'totals'  => 'المجاميع',
            'payment' => 'معلومات الدفع',
            'notes'   => 'ملاحظات',
        ],

        'fields' => [
            'number'         => 'رقم الفاتورة',
            'appointment'    => 'الموعد',
            'customer'       => 'العميل',
            'barber'         => 'الحلاق',
            'status'         => 'الحالة',
            'subtotal'       => 'المجموع الفرعي',
            'discount'       => 'الخصم',
            'tax'            => 'الضريبة',
            'total'          => 'الإجمالي',
            'payment_method' => 'طريقة الدفع',
            'paid_at'        => 'تاريخ الدفع',
            'notes'          => 'ملاحظات',
            'created_at'     => 'تاريخ الإنشاء',
        ],

        'status' => [
            'unpaid' => 'غير مدفوعة',
            'paid'   => 'مدفوعة',
            'void'   => 'ملغاة',
        ],

        'payment_methods' => [
            'cash'     => 'نقداً',
            'card'     => 'بطاقة',
            'transfer' => 'تحويل بنكي',
        ],

        'actions' => [
            'mark_as_paid'             => 'تحديد كمدفوعة',
            'mark_as_paid_description' => 'اختر طريقة الدفع لتحديد هذه الفاتورة كمدفوعة.',
            'mark_as_unpaid'           => 'تحديد كغير مدفوعة',
            'mark_as_unpaid_description' => 'سيتم إرجاع حالة الفاتورة إلى غير مدفوعة.',
            'print'                    => 'طباعة الفاتورة',
            'open_appointment'         => 'فتح الموعد',
        ],

        'messages' => [
            'marked_as_paid'   => 'تم تحديد الفاتورة كمدفوعة بنجاح!',
            'marked_as_unpaid' => 'تم تحديد الفاتورة كغير مدفوعة بنجاح!',
        ],

        'meta' => [
            'created' => 'تمت الإضافة بواسطة :by بتاريخ :at',
        ],

        'items' => [
            'title' => 'عناصر الفاتورة',
            'fields' => [
                'name'         => 'اسم الخدمة',
                'qty'          => 'الكمية',
                'duration_min' => 'المدة',
                'unit_price'   => 'سعر الوحدة',
                'line_total'   => 'الإجمالي',
            ],
        ],

        'print' => [
            'title' => 'طباعة الفاتورة',
            'print_button' => 'طباعة',
            'close_button' => 'إغلاق',
            'appointment_date' => 'تاريخ الموعد',
        ],
    ],

];
