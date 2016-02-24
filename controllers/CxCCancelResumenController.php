<?php

use SincroCobranza\Entities\CxCCancel;
use SincroCobranza\Repositories\CxCCancelRepo;
use SincroCobranza\Repositories\CiudadRepo;
use SincroCobranza\Repositories\ZonaRepo;
use SincroCobranza\Repositories\SRegionRepo;
use SincroCobranza\Repositories\RegionRepo;
use SincroCobranza\Repositories\PaisRepo;
use SincroCobranza\Repositories\EmpresaRepo;

class CxCCancelResumenController extends \BaseController {
 
    protected $cxcCancelRepo;
   	protected $ciudadRepo;
    protected $zonaRepo;
    protected $sregionRepo;
    protected $regionRepo;
    protected $paisRepo;
    protected $empresaRepo;
	protected $title;

    public function __construct(CiudadRepo $ciudadRepo, 
    							ZonaRepo $zonaRepo,
                                SRegionRepo $sregionRepo,
                                RegionRepo $regionRepo,
                                PaisRepo $paisRepo,
                                EmpresaRepo $empresaRepo,
                                CxCCancelRepo $cxcCancelRepo)
    {
       	$this->ciudadRepo = $ciudadRepo;
        $this->zonaRepo = $zonaRepo;
        $this->sregionRepo = $sregionRepo;
        $this->regionRepo = $regionRepo;
        $this->paisRepo = $paisRepo;
        $this->empresaRepo = $empresaRepo;
        $this->cxcCancelRepo = $cxcCancelRepo;
		$this->title = \Lang::get('utils.CxC_Resumen');
    }

 	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
 	public function index()
	{
		if ( ! \Util::getAccess('CxC_Resumen')) return $this->accessFail();
		
		$cxcCancel = new CxCCancel;
		$cxcCancelResumen_filter = array();
		$filter = Input::all();
        $cxcCancel->Resumen_Id = 1;
        $dates = '';

 		if ($filter) {
			$filter = array_only($filter, $cxcCancel->getFillable());
			foreach($filter as $field => $value) {
                if ($field == 'Resumen_Id') {
                    $cxcCancelResumen_filter[$field] = $value;
                    $cxcCancel->Resumen_Id = $value;
                }

                $valid_dates = true;
                if ($field == 'CxC_Can_Fch_Emision_LB' or $field == 'CxC_Can_Fch_Emision_UB') {
                    $valid_dates = \Util::setDate($value);
                }

				if ($valid_dates and ($value or $value == '0') and $field <> 'Resumen_Id') {
					if (isset($fields)) {
						$fields .= ' AND ';
					} else {
						$fields = '';
						$values = array();
					}
										
					$cxcCancelResumen_filter[$field] = $value;
					switch ($field) {
						case 'Ref_Nbr':
						case 'Ref_Codigo':
							$fields .= $field . " LIKE ?";
							$value = '%' . $value . '%';
							break;
                        case 'CxC_Can_Fch_Emision_LB':
                            $fields .= 'CxC_Can_Fch_Emision >= ?';
                            $value = \Util::setDate($value);
                            $dates .= '1' . $value;
                            break;
                        case 'CxC_Can_Fch_Emision_UB':
                            $fields .= 'CxC_Can_Fch_Emision <= ?';
                            $value = \Util::setDate($value);
                            $dates .= '2' . $value;
                            break;
						default:
							$fields .= $field . ' = ?';
					}						
					$values[] = $value;
				}
			}
		}

		if (isset($fields)) {
			$cxcCancel->fill($filter);
		   	$cxcCancelResumen_list = $this->cxcCancelRepo->getResumenFiltered($fields, $values, $cxcCancel->Resumen_Id);
            $cxcCancelResumen_total = $this->cxcCancelRepo->getResumenTotalFiltered($fields, $values);
		} else {
		   	$cxcCancelResumen_list = $this->cxcCancelRepo->getResumenAll($cxcCancel->Resumen_Id);
            $cxcCancelResumen_total = $this->cxcCancelRepo->getResumenTotalAll();
        }

        if ( ! $dates ) $dates = '0';

       	$ciudades = $this->ciudadRepo->getList();
        $zonas = $this->zonaRepo->getList();
        $sregiones = $this->sregionRepo->getList();
        $regiones = $this->regionRepo->getList();
        $paises = $this->paisRepo->getList();
        $empresas = $this->empresaRepo->getList();
		$obj_id = \Util::getObjId('CxC_Resumen');
		$title = $this->title;		
        $form_data = array('route' => 'cxcCancelResumen.index', 'method' => 'POST');
		
		return View::make('cxcCancelResumen/list', compact('cxcCancel', 'cxcCancelResumen_list', 'cxcCancelResumen_filter', 'cxcCancelResumen_total', 'dates', 'ciudades', 'zonas', 'sregiones', 'regiones', 'paises', 'empresas', 'obj_id', 'title', 'form_data'));
	}

	public function export()
	{
		if ( ! \Util::getAccess('CxC_Resumen_Export')) return $this->accessFail();
		
		return Excel::create('CxCCancelResumen', function($excel) {
		
		    $excel->sheet('CxCCancelResumen', function($sheet) {

                $cxcCancel = new CxCCancel;
                $cxcCancelResumen_filter = array();
                $filter = Input::all();
                $cxcCancel->Resumen_Id = 1;
                $dates = '';

                if ($filter) {
                    $filter = array_only($filter, $cxcCancel->getFillable());
                    foreach($filter as $field => $value) {
                        if ($field == 'Resumen_Id') {
                            $cxcCancelResumen_filter[$field] = $value;
                            $cxcCancel->Resumen_Id = $value;
                        }

                        $valid_dates = true;
                        if ($field == 'CxC_Can_Fch_Emision_LB' or $field == 'CxC_Can_Fch_Emision_UB') {
                            $valid_dates = \Util::setDate($value);
                        }

                        if ($valid_dates and ($value or $value == '0') and $field <> 'Resumen_Id') {
                            if (isset($fields)) {
                                $fields .= ' AND ';
                            } else {
                                $fields = '';
                                $values = array();
                            }

                            $cxcCancelResumen_filter[$field] = $value;
                            switch ($field) {
                                case 'Ref_Nbr':
                                case 'Ref_Codigo':
                                    $fields .= $field . " LIKE ?";
                                    $value = '%' . $value . '%';
                                    break;
                                case 'CxC_Can_Fch_Emision_LB':
                                    $fields .= 'CxC_Can_Fch_Emision >= ?';
                                    $value = \Util::setDate($value);
                                    $dates .= '1' . $value;
                                    break;
                                case 'CxC_Can_Fch_Emision_UB':
                                    $fields .= 'CxC_Can_Fch_Emision <= ?';
                                    $value = \Util::setDate($value);
                                    $dates .= '2' . $value;
                                    break;
                                default:
                                    $fields .= $field . ' = ?';
                            }
                            $values[] = $value;
                        }
                    }
                }

                if (isset($fields)) {
                    $cxcCancel->fill($filter);
                    $cxcCancelResumen_list = $this->cxcCancelRepo->getResumenDataFiltered($fields, $values, $cxcCancel->Resumen_Id);
                    $cxcCancelResumen_total = $this->cxcCancelRepo->getResumenTotalFiltered($fields, $values);
                } else {
                    $cxcCancelResumen_list = $this->cxcCancelRepo->getResumenData($cxcCancel->Resumen_Id);
                    $cxcCancelResumen_total = $this->cxcCancelRepo->getResumenTotalAll();
                }

		        $sheet->loadView('cxcCancelResumen/export', ['cxcCancelResumen_list' => $cxcCancelResumen_list, 'cxcCancelResumen_total' => $cxcCancelResumen_total]);
		
		    });
		
		})->export('xls');
	}


}
