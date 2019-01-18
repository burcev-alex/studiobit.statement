<?
namespace Studiobit\Statement\Form;

use Bitrix\Main\Localization\Loc;
use Studiobit\Matrix\Entity as Entity;

Loc::loadMessages(__FILE__);

class Statement extends Prototype
{
    protected $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function validate($post)
    {
        if(isset($post['UF_VIEW']) && empty($post['UF_VIEW'])){
            $this->message[] = 'Не заполнено поле "Вид"';
            $this->errorFields[] = 'UF_VIEW';
        }

        if(isset($post['UF_REQUIRED_DC']) && empty($post['UF_REQUIRED_DC'])){
            $this->message[] = 'Не заполнено поле "Требует ДС"';
            $this->errorFields[] = 'UF_REQUIRED_DC';
        }

	    if(isset($post['UF_DATE_CREATE']) && empty($post['UF_DATE_CREATE'])){
		    $this->message[] = 'Не заполнено поле "Дата создания"';
		    $this->errorFields[] = 'UF_DATE_CREATE';
	    }

	    if(isset($post['UF_CLIENT']) && empty($post['UF_CLIENT'])){
		    $this->message[] = 'Не заполнено поле "Клиент"';
		    $this->errorFields[] = 'UF_CLIENT';
	    }

        if(isset($post['UF_OBJECT_ID']) && empty($post['UF_OBJECT_ID'])){
            $this->message[] = 'Не заполнено поле "Помещение"';
            $this->errorFields[] = 'UF_OBJECT_ID';
        }

	    if (IntVal($post['UF_REQUIRED_DC']) == 248 && (strlen($post['UF_DATE_DEADLINE_DC']) == 0)) {
		    $this->message[] = 'Не заполнено поле "Срок подготовки ДС"';
		    $this->errorFields[] = 'UF_DATE_DEADLINE_DC';
	    }

        return count($this->errorFields) == 0;
    }

    public function getSettings()
    {
        return [
            'general' => [
                'NAME' => 'Документ',
                'HELP' => '',
                'TYPE' => 'GROUP',
	            'SHOW_TITLE' => 'N',
                'FIELDS' => [
                    [
	                    'TYPE' => 'GROUP',
	                    'FIELDS' => [
	                    	[
			                    'NAME' => 'Документ',
			                    'TYPE' => 'GROUP',
			                    'ALIGMENT' => 'HORIZONTAL',
			                    'FIELDS' => [
				                    [
					                    'TYPE' => 'GROUP',
					                    'FIELDS' => [
						                    'UF_DATE_CREATE',
						                    'UF_CLIENT',
                                            'UF_OBJECT_ID',
						                    'UF_RESPONSIBLE',
						                    'UF_STATUS'
					                    ]
				                    ],
				                    [
					                    'TYPE' => 'GROUP',
					                    'FIELDS' => [
						                    'UF_STAGE_ID',
						                    'UF_NUMBER',
						                    'UF_VIEW',
						                    'UF_FILES',
						                    'UF_REQUIRED_DC',
						                    'UF_DATE_DEADLINE_DC'
					                    ]
				                    ]
			                    ]
		                    ],
		                    [
			                    'NAME' => 'Оплата',
			                    'TYPE' => 'GROUP',
			                    'ID' => 'property_payment',
			                    'ALIGMENT' => 'HORIZONTAL',
			                    'FIELDS' => [
				                    [
					                    'TYPE' => 'GROUP',
					                    'ID' => 'property_payment_group_left',
					                    'FIELDS' => [
						                    'UF_IS_PAY'
					                    ]
				                    ],
				                    [
					                    'TYPE' => 'GROUP',
					                    'ID' => 'property_payment_group_right',
					                    'FIELDS' => [
						                    [
							                    'TYPE' => 'GROUP',
							                    'ALIGMENT' => 'HORIZONTAL',
							                    'ID' => 'property_payment_group_right_p1',
							                    'FIELDS' => [
								                    'UF_PAY_100PERCENT',
								                    'UF_PAY_100PERCENT_D',
								                    'UF_PAY_100PERCENT_S',
							                    ]
						                    ],
						                    [
							                    'TYPE' => 'GROUP',
							                    'ALIGMENT' => 'HORIZONTAL',
							                    'ID' => 'property_payment_group_right_p2',
							                    'FIELDS' => [
								                    [
									                    'TYPE' => 'GROUP',
									                    'ID' => 'property_payment_group_right_p2_cols1',
									                    'FIELDS' => [
										                    'UF_PAY_INSTALLMENTS'
									                    ]
								                    ],
								                    [
									                    'TYPE' => 'GROUP',
									                    'ID' => 'property_payment_group_right_p2_cols2',
									                    'FIELDS' => [
										                    'UF_PAY_INST_P1',
									                    ]
								                    ],
								                    [
									                    'TYPE' => 'GROUP',
									                    'ID' => 'property_payment_group_right_p2_cols3',
									                    'FIELDS' => [
										                    'UF_PAY_INST_P2',
										                    'UF_PAY_INST_P4',
										                    '',
									                    ]
								                    ],
								                    [
									                    'TYPE' => 'GROUP',
									                    'ID' => 'property_payment_group_right_p2_cols4',
									                    'FIELDS' => [
										                    'UF_PAY_INST_P3',
										                    '',
										                    '',
										                    'UF_PAY_INST_P5',
										                    'UF_PAY_INST_P6'
									                    ]
								                    ],
							                    ]
						                    ],
						                    [
							                    'TYPE' => 'GROUP',
							                    'ALIGMENT' => 'HORIZONTAL',
							                    'ID' => 'property_payment_group_right_p3',
							                    'FIELDS' => [
								                    'UF_PAY_D_P1',
								                    'UF_PAY_D_P2',
								                    'UF_PAY_D_P3',
							                    ]
						                    ],
						                    [
							                    'TYPE' => 'GROUP',
							                    'ALIGMENT' => 'HORIZONTAL',
							                    'ID' => 'property_payment_group_right_p4',
							                    'FIELDS' => [
								                    'UF_PAY_P_P1',
								                    'UF_PAY_P_P2'
							                    ]
						                    ],
						                    [
							                    'TYPE' => 'GROUP',
							                    'ALIGMENT' => 'HORIZONTAL',
							                    'ID' => 'property_payment_group_right_p5',
							                    'FIELDS' => [
								                    [
									                    'TYPE' => 'GROUP',
									                    'ID' => 'property_payment_group_right_p5_cols1',
									                    'FIELDS' => [
										                    'UF_PAY_G_P1'
									                    ]
								                    ],
								                    [
									                    'TYPE' => 'GROUP',
									                    'ID' => 'property_payment_group_right_p5_cols2',
									                    'FIELDS' => [
										                    'UF_PAY_G_P2',
										                    'UF_PAY_G_P3'
									                    ]
								                    ]
							                    ]
						                    ],
						                    [
							                    'TYPE' => 'GROUP',
							                    'ALIGMENT' => 'HORIZONTAL',
							                    'ID' => 'property_payment_group_right_p6',
							                    'FIELDS' => [
								                    'UF_PAY_R_P1',
								                    'UF_PAY_R_P2',
								                    'UF_PAY_R_P3',
								                    'UF_PAY_R_P5',
								                    'UF_PAY_G_P4',
							                    ]
						                    ],
					                    ]
				                    ],
			                    ]
		                    ],
		                    [
			                    'NAME' => 'Расторжение договора',
			                    'TYPE' => 'GROUP',
			                    'ID' => 'property_termination',
			                    'ALIGMENT' => 'HORIZONTAL',
			                    'FIELDS' => [
				                    [
					                    'TYPE' => 'GROUP',
					                    'ID' => 'property_termination_group_left',
					                    'FIELDS' => [
						                    'UF_IS_TERMINATION'
					                    ]
				                    ],
				                    [
					                    'TYPE' => 'GROUP',
					                    'ID' => 'property_termination_group_right',
					                    'FIELDS' => [
						                    [
							                    'TYPE' => 'GROUP',
							                    'ALIGMENT' => 'HORIZONTAL',
							                    'ID' => 'property_termination_group_right_p1',
							                    'FIELDS' => [
								                    'UF_TERM_A_P1',
								                    'UF_TERM_A_P3',
								                    'UF_TERM_A_P4',
							                    ]
						                    ],
						                    [
							                    'TYPE' => 'GROUP',
							                    'ALIGMENT' => 'HORIZONTAL',
							                    'ID' => 'property_termination_group_right_p2',
							                    'FIELDS' => [
								                    'UF_TERM_B_P1',
								                    'UF_TERM_B_P2',
								                    'UF_TERM_B_P3',
							                    ]
						                    ],
						                    [
							                    'TYPE' => 'GROUP',
							                    'ALIGMENT' => 'HORIZONTAL',
							                    'ID' => 'property_termination_group_right_p3',
							                    'FIELDS' => [
								                    [
									                    'TYPE' => 'GROUP',
									                    'ID' => 'property_termination_group_right_p3_cols1',
									                    'FIELDS' => [
										                    'UF_TERM_C_P1',
										                    'UF_TERM_C_P2'
									                    ]
								                    ],
								                    [
									                    'TYPE' => 'GROUP',
									                    'ID' => 'property_termination_group_right_p3_cols2',
									                    'FIELDS' => [
										                    [
											                    'ALIGMENT' => 'HORIZONTAL',
											                    'TYPE' => 'GROUP',
											                    'ID' => 'property_termination_group_right_p3_cols3',
											                    'FIELDS' => [
												                    'UF_TERM_C_P3',
												                    'UF_TERM_C_P4',
												                    'UF_TERM_C_P5',
												                    ''
											                    ]
										                    ],
										                    [
											                    'ALIGMENT' => 'HORIZONTAL',
											                    'TYPE' => 'GROUP',
											                    'ID' => 'property_termination_group_right_p3_cols4',
											                    'FIELDS' => [
												                    'UF_TERM_C_P9',
												                    'UF_TERM_C_P10'
											                    ]
										                    ],
										                    [
											                    'ALIGMENT' => 'HORIZONTAL',
											                    'TYPE' => 'GROUP',
											                    'ID' => 'property_termination_group_right_p3_cols5',
											                    'FIELDS' => [
												                    'UF_TERM_C_P11',
												                    'UF_TERM_C_P12',
												                    'UF_TERM_C_P13'
											                    ]
										                    ],
										                    [
											                    'ALIGMENT' => 'HORIZONTAL',
											                    'TYPE' => 'GROUP',
											                    'ID' => 'property_termination_group_right_p3_cols6',
											                    'FIELDS' => [
												                    'UF_TERM_C_P14',
												                    'UF_TERM_C_P15'
											                    ]
										                    ],
									                    ]
								                    ]
							                    ]
						                    ]
					                    ]
				                    ],
			                    ]
		                    ],
		                    [
			                    'NAME' => 'Смена реквизитов клиента',
			                    'TYPE' => 'GROUP',
			                    'ID' => 'property_client_detail',
			                    'ALIGMENT' => 'HORIZONTAL',
			                    'FIELDS' => [
				                    [
					                    'TYPE' => 'GROUP',
					                    'ID' => 'property_client_detail_group_left',
					                    'FIELDS' => [
						                    'UF_IS_CLIENT_DETAIL'
					                    ]
				                    ],
				                    [
					                    'TYPE' => 'GROUP',
					                    'ID' => 'property_client_detail_group_right',
					                    'FIELDS' => [
						                    ''
					                    ]
				                    ],
			                    ]
		                    ],
		                    [
			                    'NAME' => 'Выдача дубликата договора',
			                    'TYPE' => 'GROUP',
			                    'ID' => 'property_duplicate',
			                    'ALIGMENT' => 'HORIZONTAL',
			                    'FIELDS' => [
				                    [
					                    'TYPE' => 'GROUP',
					                    'ID' => 'property_duplicate_group_left',
					                    'FIELDS' => [
						                    'UF_IS_DUPLICATE'
					                    ]
				                    ],
				                    [
					                    'TYPE' => 'GROUP',
					                    'ID' => 'property_duplicate_group_right',
					                    'FIELDS' => [
						                    ''
					                    ]
				                    ],
			                    ]
		                    ],
		                    [
			                    'NAME' => 'Возврат уплаченного взноса',
			                    'TYPE' => 'GROUP',
			                    'ID' => 'property_return_paid',
			                    'ALIGMENT' => 'HORIZONTAL',
			                    'FIELDS' => [
				                    [
					                    'TYPE' => 'GROUP',
					                    'ID' => 'property_return_paid_group_left',
					                    'FIELDS' => [
						                    'UF_IS_RETURN_PAID'
					                    ]
				                    ],
				                    [
					                    'TYPE' => 'GROUP',
					                    'ID' => 'property_return_paid_group_right',
					                    'FIELDS' => [
						                    'UF_RETURN_A_P1'
					                    ]
				                    ],
			                    ]
		                    ],
		                    [
			                    'NAME' => 'Перевод суммы',
			                    'TYPE' => 'GROUP',
			                    'ID' => 'property_overpayment',
			                    'ALIGMENT' => 'HORIZONTAL',
			                    'FIELDS' => [
				                    [
					                    'TYPE' => 'GROUP',
					                    'ID' => 'property_overpayment_group_left',
					                    'FIELDS' => [
						                    'UF_IS_OVERPAYMENT'
					                    ]
				                    ],
				                    [
					                    'TYPE' => 'GROUP',
					                    'ID' => 'property_overpayment_group_right',
					                    'FIELDS' => [
						                    [
							                    'TYPE' => 'GROUP',
							                    'ALIGMENT' => 'HORIZONTAL',
							                    'ID' => 'property_overpayment_group_right_cols1',
							                    'FIELDS' => [
								                    'UF_OVER_A_P1',
								                    'UF_OVER_A_P2'
							                    ]
						                    ],
						                    [
							                    'TYPE' => 'GROUP',
							                    'ALIGMENT' => 'HORIZONTAL',
							                    'ID' => 'property_overpayment_group_right_cols2',
							                    'FIELDS' => [
								                    '',
								                    'UF_OVER_A_P3',
								                    'UF_OVER_A_P4'
							                    ]
						                    ]
					                    ]
				                    ]
			                    ]
		                    ],
		                    [
			                    'NAME' => 'Досрочное погашение',
			                    'TYPE' => 'GROUP',
			                    'ID' => 'property_repayment',
			                    'ALIGMENT' => 'HORIZONTAL',
			                    'FIELDS' => [
				                    [
					                    'TYPE' => 'GROUP',
					                    'ID' => 'property_repayment_group_left',
					                    'FIELDS' => [
						                    'UF_IS_REPAYMENT'
					                    ]
				                    ],
				                    [
					                    'TYPE' => 'GROUP',
					                    'ALIGMENT' => 'HORIZONTAL',
					                    'ID' => 'property_repayment_group_right',
					                    'FIELDS' => [
						                    'UF_REPAYMENT_C_P1',
						                    'UF_REPAYMENT_C_P2'
					                    ]
				                    ],
			                    ]
		                    ],
		                    [
			                    'NAME' => 'Перепланировка',
			                    'TYPE' => 'GROUP',
			                    'ID' => 'property_remodeling',
			                    'ALIGMENT' => 'HORIZONTAL',
			                    'FIELDS' => [
				                    [
					                    'TYPE' => 'GROUP',
					                    'ID' => 'property_remodeling_group_left',
					                    'FIELDS' => [
						                    'UF_IS_REMODELING'
					                    ]
				                    ],
				                    [
					                    'TYPE' => 'GROUP',
					                    'ID' => 'property_remodeling_group_right',
					                    'FIELDS' => [
						                    ''
					                    ]
				                    ],
			                    ]
		                    ],
		                    [
			                    'NAME' => 'Переход на другой паркинг',
			                    'TYPE' => 'GROUP',
			                    'ID' => 'property_parking',
			                    'ALIGMENT' => 'HORIZONTAL',
			                    'FIELDS' => [
				                    [
					                    'TYPE' => 'GROUP',
					                    'ID' => 'property_parking_group_left',
					                    'FIELDS' => [
						                    'UF_IS_PARKING'
					                    ]
				                    ],
				                    [
					                    'TYPE' => 'GROUP',
					                    'ALIGMENT' => 'HORIZONTAL',
					                    'ID' => 'property_parking_group_right',
					                    'FIELDS' => [
						                    'UF_PARKING_A_P1',
						                    'UF_PARKING_A_P2'
					                    ]
				                    ],
			                    ]
		                    ],
		                    [
			                    'NAME' => 'Дополнительно',
			                    'TYPE' => 'GROUP',
			                    'ID' => 'property_other',
			                    'ALIGMENT' => 'HORIZONTAL',
			                    'FIELDS' => [
				                    [
					                    'TYPE' => 'GROUP',
					                    'ID' => 'property_other_group',
					                    'FIELDS' => [
						                    'UF_COMMENTS'
					                    ]
				                    ]
			                    ]
		                    ]
	                    ]
                    ],
                ]
            ],
        ];
    }

	public function bindFields(){
		return array(
			'UF_REQUIRED_DC' => array(
				'JS' => 'BX.onCustomEvent(window, "Helper.Statement.RequiredDC", ["edit"]);',
			),
			'UF_IS_PAY' => array(
				'JS' => 'BX.onCustomEvent(window, "Helper.Statement.ToggleFields", ["edit", "UF_IS_PAY", "property_payment"]);',
			),
			'UF_IS_TERMINATION' => array(
				'JS' => 'BX.onCustomEvent(window, "Helper.Statement.ToggleFields", ["edit", "UF_IS_TERMINATION", "property_termination"]);',
			),
			'UF_IS_CLIENT_DETAIL' => array(
				'JS' => 'BX.onCustomEvent(window, "Helper.Statement.ToggleFields", ["edit", "UF_IS_CLIENT_DETAIL", "property_client_detail"]);',
			),
			'UF_IS_DUPLICATE' => array(
				'JS' => 'BX.onCustomEvent(window, "Helper.Statement.ToggleFields", ["edit", "UF_IS_DUPLICATE", "property_duplicate"]);',
			),
			'UF_IS_RETURN_PAID' => array(
				'JS' => 'BX.onCustomEvent(window, "Helper.Statement.ToggleFields", ["edit", "UF_IS_RETURN_PAID", "property_return_paid"]);',
			),
			'UF_IS_OVERPAYMENT' => array(
				'JS' => 'BX.onCustomEvent(window, "Helper.Statement.ToggleFields", ["edit", "UF_IS_OVERPAYMENT", "property_overpayment"]);',
			),
			'UF_IS_REPAYMENT' => array(
				'JS' => 'BX.onCustomEvent(window, "Helper.Statement.ToggleFields", ["edit", "UF_IS_REPAYMENT", "property_repayment"]);',
			),
			'UF_IS_REMODELING' => array(
				'JS' => 'BX.onCustomEvent(window, "Helper.Statement.ToggleFields", ["edit", "UF_IS_REMODELING", "property_remodeling"]);',
			),
			'UF_IS_PARKING' => array(
				'JS' => 'BX.onCustomEvent(window, "Helper.Statement.ToggleFields", ["edit", "UF_IS_PARKING", "property_parking"]);',
			),
			'UF_PAY_100PERCENT' => array(
				'JS' => 'BX.onCustomEvent(window, "Helper.Statement.ToggleRowsMulti", ["edit", "UF_PAY_100PERCENT", "property_payment_group_right_p1"]);',
			),
			'UF_PAY_INSTALLMENTS' => array(
				'JS' => 'BX.onCustomEvent(window, "Helper.Statement.ToggleRowsMulti", ["edit", "UF_PAY_INSTALLMENTS", "property_payment_group_right_p2"]);',
			),
			'UF_PAY_D_P1' => array(
				'JS' => 'BX.onCustomEvent(window, "Helper.Statement.ToggleRow", ["edit", "UF_PAY_D_P1", "property_payment_group_right_p3"]);',
			),
			'UF_PAY_P_P1' => array(
				'JS' => 'BX.onCustomEvent(window, "Helper.Statement.ToggleRow", ["edit", "UF_PAY_P_P1", "property_payment_group_right_p4"]);',
			),
			'UF_PAY_G_P1' => array(
				'JS' => 'BX.onCustomEvent(window, "Helper.Statement.ToggleRowsMulti", ["edit", "UF_PAY_G_P1", "property_payment_group_right_p5"]);',
			),
			'UF_PAY_R_P1' => array(
				'JS' => 'BX.onCustomEvent(window, "Helper.Statement.ToggleRow", ["edit", "UF_PAY_R_P1", "property_payment_group_right_p6"]);',
			),
			'UF_TERM_A_P1' => array(
				'JS' => 'BX.onCustomEvent(window, "Helper.Statement.ToggleRow", ["edit", "UF_TERM_A_P1", "property_termination_group_right_p1"]);',
			),
			'UF_TERM_B_P1' => array(
				'JS' => 'BX.onCustomEvent(window, "Helper.Statement.ToggleRow", ["edit", "UF_TERM_B_P1", "property_termination_group_right_p2"]);',
			),
			'UF_TERM_C_P1' => array(
				'JS' => 'BX.onCustomEvent(window, "Helper.Statement.ToggleRowsMulti", ["edit", "UF_TERM_C_P1", "property_termination_group_right_p3"]);',
			),
		);
	}

	/**
	 * События которые должны быть вызваты при генерации страницы
	 * @return array
	 */
	public function bindStartFields($type = "EDIT"){
		$result = [];

		$result[] = 'new $.Studiobit.StatementHelper({});';

		if($type == "EDIT") {
			$result[] = 'BX.onCustomEvent(window, "Helper.Statement.Start", ["edit"]);';
			$result[] = 'BX.onCustomEvent(window, "Helper.Statement.ToggleRowsMulti", ["edit", "UF_PAY_100PERCENT", "property_payment_group_right_p1"]);';
			$result[] = 'BX.onCustomEvent(window, "Helper.Statement.ToggleRowsMulti", ["edit", "UF_PAY_INSTALLMENTS", "property_payment_group_right_p2"]);';
			$result[] = 'BX.onCustomEvent(window, "Helper.Statement.ToggleRow", ["edit", "UF_PAY_D_P1", "property_payment_group_right_p3"]);';
			$result[] = 'BX.onCustomEvent(window, "Helper.Statement.ToggleRow", ["edit", "UF_PAY_P_P1", "property_payment_group_right_p4"]);';
			$result[] = 'BX.onCustomEvent(window, "Helper.Statement.ToggleRowsMulti", ["edit", "UF_PAY_G_P1", "property_payment_group_right_p5"]);';
			$result[] = 'BX.onCustomEvent(window, "Helper.Statement.ToggleRow", ["edit", "UF_PAY_R_P1", "property_payment_group_right_p6"]);';
			$result[] = 'BX.onCustomEvent(window, "Helper.Statement.ToggleRow", ["edit", "UF_TERM_A_P1", "property_termination_group_right_p1"]);';
			$result[] = 'BX.onCustomEvent(window, "Helper.Statement.ToggleRow", ["edit", "UF_TERM_B_P1", "property_termination_group_right_p2"]);';
			$result[] = 'BX.onCustomEvent(window, "Helper.Statement.ToggleRowsMulti", ["edit", "UF_TERM_C_P1", "property_termination_group_right_p3"]);';
		}
		else{
			$result[] = 'BX.onCustomEvent(window, "Helper.Statement.Start", ["show"]);';
			$result[] = 'BX.onCustomEvent(window, "Helper.Statement.ToggleFields", ["show", "UF_IS_PAY", "property_payment"]);';
			$result[] = 'BX.onCustomEvent(window, "Helper.Statement.ToggleFields", ["show", "UF_IS_TERMINATION", "property_termination"]);';
			$result[] = 'BX.onCustomEvent(window, "Helper.Statement.ToggleFields", ["show", "UF_IS_CLIENT_DETAIL", "property_client_detail"]);';
			$result[] = 'BX.onCustomEvent(window, "Helper.Statement.ToggleFields", ["show", "UF_IS_DUPLICATE", "property_duplicate"]);';
			$result[] = 'BX.onCustomEvent(window, "Helper.Statement.ToggleFields", ["show", "UF_IS_RETURN_PAID", "property_return_paid"]);';
			$result[] = 'BX.onCustomEvent(window, "Helper.Statement.ToggleFields", ["show", "UF_IS_OVERPAYMENT", "property_overpayment"]);';
			$result[] = 'BX.onCustomEvent(window, "Helper.Statement.ToggleFields", ["show", "UF_IS_REPAYMENT", "property_repayment"]);';
			$result[] = 'BX.onCustomEvent(window, "Helper.Statement.ToggleFields", ["show", "UF_IS_REMODELING", "property_remodeling"]);';
			$result[] = 'BX.onCustomEvent(window, "Helper.Statement.ToggleFields", ["show", "UF_IS_PARKING", "property_parking"]);';
			$result[] = 'BX.onCustomEvent(window, "Helper.Statement.ToggleRowsMulti", ["show", "UF_PAY_100PERCENT", "property_payment_group_right_p1"]);';
			$result[] = 'BX.onCustomEvent(window, "Helper.Statement.ToggleRowsMulti", ["show", "UF_PAY_INSTALLMENTS", "property_payment_group_right_p2"]);';
			$result[] = 'BX.onCustomEvent(window, "Helper.Statement.ToggleRow", ["show", "UF_PAY_D_P1", "property_payment_group_right_p3"]);';
			$result[] = 'BX.onCustomEvent(window, "Helper.Statement.ToggleRow", ["show", "UF_PAY_P_P1", "property_payment_group_right_p4"]);';
			$result[] = 'BX.onCustomEvent(window, "Helper.Statement.ToggleRowsMulti", ["show", "UF_PAY_G_P1", "property_payment_group_right_p5"]);';
			$result[] = 'BX.onCustomEvent(window, "Helper.Statement.ToggleRow", ["show", "UF_PAY_R_P1", "property_payment_group_right_p6"]);';
			$result[] = 'BX.onCustomEvent(window, "Helper.Statement.ToggleRow", ["show", "UF_TERM_A_P1", "property_termination_group_right_p1"]);';
			$result[] = 'BX.onCustomEvent(window, "Helper.Statement.ToggleRow", ["show", "UF_TERM_B_P1", "property_termination_group_right_p2"]);';
			$result[] = 'BX.onCustomEvent(window, "Helper.Statement.ToggleRowsMulti", ["show", "UF_TERM_C_P1", "property_termination_group_right_p3"]);';
		}

		return $result;
	}
}
