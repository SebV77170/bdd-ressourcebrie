<?php
require('../../config.php');
require '../../app/bootstrap.php';
require '../../app/models/compil_tickets.php';
require '../../app/models/fpdf/fpdf/src/Fpdf/Fpdf.php';
require '../../app/models/fpdf/fpdf/src/Fpdf/pdf.php';


$pdo = get_pdo();

$compil = new compil_tickets($_GET['date']);

$file = $compil->compilFile();


?>