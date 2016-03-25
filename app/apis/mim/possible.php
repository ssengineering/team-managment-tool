<?php

namespace TMT\api\mim;

class possible extends \TMT\APIController
{
    private $mimAcc;
    private $areaAcc;

    public function __construct()
    {
        parent::__construct();
        $this->mimAcc = new \TMT\accessor\MimAccessor();
        $this->areaAcc = new \TMT\accessor\AreaAccessor();
    }

    public function get()
    {   
		$areas_detailed = $this->areaAcc->getAll($this->user['netId']);
		$areas = array();
		foreach($areas_detailed as $area) {
			$areas[] = $area->ID;
		}
		$results = $this->mimAcc->getPossible($areas);
		$this->respond($results);
    }   
}

?>
