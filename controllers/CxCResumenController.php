<?php

use SincroCobranza\Entities\CxC;
use SincroCobranza\Repositories\CxCRepo;
use SincroCobranza\Repositories\CiudadRepo;
use SincroCobranza\Repositories\ZonaRepo;
use SincroCobranza\Repositories\SRegionRepo;
use SincroCobranza\Repositories\RegionRepo;
use SincroCobranza\Repositories\PaisRepo;
use SincroCobranza\Repositories\EmpresaRepo;

class CxCResumenController extends \BaseController {
 
    protected $cxcRepo;
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
                                CxCRepo $cxcRepo)
    {
       	$this->ciudadRepo = $ciudadRepo;
        $this->zonaRepo = $zonaRepo;
        $this->sregionRepo = $sregionRepo;
        $this->regionRepo = $regionRepo;
        $this->paisRepo = $paisRepo;
        $this->empresaRepo = $empresaRepo;
        $this->cxcRepo = $cxcRepo;
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
		
		$cxc = new CxC;
		$cxcResumen_filter = array();
		$filter = Input::all();
        $cxc->Resumen_Id = 1;
        $impagos = '';

 		if ($filter) {
			$filter = array_only($filter, $cxc->getFillable());
			foreach($filter as $field => $value) {
                if ($field == 'Resumen_Id') {
                    $cxcResumen_filter[$field] = $value;
                    $cxc->Resumen_Id = $value;
                }
                if ($field == 'CxC_Antiguedad') {
                    $cxcResumen_filter[$field] = $value;
                    $cxc->CxC_Antiguedad = $value;
                }
                if ($field == 'CxC_Impagos') {
                    $impagos = '_impagos';
                    $cxcResumen_filter[$field] = $value;
                    $cxc->CxC_Impagos = $value;
                }

				if (($value or $value == '0') and $field <> 'Resumen_Id' and $field <> 'CxC_Antiguedad' and $field <> 'CxC_Impagos') {
					if (isset($fields)) {
						$fields .= ' AND ';
					} else {
						$fields = '';
						$values = array();
					}
										
					$cxcResumen_filter[$field] = $value;
					switch ($field) {
						case 'Ref_Nbr':
						case 'Ref_Codigo':
							$fields .= $field . " LIKE ?";
							$value = '%' . $value . '%';
							break;
						default:
							$fields .= $field . ' = ?';
					}						
					$values[] = $value;
				}
			}
		}

		if (isset($fields)) {
			$cxc->fill($filter);
		   	$cxcResumen_list = $this->cxcRepo->getResumenFiltered($fields, $values, $cxc->Resumen_Id, $impagos);
            $cxcResumen_total = $this->cxcRepo->getResumenTotalFiltered($fields, $values);
		} else {
		   	$cxcResumen_list = $this->cxcRepo->getResumenAll($cxc->Resumen_Id, $impagos);
            $cxcResumen_total = $this->cxcRepo->getResumenTotalAll();
        }

       	$ciudades = $this->ciudadRepo->getList();
        $zonas = $this->zonaRepo->getList();
        $sregiones = $this->sregionRepo->getList();
        $regiones = $this->regionRepo->getList();
        $paises = $this->paisRepo->getList();
        $empresas = $this->empresaRepo->getList();
		$obj_id = \Util::getObjId('CxC_Resumen');
		$title = $this->title;		
        $form_data = array('route' => 'cxcResumen.index', 'method' => 'POST');
		
		return View::make('cxcResumen/list', compact('cxc', 'cxcResumen_list', 'cxcResumen_filter', 'cxcResumen_total', 'ciudades', 'zonas', 'sregiones', 'regiones', 'paises', 'empresas', 'obj_id', 'title', 'form_data'));
	}

	public function export()
	{
		if ( ! \Util::getAccess('CxC_Resumen_Export')) return $this->accessFail();
		
		return Excel::create('CxCResumen', function($excel) {
		
		    $excel->sheet('CxCResumen', function($sheet) {

                $cxc = new CxC;
                $cxcResumen_filter = array();
                $filter = Input::all();
                $cxc->Resumen_Id = 1;
                $impagos = '';

                if ($filter) {
                    $filter = array_only($filter, $cxc->getFillable());
                    foreach($filter as $field => $value) {
                        if ($field == 'Resumen_Id') {
                            $cxcResumen_filter[$field] = $value;
                            $cxc->Resumen_Id = $value;
                        }
                        if ($field == 'CxC_Antiguedad') {
                            $cxcResumen_filter[$field] = $value;
                            $cxc->CxC_Antiguedad = $value;
                        }
                        if ($field == 'CxC_Impagos') {
                            $impagos = '_impagos';
                            $cxcResumen_filter[$field] = $value;
                            $cxc->CxC_Impagos = $value;
                        }

                        if (($value or $value == '0') and $field <> 'Resumen_Id' and $field <> 'CxC_Antiguedad' and $field <> 'CxC_Impagos') {
                            if (isset($fields)) {
                                $fields .= ' AND ';
                            } else {
                                $fields = '';
                                $values = array();
                            }

                            $cxcResumen_filter[$field] = $value;
                            switch ($field) {
                                case 'Ref_Nbr':
                                case 'Ref_Codigo':
                                    $fields .= $field . " LIKE ?";
                                    $value = '%' . $value . '%';
                                    break;
                                default:
                                    $fields .= $field . ' = ?';
                            }
                            $values[] = $value;
                        }
                    }
                }

                if (isset($fields)) {
                    $cxc->fill($filter);
                    $cxcResumen_list = $this->cxcRepo->getResumenDataFiltered($fields, $values, $cxc->Resumen_Id, $impagos);
                    $cxcResumen_total = $this->cxcRepo->getResumenTotalFiltered($fields, $values);
                } else {
                    $cxcResumen_list = $this->cxcRepo->getResumenData($cxc->Resumen_Id, $impagos);
                    $cxcResumen_total = $this->cxcRepo->getResumenTotalAll();
                }

		        $sheet->loadView('cxcResumen/export', ['cxcResumen_list' => $cxcResumen_list, 'cxcResumen_total' => $cxcResumen_total, 'cxc' => $cxc]);
		
		    });
		
		})->export('xls');
	}


}
