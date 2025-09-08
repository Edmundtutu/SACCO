<?php

namespace App\Filters\V1;

use App\Filters\ApiFilter;

class MemberFilter extends ApiFilter {

    protected $allowedparams =[
        'phone' => ['eq', 'like'],
        'NIN' => ['eq', 'like'],
        'DOB' => ['eq', 'lt', 'gt', 'lte', 'gte'],
        'gender' => ['eq', 'like'],
        'occupation' => ['eq', 'like'],
        'monthlyIncome' => ['eq', 'lt', 'gt', 'lte', 'gte','btw'],
        'nextOfKinName' => ['eq', 'like'],
        'nextOfKinRelationship' => ['eq', 'like'],
        'nextOfKinPhone' => ['eq', 'like'],
        'nextOfKinAddress' => ['eq', 'like'],
        'emergencyContactName' => ['eq', 'like'],
        'emergencyContactPhone' => ['eq', 'like'],
        'employerName' => ['eq', 'like'],
        'employerAddress' => ['eq', 'like'],
        'employerPhone' => ['eq', 'like'],
        'bankName' => ['eq', 'like'],
        'bankAccountNumber' => ['eq', 'like'],
        'additionalNotes' => ['eq', 'like'],
        'referee' =>['eq', 'like','exists:users,id'],
        'role' => ['eq', 'like', 'in:member,admin,staff_level'],
        'status' => ['eq','like','in:active,inactive,suspended,pending_approval'],
        'membershipDate'=> ['eq','gt','lt', 'lte', 'gte'],
        'approvalStatus'=> ['eq','like','in:approved,rejected,pending'],
        'approvedAt' => ['eq','gt','lt', 'lte', 'gte'],
        'approvedBy'=>['eq','gt','lt', 'lte', 'gte'],
        'accountVerified_at' =>['eq','gt','lt', 'lte', 'gte'],
        'subCounty' => ['eq', 'like'],
        'membershipCount'=>['eq','gt','lt', 'lte', 'gte','btw'],

    ];

    protected $colum_Map = [
        'NIN' => 'national_id',
        'phone' => 'phone',
        'DOB' => 'date_of_birth',
        'monthlyIncome' => 'monthly_income',
        'nextOfKin' => 'next_of_kin',
        'emergencyContact' => 'emergency_contact',
        'nextOfKinPhone' => 'next_of_kin_phone',
        'nextOfKinAddress' => 'next_of_kin_address',
        'emergencyContactPhone' => 'emergency_contact_phone',
        'employerName'=>'employer_name',
        'employerAddress'=>'employer_address',
        'employerPhone'=>'employer_phone',
        'bankName'=>'bank_name',
        'bankAccountNumber'=>'bank_account_number',
        'additionalNotes'=>'additional_notes',
        'membershipDate'=>'membership_date',
        'approvalStatus'=>'approval_status',
        'approvedDate'=>'approved_at',
        'approvedBy'=>'approved_by',
        'accountVerifiedDate'=>'account_verified_at',
        'subCounty' => 'sub_county',
        'membershipCount'=>'membership_count',

    ];

}
