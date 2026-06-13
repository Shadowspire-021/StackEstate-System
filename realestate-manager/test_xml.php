<?php
$doc = new DOMDocument();
libxml_use_internal_errors(true);
$doc->load('storage/app/receipts/test_docx_unzipped/word/document.xml');
$errors = libxml_get_errors();
if (empty($errors)) {
    echo "XML is valid.\n";
} else {
    foreach($errors as $error) {
        echo "Error on line " . $error->line . ": " . $error->message . "\n";
    }
}
