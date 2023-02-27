<?php
namespace Fpdf;
class PDF extends FPDF

//Etend la classe FPDF afin de créer un PDF sur 2 colonnes avant d'avoir un page Break
{
    protected $col = 0;

    function SetCol($col)
    {
        // Move position to a column
        $this->col = $col;
        $x = 10 + $col*100;
        $this->SetLeftMargin($x);
        $this->SetX($x);
    }

    function AcceptPageBreak()
    {
        if($this->col<1)
        {
            // Go to next column
            $this->SetCol($this->col+1);
            $this->SetY(10);
            return false;
        }
        else
        {
            // Go back to first column and issue page break
            $this->SetCol(0);
            return true;
        }
    }
}
?>