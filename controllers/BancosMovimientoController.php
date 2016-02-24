<?php

use SincroCobranza\Entities\BancoMovimiento;
use SincroCobranza\Managers\BancoMovimientoManager;
use SincroCobranza\Repositories\BancoMovimientoRepo;
use SincroCobranza\Repositories\BancoRepo;
use SincroCobranza\Repositories\BancoCuentaRepo;
use SincroCobranza\Repositories\EmpresaRepo;
use SincroCobranza\Repositories\BancoMovimientoChequeRepo;

class BancosMovimientoController extends \BaseController {
 
    protected $bancoMovimientoRepo;
    protected $bancoRepo;
    protected $bancoCuentaRepo;
    protected $empresaRepo;
    protected $bancoMovimientoChequeRepo;
	protected $title;

    public function __construct(BancoRepo $bancoRepo,
                                BancoCuentaRepo $bancoCuentaRepo,
                                EmpresaRepo $empresaRepo,
                                BancoMovimientoRepo $bancoMovimientoRepo,
                                BancoMovimientoChequeRepo $bancoMovimientoChequeRepo)
    {
        $this->bancoRepo = $bancoRepo;
        $this->bancoCuentaRepo = $bancoCuentaRepo;
        $this->empresaRepo = $empresaRepo;
        $this->bancoMovimientoRepo = $bancoMovimientoRepo;
        $this->bancoMovimientoChequeRepo = $bancoMovimientoChequeRepo;
		$this->title = \Lang::get('utils.Bancos_Movimiento');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
  	public function index()
	{
		if ( ! \Util::getAccess('Bancos_Movimiento')) return $this->accessFail();
		
		$bancoMovimiento = new BancoMovimiento;
		$bancosMovimiento_filter = array();
		$filter = Input::all();
        $bancosMovimiento_orden = 1;
        $bancoMovimiento->BMov_Orden = 1;

 		if ($filter) {
			$filter = array_only($filter, $bancoMovimiento->getFillable());
			foreach($filter as $field => $value) {
                if ($field == 'BMov_Orden') {
                    $bancosMovimiento_orden = $value;
                    $bancosMovimiento_filter[$field] = $value;
                    $bancoMovimiento->BMov_Orden = $value;
                }

                $valid_dates = true;
                if ($field == 'BMov_Fch_Emision_LB' or $field == 'BMov_Fch_Emision_UB') {
                    $valid_dates = \Util::setDate($value);
                }

				if ($valid_dates and ($value or $value == '0') and $field <> 'BMov_Orden') {
					if (isset($fields)) {
						$fields .= ' AND ';
					} else {
						$fields = '';
						$values = array();
					}
										
					$bancosMovimiento_filter[$field] = $value;
					switch ($field) {
	                    case 'BMov_Fch_Emision_LB':
                            $fields .= 'BMov_Fch_Emision >= ?';
                            $value = \Util::setDate($value);
                            break;
                        case 'BMov_Fch_Emision_UB':
                            $fields .= 'BMov_Fch_Emision <= ?';
                            $value = \Util::setDate($value);
                            break;
 						default:
							$fields .= $field . ' = ?';
					}		
                    
					$values[] = $value;
			    }
            }
        }

		if (isset($fields)) {
			$bancoMovimiento->fill($filter);
		   	$bancosMovimiento_list = $this->bancoMovimientoRepo->getFiltered($fields, $values, $bancosMovimiento_orden);
            $bancosMovimiento_total = $this->bancoMovimientoRepo->getTotalFiltered($fields, $values);
		} else {
		   	$bancosMovimiento_list = $this->bancoMovimientoRepo->getAll($bancosMovimiento_orden);
            $bancosMovimiento_total = $this->bancoMovimientoRepo->getTotalAll();
		}

        $bancos = $this->bancoRepo->getList();
        $bancosCuentas = $this->bancoCuentaRepo->getList();
        $empresas = $this->empresaRepo->getList();
		$obj_id = \Util::getObjId('Bancos_Movimiento');
		$title = $this->title;		
        $form_data = array('route' => array('bancosMovimiento.index'), 'method' => 'POST');
		
		return View::make('bancosMovimiento/list', compact('bancoMovimiento', 'bancosMovimiento_list', 'bancosMovimiento_filter', 'bancosMovimiento_total', 'bancosMovimiento_orden', 'bancos', 'bancosCuentas', 'empresas', 'obj_id', 'title', 'form_data'));
	}

	public function export()
	{
		if ( ! \Util::getAccess('Bancos_Movimiento_Export')) return $this->accessFail();
		
		return Excel::create('Bancos_Movimiento', function($excel) {
		
		    $excel->sheet('Bancos_Movimiento', function($sheet) {

                $bancoMovimiento = new BancoMovimiento;
                $bancosMovimiento_filter = array();
                $filter = Input::all();
                $bancosMovimiento_orden = 1;
                $bancoMovimiento->BMov_Orden = 1;

                if ($filter) {
                    $filter = array_only($filter, $bancoMovimiento->getFillable());
                    foreach($filter as $field => $value) {
                        if ($field == 'BMov_Orden') {
                            $bancosMovimiento_orden = $value;
                            $bancosMovimiento_filter[$field] = $value;
                            $bancoMovimiento->BMov_Orden = $value;
                        }

                        $valid_dates = true;
                        if ($field == 'BMov_Fch_Emision_LB' or $field == 'BMov_Fch_Emision_UB') {
                            $valid_dates = \Util::setDate($value);
                        }

                        if ($valid_dates and ($value or $value == '0') and $field <> 'BMov_Orden') {
                            if (isset($fields)) {
                                $fields .= ' AND ';
                            } else {
                                $fields = '';
                                $values = array();
                            }

                            $bancosMovimiento_filter[$field] = $value;
                            switch ($field) {
                                case 'BMov_Fch_Emision_LB':
                                    $fields .= 'BMov_Fch_Emision >= ?';
                                    $value = \Util::setDate($value);
                                    break;
                                case 'BMov_Fch_Emision_UB':
                                    $fields .= 'BMov_Fch_Emision <= ?';
                                    $value = \Util::setDate($value);
                                    break;
                                default:
                                    $fields .= $field . ' = ?';
                            }

                            $values[] = $value;
                        }
                    }
                }

                if (isset($fields)) {
                    $bancoMovimiento->fill($filter);
                    $bancosMovimiento_list = $this->bancoMovimientoRepo->getDataFiltered($fields, $values, $bancosMovimiento_orden);
                    $bancosMovimiento_total = $this->bancoMovimientoRepo->getTotalFiltered($fields, $values);
                } else {
                    $bancosMovimiento_list = $this->bancoMovimientoRepo->geData($bancosMovimiento_orden);
                    $bancosMovimiento_total = $this->bancoMovimientoRepo->getTotalAll();
                }

		        $sheet->loadView('bancosMovimiento/export', ['bancosMovimiento_list' => $bancosMovimiento_list, 'bancosMovimiento_total' => $bancosMovimiento_total]);
		
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
        if ( ! \Util::getAccess('Bancos_Movimiento')) return $this->accessFail();

        $bancoMovimiento = $this->bancoMovimientoRepo->find($id, 'view');

        $this->notFoundUnless($bancoMovimiento);

        $bancosMovimientoCheques_list = $this->bancoMovimientoChequeRepo->getAll($id);
        $bancosMovimiento_filter = array('BMov_Id' => $id);

        $obj_id = \Util::getObjId('Bancos_Movimiento');
        $title = $this->title;
        $form_data = array('route' => array('bancosMovimiento.index'), 'method' => 'GET');
        $action    = '';
        $action_label = \Lang::get('utils.Actualizar');

        return View::make('bancosMovimiento/form', compact('bancoMovimiento', 'bancosMovimientoCheques_list', 'bancosMovimiento_filter', 'obj_id', 'title', 'form_data', 'action', 'action_label'));
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        if ( ! \Util::getAccess('Bancos_Movimiento')) return $this->accessFail();

        $bancoMovimiento = $this->bancoMovimientoRepo->find($id, 'view');

        $this->notFoundUnless($bancoMovimiento);

        $bancosMovimientoCheques_list = $this->bancoMovimientoChequeRepo->getAll($id);
        $bancosMovimiento_filter = array('BMov_Id' => $id);

        $obj_id = \Util::getObjId('Bancos_Movimiento');
        $title = $this->title;
        $form_data = array('route' => array('bancosMovimiento.update', $bancoMovimiento->BMov_Id), 'method' => 'PATCH');
        $action    = '';
        if ( \Util::getAccess('Bancos_Movimiento', 'Update')) $action = 'Actualizar';
        $action_label = \Lang::get('utils.Actualizar');

        return View::make('bancosMovimiento/form', compact('bancoMovimiento', 'bancosMovimientoCheques_list', 'bancosMovimiento_filter', 'obj_id', 'title', 'form_data', 'action', 'action_label'));
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id)
    {
        $bancoMovimiento = $this->bancoMovimientoRepo->find($id);

        $this->notFoundUnless($bancoMovimiento);

        $manager = new BancoMovimientoManager($bancoMovimiento, Input::all());

        $manager->save();

        return Redirect::route('bancosMovimiento.index');
    }

}
