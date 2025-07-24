<?php
	require_once(dirname(__FILE__).'/../../../components/auth.php');
	require_once(dirname(__FILE__).'/../../../components/_global_variables.php');

	return [
		[
			"field"=> "id",
			"title"=> "#",
			"align"=> "center"
		],
		[
			"field"=> "date",
			"title"=> io::_('Date'),
			"align"=> "center"
		],
		[
			"field"=> "description",
			"title"=> io::_('Description'),
			"align"=> "center"
		],
		[
			"field"=> "type",
			"title"=> io::_('Tag'),
			"align"=> "center"
		],
		[
			"field"=> "wallet",
			"title"=> io::_('Wallet'),
			"align"=> "center"
		],
		[
			"field"=> "amount",
			"title"=> io::_('Amount'),
			"align"=> "center"
		]
	];
?>
<?php io::w(); ?>