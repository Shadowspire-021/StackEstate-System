<?php

namespace App\Services;

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use App\Models\Receipt;
use App\Models\Setting;
use App\Models\Payment;

class ReceiptService
{
    public function generate(Receipt $receipt)
    {
        \PhpOffice\PhpWord\Settings::setOutputEscapingEnabled(true);
        $phpWord = new PhpWord();
        
        $companyName = Setting::getValue('company_name', 'Builder/Developer');
        $companyAddress = Setting::getValue('company_address', 'City, Country');
        $vendorName = Setting::getValue('vendor_name', 'Vendor Name');
        $vendorCnic = Setting::getValue('vendor_cnic', '00000-0000000-0');
        
        $client = $receipt->client;
        $property = $receipt->property;

        if ($client && $client->vendor_type === 'custom') {
            if (!empty($client->vendor_name)) {
                $vendorName = $client->vendor_name;
            }
            if (!empty($client->vendor_cnic)) {
                $vendorCnic = $client->vendor_cnic;
            }
        }
        
        // Find all payments associated with this client and property to display details
        $query = Payment::with('installment')->where('client_id', $receipt->client_id);
        if ($receipt->property_id) {
            $query->where('property_id', $receipt->property_id);
        }
        $payments = $query->orderBy('payment_date', 'asc')->get();

        $totalPaymentsAmount = $payments->sum('amount');
        if ($totalPaymentsAmount == 0) {
            $totalPaymentsAmount = $receipt->total_amount_this_receipt;
        }
        $totalAmountInWords = AmountToWordsService::convert($totalPaymentsAmount);
        
        $section = $phpWord->addSection([
            'marginTop' => 500,
            'marginBottom' => 500,
            'marginLeft' => 720,
            'marginRight' => 720,
        ]);
        
        // Header (YM BUILDERS & DEVELOPERS)
        $section->addText($companyName, ['name' => 'Times New Roman', 'size' => 18, 'bold' => true, 'color' => '1B365D'], ['alignment' => 'center', 'spaceAfter' => 0]);
        $section->addText($companyAddress, ['name' => 'Times New Roman', 'size' => 9, 'bold' => true, 'color' => '4A5568'], ['alignment' => 'center', 'spaceAfter' => 120]);
        
        // Title
        $section->addText('RECEIPT', ['name' => 'Times New Roman', 'size' => 14, 'bold' => true, 'color' => '1B365D', 'underline' => 'single'], ['alignment' => 'center', 'spaceAfter' => 120]);
        
        // Ref and Date on same table to save vertical space
        $refDateTable = $section->addTable(['width' => 10000, 'unit' => 'dxa']);
        $refDateRow = $refDateTable->addRow();
        $refDateRow->addCell(5000)->addText('Ref: ' . $receipt->receipt_number, ['name' => 'Times New Roman', 'size' => 10, 'bold' => true, 'color' => '4A5568'], ['alignment' => 'left']);
        $refDateRow->addCell(5000)->addText('Date: ' . date('d F Y', strtotime($receipt->receipt_date)), ['name' => 'Times New Roman', 'size' => 10, 'bold' => true, 'color' => '4A5568'], ['alignment' => 'right']);
        
        // Opening statement
        $openingText = "That the \"Vendor\" has this day received a sum of Rs. " . number_format($totalPaymentsAmount) . "/- (" . $totalAmountInWords . ") in the following manner:";
        $section->addText($openingText, ['name' => 'Times New Roman', 'size' => 10], ['alignment' => 'both', 'spaceAfter' => 60, 'spaceBefore' => 120]);
        
        // Table 1: Manner of Payment Table
        $tableStyle = [
            'borderColor' => 'D0D0D0',
            'borderSize' => 6,
            'cellMargin' => 40,
            'width' => 10000,
            'unit' => 'dxa'
        ];
        $phpWord->addTableStyle('MannerTable', $tableStyle);
        $mannerTable = $section->addTable('MannerTable');
        
        // Table 1 Header
        $headerStyle = ['bgColor' => '1B365D', 'valign' => 'center'];
        $headerTextStyle = ['name' => 'Times New Roman', 'size' => 10, 'bold' => true, 'color' => 'FFFFFF'];
        
        $row = $mannerTable->addRow();
        $row->addCell(3000, $headerStyle)->addText('Amount', $headerTextStyle, ['alignment' => 'left']);
        $row->addCell(7000, $headerStyle)->addText('Particulars', $headerTextStyle, ['alignment' => 'left']);
        
        // Table 1 Data Rows
        if ($payments->isNotEmpty()) {
            foreach ($payments as $payment) {
                $row = $mannerTable->addRow();
                
                // Amount Cell
                $amountCell = $row->addCell(3000);
                $amountCell->addText("Rs. " . number_format($payment->amount) . "/-", ['name' => 'Times New Roman', 'size' => 10, 'bold' => true]);
                $amountCell->addText("(" . AmountToWordsService::convert($payment->amount) . ")", ['name' => 'Times New Roman', 'size' => 9, 'italic' => true]);
                
                // Particulars Cell
                $particularsCell = $row->addCell(7000);
                
                $method = $payment->payment_method;
                $particularsCell->addText("Method: " . ($method === 'PO' ? 'PAY ORDER (PO)' : $method), ['name' => 'Times New Roman', 'size' => 9, 'bold' => true]);
                
                if ($method === 'CHEQUE') {
                    $particularsCell->addText("Cheque No: " . ($payment->cheque_number ?: 'N/A'), ['name' => 'Times New Roman', 'size' => 9]);
                    $particularsCell->addText("Bank: " . ($payment->bank_name ?: 'N/A'), ['name' => 'Times New Roman', 'size' => 9]);
                } elseif ($method === 'PO') {
                    $particularsCell->addText("Pay Order No: " . ($payment->cheque_number ?: 'N/A'), ['name' => 'Times New Roman', 'size' => 9]);
                    $particularsCell->addText("Bank: " . ($payment->bank_name ?: 'N/A'), ['name' => 'Times New Roman', 'size' => 9]);
                } elseif (in_array($method, ['BANK_TRANSFER', 'ONLINE'])) {
                    $particularsCell->addText("Transaction ID / Count: " . ($payment->cheque_number ?: 'N/A'), ['name' => 'Times New Roman', 'size' => 9]);
                    $particularsCell->addText("Bank: " . ($payment->bank_name ?: 'N/A'), ['name' => 'Times New Roman', 'size' => 9]);
                }
                
                if ($payment->particulars) {
                    $particularsCell->addText("Details: " . $payment->particulars, ['name' => 'Times New Roman', 'size' => 9]);
                }
                $particularsCell->addText("Date: " . date('d-m-Y', strtotime($payment->payment_date)), ['name' => 'Times New Roman', 'size' => 9]);
            }
        } else {
            $row = $mannerTable->addRow();
            $amountCell = $row->addCell(3000);
            $amountCell->addText("Rs. " . number_format($receipt->total_amount_this_receipt) . "/-", ['name' => 'Times New Roman', 'size' => 10, 'bold' => true]);
            $amountCell->addText("(" . AmountToWordsService::convert($receipt->total_amount_this_receipt) . ")", ['name' => 'Times New Roman', 'size' => 9, 'italic' => true]);
            
            $particularsCell = $row->addCell(7000);
            $particularsCell->addText("Method: CASH", ['name' => 'Times New Roman', 'size' => 9, 'bold' => true]);
            $particularsCell->addText("Date: " . date('d-m-Y', strtotime($receipt->receipt_date)), ['name' => 'Times New Roman', 'size' => 9]);
        }
        
        $section->addTextBreak(1, ['size' => 6]); // Small spacing before next section
        
        // Section Title: BUYER & PROPERTY DETAILS
        $phpWord->addTableStyle('SectionHeaderTable', [
            'borderColor' => '1B365D',
            'borderSize' => 6,
            'cellMargin' => 40,
            'width' => 10000,
            'unit' => 'dxa'
        ]);
        $secTable = $section->addTable('SectionHeaderTable');
        $secRow = $secTable->addRow();
        $secCell = $secRow->addCell(10000, ['bgColor' => '1B365D']);
        $secCell->addText('BUYER & PROPERTY DETAILS', ['name' => 'Times New Roman', 'size' => 10, 'bold' => true, 'color' => 'FFFFFF'], ['alignment' => 'left']);
        
        // Table 2: Details Table
        $phpWord->addTableStyle('DetailsTable', $tableStyle);
        $detailsTable = $section->addTable('DetailsTable');
        
        // Rows
        $this->addTableRow($detailsTable, 'Buyer Name', trim($client->salutation . ' ' . $client->full_name));
        $this->addTableRow($detailsTable, 'Father/Husband Name', trim($client->father_husband_salutation . ' ' . $client->father_husband_name));
        $this->addTableRow($detailsTable, 'Buyer CNIC', $client->cnic);
        $this->addTableRow($detailsTable, 'Residential Address', $client->residential_address);
        
        $propString = "";
        if ($property) {
            $propString = trim($property->property_type) . " No. " . trim($property->plot_number);
            if ($property->block_name) {
                $blockStr = trim($property->block_name);
                if (stripos($blockStr, 'Block') === false && stripos($blockStr, 'Phase') === false) {
                    $blockStr = "Block " . $blockStr;
                }
                $propString .= ", " . $blockStr;
            }
            if ($property->size_sqyards) {
                $propString .= ", measuring " . trim($property->size_sqyards) . " Sq. Yards";
            }
            if ($property->location) {
                $propString .= ", situated at " . trim($property->location);
            }
        }
        $this->addTableRow($detailsTable, 'Property Details', $propString ?: 'N/A');
        
        $this->addTableRow($detailsTable, 'Agreement Date', ($property && $property->agreement_date) ? date('d F Y', strtotime($property->agreement_date)) : '');
        // Removed receipt date from here since it's now at the top
        
        $section->addTextBreak(1, ['size' => 6]); // Small spacing
        
        // Summary texts
        $totalReceivedWords = AmountToWordsService::convert($receipt->total_received_to_date);
        $section->addText("Total amount received so far: Rs. " . number_format($receipt->total_received_to_date) . "/- (" . $totalReceivedWords . ")", ['name' => 'Times New Roman', 'size' => 10, 'bold' => true, 'color' => '1B365D'], ['spaceBefore' => 120]);
        
        $balanceWords = AmountToWordsService::convert($receipt->remaining_balance);
        $section->addText("Balance: Rs. " . number_format($receipt->remaining_balance) . "/- (" . $balanceWords . ")", ['name' => 'Times New Roman', 'size' => 10, 'bold' => true, 'color' => '1B365D']);
        
        // Signature Block (Aligned Right)
        $sigTable = $section->addTable(['width' => 10000, 'unit' => 'dxa']);
        $sigRow = $sigTable->addRow();
        $sigRow->addCell(6000); // Spacer
        $vendorCell = $sigRow->addCell(4000);
        $vendorCell->addText('"Vendor"', ['name' => 'Times New Roman', 'size' => 10, 'bold' => true, 'underline' => 'single'], ['alignment' => 'right', 'spaceBefore' => 400]);
        $vendorCell->addText($vendorName, ['name' => 'Times New Roman', 'size' => 10, 'bold' => true], ['alignment' => 'right', 'spaceBefore' => 60]);
        $vendorCell->addText('CNIC: ' . $vendorCnic, ['name' => 'Times New Roman', 'size' => 10], ['alignment' => 'right']);
        
        // Save
        $fileName = $receipt->docx_filename;
        $tempPath = storage_path('app/receipts/' . $fileName);
        
        if (!file_exists(storage_path('app/receipts'))) {
            mkdir(storage_path('app/receipts'), 0755, true);
        }
        
        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($tempPath);
        
        return $tempPath;
    }
    
    private function addTableRow($table, $label, $value)
    {
        $row = $table->addRow();
        $row->addCell(3000)->addText($label, ['name' => 'Times New Roman', 'size' => 9, 'bold' => true]);
        $row->addCell(7000)->addText($value, ['name' => 'Times New Roman', 'size' => 9]);
    }
}
