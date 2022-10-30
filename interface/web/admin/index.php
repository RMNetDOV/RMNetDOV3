<?php

class admin_index {

	var $status = 'OK';
	var $target = '';

	function render() {

		global $app;

		$app->uses('tpl');
		$app->tpl->newTemplate("form.tpl.htm");

		$app->tpl->setVar('error', $error);
		$app->tpl->setInclude('content_tpl', 'admin/templates/index.htm');
		return $app->tpl->grab();
		/*
		$filename = 'test.txt';
$somecontent = $app->tpl->grab();

// Sichergehen, dass die Datei existiert und beschreibbar ist


    // Wir öffnen $filename im "Anhänge" - Modus.
    // Der Dateizeiger befindet sich am Ende der Datei, und
    // dort wird $somecontent später mit fwrite() geschrieben.
    if (!$handle = fopen($filename, "w")) {
         print "Kann die Datei $filename nicht öffnen";
         exit;
    }

    // Schreibe $somecontent in die geöffnete Datei.
    if (!fwrite($handle, $somecontent)) {
        print "Kann in die Datei $filename nicht schreiben";
        exit;
    }


    fclose($handle);




		return 'dd';
		*/
	}

}

?>
