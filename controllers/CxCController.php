<?php

use SincroCobranza\Entities\CxC;
use SincroCobranza\Managers\CxCManager;
use SincroCobranza\Repositories\CxCRepo;
use SincroCobranza\Repositories\CiudadRepo;
use SincroCobranza\Repositories\ZonaRepo;
use SincroCobranza\Repositories\SRegionRepo;
use SincroCobranza\Repositories\RegionRepo;
use SincroCobranza\Repositories\PaisRepo;
use SincroCobranza\Repositories\ReferenciaRepo;
use SincroCobranza\Repositories\TipoImpagoRepo;
use SincroCobranza\Repositories\EmpresaRepo;

class CxCController extends \BaseController {
 
    protected $cxcRepo;
   	protected $ciudadRepo;
    protected $zonaRepo;
    protected $sregionRepo;
    protected $regionRepo;
    protected $paisRepo;
    protected $referenciaRepo;
    protected $tipoImpagoRepo;
    protected $empresaRepo;
	protected $title;

    public function __construct(CiudadRepo $ciudadRepo, 
    							ZonaRepo $zonaRepo,
                                SRegionRepo $sregionRepo,
                                RegionRepo $regionRepo,
                                PaisRepo $paisRepo,
                                ReferenciaRepo $referenciaRepo,
                                TipoImpagoRepo $tipoImpagoRepo,
                                EmpresaRepo $empresaRepo,
                                CxCRepo $cxcRepo)
    {
       	$this->ciudadRepo = $ciudadRepo;
        $this->zonaRepo = $zonaRepo;
        $this->sregionRepo = $sregionRepo;
        $this->regionRepo = $regionRepo;
        $this->paisRepo = $paisRepo;
        $this->referenciaRepo = $referenciaRepo;
        $this->tipoImpagoRepo = $tipoImpagoRepo;
        $this->empresaRepo = $empresaRepo;
        $this->cxcRepo = $cxcRepo;
		$this->title = \Lang::get('utils.CxC');
    }

 	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
    public function index()
    {
        if ( ! \Util::getAccess('CxC')) return $this->accessFail();

        $cxc = new CxC;
        $cxc_filter = array();
        $filter = Input::all();
        $cxc_orden = 1;
        $cxc->CxC_Orden = 1;
        $fields = 'CxC_Status < ?';
        $values[] = 3;
        $cxc->CxC_Status = 4;
        $cxc_status = $cxc->CxC_Status;

        $cxc_list = $this->cxcRepo->getFiltered($fields, $values, $cxc_orden);
        $cxc_total = $this->cxcRepo->getTotalFiltered($fields, $values);

        $ciudades = $this->ciudadRepo->getList();
        $zonas = $this->zonaRepo->getList();
        $sregiones = $this->sregionRepo->getList();
        $regiones = $this->regionRepo->getList();
        $paises = $this->paisRepo->getList();
        $empresas = $this->empresaRepo->getList();
        $tiposImpagos = $this->tipoImpagoRepo->getList();
        $obj_id = \Util::getObjId('CxC');
        $title = $this->title;
        $form_data = array('route' => array('cxc.list', 0), 'method' => 'POST');

        return View::make('cxc/list', compact('cxc', 'cxc_list', 'cxc_filter', 'cxc_total', 'cxc_orden', 'cxc_status', 'ciudades', 'zonas', 'sregiones', 'regiones', 'paises', 'empresas', 'tiposImpagos', 'obj_id', 'title', 'form_data'));
    }

 	public function seek($id)
	{
		if ( ! \Util::getAccess('CxC')) return $this->accessFail();
		
		$cxc = new CxC;
		$cxc_filter = array();
		$filter = Input::all();
        $cxc_orden = 1;
        $cxc->CxC_Orden = 1;

 		if ($filter) {
			$filter = array_only($filter, $cxc->getFillable());
			foreach($filter as $field => $value) {
                if ($field == 'CxC_Orden') {
                    $cxc_orden = $value;
                    $cxc_filter[$field] = $value;
                    $cxc->CxC_Orden = $value;
                }

                $valid_dates = true;
                if ($field == 'CxC_Fch_Emision_LB' or $field == 'CxC_Fch_Emision_UB') {
                    $valid_dates = \Util::setDate($value);
                }

				if ($valid_dates and ($value or $value == '0') and $field <> 'CxC_Orden') {
					if (isset($fields)) {
						$fields .= ' AND ';
					} else {
						$fields = '';
						$values = array();
					}
										
					$cxc_filter[$field] = $value;
					switch ($field) {
						case 'Ref_Nbr':
							$fields .= '(Ref_Nbr LIKE ? OR Ref_Codigo LIKE ?)';
							$value = '%' . $value . '%';
                            $values[] = $value;
							break;
                        case 'CxC_Status':
                            if ($value == 4) {
                                $fields .= $field . " < ?";
                                $value = 3;
                            } else {
                                $fields .= $field . ' = ?';
                            }
                            break;
                        case 'CxC_Fch_Emision_LB':
                            $fields .= 'CxC_Fch_Emision >= ?';
                            $value = \Util::setDate($value);
                            break;
                        case 'CxC_Fch_Emision_UB':
                            $fields .= 'CxC_Fch_Emision <= ?';
                            $value = \Util::setDate($value);
                            break;
                        case 'CxC_Vencidas':
                            $fields .= 'CxC_Dias_Venc > ?';
                            $value = 0;
                            break;
                        case 'CxC_Act_Cant':
                            $fields .= 'CxC_Act_Cant > ?';
                            $value = 0;
                            break;
						default:
							$fields .= $field . ' = ?';
					}						
					$values[] = $value;
				}
			}
		} else {
            if (preg_match('/^[0-9]-[0-9]+-[0-9]+$/', $id)) {
                $ids = explode('-', $id);
                $fields = 'CxC_Status < ?';
                $values[] = 3;
                $cxc->CxC_Status = 4;
                $cxc_filter['CxC_Status'] = 4;
                switch ($ids[0]) {
                    case 1:
                        $fields .= ' AND CxC_Ref_Id = ?';
                        $referencia = $this->referenciaRepo->find('C-' . $ids[1]);
                        $cxc_filter['CxC_Ref_Id'] = $ids[1];
                        if ($referencia) {
                            $cxc->Ref_Nbr = $referencia->Ref_Nbr;
                        }
                        break;
                    case 2:
                        $fields .= ' AND Zon_Id = ?';
                        $cxc->Zon_Id = $ids[1];
                        $cxc_filter['Zon_Id'] = $ids[1];
                        break;
                    case 3:
                        $fields .= ' AND SReg_Id = ?';
                        $cxc->SReg_Id = $ids[1];
                        $cxc_filter['SReg_Id'] = $ids[1];
                        break;
                    case 4:
                        $fields .= ' AND Reg_Id = ?';
                        $cxc->Reg_Id = $ids[1];
                        $cxc_filter['Reg_Id'] = $ids[1];
                        break;
                    case 5:
                        $fields .= ' AND Pais_Id = ?';
                        $cxc->Pais_Id = $ids[1];
                        $cxc_filter['Pais_Id'] = $ids[1];
                        break;
                }
                $values[] = $ids[1];

                if ($ids[2]) {
                    $fields .= ' AND Tp_Impag_Id = ?';
                    $values[] = $ids[2];
                    $cxc->Tp_Impag_Id = $ids[2];
                }
            }
        }

		if (isset($fields)) {
			$cxc->fill($filter);
		   	$cxc_list = $this->cxcRepo->getFiltered($fields, $values, $cxc_orden);
            $cxc_total = $this->cxcRepo->getTotalFiltered($fields, $values);
		} else {
		   	$cxc_list = $this->cxcRepo->getAll($cxc_orden);
            $cxc_total = $this->cxcRepo->getTotalAll();
		}

        $cxc_status = $cxc->CxC_Status;
        $ciudades = $this->ciudadRepo->getList();
        $zonas = $this->zonaRepo->getList();
        $sregiones = $this->sregionRepo->getList();
        $regiones = $this->regionRepo->getList();
        $paises = $this->paisRepo->getList();
        $empresas = $this->empresaRepo->getList();
        $tiposImpagos = $this->tipoImpagoRepo->getList();
		$obj_id = \Util::getObjId('CxC');
		$title = $this->title;		
        $form_data = array('route' => array('cxc.list', $id), 'method' => 'POST');
		
		return View::make('cxc/list', compact('cxc', 'cxc_list', 'cxc_filter', 'cxc_total', 'cxc_orden', 'cxc_status', 'ciudades', 'zonas', 'sregiones', 'regiones', 'paises', 'empresas', 'tiposImpagos', 'obj_id', 'title', 'form_data'));
	}

	public function export()
	{
		if ( ! \Util::getAccess('CxC_Export')) return $this->accessFail();

		return Excel::create('CxC', function($excel) {
		
		    $excel->sheet('CxC', function($sheet) {

                $cxc = new CxC;
                $filter = Input::all();
                $cxc_orden = 1;
                $cxc_status = '';

                if ($filter) {
                    $filter = array_only($filter, $cxc->getFillable());
                    foreach($filter as $field => $value) {
                        if ($field == 'CxC_Orden') {
                            $cxc_orden = $value;
                            $cxc_filter[$field] = $value;
                        }

                        $valid_dates = true;
                        if ($field == 'CxC_Fch_Emision_LB' or $field == 'CxC_Fch_Emision_UB') {
                            $valid_dates = \Util::setDate($value);
                        }

                        if ($valid_dates and ($value or $value == '0') and $field <> 'CxC_Orden') {
                            if (isset($fields)) {
                                $fields .= ' AND ';
                            } else {
                                $fields = '';
                                $values = array();
                            }

                            $cxc_filter[$field] = $value;
                            switch ($field) {
                                case 'Ref_Nbr':
                                    $fields .= '(Ref_Nbr LIKE ? OR Ref_Codigo LIKE ?)';
                                    $value = '%' . $value . '%';
                                    $values[] = $value;
                                    break;
                                case 'CxC_Status':
                                    $cxc_status = $value;
                                    if ($value == 4) {
                                        $fields .= $field . " < ?";
                                        $value = 3;
                                    } else {
                                        $fields .= $field . ' = ?';
                                    }
                                    break;
                                case 'CxC_Fch_Emision_LB':
                                    $fields .= 'CxC_Fch_Emision >= ?';
                                    $value = \Util::setDate($value);
                                    break;
                                case 'CxC_Fch_Emision_UB':
                                    $fields .= 'CxC_Fch_Emision <= ?';
                                    $value = \Util::setDate($value);
                                    break;
                                case 'CxC_Vencidas':
                                    $fields .= 'CxC_Dias_Venc > ?';
                                    $value = 0;
                                    break;
                                case 'CxC_Act_Cant':
                                    $fields .= 'CxC_Act_Cant > ?';
                                    $value = 0;
                                    break;
                                default:
                                    $fields .= $field . ' = ?';
                            }
                            $values[] = $value;
                        }
                    }
                }

                if (isset($fields)) {
                    $cxc_list = $this->cxcRepo->getDataFiltered($fields, $values, $cxc_orden);
                    $cxc_total = $this->cxcRepo->getTotalFiltered($fields, $values);
                } else {
                    $cxc_list = $this->cxcRepo->getData($cxc_orden);
                    $cxc_total = $this->cxcRepo->getTotalAll();
                }

		        $sheet->loadView('cxc/export', ['cxc_list' => $cxc_list, 'cxc_total' => $cxc_total, 'cxc_status' => $cxc_status]);
		
		    });
		
		})->export('xls');
	}


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        if ( ! \Util::getAccess('CxC')) return $this->accessFail();

        $cxc = $this->cxcRepo->find($id, 'view');

        $this->notFoundUnless($cxc);

        $tiposImpagos = $this->tipoImpagoRepo->getList();
        $referencia = $this->referenciaRepo->find($cxc->CxC_Tp_Ref . '-' . $cxc->CxC_Ref_Id);
        $ciudad = $this->ciudadRepo->find($cxc->Ciudad_Id, 'view');
        $obj_id = \Util::getObjId('CxC');
        $title = $this->title;
        $form_data = array('route' => array('cxc.list', '1-' . $cxc->CxC_Ref_Id), 'method' => 'GET');
        $action    = '';
        $action_label = '';

        return View::make('cxc/form', compact('cxc', 'tiposImpagos', 'referencia', 'ciudad', 'obj_id', 'title', 'form_data', 'action', 'action_label'));
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        if ( ! \Util::getAccess('CxC')) return $this->accessFail();

        $cxc = $this->cxcRepo->find($id, 'view');

        $this->notFoundUnless($cxc);

        $tiposImpagos = $this->tipoImpagoRepo->getList();
        $referencia = $this->referenciaRepo->find($cxc->CxC_Tp_Ref . '-' . $cxc->CxC_Ref_Id);
        $ciudad = $this->ciudadRepo->find($cxc->Ciudad_Id, 'view');
        $obj_id = \Util::getObjId('CxC');
        $title = $this->title;
        $form_data = array('route' => array('cxc.update', $cxc->CxC_Id), 'method' => 'PATCH');
        $action    = '';
        if ( \Util::getAccess('CxC', 'Update')) $action = 'Actualizar';
        $action_label = \Lang::get('utils.Actualizar');

        return View::make('cxc/form', compact('cxc', 'tiposImpagos', 'referencia', 'ciudad', 'obj_id', 'title', 'form_data', 'action', 'action_label'));
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id)
    {
        $cxc = $this->cxcRepo->find($id);

        $this->notFoundUnless($cxc);

        $manager = new CxCManager($cxc, Input::all());

        $manager->save();

        return Redirect::route('cxc.list', '1-' . $cxc->CxC_Ref_Id . '-0');
    }

}
