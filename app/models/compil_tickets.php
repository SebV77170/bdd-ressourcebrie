<?php
namespace app;

use \Fpdf\Fpdf;
use \Fpdf\pdf;

class compil_tickets {

    
    
    protected $name;
    protected $link;
    protected $id_tickets; 
    protected $date;        
    
    public function __construct(string $date_compil){
        require('../db.php');

        $sql ="SELECT id_ticket FROM ticketdecaisse WHERE date_achat_dt LIKE '$date_compil%'";
        $sth=$db->query($sql);
        
        $results = $sth->fetchAll(\PDO::FETCH_ASSOC);    

        $this->id_tickets = $results;
        $this->name = 'compil'.$date_compil.'';
        $this->link = '../../tickets/compilations/'.$this->name.'.pdf';
        $this->date = $date_compil;
    }

    public function getName(){
        return $this->name;
    }
    
    public function getLink(){
        return $this->link;
    }

    public function getDate(){
        return $this->date;
    }

    public function compilFile(){
        $fichier = new PDF('P', 'mm', array(210,297));
        $fichier->AddPage();
        $fichier->SetFont('Arial','',8);
        
        foreach($this->id_tickets as $k1=>$v1):
            foreach($v1 as $v2):
                if(file_exists('../../tickets/ticket'.$v2.'.txt')):
                    $fichier1=file_get_contents('../../tickets/ticket'.$v2.'.txt');
                    $contenu1=''.$fichier1.'
            
            -------------------------------------------
            
                ';
                    $contenu1=str_replace(array("\r\n", "\r", "\n"), "<br />", $contenu1);
                    $contenu1=preg_replace('/\<br(\s*)?\/?\>/i', "\n", $contenu1);
                    $fichier->Multicell(0,3,$contenu1);
                endif;
            endforeach;
        endforeach;
        $fichier->Output('F', $this->link, $this->name, false);
        $fichier->Output('D', $this->name, false);
    }
}

?>
