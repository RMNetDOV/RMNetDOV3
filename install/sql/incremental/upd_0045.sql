UPDATE `web_domain` SET `redirect_path` = CONCAT(`redirect_path`, '/') WHERE `redirect_path` != '' AND RIGHT(`redirect_path`, 1) != '/' AND (LEFT(`redirect_path`, 7) = 'http://' OR  LEFT(`redirect_path`, 8) = 'https://' OR LEFT(`redirect_path`, 11) = '[scheme]://'); 
