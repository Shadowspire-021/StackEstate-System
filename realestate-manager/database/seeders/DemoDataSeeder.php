<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Property;
use App\Models\Payment;
use App\Models\Installment;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $existingClientIds = Client::withTrashed()->pluck('client_id')->toArray();

        $userId = 1;

        $clientsData = [
            [
                'client_id' => 'CL-2026-001',
                'salutation' => 'Mr.',
                'full_name' => 'Ahmed Khan',
                'father_husband_salutation' => 'S/O',
                'father_husband_name' => 'Mohammad Khan',
                'cnic' => '42101-1234567-1',
                'phone' => '0300-1234567',
                'residential_address' => 'House 12, Street 5, Block A, Gulshan-e-Maymar, Karachi',
                'status' => 'active',
                'created_by' => $userId,
                'property' => [
                    'property_type' => 'Residential Plot',
                    'plot_number' => 'PLOT-A-101',
                    'block_name' => 'Block A',
                    'location' => 'Gulshan-e-Maymar, Karachi',
                    'size_sqyards' => '120.00',
                    'total_deal_value' => 3000000,
                    'agreement_date' => '2026-01-15',
                ],
                'unit' => [
                    'unit_number' => 'UNIT-A-101',
                    'floor_number' => 1,
                    'size' => '120.00',
                    'price' => 3000000,
                    'status' => 'booked',
                ],
                'payments' => [
                    ['amount' => 500000, 'payment_method' => 'CASH', 'payment_date' => '2026-01-20', 'particulars' => 'Advance payment'],
                    ['amount' => 200000, 'payment_method' => 'BANK_TRANSFER', 'payment_date' => '2026-03-15', 'particulars' => '2nd installment'],
                ],
                'installments' => [
                    ['installment_number' => 1, 'amount' => 250000, 'due_date' => '2026-02-15', 'status' => 'paid'],
                    ['installment_number' => 2, 'amount' => 250000, 'due_date' => '2026-04-15', 'status' => 'paid'],
                    ['installment_number' => 3, 'amount' => 250000, 'due_date' => '2026-06-15', 'status' => 'pending'],
                    ['installment_number' => 4, 'amount' => 250000, 'due_date' => '2026-08-15', 'status' => 'pending'],
                ],
            ],
            [
                'client_id' => 'CL-2026-002',
                'salutation' => 'Ms.',
                'full_name' => 'Fatima Ali',
                'father_husband_salutation' => 'D/O',
                'father_husband_name' => 'Ali Ahmed',
                'cnic' => '42201-7654321-2',
                'phone' => '0311-9876543',
                'residential_address' => 'Flat 3, Building 7, Phase 2, DHA, Lahore',
                'status' => 'active',
                'created_by' => $userId,
                'property' => [
                    'property_type' => 'Commercial Plot',
                    'plot_number' => 'PLOT-C-205',
                    'block_name' => 'Commercial Zone',
                    'location' => 'DHA Phase 2, Lahore',
                    'size_sqyards' => '80.00',
                    'total_deal_value' => 5000000,
                    'agreement_date' => '2026-02-01',
                ],
                'unit' => [
                    'unit_number' => 'UNIT-C-205',
                    'floor_number' => 0,
                    'size' => '80.00',
                    'price' => 5000000,
                    'status' => 'available',
                ],
                'payments' => [
                    ['amount' => 1000000, 'payment_method' => 'CHEQUE', 'payment_date' => '2026-02-05', 'particulars' => 'Down payment'],
                    ['amount' => 400000, 'payment_method' => 'BANK_TRANSFER', 'payment_date' => '2026-04-10', 'particulars' => 'Monthly installment'],
                ],
                'installments' => [
                    ['installment_number' => 1, 'amount' => 500000, 'due_date' => '2026-03-01', 'status' => 'paid'],
                    ['installment_number' => 2, 'amount' => 500000, 'due_date' => '2026-05-01', 'status' => 'paid'],
                    ['installment_number' => 3, 'amount' => 500000, 'due_date' => '2026-07-01', 'status' => 'pending'],
                    ['installment_number' => 4, 'amount' => 500000, 'due_date' => '2026-09-01', 'status' => 'pending'],
                ],
            ],
            [
                'client_id' => 'CL-2026-003',
                'salutation' => 'Dr.',
                'full_name' => 'Usman Raza',
                'father_husband_salutation' => 'S/O',
                'father_husband_name' => 'Raza Hussain',
                'cnic' => '42301-5555555-3',
                'phone' => '0333-4567890',
                'residential_address' => '456, Shahrah-e-Faisal, Karachi',
                'status' => 'active',
                'created_by' => $userId,
                'property' => [
                    'property_type' => 'House',
                    'plot_number' => 'HSE-B-310',
                    'block_name' => 'Block B',
                    'location' => 'Scheme 33, Karachi',
                    'size_sqyards' => '200.00',
                    'total_deal_value' => 8000000,
                    'agreement_date' => '2026-03-10',
                ],
                'unit' => [
                    'unit_number' => 'UNIT-H-310',
                    'floor_number' => 2,
                    'size' => '200.00',
                    'price' => 8000000,
                    'status' => 'reserved',
                ],
                'payments' => [
                    ['amount' => 1500000, 'payment_method' => 'ONLINE', 'payment_date' => '2026-03-12', 'particulars' => 'Initial down payment'],
                ],
                'installments' => [
                    ['installment_number' => 1, 'amount' => 500000, 'due_date' => '2026-04-10', 'status' => 'paid'],
                    ['installment_number' => 2, 'amount' => 500000, 'due_date' => '2026-05-10', 'status' => 'paid'],
                    ['installment_number' => 3, 'amount' => 500000, 'due_date' => '2026-06-10', 'status' => 'pending'],
                    ['installment_number' => 4, 'amount' => 500000, 'due_date' => '2026-05-01', 'status' => 'pending'],
                    ['installment_number' => 5, 'amount' => 500000, 'due_date' => '2026-01-01', 'status' => 'pending'],
                    ['installment_number' => 6, 'amount' => 500000, 'due_date' => '2026-07-10', 'status' => 'pending'],
                ],
            ],
        ];

        foreach ($clientsData as $data) {
            if (in_array($data['client_id'], $existingClientIds)) {
                continue;
            }

            $propertyData = $data['property'];
            unset($data['property']);
            $unitData = $data['unit'];
            unset($data['unit']);
            $paymentsData = $data['payments'];
            unset($data['payments']);
            $installmentsData = $data['installments'];
            unset($data['installments']);

            $client = Client::create($data);

            $propertyData['client_id'] = $client->id;
            $property = Property::create($propertyData);

            $unitData['property_id'] = $property->id;
            Unit::create($unitData);

            $paymentNumber = 1;
            foreach ($paymentsData as $pd) {
                $pd['client_id'] = $client->id;
                $pd['property_id'] = $property->id;
                $pd['payment_number'] = $paymentNumber++;
                $pd['created_by'] = $userId;
                Payment::create($pd);
            }

            foreach ($installmentsData as $id) {
                $id['client_id'] = $client->id;
                $id['property_id'] = $property->id;
                $id['original_amount'] = $id['amount'];
                $id['late_fee_amount'] = 0;
                Installment::create($id);
            }
        }
    }
}
