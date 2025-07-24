<?php
require_once(dirname(__FILE__) .'/../../api/_wrapper.php');
require_once(dirname(__FILE__).'/../../components/_global_variables.php');



return [
	"showProgress" => true, 
	"animate"=> true,
	"nextBtnText" => io::_("Next"), 
	"prevBtnText" => io::_("Back"), 
	"doneBtnText" => io::_("Done"), 
	"steps" => [
		[
			"element" => '#box-income', // ID or element selector
			"popover" => [
					"title" => io::_('Income'),
					"description" => io::_('Shows the total income for the current month.'),
					"side" => "bottom", 
					"align" => "center" 
				],
		],
		[
			"element" => '#box-expenses',
			"popover" => [
					"title" => io::_('Expenses'),
					"description" => io::_('Shows the total expenses for the current month.'),
					"side" => "bottom", 
					"align" => "center" 
			],
		],
		[
			"element" => '#box-balance',
			"popover" => [
					"title" => io::_('Balance'),
					"description" => io::_('Displays the available balance.'),
					"side" => "bottom", 
					"align" => "center" 
			],
		],
		[
			"element" => '#container-recent-activities',
			"popover" => [
					"title" => io::_('Recent Activities'),
					"description" => io::_('Here, you can view the most recently recorded transactions.'),
					"side" => "bottom", 
					"align" => "center" 
			],
		],
		[
			"element" => '.btn-new-transaction',
			"popover" => [
					"title" => io::_('New Transaction'),
					"description" => io::_('From here, you can quickly register both a new payment and a new income. The process will be guided.'),
					"side" => "bottom", 
					"align" => "center",
					"onPrevClick"=>" $('.modal').modal('hide'); driver_page.movePrevious(); ",
					"onNextClick"=>"$('.btn-new-transaction').trigger('click'); "
			],
		],
		[
			"element" => '#transaction-actions-modal .modal-body',
			"popover" => [
					"title" => io::_('Choose Transaction Type'),
					"description" => io::_('You can choose either "Income" or "Expense" by clicking on the respective button.'),
					"side" => "bottom", 
					"align" => "center",
					"onPrevClick"=>" $('.modal').modal('hide'); driver_page.movePrevious(); ",
					"onNextClick"=>"$('.btn-action-transaction[data-income=\"0\"]').trigger('click'); "
			],
		],
		[
			"element" => '#list-wallets-modal .modal-body',
			"popover" => [
					"title" => io::_('Select Wallet'),
					"description" => io::_('Choose the wallet from which this transaction will be made.'),
					"side" => "bottom", 
					"align" => "center",
					"onPrevClick"=>" $('.modal').modal('hide'); driver_page.movePrevious(); ",
					"onNextClick"=>"$('.wallet-card:first-child').trigger('click'); "
			],
		],
		[
			"element" => '#form-new-transaction',
			"popover" => [
					"title" => io::_('Add a Payment'),
					"description" => io::_('Fill in the required fields to register your payment. Provide a description, amount, tag, date, and choose if it is a transfer. Click "Save" to complete the process.'),
					"side" => "bottom", 
					"align" => "center",
			],
		],
	],
		
];

?>
<?php io::w(); ?>