<?php

$app->uses('simplepie');
$app->uses('auth');

$app->tpl->newTemplate('dashboard/templates/custom_menu.htm');

$app->uses('getconf');
$misc_config = $app->getconf->get_global_config('misc');


switch($_SESSION['s']['user']['typ']) {
case 'admin':
	$atom_url = $misc_config['dashboard_atom_url_admin'];
	break;
case 'user':
	if ($app->auth->has_clients($_SESSION['s']['user']['userid']) === true)
		$atom_url = $misc_config['dashboard_atom_url_reseller'];
	else
		$atom_url = $misc_config['dashboard_atom_url_client'];
	break;
default:
	$atom_url = "";
}

$rows = array();

if( $atom_url != '' ) {
	if(!isset($_SESSION['s']['rss_news'])) {

		$app->simplepie->set_feed_url($atom_url);
		$app->simplepie->enable_cache(false);
		$app->simplepie->init();
		$items = $app->simplepie->get_items();

		$rows = array();
		$n = 1;

		foreach ($items as $item)
		{
			//* We want to show only the first 10 news records
			if($n <= 10) {
				$rows[] = array('title' => $item->get_title(),
					'link' => $item->get_link(),
					'content' => $item->get_content(),
					'date' => $item->get_date($app->lng('conf_format_dateshort'))
				);
			}
			$n++;
		}

		$_SESSION['s']['rss_news'] = $rows;

	} else {
		$rows = $_SESSION['s']['rss_news'];
	}

	$app->tpl->setVar('latest_news_txt', $app->lng('latest_news_txt'));

}

$app->tpl->setLoop('news', $rows);

?>
